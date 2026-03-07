<?php
/**
 * Public-facing class – enqueue scripts, register shortcodes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Public {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_sinc_update_visa_estado', [ $this, 'handle_update_visa_estado' ] );
        add_action( 'admin_post_sinc_update_visa_estado', [ $this, 'handle_update_visa_estado' ] );
        add_shortcode( 'sincetur_tours_listing',      [ $this, 'shortcode_tours' ] );
        add_shortcode( 'sincetur_hotels_listing',     [ $this, 'shortcode_hotels' ] );
        add_shortcode( 'sincetur_activities_listing', [ $this, 'shortcode_activities' ] );
        add_shortcode( 'sincetur_events_listing',     [ $this, 'shortcode_events' ] );
    }

    public function enqueue_assets(): void {
        wp_enqueue_style(
            'sincetur-public',
            SINCETUR_PLUGIN_URL . 'assets/css/public.css',
            [],
            SINCETUR_VERSION
        );
        wp_enqueue_script(
            'sincetur-public',
            SINCETUR_PLUGIN_URL . 'assets/js/public.js',
            [ 'jquery' ],
            SINCETUR_VERSION,
            true
        );
        wp_localize_script( 'sincetur-public', 'sinceturAjax', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'sinc_public_nonce' ),
            'i18n'  => [
                'reserving'    => __( 'A reservar…', 'sincetur-portal' ),
                'sending'      => __( 'A enviar…', 'sincetur-portal' ),
                'error_server' => __( 'Erro no servidor. Tente novamente.', 'sincetur-portal' ),
            ],
        ] );
    }

    /**
     * [sincetur_tours_listing] shortcode.
     */
    public function shortcode_tours( array $atts ): string {
        $atts = shortcode_atts( [ 'posts_per_page' => 6, 'destino' => '' ], $atts );
        $args = [
            'post_type'      => 'sinc_tour',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'post_status'    => 'publish',
        ];
        if ( $atts['destino'] ) {
            $args['tax_query'] = [ [ 'taxonomy' => 'sinc_destino', 'field' => 'slug', 'terms' => $atts['destino'] ] ];
        }
        return $this->render_listing( new WP_Query( $args ), 'tour' );
    }

    /**
     * [sincetur_hotels_listing] shortcode.
     */
    public function shortcode_hotels( array $atts ): string {
        $atts = shortcode_atts( [ 'posts_per_page' => 6 ], $atts );
        $query = new WP_Query( [
            'post_type'      => 'sinc_hotel',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'post_status'    => 'publish',
        ] );
        return $this->render_listing( $query, 'hotel' );
    }

    /**
     * [sincetur_activities_listing] shortcode.
     */
    public function shortcode_activities( array $atts ): string {
        $atts = shortcode_atts( [ 'posts_per_page' => 6 ], $atts );
        $query = new WP_Query( [
            'post_type'      => 'sinc_atividade',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'post_status'    => 'publish',
        ] );
        return $this->render_listing( $query, 'atividade' );
    }

    /**
     * [sincetur_events_listing] shortcode.
     */
    public function shortcode_events( array $atts ): string {
        $atts = shortcode_atts( [ 'posts_per_page' => 6 ], $atts );
        $query = new WP_Query( [
            'post_type'      => 'sinc_evento',
            'posts_per_page' => (int) $atts['posts_per_page'],
            'post_status'    => 'publish',
            'meta_key'       => 'sinc_evento_data_inicio',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        ] );
        return $this->render_listing( $query, 'evento' );
    }

    /**
     * Generic card listing renderer.
     */
    private function render_listing( \WP_Query $query, string $type ): string {
        if ( ! $query->have_posts() ) {
            return '<p class="sinc-no-results">' . esc_html__( 'Nenhum resultado encontrado.', 'sincetur-portal' ) . '</p>';
        }

        $meta_keys = [
            'tour'      => [ 'label' => __( 'Preço', 'sincetur-portal' ),      'key' => 'sinc_tour_preco',      'suffix' => ' AOA' ],
            'hotel'     => [ 'label' => __( 'Noite a partir de', 'sincetur-portal' ), 'key' => 'sinc_hotel_preco_noite', 'suffix' => ' AOA' ],
            'atividade' => [ 'label' => __( 'Preço por pessoa', 'sincetur-portal' ),  'key' => 'sinc_atv_preco',         'suffix' => ' AOA' ],
            'evento'    => [ 'label' => __( 'Bilhete a partir de', 'sincetur-portal' ),'key' => 'sinc_evento_preco_geral','suffix' => ' AOA' ],
        ];

        ob_start();
        echo '<div class="sinc-listing sinc-listing-' . esc_attr( $type ) . '">';
        while ( $query->have_posts() ) :
            $query->the_post();
            $meta  = $meta_keys[ $type ] ?? null;
            $price = $meta ? (float) get_post_meta( get_the_ID(), $meta['key'], true ) : null;
            include SINCETUR_PLUGIN_DIR . 'public/templates/card.php';
        endwhile;
        echo '</div>';
        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Handle visa estado update (admin-post).
     */
    public function handle_update_visa_estado(): void {
        check_admin_referer( 'sinc_update_visa_estado' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sem permissão.', 'sincetur-portal' ) );
        }
        global $wpdb;
        $id     = isset( $_POST['id'] )     ? (int) $_POST['id']                                           : 0;
        $estado = isset( $_POST['estado'] ) ? sanitize_text_field( wp_unslash( $_POST['estado'] ) )        : '';
        if ( $id && $estado ) {
            $wpdb->update(
                Sincetur_Installer::get_tables()['visa_requests'],
                [ 'estado' => $estado ],
                [ 'id'     => $id ],
                [ '%s' ],
                [ '%d' ]
            );
        }
        wp_safe_redirect( admin_url( "admin.php?page=sinc-visa-requests&action=view&id={$id}&updated=1" ) );
        exit;
    }
}
