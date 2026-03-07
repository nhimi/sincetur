/* SINCETUR Portal – Admin JS */
/* global jQuery */
(function ($) {
    'use strict';

    // ── Invoice Line Items ──────────────────────────────────────────────────
    function recalcTotals() {
        $('#sinc-items-table tbody .sinc-item-row').each(function () {
            var qty   = parseFloat($(this).find('.sinc-qty').val())        || 0;
            var price = parseFloat($(this).find('.sinc-unit-price').val())  || 0;
            var total = (qty * price).toFixed(2);
            $(this).find('.sinc-line-total').val(total);
        });
    }

    $(document).on('input', '.sinc-qty, .sinc-unit-price', recalcTotals);

    var rowIndex = $('#sinc-items-table tbody .sinc-item-row').length;

    $('#sinc-add-item').on('click', function () {
        var firstRow = $('#sinc-items-table tbody .sinc-item-row:first').clone();
        firstRow.find('input').val('');
        firstRow.find('input[type="number"]').val('0');
        firstRow.find('select').prop('selectedIndex', 0);
        // Update name indices
        firstRow.find('[name]').each(function () {
            var name = $(this).attr('name').replace(/\[\d+\]/, '[' + rowIndex + ']');
            $(this).attr('name', name);
        });
        $('#sinc-items-table tbody').append(firstRow);
        rowIndex++;
    });

    $(document).on('click', '.sinc-remove-row', function () {
        if ($('#sinc-items-table tbody .sinc-item-row').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    // ── Journal Lines ───────────────────────────────────────────────────────
    function recalcJournal() {
        var totalDebit  = 0;
        var totalCredit = 0;
        $('.sinc-journal-line').each(function () {
            totalDebit  += parseFloat($(this).find('[name$="[debito]"]').val())  || 0;
            totalCredit += parseFloat($(this).find('[name$="[credito]"]').val()) || 0;
        });
        $('#sinc-total-debit').text(totalDebit.toFixed(2).replace('.', ','));
        $('#sinc-total-credit').text(totalCredit.toFixed(2).replace('.', ','));
    }

    $(document).on('input', '[name$="[debito]"], [name$="[credito]"]', recalcJournal);

    var jLineIndex = $('.sinc-journal-line').length;

    $('#sinc-add-journal-line').on('click', function () {
        var firstLine = $('.sinc-journal-line:first').clone();
        firstLine.find('input[type="number"]').val('0.00');
        firstLine.find('input[type="text"]').val('');
        firstLine.find('select').prop('selectedIndex', 0);
        firstLine.find('[name]').each(function () {
            var name = $(this).attr('name').replace(/\[\d+\]/, '[' + jLineIndex + ']');
            $(this).attr('name', name);
        });
        $(this).closest('tfoot').before(firstLine);
        jLineIndex++;
    });

    $(document).on('click', '.sinc-remove-journal-line', function () {
        if ($('.sinc-journal-line').length > 2) {
            $(this).closest('tr').remove();
            recalcJournal();
        }
    });

}(jQuery));
