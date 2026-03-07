<?php
/**
 * Admin View: ERP – Reports (Relatórios)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$t       = Sincetur_Installer::get_tables();
$report  = isset( $_GET['report'] ) ? sanitize_text_field( wp_unslash( $_GET['report'] ) ) : 'balancete';
$year    = isset( $_GET['year'] )   ? (int) $_GET['year']                                  : (int) current_time( 'Y' );
?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Relatórios ERP', 'sincetur-portal' ); ?></h1>

    <form method="get" style="margin-bottom:20px">
        <input type="hidden" name="page" value="sinc-reports">
        <select name="report">
            <option value="balancete" <?php selected( $report, 'balancete' ); ?>><?php esc_html_e( 'Balancete de Verificação', 'sincetur-portal' ); ?></option>
            <option value="receitas"  <?php selected( $report, 'receitas' );  ?>><?php esc_html_e( 'Receitas por Período', 'sincetur-portal' ); ?></option>
            <option value="clientes"  <?php selected( $report, 'clientes' );  ?>><?php esc_html_e( 'Extracto de Clientes', 'sincetur-portal' ); ?></option>
        </select>
        <select name="year">
            <?php for ( $y = (int) current_time( 'Y' ); $y >= 2020; $y-- ) : ?>
            <option value="<?php echo esc_attr( $y ); ?>" <?php selected( $year, $y ); ?>><?php echo esc_html( $y ); ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" class="button"><?php esc_html_e( 'Gerar', 'sincetur-portal' ); ?></button>
    </form>

    <?php if ( $report === 'balancete' ) : ?>

        <h2><?php esc_html_e( 'Balancete de Verificação', 'sincetur-portal' ); ?> – <?php echo esc_html( $year ); ?></h2>
        <?php $balance = Sincetur_ERP::trial_balance(); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th><?php esc_html_e( 'Código', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Conta', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Débito', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Crédito', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Saldo', 'sincetur-portal' ); ?></th>
            </tr></thead>
            <tbody>
            <?php
            $tot_deb = 0; $tot_cre = 0;
            foreach ( $balance as $row ) :
                $tot_deb += $row->total_debito;
                $tot_cre += $row->total_credito;
                $saldo_color = (float)$row->saldo < 0 ? 'color:red' : '';
            ?>
            <tr>
                <td><?php echo esc_html( $row->codigo ); ?></td>
                <td><?php echo esc_html( $row->descricao ); ?></td>
                <td><?php echo esc_html( ucfirst( $row->tipo ) ); ?></td>
                <td style="text-align:right"><?php echo esc_html( number_format( (float)$row->total_debito,  2, ',', '.' ) ); ?></td>
                <td style="text-align:right"><?php echo esc_html( number_format( (float)$row->total_credito, 2, ',', '.' ) ); ?></td>
                <td style="text-align:right;<?php echo esc_attr( $saldo_color ); ?>"><?php echo esc_html( number_format( (float)$row->saldo, 2, ',', '.' ) ); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr>
                <th colspan="3"><strong><?php esc_html_e( 'Totais', 'sincetur-portal' ); ?></strong></th>
                <th style="text-align:right"><strong><?php echo esc_html( number_format( $tot_deb, 2, ',', '.' ) ); ?></strong></th>
                <th style="text-align:right"><strong><?php echo esc_html( number_format( $tot_cre, 2, ',', '.' ) ); ?></strong></th>
                <th></th>
            </tr></tfoot>
        </table>

    <?php elseif ( $report === 'receitas' ) : ?>

        <h2><?php esc_html_e( 'Receitas por Período', 'sincetur-portal' ); ?> – <?php echo esc_html( $year ); ?></h2>
        <?php
        $monthly = $wpdb->get_results( $wpdb->prepare(
            "SELECT MONTH(data_emissao) AS mes, SUM(total) AS total_mes, COUNT(*) AS num_facturas
             FROM {$t['invoices']}
             WHERE YEAR(data_emissao) = %d AND estado = 'paga'
             GROUP BY MONTH(data_emissao) ORDER BY mes",
            $year
        ) );
        $meses = [ 1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez' ];
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th><?php esc_html_e( 'Mês', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Nº Facturas', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Total (AOA)', 'sincetur-portal' ); ?></th>
            </tr></thead>
            <tbody>
            <?php if ( empty( $monthly ) ) : ?>
                <tr><td colspan="3"><?php esc_html_e( 'Sem dados para este período.', 'sincetur-portal' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $monthly as $m ) : ?>
                <tr>
                    <td><?php echo esc_html( $meses[ (int)$m->mes ] ?? $m->mes ); ?></td>
                    <td style="text-align:right"><?php echo esc_html( $m->num_facturas ); ?></td>
                    <td style="text-align:right"><?php echo esc_html( number_format( (float)$m->total_mes, 2, ',', '.' ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ( $report === 'clientes' ) : ?>

        <h2><?php esc_html_e( 'Extracto de Clientes', 'sincetur-portal' ); ?></h2>
        <?php
        $client_totals = $wpdb->get_results(
            "SELECT c.nome, c.nif, c.email,
                    COUNT(i.id) AS num_facturas,
                    COALESCE(SUM(i.total),0) AS total_facturado,
                    COALESCE(SUM(CASE WHEN i.estado='paga' THEN i.total ELSE 0 END),0) AS total_pago
             FROM {$t['erp_clients']} c
             LEFT JOIN {$t['invoices']} i ON i.client_id = c.id
             GROUP BY c.id ORDER BY total_facturado DESC LIMIT 100"
        );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr>
                <th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'NIF', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Facturas', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Total Facturado', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Total Pago', 'sincetur-portal' ); ?></th>
                <th style="text-align:right"><?php esc_html_e( 'Saldo Pendente', 'sincetur-portal' ); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $client_totals as $cl ) :
                $pendente = (float)$cl->total_facturado - (float)$cl->total_pago;
            ?>
            <tr>
                <td><?php echo esc_html( $cl->nome ); ?></td>
                <td><?php echo esc_html( $cl->nif ?: '—' ); ?></td>
                <td style="text-align:right"><?php echo esc_html( $cl->num_facturas ); ?></td>
                <td style="text-align:right"><?php echo esc_html( number_format( (float)$cl->total_facturado, 2, ',', '.' ) ); ?></td>
                <td style="text-align:right"><?php echo esc_html( number_format( (float)$cl->total_pago, 2, ',', '.' ) ); ?></td>
                <td style="text-align:right;<?php echo $pendente > 0 ? 'color:red' : ''; ?>"><?php echo esc_html( number_format( $pendente, 2, ',', '.' ) ); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>
