<?php
/**
 * Customers (CRM) Module – Client management & process tracking.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Customers {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_post_sinc_save_client',  [ $this, 'handle_save_client' ] );
        add_action( 'admin_post_sinc_save_process', [ $this, 'handle_save_process' ] );
        add_action( 'admin_post_sinc_save_note',    [ $this, 'handle_save_note' ] );
        add_action( 'user_register', [ $this, 'auto_create_client_on_register' ] );
    }

    public function register_menu(): void {
        add_submenu_page(
            'sincetur-portal',
            __( 'Clientes', 'sincetur-portal' ),
            __( 'Clientes', 'sincetur-portal' ),
            'manage_options',
            'sinc-clients',
            [ $this, 'page_clients' ]
        );
        add_submenu_page(
            'sincetur-portal',
            __( 'Processos', 'sincetur-portal' ),
            __( 'Processos', 'sincetur-portal' ),
            'manage_options',
            'sinc-processes',
            [ $this, 'page_processes' ]
        );
    }

    // ─── Page Callbacks ──────────────────────────────────────────────────────

    public function page_clients(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . 'admin/views/clients.php';
    }

    public function page_processes(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . 'admin/views/processes.php';
    }

    // ─── Form Handlers ───────────────────────────────────────────────────────

    public function handle_save_client(): void {
        check_admin_referer( 'sinc_save_client' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $table = Sincetur_Installer::get_tables()['erp_clients'];
        $id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

        $data = [
            'nome'      => sanitize_text_field( wp_unslash( $_POST['nome']      ?? '' ) ),
            'nif'       => sanitize_text_field( wp_unslash( $_POST['nif']       ?? '' ) ) ?: null,
            'email'     => sanitize_email( wp_unslash( $_POST['email']          ?? '' ) ),
            'telefone'  => sanitize_text_field( wp_unslash( $_POST['telefone']  ?? '' ) ),
            'morada'    => sanitize_textarea_field( wp_unslash( $_POST['morada']?? '' ) ),
            'provincia' => sanitize_text_field( wp_unslash( $_POST['provincia'] ?? '' ) ),
            'pais'      => sanitize_text_field( wp_unslash( $_POST['pais']      ?? 'Angola' ) ),
            'tipo'      => sanitize_text_field( wp_unslash( $_POST['tipo']      ?? 'particular' ) ),
        ];

        if ( $id > 0 ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=sinc-clients&saved=1' ) );
        exit;
    }

    public function handle_save_process(): void {
        check_admin_referer( 'sinc_save_process' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $table = Sincetur_Installer::get_tables()['processes'];
        $id    = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

        $data = [
            'client_id'   => (int) ( $_POST['client_id']   ?? 0 ),
            'tipo'        => sanitize_text_field( wp_unslash( $_POST['tipo']        ?? '' ) ),
            'titulo'      => sanitize_text_field( wp_unslash( $_POST['titulo']      ?? '' ) ),
            'estado'      => sanitize_text_field( wp_unslash( $_POST['estado']      ?? 'aberto' ) ),
            'prioridade'  => sanitize_text_field( wp_unslash( $_POST['prioridade']  ?? 'normal' ) ),
            'responsavel' => (int) ( $_POST['responsavel']  ?? 0 ) ?: null,
            'data_inicio' => sanitize_text_field( wp_unslash( $_POST['data_inicio'] ?? '' ) ) ?: null,
            'data_fim'    => sanitize_text_field( wp_unslash( $_POST['data_fim']    ?? '' ) ) ?: null,
            'descricao'   => sanitize_textarea_field( wp_unslash( $_POST['descricao'] ?? '' ) ),
        ];

        if ( $id > 0 ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $data['referencia'] = $this->generate_process_ref( $data['tipo'] );
            $wpdb->insert( $table, $data );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=sinc-processes&saved=1' ) );
        exit;
    }

    public function handle_save_note(): void {
        check_admin_referer( 'sinc_save_note' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }

        global $wpdb;
        $table      = Sincetur_Installer::get_tables()['process_notes'];
        $process_id = isset( $_POST['process_id'] ) ? (int) $_POST['process_id'] : 0;
        $nota       = isset( $_POST['nota'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nota'] ) ) : '';

        if ( $process_id && $nota ) {
            $wpdb->insert( $table, [
                'process_id' => $process_id,
                'autor_id'   => get_current_user_id(),
                'nota'       => $nota,
            ] );
        }

        wp_safe_redirect( admin_url( "admin.php?page=sinc-processes&action=view&id={$process_id}" ) );
        exit;
    }

    /**
     * When a new WP user registers, create a corresponding ERP client.
     */
    public function auto_create_client_on_register( int $user_id ): void {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        global $wpdb;
        $table = Sincetur_Installer::get_tables()['erp_clients'];

        $wpdb->insert( $table, [
            'nome'       => $user->display_name ?: $user->user_login,
            'email'      => $user->user_email,
            'wp_user_id' => $user_id,
            'pais'       => 'Angola',
            'tipo'       => 'particular',
        ] );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function generate_process_ref( string $tipo ): string {
        $prefix = strtoupper( substr( preg_replace( '/[^a-z]/i', '', $tipo ), 0, 3 ) );
        return $prefix . '-' . current_time( 'Ymd' ) . '-' . wp_rand( 100, 999 );
    }

    /**
     * Retrieve clients list (for admin tables & dropdowns).
     */
    public static function get_clients( array $args = [] ): array {
        global $wpdb;
        $table  = Sincetur_Installer::get_tables()['erp_clients'];
        $limit  = isset( $args['limit'] )  ? (int) $args['limit']  : 50;
        $offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;
        $search = isset( $args['search'] ) ? '%' . $wpdb->esc_like( $args['search'] ) . '%' : null;

        if ( $search ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE nome LIKE %s OR email LIKE %s ORDER BY nome LIMIT %d OFFSET %d",
                $search, $search, $limit, $offset
            ) );
        }

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY nome LIMIT %d OFFSET %d",
            $limit, $offset
        ) );
    }

    /**
     * Retrieve processes list (with client name).
     */
    public static function get_processes( array $args = [] ): array {
        global $wpdb;
        $t_proc = Sincetur_Installer::get_tables()['processes'];
        $t_cli  = Sincetur_Installer::get_tables()['erp_clients'];
        $limit  = isset( $args['limit'] )    ? (int) $args['limit']                                   : 20;
        $offset = isset( $args['offset'] )   ? (int) $args['offset']                                  : 0;
        $estado = isset( $args['estado'] )   ? sanitize_text_field( $args['estado'] )                 : '';
        $cli_id = isset( $args['client_id'] )? (int) $args['client_id']                               : 0;

        $where  = '1=1';
        $params = [];

        if ( $estado ) {
            $where   .= ' AND p.estado = %s';
            $params[] = $estado;
        }
        if ( $cli_id ) {
            $where   .= ' AND p.client_id = %d';
            $params[] = $cli_id;
        }

        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT p.*, c.nome AS client_nome
             FROM {$t_proc} p
             LEFT JOIN {$t_cli} c ON c.id = p.client_id
             WHERE {$where}
             ORDER BY p.created_at DESC
             LIMIT %d OFFSET %d",
            ...$params
        ) );
    }
}
