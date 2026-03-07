<?php
/**
 * Admin View: CRM – Clients
 * Variables: $action (list|new|edit), $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = Sincetur_Installer::get_tables()['erp_clients'];

$provincias_angola = [
    'Bengo','Benguela','Bié','Cabinda','Cuando Cubango','Cuanza Norte','Cuanza Sul',
    'Cunene','Huambo','Huíla','Luanda','Lunda Norte','Lunda Sul','Malange','Moxico',
    'Namibe','Uíge','Zaire',
];

if ( $action === 'new' || $action === 'edit' ) :
    $client = ( $action === 'edit' && $id ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) : null;
?>
<div class="wrap sinc-wrap">
    <h1><?php echo $client ? esc_html__( 'Editar Cliente', 'sincetur-portal' ) : esc_html__( 'Novo Cliente', 'sincetur-portal' ); ?></h1>
    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-clients' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sinc-form">
        <?php wp_nonce_field( 'sinc_save_client' ); ?>
        <input type="hidden" name="action" value="sinc_save_client">
        <?php if ( $client ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $client->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><?php esc_html_e( 'Nome *', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="nome" value="<?php echo esc_attr( $client->nome ?? '' ); ?>" class="regular-text" required>
            </td></tr>
            <tr><th><?php esc_html_e( 'NIF', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="nif" value="<?php echo esc_attr( $client->nif ?? '' ); ?>" class="regular-text">
            </td></tr>
            <tr><th><?php esc_html_e( 'E-mail', 'sincetur-portal' ); ?></th><td>
                <input type="email" name="email" value="<?php echo esc_attr( $client->email ?? '' ); ?>" class="regular-text">
            </td></tr>
            <tr><th><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="telefone" value="<?php echo esc_attr( $client->telefone ?? '' ); ?>" class="regular-text">
            </td></tr>
            <tr><th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th><td>
                <select name="tipo">
                    <option value="particular" <?php selected( $client->tipo ?? 'particular', 'particular' ); ?>><?php esc_html_e( 'Particular', 'sincetur-portal' ); ?></option>
                    <option value="empresa"    <?php selected( $client->tipo ?? '',            'empresa'    ); ?>><?php esc_html_e( 'Empresa',    'sincetur-portal' ); ?></option>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Morada', 'sincetur-portal' ); ?></th><td>
                <textarea name="morada" rows="3" class="large-text"><?php echo esc_textarea( $client->morada ?? '' ); ?></textarea>
            </td></tr>
            <tr><th><?php esc_html_e( 'Província', 'sincetur-portal' ); ?></th><td>
                <select name="provincia">
                    <option value=""><?php esc_html_e( '— Seleccione —', 'sincetur-portal' ); ?></option>
                    <?php foreach ( $provincias_angola as $prov ) : ?>
                        <option value="<?php echo esc_attr( $prov ); ?>" <?php selected( $client->provincia ?? '', $prov ); ?>><?php echo esc_html( $prov ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'País', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="pais" value="<?php echo esc_attr( $client->pais ?? 'Angola' ); ?>" class="regular-text">
            </td></tr>
        </table>
        <p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar Cliente', 'sincetur-portal' ); ?></button></p>
    </form>
</div>

<?php else : // list ?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Clientes', 'sincetur-portal' ); ?>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-clients&action=new' ) ); ?>"><?php esc_html_e( 'Novo Cliente', 'sincetur-portal' ); ?></a>
    </h1>
    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Cliente guardado.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>

    <form method="get" style="margin-bottom:10px">
        <input type="hidden" name="page" value="sinc-clients">
        <input type="search" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Pesquisar…', 'sincetur-portal' ); ?>">
        <button type="submit" class="button"><?php esc_html_e( 'Pesquisar', 'sincetur-portal' ); ?></button>
    </form>

    <?php
    $search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
    $clients = Sincetur_Customers::get_clients( [ 'search' => $search, 'limit' => 50 ] );
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Nome', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'NIF', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'E-mail', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Província', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Acções', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
        <?php if ( empty( $clients ) ) : ?>
            <tr><td colspan="7"><?php esc_html_e( 'Nenhum cliente encontrado.', 'sincetur-portal' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $clients as $c ) : ?>
            <tr>
                <td><strong><?php echo esc_html( $c->nome ); ?></strong></td>
                <td><?php echo esc_html( $c->nif ?: '—' ); ?></td>
                <td><?php echo esc_html( $c->email ?: '—' ); ?></td>
                <td><?php echo esc_html( $c->telefone ?: '—' ); ?></td>
                <td><?php echo esc_html( ucfirst( $c->tipo ) ); ?></td>
                <td><?php echo esc_html( $c->provincia ?: '—' ); ?></td>
                <td>
                    <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-clients&action=edit&id={$c->id}" ) ); ?>"><?php esc_html_e( 'Editar', 'sincetur-portal' ); ?></a>
                    | <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-processes&client_id={$c->id}" ) ); ?>"><?php esc_html_e( 'Processos', 'sincetur-portal' ); ?></a>
                    | <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-invoices&client_id={$c->id}" ) ); ?>"><?php esc_html_e( 'Facturas', 'sincetur-portal' ); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
