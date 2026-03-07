<?php
/**
 * Events Module – Custom Post Type, Ticket Sales & Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Events {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_sinc_evento', [ $this, 'save_meta' ], 10, 2 );
        add_action( 'wp_ajax_sinc_comprar_ticket', [ $this, 'ajax_comprar_ticket' ] );
        add_action( 'wp_ajax_nopriv_sinc_comprar_ticket', [ $this, 'ajax_comprar_ticket' ] );
        add_shortcode( 'sincetur_comprar_ticket', [ $this, 'shortcode_ticket' ] );
    }

    public function register_post_type(): void {
        $labels = [
            'name'               => __( 'Eventos', 'sincetur-portal' ),
            'singular_name'      => __( 'Evento', 'sincetur-portal' ),
            'add_new'            => __( 'Novo Evento', 'sincetur-portal' ),
            'add_new_item'       => __( 'Adicionar Novo Evento', 'sincetur-portal' ),
            'edit_item'          => __( 'Editar Evento', 'sincetur-portal' ),
            'new_item'           => __( 'Novo Evento', 'sincetur-portal' ),
            'view_item'          => __( 'Ver Evento', 'sincetur-portal' ),
            'search_items'       => __( 'Pesquisar Eventos', 'sincetur-portal' ),
            'not_found'          => __( 'Nenhum evento encontrado.', 'sincetur-portal' ),
            'not_found_in_trash' => __( 'Nenhum evento no lixo.', 'sincetur-portal' ),
            'menu_name'          => __( 'Eventos', 'sincetur-portal' ),
        ];

        register_post_type( 'sinc_evento', [
            'labels'       => $labels,
            'public'       => true,
            'show_in_menu' => 'sincetur-portal',
            'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'has_archive'  => true,
            'rewrite'      => [ 'slug' => 'eventos' ],
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-tickets-alt',
        ] );
    }

    public function register_taxonomies(): void {
        register_taxonomy( 'sinc_tipo_evento', 'sinc_evento', [
            'label'        => __( 'Tipo de Evento', 'sincetur-portal' ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'tipo-evento' ],
            'show_in_rest' => true,
        ] );
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'sinc_evento_details',
            __( 'Detalhes do Evento', 'sincetur-portal' ),
            [ $this, 'render_meta_box' ],
            'sinc_evento',
            'normal',
            'high'
        );
    }

    public function render_meta_box( \WP_Post $post ): void {
        wp_nonce_field( 'sinc_evento_save', 'sinc_evento_nonce' );
        $fields = [
            'sinc_evento_data_inicio'   => [ 'label' => __( 'Data de Início', 'sincetur-portal' ),     'type' => 'datetime-local' ],
            'sinc_evento_data_fim'       => [ 'label' => __( 'Data de Fim', 'sincetur-portal' ),        'type' => 'datetime-local' ],
            'sinc_evento_local'          => [ 'label' => __( 'Local do Evento', 'sincetur-portal' ),    'type' => 'text' ],
            'sinc_evento_capacidade'     => [ 'label' => __( 'Capacidade Total', 'sincetur-portal' ),   'type' => 'number' ],
            'sinc_evento_preco_geral'    => [ 'label' => __( 'Preço Bilhete Geral (AOA)', 'sincetur-portal' ), 'type' => 'number' ],
            'sinc_evento_preco_vip'      => [ 'label' => __( 'Preço Bilhete VIP (AOA)', 'sincetur-portal' ),   'type' => 'number' ],
            'sinc_evento_organizador'    => [ 'label' => __( 'Organizador', 'sincetur-portal' ),         'type' => 'text' ],
            'sinc_evento_contacto'       => [ 'label' => __( 'Contacto do Organizador', 'sincetur-portal' ),'type' => 'text' ],
        ];

        echo '<table class="form-table">';
        foreach ( $fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
            echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public function save_meta( int $post_id, \WP_Post $post ): void {
        if ( ! isset( $_POST['sinc_evento_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sinc_evento_nonce'] ) ), 'sinc_evento_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'sinc_evento_data_inicio', 'sinc_evento_data_fim', 'sinc_evento_local',
            'sinc_evento_capacidade',  'sinc_evento_preco_geral', 'sinc_evento_preco_vip',
            'sinc_evento_organizador', 'sinc_evento_contacto',
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }
    }

    /**
     * Shortcode: [sincetur_comprar_ticket evento_id="123"]
     */
    public function shortcode_ticket( array $atts ): string {
        $atts = shortcode_atts( [ 'evento_id' => get_the_ID() ], $atts, 'sincetur_comprar_ticket' );
        $evento_id = (int) $atts['evento_id'];

        if ( ! $evento_id ) {
            return '';
        }

        $preco_geral = (float) get_post_meta( $evento_id, 'sinc_evento_preco_geral', true );
        $preco_vip   = (float) get_post_meta( $evento_id, 'sinc_evento_preco_vip', true );

        ob_start();
        include SINCETUR_PLUGIN_DIR . 'public/templates/ticket-form.php';
        return ob_get_clean();
    }

    /**
     * AJAX: process ticket purchase.
     */
    public function ajax_comprar_ticket(): void {
        check_ajax_referer( 'sinc_ticket_nonce', 'nonce' );

        $evento_id    = isset( $_POST['evento_id'] )    ? (int) sanitize_text_field( wp_unslash( $_POST['evento_id'] ) )    : 0;
        $nome         = isset( $_POST['nome'] )         ? sanitize_text_field( wp_unslash( $_POST['nome'] ) )         : '';
        $email        = isset( $_POST['email'] )        ? sanitize_email( wp_unslash( $_POST['email'] ) )              : '';
        $telefone     = isset( $_POST['telefone'] )     ? sanitize_text_field( wp_unslash( $_POST['telefone'] ) )     : '';
        $tipo_bilhete = isset( $_POST['tipo_bilhete'] ) ? sanitize_text_field( wp_unslash( $_POST['tipo_bilhete'] ) ) : 'geral';

        if ( ! $evento_id || ! $nome || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Dados inválidos. Por favor preencha todos os campos obrigatórios.', 'sincetur-portal' ) ] );
        }

        $preco_key = ( $tipo_bilhete === 'vip' ) ? 'sinc_evento_preco_vip' : 'sinc_evento_preco_geral';
        $preco     = (float) get_post_meta( $evento_id, $preco_key, true );

        global $wpdb;
        $tickets_table = Sincetur_Installer::get_tables()['tickets'];
        $codigo        = strtoupper( 'TK' . wp_generate_password( 8, false ) );

        $inserted = $wpdb->insert(
            $tickets_table,
            [
                'evento_id'    => $evento_id,
                'nome_cliente' => $nome,
                'email_cliente'=> $email,
                'telefone'     => $telefone,
                'tipo_bilhete' => $tipo_bilhete,
                'preco'        => $preco,
                'moeda'        => 'AOA',
                'codigo'       => $codigo,
                'estado'       => 'reservado',
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( [ 'message' => __( 'Erro ao registar o bilhete. Tente novamente.', 'sincetur-portal' ) ] );
        }

        wp_send_json_success( [
            'message' => __( 'Bilhete reservado com sucesso!', 'sincetur-portal' ),
            'codigo'  => $codigo,
            'preco'   => number_format( $preco, 2, ',', '.' ) . ' AOA',
        ] );
    }
}
