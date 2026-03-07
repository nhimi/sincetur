<?php
/**
 * Template: Visa Advisory Request Form.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="sinc-visa-form sinc-form-container">
    <h3><?php esc_html_e( 'Pedido de Assessoria de Visto', 'sincetur-portal' ); ?></h3>
    <div class="sinc-alert" id="sinc-visa-msg" style="display:none"></div>

    <form id="sinc-visa-request-form">
        <?php wp_nonce_field( 'sinc_visto_nonce', 'sinc_visto_wp_nonce' ); ?>

        <div class="sinc-field">
            <label for="sinc-visto-nome"><?php esc_html_e( 'Nome Completo *', 'sincetur-portal' ); ?></label>
            <input type="text" id="sinc-visto-nome" name="nome_completo" class="sinc-input" required>
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-email"><?php esc_html_e( 'E-mail *', 'sincetur-portal' ); ?></label>
            <input type="email" id="sinc-visto-email" name="email" class="sinc-input" required>
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-telefone"><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></label>
            <input type="tel" id="sinc-visto-telefone" name="telefone" class="sinc-input">
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-passaporte"><?php esc_html_e( 'Nº do Passaporte', 'sincetur-portal' ); ?></label>
            <input type="text" id="sinc-visto-passaporte" name="passaporte_num" class="sinc-input">
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-pais"><?php esc_html_e( 'País de Destino *', 'sincetur-portal' ); ?></label>
            <input type="text" id="sinc-visto-pais" name="pais_destino" class="sinc-input" required>
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-tipo"><?php esc_html_e( 'Tipo de Visto *', 'sincetur-portal' ); ?></label>
            <select id="sinc-visto-tipo" name="tipo_visto" class="sinc-input" required>
                <option value=""><?php esc_html_e( '— Seleccione —', 'sincetur-portal' ); ?></option>
                <option value="turismo"><?php esc_html_e( 'Turismo', 'sincetur-portal' ); ?></option>
                <option value="negócios"><?php esc_html_e( 'Negócios', 'sincetur-portal' ); ?></option>
                <option value="estudante"><?php esc_html_e( 'Estudante', 'sincetur-portal' ); ?></option>
                <option value="trabalho"><?php esc_html_e( 'Trabalho', 'sincetur-portal' ); ?></option>
                <option value="transit"><?php esc_html_e( 'Trânsito', 'sincetur-portal' ); ?></option>
                <option value="outro"><?php esc_html_e( 'Outro', 'sincetur-portal' ); ?></option>
            </select>
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-data"><?php esc_html_e( 'Data Prevista de Viagem', 'sincetur-portal' ); ?></label>
            <input type="date" id="sinc-visto-data" name="data_viagem" class="sinc-input">
        </div>

        <div class="sinc-field">
            <label for="sinc-visto-obs"><?php esc_html_e( 'Observações', 'sincetur-portal' ); ?></label>
            <textarea id="sinc-visto-obs" name="observacoes" rows="4" class="sinc-input"></textarea>
        </div>

        <button type="submit" class="button button-primary sinc-btn-submit">
            <?php esc_html_e( 'Enviar Pedido', 'sincetur-portal' ); ?>
        </button>
    </form>
</div>
