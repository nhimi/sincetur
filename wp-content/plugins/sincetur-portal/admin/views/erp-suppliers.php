<?php
/**
 * Admin View: ERP – Suppliers
 * Variables from caller: $action (list|new|edit), $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = Sincetur_Installer::get_tables()['erp_suppliers'];

if ( $action === 'new' || $action === 'edit' ) :
    $supplier = ( $action === 'edit' && $id ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) : null;
?>
<div class="wrap sinc-wrap">
    <h1><?php echo $supplier ? esc_html__( 'Editar Fornecedor', 'sincetur-portal' ) : esc_html__( 'Novo Fornecedor', 'sincetur-portal' ); ?></h1>
    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-suppliers' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sinc-form">
        <?php wp_nonce_field( 'sinc_save_supplier' ); ?>
        <input type="hidden" name="action" value="sinc_save_supplier">
        <?php if ( $supplier ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $supplier->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <?php
            $fields = [
                'nome'      => [ 'label' => __( 'Nome', 'sincetur-portal' ),      'type' => 'text',     'required' => true ],
                'nif'       => [ 'label' => __( 'NIF', 'sincetur-portal' ),       'type' => 'text',     'required' => false ],
                'email'     => [ 'label' => __( 'E-mail', 'sincetur-portal' ),    'type' => 'email',    'required' => false ],
                'telefone'  => [ 'label' => __( 'Telefone', 'sincetur-portal' ),  'type' => 'text',     'required' => false ],
                'morada'    => [ 'label' => __( 'Morada', 'sincetur-portal' ),    'type' => 'textarea', 'required' => false ],
                'pais'      => [ 'label' => __( 'País', 'sincetur-portal' ),      'type' => 'text',     'required' => false ],
                'categoria' => [ 'label' => __( 'Categoria', 'sincetur-portal' ), 'type' => 'text',     'required' => false ],
            ];
            foreach ( $fields as $name => $f ) :
                $val = $supplier->$name ?? ( $name === 'pais' ? 'Angola' : '' );
            ?>
            <tr>
                <th><label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $f['label'] ); ?><?php echo $f['required'] ? ' *' : ''; ?></label></th>
                <td>
                    <?php if ( $f['type'] === 'textarea' ) : ?>
                        <textarea id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $val ); ?></textarea>
                    <?php else : ?>
                        <input type="<?php echo esc_attr( $f['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>" class="regular-text" <?php echo $f['required'] ? 'required' : ''; ?>>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar Fornecedor', 'sincetur-portal' ); ?></button></p>
    </form>
</div>

<?php else : // list ?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Fornecedores', 'sincetur-portal' ); ?>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-suppliers&action=new' ) ); ?>"><?php esc_html_e( 'Novo Fornecedor', 'sincetur-portal' ); ?></a>
    </h1>
    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Fornecedor guardado.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Nome', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'NIF', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'E-mail', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Categoria', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'País', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Acções', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
            <?php $rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY nome" ); ?>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="7"><?php esc_html_e( 'Nenhum fornecedor registado.', 'sincetur-portal' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $rows as $row ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $row->nome ); ?></strong></td>
                    <td><?php echo esc_html( $row->nif ?: '—' ); ?></td>
                    <td><?php echo esc_html( $row->email ?: '—' ); ?></td>
                    <td><?php echo esc_html( $row->telefone ?: '—' ); ?></td>
                    <td><?php echo esc_html( $row->categoria ?: '—' ); ?></td>
                    <td><?php echo esc_html( $row->pais ); ?></td>
                    <td><a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-suppliers&action=edit&id={$row->id}" ) ); ?>"><?php esc_html_e( 'Editar', 'sincetur-portal' ); ?></a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
