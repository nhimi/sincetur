<?php
/**
 * Admin View: ERP – Invoices
 * Variables from caller: $action (list|new|edit|view), $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( $action === 'new' || $action === 'edit' ) :
    $invoice = ( $action === 'edit' && $id ) ? Sincetur_ERP::get_invoice( $id ) : null;
    $clients = Sincetur_Customers::get_clients( [ 'limit' => 200 ] );
    global $wpdb;
    $accounts = $wpdb->get_results( "SELECT id, codigo, descricao FROM " . Sincetur_Installer::get_tables()['chart_of_accounts'] . " WHERE nivel >= 2 AND activa = 1 ORDER BY codigo" );
?>
<div class="wrap sinc-wrap">
    <h1><?php echo $invoice ? esc_html__( 'Editar Factura', 'sincetur-portal' ) : esc_html__( 'Nova Factura', 'sincetur-portal' ); ?></h1>
    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-invoices' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sinc-form">
        <?php wp_nonce_field( 'sinc_save_invoice' ); ?>
        <input type="hidden" name="action" value="sinc_save_invoice">
        <?php if ( $invoice ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $invoice->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th><td>
                <select name="client_id" required>
                    <option value=""><?php esc_html_e( '— Seleccione —', 'sincetur-portal' ); ?></option>
                    <?php foreach ( $clients as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $invoice->client_id ?? '', $c->id ); ?>><?php echo esc_html( $c->nome ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th><td>
                <select name="tipo">
                    <?php foreach ( [ 'factura' => 'Factura', 'nota_credito' => 'Nota de Crédito', 'recibo' => 'Recibo', 'pro_forma' => 'Pro-Forma' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $invoice->tipo ?? 'factura', $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Data Emissão', 'sincetur-portal' ); ?></th><td>
                <input type="date" name="data_emissao" value="<?php echo esc_attr( $invoice->data_emissao ?? current_time( 'Y-m-d' ) ); ?>" required>
            </td></tr>
            <tr><th><?php esc_html_e( 'Data Vencimento', 'sincetur-portal' ); ?></th><td>
                <input type="date" name="data_vencimento" value="<?php echo esc_attr( $invoice->data_vencimento ?? '' ); ?>">
            </td></tr>
            <tr><th><?php esc_html_e( 'IVA (%)', 'sincetur-portal' ); ?></th><td>
                <input type="number" step="0.01" name="iva_percentagem" value="<?php echo esc_attr( $invoice->iva_percentagem ?? 14 ); ?>" class="small-text">
            </td></tr>
            <tr><th><?php esc_html_e( 'Moeda', 'sincetur-portal' ); ?></th><td>
                <select name="moeda">
                    <?php foreach ( [ 'AOA' => 'Kwanza (AOA)', 'USD' => 'Dólar (USD)', 'EUR' => 'Euro (EUR)' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $invoice->moeda ?? 'AOA', $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th><td>
                <select name="estado">
                    <?php foreach ( [ 'rascunho' => 'Rascunho', 'emitida' => 'Emitida', 'paga' => 'Paga', 'cancelada' => 'Cancelada' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $invoice->estado ?? 'rascunho', $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Observações', 'sincetur-portal' ); ?></th><td>
                <textarea name="observacoes" rows="3" class="large-text"><?php echo esc_textarea( $invoice->observacoes ?? '' ); ?></textarea>
            </td></tr>
        </table>

        <h3><?php esc_html_e( 'Linhas da Factura', 'sincetur-portal' ); ?></h3>
        <table class="widefat sinc-line-items" id="sinc-items-table">
            <thead><tr>
                <th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Qtd', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Preço Unit.', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Total', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Conta PCGA', 'sincetur-portal' ); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
                <?php
                $items = $invoice->items ?? [];
                if ( empty( $items ) ) {
                    $items = [ (object)[ 'descricao'=>'', 'quantidade'=>1, 'preco_unit'=>0, 'total'=>0, 'conta_pcga'=>'' ] ];
                }
                foreach ( $items as $i => $item ) : ?>
                <tr class="sinc-item-row">
                    <td><input type="text" name="items[<?php echo $i; ?>][descricao]" value="<?php echo esc_attr( $item->descricao ); ?>" class="regular-text" required></td>
                    <td><input type="number" step="0.01" name="items[<?php echo $i; ?>][quantidade]" value="<?php echo esc_attr( $item->quantidade ); ?>" class="small-text sinc-qty" min="0.01"></td>
                    <td><input type="number" step="0.01" name="items[<?php echo $i; ?>][preco_unit]" value="<?php echo esc_attr( $item->preco_unit ); ?>" class="regular-text sinc-unit-price" min="0"></td>
                    <td><input type="number" step="0.01" name="items[<?php echo $i; ?>][total]" value="<?php echo esc_attr( $item->total ); ?>" class="regular-text sinc-line-total" readonly></td>
                    <td>
                        <select name="items[<?php echo $i; ?>][conta_pcga]">
                            <option value=""><?php esc_html_e( '— Conta —', 'sincetur-portal' ); ?></option>
                            <?php foreach ( $accounts as $acc ) : ?>
                                <option value="<?php echo esc_attr( $acc->codigo ); ?>" <?php selected( $item->conta_pcga ?? '', $acc->codigo ); ?>><?php echo esc_html( $acc->codigo . ' – ' . $acc->descricao ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><button type="button" class="button sinc-remove-row">✕</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr>
                <td colspan="6"><button type="button" class="button" id="sinc-add-item"><?php esc_html_e( '+ Linha', 'sincetur-portal' ); ?></button></td>
            </tr></tfoot>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar Factura', 'sincetur-portal' ); ?></button>
        </p>
    </form>
</div>

<?php else : // list ?>

<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Facturas', 'sincetur-portal' ); ?>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-invoices&action=new' ) ); ?>"><?php esc_html_e( 'Nova Factura', 'sincetur-portal' ); ?></a>
    </h1>

    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Factura guardada com sucesso.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Número', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Data', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Total', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Acções', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
            <?php $invoices = Sincetur_ERP::get_invoices(); ?>
            <?php if ( empty( $invoices ) ) : ?>
                <tr><td colspan="7"><?php esc_html_e( 'Nenhuma factura encontrada.', 'sincetur-portal' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $invoices as $inv ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $inv->numero ); ?></strong></td>
                    <td><?php echo esc_html( ucfirst( $inv->tipo ) ); ?></td>
                    <td><?php echo esc_html( $inv->client_nome ?? '—' ); ?></td>
                    <td><?php echo esc_html( $inv->data_emissao ); ?></td>
                    <td><?php echo esc_html( number_format( (float)$inv->total, 2, ',', '.' ) . ' ' . $inv->moeda ); ?></td>
                    <td><span class="sinc-badge sinc-badge-<?php echo esc_attr( $inv->estado ); ?>"><?php echo esc_html( ucfirst( $inv->estado ) ); ?></span></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-invoices&action=edit&id={$inv->id}" ) ); ?>"><?php esc_html_e( 'Editar', 'sincetur-portal' ); ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
