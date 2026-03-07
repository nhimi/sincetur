<?php
/**
 * Visa Advisory Module – Custom Post Type & Request Management.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sincetur_Visa {

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'wp_ajax_sinc_pedir_visto', [ $this, 'ajax_pedir_visto' ] );
        add_action( 'wp_ajax_nopriv_sinc_pedir_visto', [ $this, 'ajax_pedir_visto' ] );
        add_shortcode( 'sincetur_pedido_visto', [ $this, 'shortcode_visto' ] );
    }

    /**
     * Register an informational CPT for visa destination guides.
     */
    public function register_post_type(): void {
        $labels = [
            'name'               => __( 'Guias de Visto', 'sincetur-portal' ),
            'singular_name'      => __( 'Guia de Visto', 'sincetur-portal' ),
            'add_new'            => __( 'Novo Guia', 'sincetur-portal' ),
            'add_new_item'       => __( 'Adicionar Guia de Visto', 'sincetur-portal' ),
            'edit_item'          => __( 'Editar Guia de Visto', 'sincetur-portal' ),
            'new_item'           => __( 'Novo Guia', 'sincetur-portal' ),
            'view_item'          => __( 'Ver Guia', 'sincetur-portal' ),
            'search_items'       => __( 'Pesquisar Guias', 'sincetur-portal' ),
            'not_found'          => __( 'Nenhum guia encontrado.', 'sincetur-portal' ),
            'not_found_in_trash' => __( 'Nenhum guia no lixo.', 'sincetur-portal' ),
            'menu_name'          => __( 'Guias de Visto', 'sincetur-portal' ),
        ];

        register_post_type( 'sinc_visto', [
            'labels'       => $labels,
            'public'       => true,
            'show_in_menu' => 'sincetur-portal',
            'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
            'has_archive'  => true,
            'rewrite'      => [ 'slug' => 'vistos' ],
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-id-alt',
        ] );
    }

    /**
     * Shortcode: [sincetur_pedido_visto]
     */
    public function shortcode_visto( array $atts ): string {
        ob_start();
        include SINCETUR_PLUGIN_DIR . 'public/templates/visa-form.php';
        return ob_get_clean();
    }

    /**
     * AJAX: submit a visa advisory request.
     */
    public function ajax_pedir_visto(): void {
        check_ajax_referer( 'sinc_visto_nonce', 'nonce' );

        $nome_completo  = isset( $_POST['nome_completo'] )  ? sanitize_text_field( wp_unslash( $_POST['nome_completo'] ) )  : '';
        $email          = isset( $_POST['email'] )          ? sanitize_email( wp_unslash( $_POST['email'] ) )               : '';
        $telefone       = isset( $_POST['telefone'] )       ? sanitize_text_field( wp_unslash( $_POST['telefone'] ) )       : '';
        $passaporte_num = isset( $_POST['passaporte_num'] ) ? sanitize_text_field( wp_unslash( $_POST['passaporte_num'] ) ) : '';
        $pais_destino   = isset( $_POST['pais_destino'] )   ? sanitize_text_field( wp_unslash( $_POST['pais_destino'] ) )   : '';
        $tipo_visto     = isset( $_POST['tipo_visto'] )     ? sanitize_text_field( wp_unslash( $_POST['tipo_visto'] ) )     : '';
        $data_viagem    = isset( $_POST['data_viagem'] )    ? sanitize_text_field( wp_unslash( $_POST['data_viagem'] ) )    : null;
        $observacoes    = isset( $_POST['observacoes'] )    ? sanitize_textarea_field( wp_unslash( $_POST['observacoes'] ) ): '';

        if ( ! $nome_completo || ! is_email( $email ) || ! $pais_destino || ! $tipo_visto ) {
            wp_send_json_error( [ 'message' => __( 'Preencha todos os campos obrigatórios.', 'sincetur-portal' ) ] );
        }

        global $wpdb;
        $table = Sincetur_Installer::get_tables()['visa_requests'];

        $client_id = null;
        if ( is_user_logged_in() ) {
            $client_id = $this->get_client_id_by_wp_user( get_current_user_id() );
        }

        $inserted = $wpdb->insert(
            $table,
            [
                'client_id'      => $client_id,
                'nome_completo'  => $nome_completo,
                'email'          => $email,
                'telefone'       => $telefone,
                'passaporte_num' => $passaporte_num,
                'pais_destino'   => $pais_destino,
                'tipo_visto'     => $tipo_visto,
                'data_viagem'    => $data_viagem ?: null,
                'observacoes'    => $observacoes,
                'estado'         => 'pendente',
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            wp_send_json_error( [ 'message' => __( 'Erro ao registar o pedido. Tente novamente.', 'sincetur-portal' ) ] );
        }

        wp_send_json_success( [
            'message' => __( 'Pedido de assessoria de visto registado! Entraremos em contacto em breve.', 'sincetur-portal' ),
        ] );
    }

    /**
     * Get ERP client ID for a given WordPress user ID.
     */
    private function get_client_id_by_wp_user( int $wp_user_id ): ?int {
        global $wpdb;
        $table = Sincetur_Installer::get_tables()['erp_clients'];
        $id    = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE wp_user_id = %d LIMIT 1", $wp_user_id ) );
        return $id ? (int) $id : null;
    }
}
