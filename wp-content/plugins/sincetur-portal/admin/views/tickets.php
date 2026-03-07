<?php
/**
 * Admin View: Tickets Sold (Bilhetes Vendidos)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = Sincetur_Installer::get_tables()['tickets'];
$rows  = $wpdb->get_results( "SELECT t.*, p.post_title AS evento_nome FROM {$table} t LEFT JOIN {$wpdb->posts} p ON p.ID = t.evento_id ORDER BY t.data_compra DESC LIMIT 200" );
?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Bilhetes Vendidos', 'sincetur-portal' ); ?></h1>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Código', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Evento', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'E-mail', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th>
            <th style="text-align:right"><?php esc_html_e( 'Preço', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Data', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
        <?php if ( empty( $rows ) ) : ?>
            <tr><td colspan="8"><?php esc_html_e( 'Nenhum bilhete vendido ainda.', 'sincetur-portal' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $rows as $row ) : ?>
            <tr>
                <td><code><?php echo esc_html( $row->codigo ); ?></code></td>
                <td><?php echo esc_html( $row->evento_nome ?: '#' . $row->evento_id ); ?></td>
                <td><?php echo esc_html( $row->nome_cliente ); ?></td>
                <td><?php echo esc_html( $row->email_cliente ); ?></td>
                <td><?php echo esc_html( strtoupper( $row->tipo_bilhete ) ); ?></td>
                <td style="text-align:right"><?php echo esc_html( number_format( (float)$row->preco, 2, ',', '.' ) . ' ' . $row->moeda ); ?></td>
                <td><span class="sinc-badge sinc-badge-<?php echo esc_attr( $row->estado ); ?>"><?php echo esc_html( ucfirst( $row->estado ) ); ?></span></td>
                <td><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $row->data_compra ) ) ); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
