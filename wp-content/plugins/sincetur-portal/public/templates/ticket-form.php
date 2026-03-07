<?php
/**
 * Template: Ticket Purchase Form.
 * Available variables: $evento_id, $preco_geral, $preco_vip
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="sinc-ticket-form sinc-form-container" id="sinc-ticket-form-<?php echo esc_attr( $evento_id ); ?>">
    <h3><?php esc_html_e( 'Comprar Bilhete', 'sincetur-portal' ); ?></h3>

    <div class="sinc-alert" id="sinc-ticket-msg-<?php echo esc_attr( $evento_id ); ?>" style="display:none"></div>

    <form class="sinc-ticket-purchase" data-evento="<?php echo esc_attr( $evento_id ); ?>">
        <?php wp_nonce_field( 'sinc_ticket_nonce', 'sinc_ticket_wp_nonce' ); ?>

        <div class="sinc-field">
            <label for="sinc-tk-nome-<?php echo esc_attr( $evento_id ); ?>"><?php esc_html_e( 'Nome Completo *', 'sincetur-portal' ); ?></label>
            <input type="text" id="sinc-tk-nome-<?php echo esc_attr( $evento_id ); ?>" name="nome" class="sinc-input" required>
        </div>

        <div class="sinc-field">
            <label for="sinc-tk-email-<?php echo esc_attr( $evento_id ); ?>"><?php esc_html_e( 'E-mail *', 'sincetur-portal' ); ?></label>
            <input type="email" id="sinc-tk-email-<?php echo esc_attr( $evento_id ); ?>" name="email" class="sinc-input" required>
        </div>

        <div class="sinc-field">
            <label for="sinc-tk-tel-<?php echo esc_attr( $evento_id ); ?>"><?php esc_html_e( 'Telefone', 'sincetur-portal' ); ?></label>
            <input type="tel" id="sinc-tk-tel-<?php echo esc_attr( $evento_id ); ?>" name="telefone" class="sinc-input">
        </div>

        <div class="sinc-field">
            <label><?php esc_html_e( 'Tipo de Bilhete', 'sincetur-portal' ); ?></label>
            <div class="sinc-ticket-types">
                <?php if ( $preco_geral > 0 ) : ?>
                <label class="sinc-ticket-option">
                    <input type="radio" name="tipo_bilhete" value="geral" checked>
                    <?php esc_html_e( 'Geral', 'sincetur-portal' ); ?> — <?php echo esc_html( number_format( $preco_geral, 2, ',', '.' ) . ' AOA' ); ?>
                </label>
                <?php endif; ?>
                <?php if ( $preco_vip > 0 ) : ?>
                <label class="sinc-ticket-option">
                    <input type="radio" name="tipo_bilhete" value="vip">
                    <?php esc_html_e( 'VIP', 'sincetur-portal' ); ?> — <?php echo esc_html( number_format( $preco_vip, 2, ',', '.' ) . ' AOA' ); ?>
                </label>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="button button-primary sinc-btn-submit">
            <?php esc_html_e( 'Reservar Bilhete', 'sincetur-portal' ); ?>
        </button>
    </form>
</div>
