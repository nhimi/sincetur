<?php
/**
 * Admin View: ERP – Chart of Accounts (Plano de Contas PCGA-Angolano)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table    = Sincetur_Installer::get_tables()['chart_of_accounts'];
$accounts = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY codigo" );
?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Plano de Contas – PCGA Angola', 'sincetur-portal' ); ?></h1>
    <p><?php esc_html_e( 'Plano Geral de Contabilidade Angolano (PGCA) em uso no portal.', 'sincetur-portal' ); ?></p>

    <table class="wp-list-table widefat fixed striped sinc-coa-table">
        <thead><tr>
            <th style="width:100px"><?php esc_html_e( 'Código', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th>
            <th style="width:120px"><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th>
            <th style="width:60px"><?php esc_html_e( 'Nível', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ( $accounts as $acc ) :
            $indent = str_repeat( '&nbsp;&nbsp;&nbsp;', max( 0, $acc->nivel - 1 ) );
            $bold   = $acc->nivel === 1 ? 'font-weight:bold' : '';
        ?>
        <tr>
            <td style="<?php echo esc_attr( $bold ); ?>"><?php echo esc_html( $acc->codigo ); ?></td>
            <td style="<?php echo esc_attr( $bold ); ?>"><?php echo $indent . esc_html( $acc->descricao ); ?></td>
            <td><?php echo esc_html( ucfirst( $acc->tipo ) ); ?></td>
            <td><?php echo esc_html( $acc->nivel ); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
