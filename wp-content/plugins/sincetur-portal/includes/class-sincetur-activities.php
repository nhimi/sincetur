<?php
/**
 * Activities Module – Custom Post Type & Meta Boxes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Activities {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_sinc_atividade', [ $this, 'save_meta' ], 10, 2 );
    }

    public function register_post_type(): void {
        $labels = [
            'name'               => __( 'Actividades', 'sincetur-portal' ),
            'singular_name'      => __( 'Actividade', 'sincetur-portal' ),
            'add_new'            => __( 'Nova Actividade', 'sincetur-portal' ),
            'add_new_item'       => __( 'Adicionar Nova Actividade', 'sincetur-portal' ),
            'edit_item'          => __( 'Editar Actividade', 'sincetur-portal' ),
            'new_item'           => __( 'Nova Actividade', 'sincetur-portal' ),
            'view_item'          => __( 'Ver Actividade', 'sincetur-portal' ),
            'search_items'       => __( 'Pesquisar Actividades', 'sincetur-portal' ),
            'not_found'          => __( 'Nenhuma actividade encontrada.', 'sincetur-portal' ),
            'not_found_in_trash' => __( 'Nenhuma actividade no lixo.', 'sincetur-portal' ),
            'menu_name'          => __( 'Actividades', 'sincetur-portal' ),
        ];

        register_post_type( 'sinc_atividade', [
            'labels'       => $labels,
            'public'       => true,
            'show_in_menu' => 'sincetur-portal',
            'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'has_archive'  => true,
            'rewrite'      => [ 'slug' => 'actividades' ],
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-chart-area',
        ] );
    }

    public function register_taxonomies(): void {
        register_taxonomy( 'sinc_tipo_atividade', 'sinc_atividade', [
            'label'        => __( 'Tipo de Actividade', 'sincetur-portal' ),
            'hierarchical' => true,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'tipo-actividade' ],
            'show_in_rest' => true,
        ] );
    }

    public function add_meta_boxes(): void {
        add_meta_box(
            'sinc_atividade_details',
            __( 'Detalhes da Actividade', 'sincetur-portal' ),
            [ $this, 'render_meta_box' ],
            'sinc_atividade',
            'normal',
            'high'
        );
    }

    public function render_meta_box( \WP_Post $post ): void {
        wp_nonce_field( 'sinc_atividade_save', 'sinc_atividade_nonce' );
        $fields = [
            'sinc_atv_preco'         => [ 'label' => __( 'Preço por Pessoa (AOA)', 'sincetur-portal' ), 'type' => 'number' ],
            'sinc_atv_duracao_horas' => [ 'label' => __( 'Duração (horas)', 'sincetur-portal' ),        'type' => 'number' ],
            'sinc_atv_max_pessoas'   => [ 'label' => __( 'Máx. Pessoas', 'sincetur-portal' ),           'type' => 'number' ],
            'sinc_atv_nivel'         => [ 'label' => __( 'Nível de Esforço', 'sincetur-portal' ),       'type' => 'select',
                'options' => [ 'baixo' => 'Baixo', 'moderado' => 'Moderado', 'intenso' => 'Intenso' ] ],
            'sinc_atv_requisitos'    => [ 'label' => __( 'Requisitos', 'sincetur-portal' ),              'type' => 'textarea' ],
            'sinc_atv_local'         => [ 'label' => __( 'Local de Encontro', 'sincetur-portal' ),       'type' => 'text' ],
        ];

        echo '<table class="form-table">';
        foreach ( $fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
            if ( $field['type'] === 'textarea' ) {
                echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="3" class="large-text">' . esc_textarea( $value ) . '</textarea>';
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
        if ( ! isset( $_POST['sinc_atividade_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sinc_atividade_nonce'] ) ), 'sinc_atividade_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'sinc_atv_preco', 'sinc_atv_duracao_horas', 'sinc_atv_max_pessoas',
            'sinc_atv_nivel', 'sinc_atv_requisitos', 'sinc_atv_local',
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
            }
        }
    }
}
