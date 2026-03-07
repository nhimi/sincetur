<?php
/**
 * Tours Module – Custom Post Type, Taxonomies & Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Tours {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_sinc_tour', [ $this, 'save_meta' ], 10, 2 );
    }

    public function register_post_type(): void {
        $labels = [
            'name'               => __( 'Tours', 'sincetur-portal' ),
            'singular_name'      => __( 'Tour', 'sincetur-portal' ),
            'add_new'            => __( 'Novo Tour', 'sincetur-portal' ),
            'add_new_item'       => __( 'Adicionar Novo Tour', 'sincetur-portal' ),
            'edit_item'          => __( 'Editar Tour', 'sincetur-portal' ),
            'new_item'           => __( 'Novo Tour', 'sincetur-portal' ),
            'view_item'          => __( 'Ver Tour', 'sincetur-portal' ),
            'search_items'       => __( 'Pesquisar Tours', 'sincetur-portal' ),
            'not_found'          => __( 'Nenhum tour encontrado.', 'sincetur-portal' ),
            'not_found_in_trash' => __( 'Nenhum tour no lixo.', 'sincetur-portal' ),
            'menu_name'          => __( 'Tours', 'sincetur-portal' ),
        ];

        register_post_type( 'sinc_tour', [
            'labels'              => $labels,
            'public'              => true,
            'show_in_menu'        => 'sincetur-portal',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'tours' ],
            'show_in_rest'        => true,
            'menu_icon'           => 'dashicons-palmtree',
        ] );
    }

    public function register_taxonomies(): void {
        // Destinos
        register_taxonomy( 'sinc_destino', 'sinc_tour', [
            'label'        => __( 'Destinos', 'sincetur-portal' ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'destinos' ],
            'show_in_rest' => true,
        ] );

        // Tipo de Tour
        register_taxonomy( 'sinc_tipo_tour', 'sinc_tour', [
            'label'        => __( 'Tipo de Tour', 'sincetur-portal' ),
            'hierarchical' => false,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'tipo-tour' ],
            'show_in_rest' => true,
        ] );
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'sinc_tour_details',
            __( 'Detalhes do Tour', 'sincetur-portal' ),
            [ $this, 'render_meta_box' ],
            'sinc_tour',
            'normal',
            'high'
        );
    }

    public function render_meta_box( \WP_Post $post ): void {
        wp_nonce_field( 'sinc_tour_save', 'sinc_tour_nonce' );
        $fields = [
            'sinc_tour_preco'       => [ 'label' => __( 'Preço (AOA)', 'sincetur-portal' ),    'type' => 'number' ],
            'sinc_tour_duracao'     => [ 'label' => __( 'Duração (dias)', 'sincetur-portal' ),  'type' => 'number' ],
            'sinc_tour_max_pessoas' => [ 'label' => __( 'Máx. Pessoas', 'sincetur-portal' ),    'type' => 'number' ],
            'sinc_tour_inclui'      => [ 'label' => __( 'O que inclui', 'sincetur-portal' ),    'type' => 'textarea' ],
            'sinc_tour_nao_inclui'  => [ 'label' => __( 'O que não inclui', 'sincetur-portal' ),'type' => 'textarea' ],
            'sinc_tour_itinerario'  => [ 'label' => __( 'Itinerário', 'sincetur-portal' ),      'type' => 'textarea' ],
            'sinc_tour_partida'     => [ 'label' => __( 'Local de Partida', 'sincetur-portal' ),'type' => 'text' ],
            'sinc_tour_dificuldade' => [ 'label' => __( 'Dificuldade', 'sincetur-portal' ),     'type' => 'select',
                'options' => [ 'facil' => 'Fácil', 'moderado' => 'Moderado', 'dificil' => 'Difícil' ] ],
        ];

        echo '<table class="form-table">';
        foreach ( $fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
            if ( $field['type'] === 'textarea' ) {
                echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
            } elseif ( $field['type'] === 'select' ) {
                echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
                foreach ( $field['options'] as $opt_val => $opt_label ) {
                    echo '<option value="' . esc_attr( $opt_val ) . '"' . selected( $value, $opt_val, false ) . '>' . esc_html( $opt_label ) . '</option>';
                }
                echo '</select>';
            } else {
                echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public function save_meta( int $post_id, \WP_Post $post ): void {
        if ( ! isset( $_POST['sinc_tour_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sinc_tour_nonce'] ) ), 'sinc_tour_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'sinc_tour_preco', 'sinc_tour_duracao', 'sinc_tour_max_pessoas',
            'sinc_tour_inclui', 'sinc_tour_nao_inclui', 'sinc_tour_itinerario',
            'sinc_tour_partida', 'sinc_tour_dificuldade',
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }
    }
}
