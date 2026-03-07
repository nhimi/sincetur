<?php
/**
 * Admin – top-level menu & shared admin functionality.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Admin {

    public function __construct() {
        add_action( 'admin_menu',    [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'SINCETUR Portal', 'sincetur-portal' ),
            __( 'SINCETUR', 'sincetur-portal' ),
            'manage_options',
            'sincetur-portal',
            [ $this, 'page_dashboard' ],
            'dashicons-airplane',
            3
        );

        add_submenu_page(
            'sincetur-portal',
            __( 'Dashboard', 'sincetur-portal' ),
            __( 'Dashboard', 'sincetur-portal' ),
            'manage_options',
            'sincetur-portal',
            [ $this, 'page_dashboard' ]
        );

        // Visa requests listing.
        add_submenu_page(
            'sincetur-portal',
            __( 'Pedidos de Visto', 'sincetur-portal' ),
            __( 'Pedidos de Visto', 'sincetur-portal' ),
            'manage_options',
            'sinc-visa-requests',
            [ $this, 'page_visa_requests' ]
        );

        // Tickets listing.
        add_submenu_page(
            'sincetur-portal',
            __( 'Bilhetes Vendidos', 'sincetur-portal' ),
            __( 'Bilhetes', 'sincetur-portal' ),
            'manage_options',
            'sinc-tickets',
            [ $this, 'page_tickets' ]
        );
    }

    public function enqueue_assets( string $hook ): void {
        // Only load on SINCETUR admin pages.
        if ( strpos( $hook, 'sincetur' ) === false && strpos( $hook, 'sinc-' ) === false ) {
            return;
        }
        wp_enqueue_style(
            'sincetur-admin',
            SINCETUR_PLUGIN_URL . 'assets/css/admin.css',
            [],
            SINCETUR_VERSION
        );
        wp_enqueue_script(
            'sincetur-admin',
            SINCETUR_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            SINCETUR_VERSION,
            true
        );
    }

    public function page_dashboard(): void {
        include SINCETUR_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_visa_requests(): void {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] )     ? (int) $_GET['id']                                    : 0;
        include SINCETUR_PLUGIN_DIR . 'admin/views/visa-requests.php';
    }

    public function page_tickets(): void {
        include SINCETUR_PLUGIN_DIR . 'admin/views/tickets.php';
    }

    /**
     * Shared helper: render a simple pagination bar.
     */
    public static function pagination( int $total, int $per_page, int $current, string $base_url ): void {
        $pages = (int) ceil( $total / $per_page );
        if ( $pages <= 1 ) {
            return;
        }
        echo '<div class="sinc-pagination tablenav-pages">';
        for ( $i = 1; $i <= $pages; $i++ ) {
            $url     = add_query_arg( 'paged', $i, $base_url );
            $current_class = ( $i === $current ) ? ' current' : '';
            printf(
                '<a class="button%s" href="%s">%d</a> ',
                esc_attr( $current_class ),
                esc_url( $url ),
                $i
            );
        }
        echo '</div>';
    }
}
