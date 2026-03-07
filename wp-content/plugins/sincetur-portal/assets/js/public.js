/* SINCETUR Portal – Public JS */
/* global jQuery, sinceturAjax */
(function ($) {
    'use strict';

    // ── Ticket Purchase Form ────────────────────────────────────────────────
    $(document).on('submit', '.sinc-ticket-purchase', function (e) {
        e.preventDefault();

        var $form   = $(this);
        var $btn    = $form.find('.sinc-btn-submit');
        var eventoId = $form.data('evento');
        var $msg    = $('#sinc-ticket-msg-' + eventoId);
        var nonce   = $form.find('[name="sinc_ticket_wp_nonce"]').val();

        $btn.prop('disabled', true).text(sinceturAjax.i18n.reserving);
        $msg.hide().removeClass('sinc-success sinc-error');

        $.post(sinceturAjax.url, {
            action:      'sinc_comprar_ticket',
            nonce:        nonce,
            evento_id:   eventoId,
            nome:         $form.find('[name="nome"]').val(),
            email:        $form.find('[name="email"]').val(),
            telefone:     $form.find('[name="telefone"]').val(),
            tipo_bilhete: $form.find('[name="tipo_bilhete"]:checked').val() || 'geral'
        })
        .done(function (res) {
            if (res.success) {
                $msg.addClass('sinc-success').text(
                    res.data.message + ' ' +
                    'Código: ' + res.data.codigo + ' | Preço: ' + res.data.preco
                ).show();
                $form[0].reset();
            } else {
                $msg.addClass('sinc-error').text(res.data.message).show();
            }
        })
        .fail(function () {
            $msg.addClass('sinc-error').text(sinceturAjax.i18n.error_server).show();
        })
        .always(function () {
            $btn.prop('disabled', false).text('Reservar Bilhete');
        });
    });

    // ── Visa Advisory Form ──────────────────────────────────────────────────
    $(document).on('submit', '#sinc-visa-request-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn  = $form.find('.sinc-btn-submit');
        var $msg  = $('#sinc-visa-msg');
        var nonce = $form.find('[name="sinc_visto_wp_nonce"]').val();

        $btn.prop('disabled', true).text(sinceturAjax.i18n.sending);
        $msg.hide().removeClass('sinc-success sinc-error');

        $.post(sinceturAjax.url, {
            action:         'sinc_pedir_visto',
            nonce:           nonce,
            nome_completo:  $form.find('[name="nome_completo"]').val(),
            email:           $form.find('[name="email"]').val(),
            telefone:        $form.find('[name="telefone"]').val(),
            passaporte_num:  $form.find('[name="passaporte_num"]').val(),
            pais_destino:    $form.find('[name="pais_destino"]').val(),
            tipo_visto:      $form.find('[name="tipo_visto"]').val(),
            data_viagem:     $form.find('[name="data_viagem"]').val(),
            observacoes:     $form.find('[name="observacoes"]').val()
        })
        .done(function (res) {
            if (res.success) {
                $msg.addClass('sinc-success').text(res.data.message).show();
                $form[0].reset();
            } else {
                $msg.addClass('sinc-error').text(res.data.message).show();
            }
        })
        .fail(function () {
            $msg.addClass('sinc-error').text(sinceturAjax.i18n.error_server).show();
        })
        .always(function () {
            $btn.prop('disabled', false).text('Enviar Pedido');
        });
    });

}(jQuery));
