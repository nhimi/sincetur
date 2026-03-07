<?php
/**
 * Admin View: CRM – Processes (Acompanhamento de Processos)
 * Variables: $action (list|new|edit|view), $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$t = Sincetur_Installer::get_tables();

if ( $action === 'new' || $action === 'edit' ) :
    $process = ( $action === 'edit' && $id ) ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t['processes']} WHERE id = %d", $id ) ) : null;
    $clients = Sincetur_Customers::get_clients( [ 'limit' => 200 ] );
    $users   = get_users( [ 'role__in' => [ 'administrator', 'editor' ] ] );
?>
<div class="wrap sinc-wrap">
    <h1><?php echo $process ? esc_html__( 'Editar Processo', 'sincetur-portal' ) : esc_html__( 'Novo Processo', 'sincetur-portal' ); ?></h1>
    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-processes' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sinc-form">
        <?php wp_nonce_field( 'sinc_save_process' ); ?>
        <input type="hidden" name="action" value="sinc_save_process">
        <?php if ( $process ) : ?><input type="hidden" name="id" value="<?php echo esc_attr( $process->id ); ?>"><?php endif; ?>

        <table class="form-table">
            <tr><th><?php esc_html_e( 'Cliente *', 'sincetur-portal' ); ?></th><td>
                <select name="client_id" required>
                    <option value=""><?php esc_html_e( '— Seleccione —', 'sincetur-portal' ); ?></option>
                    <?php foreach ( $clients as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $process->client_id ?? '', $c->id ); ?>><?php echo esc_html( $c->nome ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Tipo *', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="tipo" value="<?php echo esc_attr( $process->tipo ?? '' ); ?>" class="regular-text" required placeholder="<?php esc_attr_e( 'ex: Reserva de Tour, Pedido de Visto, …', 'sincetur-portal' ); ?>">
            </td></tr>
            <tr><th><?php esc_html_e( 'Título *', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="titulo" value="<?php echo esc_attr( $process->titulo ?? '' ); ?>" class="large-text" required>
            </td></tr>
            <tr><th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th><td>
                <select name="estado">
                    <?php foreach ( [ 'aberto'=>'Aberto','em_curso'=>'Em Curso','concluido'=>'Concluído','cancelado'=>'Cancelado' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $process->estado ?? 'aberto', $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Prioridade', 'sincetur-portal' ); ?></th><td>
                <select name="prioridade">
                    <?php foreach ( [ 'baixa'=>'Baixa','normal'=>'Normal','alta'=>'Alta','urgente'=>'Urgente' ] as $v => $l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $process->prioridade ?? 'normal', $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Responsável', 'sincetur-portal' ); ?></th><td>
                <select name="responsavel">
                    <option value=""><?php esc_html_e( '— Nenhum —', 'sincetur-portal' ); ?></option>
                    <?php foreach ( $users as $u ) : ?>
                        <option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $process->responsavel ?? '', $u->ID ); ?>><?php echo esc_html( $u->display_name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td></tr>
            <tr><th><?php esc_html_e( 'Data Início', 'sincetur-portal' ); ?></th><td>
                <input type="date" name="data_inicio" value="<?php echo esc_attr( $process->data_inicio ?? '' ); ?>">
            </td></tr>
            <tr><th><?php esc_html_e( 'Data Conclusão', 'sincetur-portal' ); ?></th><td>
                <input type="date" name="data_fim" value="<?php echo esc_attr( $process->data_fim ?? '' ); ?>">
            </td></tr>
            <tr><th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th><td>
                <textarea name="descricao" rows="5" class="large-text"><?php echo esc_textarea( $process->descricao ?? '' ); ?></textarea>
            </td></tr>
        </table>
        <p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Guardar Processo', 'sincetur-portal' ); ?></button></p>
    </form>
</div>

<?php elseif ( $action === 'view' && $id ) : ?>

<?php
    $process = $wpdb->get_row( $wpdb->prepare( "SELECT p.*, c.nome AS client_nome FROM {$t['processes']} p LEFT JOIN {$t['erp_clients']} c ON c.id = p.client_id WHERE p.id = %d", $id ) );
    $notes   = $wpdb->get_results( $wpdb->prepare( "SELECT n.*, u.display_name AS autor FROM {$t['process_notes']} n LEFT JOIN {$wpdb->users} u ON u.ID = n.autor_id WHERE n.process_id = %d ORDER BY n.created_at", $id ) );
?>
<div class="wrap sinc-wrap">
    <h1><?php echo esc_html( $process->titulo ?? '' ); ?>
        <a class="button" href="<?php echo esc_url( admin_url( "admin.php?page=sinc-processes&action=edit&id={$id}" ) ); ?>"><?php esc_html_e( 'Editar', 'sincetur-portal' ); ?></a>
        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-processes' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>
    </h1>
    <table class="form-table">
        <tr><th><?php esc_html_e( 'Referência', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $process->referencia ?? '' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $process->client_nome ?? '—' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Tipo', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $process->tipo ?? '' ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th><td><span class="sinc-badge sinc-badge-<?php echo esc_attr( $process->estado ?? '' ); ?>"><?php echo esc_html( ucfirst( $process->estado ?? '' ) ); ?></span></td></tr>
        <tr><th><?php esc_html_e( 'Prioridade', 'sincetur-portal' ); ?></th><td><?php echo esc_html( ucfirst( $process->prioridade ?? '' ) ); ?></td></tr>
        <tr><th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th><td><?php echo esc_html( $process->descricao ?? '—' ); ?></td></tr>
    </table>

    <h2><?php esc_html_e( 'Notas / Acompanhamento', 'sincetur-portal' ); ?></h2>
    <?php foreach ( $notes as $note ) : ?>
        <div class="sinc-note-card">
            <p><strong><?php echo esc_html( $note->autor ); ?></strong> — <em><?php echo esc_html( $note->created_at ); ?></em></p>
            <p><?php echo nl2br( esc_html( $note->nota ) ); ?></p>
        </div>
    <?php endforeach; ?>

    <h3><?php esc_html_e( 'Adicionar Nota', 'sincetur-portal' ); ?></h3>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'sinc_save_note' ); ?>
        <input type="hidden" name="action" value="sinc_save_note">
        <input type="hidden" name="process_id" value="<?php echo esc_attr( $id ); ?>">
        <textarea name="nota" rows="4" class="large-text" required></textarea><br>
        <button type="submit" class="button button-primary"><?php esc_html_e( 'Adicionar Nota', 'sincetur-portal' ); ?></button>
    </form>
</div>

<?php else : // list ?>

<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Processos', 'sincetur-portal' ); ?>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-processes&action=new' ) ); ?>"><?php esc_html_e( 'Novo Processo', 'sincetur-portal' ); ?></a>
    </h1>
    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Processo guardado.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>
    <?php $processes = Sincetur_Customers::get_processes(); ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Referência', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Cliente', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Título', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Estado', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Prioridade', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Data Início', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Acções', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
        <?php if ( empty( $processes ) ) : ?>
            <tr><td colspan="7"><?php esc_html_e( 'Nenhum processo encontrado.', 'sincetur-portal' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $processes as $proc ) : ?>
            <tr>
                <td><code><?php echo esc_html( $proc->referencia ); ?></code></td>
                <td><?php echo esc_html( $proc->client_nome ?? '—' ); ?></td>
                <td><?php echo esc_html( $proc->titulo ); ?></td>
                <td><span class="sinc-badge sinc-badge-<?php echo esc_attr( $proc->estado ); ?>"><?php echo esc_html( ucfirst( $proc->estado ) ); ?></span></td>
                <td><?php echo esc_html( ucfirst( $proc->prioridade ) ); ?></td>
                <td><?php echo esc_html( $proc->data_inicio ?: '—' ); ?></td>
                <td>
                    <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-processes&action=view&id={$proc->id}" ) ); ?>"><?php esc_html_e( 'Ver', 'sincetur-portal' ); ?></a>
                    | <a href="<?php echo esc_url( admin_url( "admin.php?page=sinc-processes&action=edit&id={$proc->id}" ) ); ?>"><?php esc_html_e( 'Editar', 'sincetur-portal' ); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
