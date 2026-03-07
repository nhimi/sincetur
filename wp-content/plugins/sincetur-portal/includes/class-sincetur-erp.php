<?php
/**
 * ERP Module – Invoicing, Suppliers & PCGA-Angolano Accounting.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_ERP {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_post_sinc_save_invoice',  [ $this, 'handle_save_invoice' ] );
        add_action( 'admin_post_sinc_save_supplier', [ $this, 'handle_save_supplier' ] );
        add_action( 'admin_post_sinc_save_journal',  [ $this, 'handle_save_journal' ] );
    }

    public function register_menu(): void {
        // Sub-menus under the main SINCETUR menu (registered in Admin class).
        add_submenu_page(
            'sincetur-portal',
            __( 'Facturas', 'sincetur-portal' ),
            __( 'Facturas', 'sincetur-portal' ),
            'manage_options',
            'sinc-invoices',
            [ $this, 'page_invoices' ]
        );
        add_submenu_page(
            'sincetur-portal',
            __( 'Fornecedores', 'sincetur-portal' ),
            __( 'Fornecedores', 'sincetur-portal' ),
            'manage_options',
            'sinc-suppliers',
            [ $this, 'page_suppliers' ]
        );
        add_submenu_page(
            'sincetur-portal',
            __( 'Plano de Contas (PCGA)', 'sincetur-portal' ),
            __( 'Plano de Contas', 'sincetur-portal' ),
            'manage_options',
            'sinc-chart-accounts',
            [ $this, 'page_chart_accounts' ]
        );
        add_submenu_page(
            'sincetur-portal',
            __( 'Diário Contabilístico', 'sincetur-portal' ),
            __( 'Diário Contab.', 'sincetur-portal' ),
            'manage_options',
            'sinc-journal',
            [ $this, 'page_journal' ]
        );
        add_submenu_page(
            'sincetur-portal',
            __( 'Relatórios ERP', 'sincetur-portal' ),
            __( 'Relatórios', 'sincetur-portal' ),
            'manage_options',
            'sinc-reports',
            [ $this, 'page_reports' ]
        );
    }

    // ─── Page Callbacks ──────────────────────────────────────────────────────

    public function page_invoices(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . "admin/views/erp-invoices.php";
    }

    public function page_suppliers(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . "admin/views/erp-suppliers.php";
    }

    public function page_chart_accounts(): void {
        include SINCETUR_PLUGIN_DIR . "admin/views/erp-chart-accounts.php";
    }

    public function page_journal(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . "admin/views/erp-journal.php";
    }

    public function page_reports(): void {
        include SINCETUR_PLUGIN_DIR . "admin/views/erp-reports.php";
    }

    // ─── Form Handlers ───────────────────────────────────────────────────────

    /**
     * Save / update an invoice.
     */
    public function handle_save_invoice(): void {
        check_admin_referer( 'sinc_save_invoice' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $table      = Sincetur_Installer::get_tables()['invoices'];
        $items_tbl  = Sincetur_Installer::get_tables()['invoice_items'];
        $id         = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

        $data = [
            'client_id'       => (int)   ( $_POST['client_id']       ?? 0 ),
            'tipo'            => sanitize_text_field( wp_unslash( $_POST['tipo']            ?? 'factura' ) ),
            'data_emissao'    => sanitize_text_field( wp_unslash( $_POST['data_emissao']    ?? current_time( 'Y-m-d' ) ) ),
            'data_vencimento' => sanitize_text_field( wp_unslash( $_POST['data_vencimento'] ?? '' ) ) ?: null,
            'iva_percentagem' => (float) ( $_POST['iva_percentagem'] ?? 14 ),
            'moeda'           => sanitize_text_field( wp_unslash( $_POST['moeda']           ?? 'AOA' ) ),
            'estado'          => sanitize_text_field( wp_unslash( $_POST['estado']          ?? 'rascunho' ) ),
            'observacoes'     => sanitize_textarea_field( wp_unslash( $_POST['observacoes'] ?? '' ) ),
        ];

        // Calculate totals from line items.
        $subtotal = 0.0;
        $items    = [];
        if ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) {
            foreach ( $_POST['items'] as $item ) {
                $qty       = (float) ( $item['quantidade']  ?? 1 );
                $unit      = (float) ( $item['preco_unit']  ?? 0 );
                $line_total = round( $qty * $unit, 2 );
                $subtotal  += $line_total;
                $items[]    = [
                    'descricao'  => sanitize_text_field( wp_unslash( $item['descricao']  ?? '' ) ),
                    'quantidade' => $qty,
                    'preco_unit' => $unit,
                    'total'      => $line_total,
                    'conta_pcga' => sanitize_text_field( wp_unslash( $item['conta_pcga'] ?? '' ) ),
                ];
            }
        }

        $iva_valor = round( $subtotal * $data['iva_percentagem'] / 100, 2 );
        $total     = round( $subtotal + $iva_valor, 2 );

        $data['subtotal']  = $subtotal;
        $data['iva_valor'] = $iva_valor;
        $data['total']     = $total;

        if ( $id > 0 ) {
            $wpdb->update( $table, $data, [ 'id' => $id ], null, [ '%d' ] );
            $wpdb->delete( $items_tbl, [ 'invoice_id' => $id ], [ '%d' ] );
        } else {
            $data['numero'] = $this->generate_invoice_number();
            $wpdb->insert( $table, $data );
            $id = (int) $wpdb->insert_id;
        }

        foreach ( $items as $item ) {
            $item['invoice_id'] = $id;
            $wpdb->insert( $items_tbl, $item );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=sinc-invoices&saved=1' ) );
        exit;
    }

    /**
     * Save / update a supplier.
     */
    public function handle_save_supplier(): void {
        check_admin_referer( 'sinc_save_supplier' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $table = Sincetur_Installer::get_tables()['erp_suppliers'];
        $id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

        $data = [
            'nome'      => sanitize_text_field( wp_unslash( $_POST['nome']      ?? '' ) ),
            'nif'       => sanitize_text_field( wp_unslash( $_POST['nif']       ?? '' ) ) ?: null,
            'email'     => sanitize_email( wp_unslash( $_POST['email']          ?? '' ) ),
            'telefone'  => sanitize_text_field( wp_unslash( $_POST['telefone']  ?? '' ) ),
            'morada'    => sanitize_textarea_field( wp_unslash( $_POST['morada']?? '' ) ),
            'pais'      => sanitize_text_field( wp_unslash( $_POST['pais']      ?? 'Angola' ) ),
            'categoria' => sanitize_text_field( wp_unslash( $_POST['categoria'] ?? '' ) ),
        ];

        if ( $id > 0 ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=sinc-suppliers&saved=1' ) );
        exit;
    }

    /**
     * Save a journal entry (lancamento contabilístico).
     */
    public function handle_save_journal(): void {
        check_admin_referer( 'sinc_save_journal' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $entries_tbl = Sincetur_Installer::get_tables()['journal_entries'];
        $lines_tbl   = Sincetur_Installer::get_tables()['journal_lines'];

        // Build entry.
        $entry = [
            'numero'     => 'LANC-' . current_time( 'Ymd' ) . '-' . wp_rand( 1000, 9999 ),
            'data'       => sanitize_text_field( wp_unslash( $_POST['data']       ?? current_time( 'Y-m-d' ) ) ),
            'descricao'  => sanitize_text_field( wp_unslash( $_POST['descricao']  ?? '' ) ),
            'criado_por' => get_current_user_id(),
        ];

        $wpdb->insert( $entries_tbl, $entry );
        $entry_id = (int) $wpdb->insert_id;

        // Lines.
        if ( ! empty( $_POST['lines'] ) && is_array( $_POST['lines'] ) ) {
            foreach ( $_POST['lines'] as $line ) {
                $wpdb->insert( $lines_tbl, [
                    'entry_id'  => $entry_id,
                    'conta_id'  => (int)   ( $line['conta_id'] ?? 0 ),
                    'debito'    => (float) ( $line['debito']   ?? 0 ),
                    'credito'   => (float) ( $line['credito']  ?? 0 ),
                    'descricao' => sanitize_text_field( wp_unslash( $line['descricao'] ?? '' ) ),
                ] );
            }
        }

        wp_safe_redirect( admin_url( 'admin.php?page=sinc-journal&saved=1' ) );
        exit;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Generate a sequential invoice number in format FACT-YYYY-NNNN.
     */
    private function generate_invoice_number(): string {
        global $wpdb;
        $table = Sincetur_Installer::get_tables()['invoices'];
        $year  = current_time( 'Y' );
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE YEAR(data_emissao) = %d",
            (int) $year
        ) );
        return sprintf( 'FACT-%s-%04d', $year, $count + 1 );
    }

    /**
     * Get all invoices with client name (for listing).
     */
    public static function get_invoices( array $args = [] ): array {
        global $wpdb;
        $t_inv = Sincetur_Installer::get_tables()['invoices'];
        $t_cli = Sincetur_Installer::get_tables()['erp_clients'];

        $limit  = isset( $args['limit'] )  ? (int) $args['limit']  : 20;
        $offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT i.*, c.nome AS client_nome
             FROM {$t_inv} i
             LEFT JOIN {$t_cli} c ON c.id = i.client_id
             ORDER BY i.data_emissao DESC
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ) );
    }

    /**
     * Get a single invoice with its line items.
     */
    public static function get_invoice( int $id ): ?object {
        global $wpdb;
        $t_inv   = Sincetur_Installer::get_tables()['invoices'];
        $t_items = Sincetur_Installer::get_tables()['invoice_items'];
        $t_cli   = Sincetur_Installer::get_tables()['erp_clients'];

        $invoice = $wpdb->get_row( $wpdb->prepare(
            "SELECT i.*, c.nome AS client_nome, c.nif AS client_nif, c.morada AS client_morada
             FROM {$t_inv} i LEFT JOIN {$t_cli} c ON c.id = i.client_id
             WHERE i.id = %d",
            $id
        ) );

        if ( $invoice ) {
            $invoice->items = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$t_items} WHERE invoice_id = %d",
                $id
            ) );
        }

        return $invoice ?: null;
    }

    /**
     * Trial balance (balancete) – sum of debits/credits per account.
     */
    public static function trial_balance(): array {
        global $wpdb;
        $t_coa   = Sincetur_Installer::get_tables()['chart_of_accounts'];
        $t_lines = Sincetur_Installer::get_tables()['journal_lines'];

        return $wpdb->get_results(
            "SELECT a.codigo, a.descricao, a.tipo,
                    COALESCE(SUM(l.debito),0)  AS total_debito,
                    COALESCE(SUM(l.credito),0) AS total_credito,
                    COALESCE(SUM(l.debito),0) - COALESCE(SUM(l.credito),0) AS saldo
             FROM {$t_coa} a
             LEFT JOIN {$t_lines} l ON l.conta_id = a.id
             GROUP BY a.id
             ORDER BY a.codigo"
        );
    }
}
