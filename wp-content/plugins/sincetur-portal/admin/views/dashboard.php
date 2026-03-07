<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$t = Sincetur_Installer::get_tables();

$total_tours      = wp_count_posts( 'sinc_tour' )->publish       ?? 0;
$total_hoteis     = wp_count_posts( 'sinc_hotel' )->publish      ?? 0;
$total_atividades = wp_count_posts( 'sinc_atividade' )->publish  ?? 0;
$total_eventos    = wp_count_posts( 'sinc_evento' )->publish     ?? 0;
$total_clientes   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['erp_clients']}" );
$total_facturas   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['invoices']}" );
$total_processos  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['processes']} WHERE estado NOT IN ('concluido','cancelado')" );
$total_vistos     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['visa_requests']} WHERE estado = 'pendente'" );
$total_tickets    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t['tickets']}" );
$receita_total    = (float) $wpdb->get_var( "SELECT COALESCE(SUM(total),0) FROM {$t['invoices']} WHERE estado = 'paga'" );
?>
<div class="wrap sinc-wrap">
    <h1>🛫 <?php esc_html_e( 'SINCETUR – Portal de Agência de Viagens', 'sincetur-portal' ); ?></h1>

    <div class="sinc-stats-grid">
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-palmtree"></span>
            <strong><?php echo esc_html( $total_tours ); ?></strong>
            <p><?php esc_html_e( 'Tours Publicados', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sinc_tour' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-building"></span>
            <strong><?php echo esc_html( $total_hoteis ); ?></strong>
            <p><?php esc_html_e( 'Hotéis', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sinc_hotel' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-chart-area"></span>
            <strong><?php echo esc_html( $total_atividades ); ?></strong>
            <p><?php esc_html_e( 'Actividades', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sinc_atividade' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-tickets-alt"></span>
            <strong><?php echo esc_html( $total_eventos ); ?></strong>
            <p><?php esc_html_e( 'Eventos', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sinc_evento' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-groups"></span>
            <strong><?php echo esc_html( $total_clientes ); ?></strong>
            <p><?php esc_html_e( 'Clientes', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-clients' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-media-spreadsheet"></span>
            <strong><?php echo esc_html( $total_facturas ); ?></strong>
            <p><?php esc_html_e( 'Facturas', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-invoices' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-clipboard"></span>
            <strong><?php echo esc_html( $total_processos ); ?></strong>
            <p><?php esc_html_e( 'Processos Activos', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-processes' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card">
            <span class="dashicons dashicons-id-alt"></span>
            <strong><?php echo esc_html( $total_vistos ); ?></strong>
            <p><?php esc_html_e( 'Vistos Pendentes', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-visa-requests' ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
        </div>
        <div class="sinc-stat-card sinc-stat-primary">
            <span class="dashicons dashicons-chart-line"></span>
            <strong><?php echo esc_html( number_format( $receita_total, 2, ',', '.' ) . ' AOA' ); ?></strong>
            <p><?php esc_html_e( 'Receita Total (Facturas Pagas)', 'sincetur-portal' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-reports' ) ); ?>"><?php esc_html_e( 'Relatórios', 'sincetur-portal' ); ?></a>
        </div>
    </div>

    <h2><?php esc_html_e( 'Acesso Rápido', 'sincetur-portal' ); ?></h2>
    <p>
        <a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=sinc_tour' ) ); ?>"><?php esc_html_e( '+ Tour', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=sinc_hotel' ) ); ?>"><?php esc_html_e( '+ Hotel', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=sinc_atividade' ) ); ?>"><?php esc_html_e( '+ Actividade', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=sinc_evento' ) ); ?>"><?php esc_html_e( '+ Evento', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-clients&action=new' ) ); ?>"><?php esc_html_e( '+ Cliente', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-invoices&action=new' ) ); ?>"><?php esc_html_e( '+ Factura', 'sincetur-portal' ); ?></a>
    </p>
</div>
