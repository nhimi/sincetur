<?php
/**
 * Admin View: Visa Requests (Pedidos de Assessoria de Visto)
 * Variables: $action (list|view), $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = Sincetur_Installer::get_tables()['visa_requests'];

if ( $action === 'view' && $id ) :
    $req = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Pedido de Visto #', 'sincetur-portal' ); ?><?php echo esc_html( $id ); ?>
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-visa-requests' ) ); ?>">&larr;</a>
    </h1>
    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Estado actualizado.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>
    <table class="form-table">
        <tr><th><?php esc_html_e( 'Nome Completo', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->nome_completo ); ?></td></tr>
        <tr><th><?php esc_html_e( 'E-mail', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->email ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->telefone ?: '—' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Nº Passaporte', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->passaporte_num ?: '—' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'País Destino', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->pais_destino ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Tipo de Visto', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->tipo_visto ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Data de Viagem', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->data_viagem ?: '—' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th><td>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                <?php wp_nonce_field( 'sinc_update_visa_estado' ); ?>
                <input type="hidden" name="action" value="sinc_update_visa_estado">
                <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
                <select name="estado">
                    <?php foreach ( [ 'pendente'=>'Pendente','em_analise'=>'Em Análise','aprovado'=>'Aprovado','rejeitado'=>'Rejeitado','entregue'=>'Entregue' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $req->estado, $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php esc_html_e( 'Actualizar', 'sincetur-portal' ); ?></button>
            </form>
        </td></tr>
        <tr><th><?php esc_html_e( 'Observações', 'sincetur-portal' ); ?></th><td><?php echo nl2br( esc_html( $req->observacoes ?: '—' ) ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Recebido em', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $req->created_at ); ?></td></tr>
    </table>
</div>

<?php else : // list ?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Pedidos de Assessoria de Visto', 'sincetur-portal' ); ?></h1>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'ID', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Nome', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'País Destino', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Tipo Visto', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Data', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Acções', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
            <?php $rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 100" ); ?>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="7"><?php esc_html_e( 'Nenhum pedido encontrado.', 'sincetur-portal' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <td><?php echo esc_html( $row->id ); ?></td>
                    <td><?php echo esc_html( $row->nome_completo ); ?></td>
                    <td><?php echo esc_html( $row->pais_destino ); ?></td>
                    <td><?php echo esc_html( $row->tipo_visto ); ?></td>
                    <td><span class="sinc-badge sinc-badge-<?php echo esc_attr( $row->estado ); ?>"><?php echo esc_html( ucfirst( $row->estado ) ); ?></span></td>
                    <td><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $row->created_at ) ) ); ?></td>
                    <td><a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-visa-requests&action=view&id={$row->id}" ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
