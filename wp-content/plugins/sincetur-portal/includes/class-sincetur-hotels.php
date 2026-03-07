<?php
/**
 * Hotels Module – Custom Post Type, Taxonomies & Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Hotels {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_sinc_hotel', [ $this, 'save_meta' ], 10, 2 );
    }

    public function register_post_type(): void {
        $labels = [
            'name'               => __( 'Hotéis', 'sincetur-portal' ),
            'singular_name'      => __( 'Hotel', 'sincetur-portal' ),
            'add_new'            => __( 'Novo Hotel', 'sincetur-portal' ),
            'add_new_item'       => __( 'Adicionar Novo Hotel', 'sincetur-portal' ),
            'edit_item'          => __( 'Editar Hotel', 'sincetur-portal' ),
            'new_item'           => __( 'Novo Hotel', 'sincetur-portal' ),
            'view_item'          => __( 'Ver Hotel', 'sincetur-portal' ),
            'search_items'       => __( 'Pesquisar Hotéis', 'sincetur-portal' ),
            'not_found'          => __( 'Nenhum hotel encontrado.', 'sincetur-portal' ),
            'not_found_in_trash' => __( 'Nenhum hotel no lixo.', 'sincetur-portal' ),
            'menu_name'          => __( 'Hotéis', 'sincetur-portal' ),
        ];

        register_post_type( 'sinc_hotel', [
            'labels'       => $labels,
            'public'       => true,
            'show_in_menu' => 'sincetur-portal',
            'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'has_archive'  => true,
            'rewrite'      => [ 'slug' => 'hoteis' ],
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-building',
        ] );
    }

    public function register_taxonomies(): void {
        register_taxonomy( 'sinc_cidade', [ 'sinc_hotel', 'sinc_tour' ], [
            'label'        => __( 'Cidade / Localidade', 'sincetur-portal' ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'cidades' ],
            'show_in_rest' => true,
        ] );

        register_taxonomy( 'sinc_categoria_hotel', 'sinc_hotel', [
            'label'        => __( 'Categoria de Hotel', 'sincetur-portal' ),
            'hierarchical' => false,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'categoria-hotel' ],
            'show_in_rest' => true,
        ] );
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'sinc_hotel_details',
            __( 'Detalhes do Hotel', 'sincetur-portal' ),
            [ $this, 'render_meta_box' ],
            'sinc_hotel',
            'normal',
            'high'
        );
    }

    public function render_meta_box( \WP_Post $post ): void {
        wp_nonce_field( 'sinc_hotel_save', 'sinc_hotel_nonce' );
        $fields = [
            'sinc_hotel_estrelas'        => [ 'label' => __( 'Estrelas', 'sincetur-portal' ),             'type' => 'number' ],
            'sinc_hotel_preco_noite'     => [ 'label' => __( 'Preço por Noite (AOA)', 'sincetur-portal' ),'type' => 'number' ],
            'sinc_hotel_endereco'        => [ 'label' => __( 'Endereço', 'sincetur-portal' ),              'type' => 'text' ],
            'sinc_hotel_telefone'        => [ 'label' => __( 'Telefone', 'sincetur-portal' ),              'type' => 'text' ],
            'sinc_hotel_email'           => [ 'label' => __( 'E-mail', 'sincetur-portal' ),                'type' => 'email' ],
            'sinc_hotel_website'         => [ 'label' => __( 'Website', 'sincetur-portal' ),               'type' => 'url' ],
            'sinc_hotel_comodidades'     => [ 'label' => __( 'Comodidades', 'sincetur-portal' ),           'type' => 'textarea' ],
            'sinc_hotel_politica_cancel' => [ 'label' => __( 'Política de Cancelamento', 'sincetur-portal' ), 'type' => 'textarea' ],
        ];

        echo '<table class="form-table">';
        foreach ( $fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
            if ( $field['type'] === 'textarea' ) {
                echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
            } else {
                echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public function save_meta( int $post_id, \WP_Post $post ): void {
        if ( ! isset( $_POST['sinc_hotel_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sinc_hotel_nonce'] ) ), 'sinc_hotel_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'sinc_hotel_estrelas', 'sinc_hotel_preco_noite', 'sinc_hotel_endereco',
            'sinc_hotel_telefone', 'sinc_hotel_email', 'sinc_hotel_website',
            'sinc_hotel_comodidades', 'sinc_hotel_politica_cancel',
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }
    }
}
