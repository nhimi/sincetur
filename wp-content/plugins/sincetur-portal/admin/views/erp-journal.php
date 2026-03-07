<?php
/**
 * Admin View: ERP – Journal (Diário Contabilístico)
 * Variables: $action, $id
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$t = Sincetur_Installer::get_tables();

if ( $action === 'new' ) :
    $accounts = $wpdb->get_results( "SELECT id, codigo, descricao FROM {$t['chart_of_accounts']} WHERE nivel >= 2 AND activa = 1 ORDER BY codigo" );
?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Novo Lançamento Contabilístico', 'sincetur-portal' ); ?></h1>
    <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-journal' ) ); ?>">&larr; <?php esc_html_e( 'Listar', 'sincetur-portal' ); ?></a>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sinc-form">
        <?php wp_nonce_field( 'sinc_save_journal' ); ?>
        <input type="hidden" name="action" value="sinc_save_journal">

        <table class="form-table">
            <tr><th><?php esc_html_e( 'Data', 'sincetur-portal' ); ?></th><td>
                <input type="date" name="data" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" required>
            </td></tr>
            <tr><th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th><td>
                <input type="text" name="descricao" class="large-text">
            </td></tr>
        </table>

        <h3><?php esc_html_e( 'Linhas do Lançamento', 'sincetur-portal' ); ?></h3>
        <p class="description"><?php esc_html_e( 'A soma dos Débitos deve ser igual à soma dos Créditos (partidas dobradas).', 'sincetur-portal' ); ?></p>

        <table class="widefat" id="sinc-journal-lines">
            <thead><tr>
                <th><?php esc_html_e( 'Conta', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Débito (AOA)', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Crédito (AOA)', 'sincetur-portal' ); ?></th>
                <th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
                <?php for ( $i = 0; $i < 2; $i++ ) : ?>
                <tr class="sinc-journal-line">
                    <td>
                        <select name="lines[<?php echo $i; ?>][conta_id]" required>
                            <option value=""><?php esc_html_e( '— Conta —', 'sincetur-portal' ); ?></option>
                            <?php foreach ( $accounts as $acc ) : ?>
                                <option value="<?php echo esc_attr( $acc->id ); ?>"><?php echo esc_html( $acc->codigo . ' – ' . $acc->descricao ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="lines[<?php echo $i; ?>][debito]" value="0.00" min="0" class="small-text"></td>
                    <td><input type="number" step="0.01" name="lines[<?php echo $i; ?>][credito]" value="0.00" min="0" class="small-text"></td>
                    <td><input type="text" name="lines[<?php echo $i; ?>][descricao]" class="regular-text"></td>
                    <td><button type="button" class="button sinc-remove-journal-line">✕</button></td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot><tr><td colspan="5">
                <button type="button" class="button" id="sinc-add-journal-line"><?php esc_html_e( '+ Linha', 'sincetur-portal' ); ?></button>
                <strong style="float:right"><?php esc_html_e( 'Total Débito:', 'sincetur-portal' ); ?> <span id="sinc-total-debit">0,00</span> | <?php esc_html_e( 'Total Crédito:', 'sincetur-portal' ); ?> <span id="sinc-total-credit">0,00</span></strong>
            </td></tr></tfoot>
        </table>

        <p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Registar Lançamento', 'sincetur-portal' ); ?></button></p>
    </form>
</div>

<?php else : // list ?>
<div class="wrap sinc-wrap">
    <h1><?php esc_html_e( 'Diário Contabilístico', 'sincetur-portal' ); ?>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=sinc-journal&action=new' ) ); ?>"><?php esc_html_e( 'Novo Lançamento', 'sincetur-portal' ); ?></a>
    </h1>
    <?php if ( isset( $_GET['saved'] ) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Lançamento registado.', 'sincetur-portal' ); ?></p></div>
    <?php endif; ?>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr>
            <th><?php esc_html_e( 'Número', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Data', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Descrição', 'sincetur-portal' ); ?></th>
            <th><?php esc_html_e( 'Criado Por', 'sincetur-portal' ); ?></th>
        </tr></thead>
        <tbody>
            <?php $entries = $wpdb->get_results( "SELECT e.*, u.display_name AS autor FROM {$t['journal_entries']} e LEFT JOIN {$wpdb->users} u ON u.ID = e.criado_por ORDER BY e.data DESC LIMIT 50" ); ?>
            <?php if ( empty( $entries ) ) : ?>
                <tr><td colspan="4"><?php esc_html_e( 'Nenhum lançamento encontrado.', 'sincetur-portal' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $entries as $entry ) : ?>
                <tr>
                    <td><?php echo esc_html( $entry->numero ); ?></td>
                    <td><?php echo esc_html( $entry->data ); ?></td>
                    <td><?php echo esc_html( $entry->descricao ?: '—' ); ?></td>
                    <td><?php echo esc_html( $entry->autor ?: '—' ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
