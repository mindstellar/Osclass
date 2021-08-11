$(function () {
    //Row actions
    $('.table .actions').each(function () {
        var $actions = $(this);
        var $rowActions = $('#table-row-actions');
        $(this).parents('tr').mouseenter(function (event) {
            event.preventDefault();
            var $containterOffset = $('.table-contains-actions').offset();
            $thisOffset = $(this).offset();
            var extra_offset = 0;
            colStatusBorderOuterWidth = $('td.col-status-border').outerWidth();
            if (!isNaN(colStatusBorderOuterWidth)) {
                extra_offset += colStatusBorderOuterWidth;
            }
            colStatusOuterWidth = $('td.col-status').outerWidth();
            if (!isNaN(colStatusOuterWidth)) {
                extra_offset += colStatusOuterWidth;
            }
            colBulkactionsOuterWidth = $('td.col-bulkactions').outerWidth();
            if (!isNaN(colBulkactionsOuterWidth)) {
                extra_offset += colBulkactionsOuterWidth;
            }
            $rowActions.empty().append($actions.clone()).css({
                width: $(this).width() - 13 - extra_offset,
                top: ($thisOffset.top - $containterOffset.top) + $(this).height() - 1,
                left: extra_offset
            }).show();
            $('tr').removeClass('collapsed-hover');
            if ($(this).parents('div.table-contains-actions').hasClass('table-collapsed')) {
                var thatRow = $(this);
                thatRow.next().addClass('collapsed-hover');
                $rowActions.mouseleave(function () {
                    $('tr').removeClass('collapsed-hover');
                });
            }
        });
    });
    $('.table-contains-actions').mouseleave(function () {
        $('tr').removeClass('collapsed-hover');
        $('#table-row-actions').hide();
    });
    //Close help
    $('.flashmessage .ico-close').on('click', function () {
        $(this).parents('.flashmessage').hide();
    });
    $('#help-box .ico-close').click(function () {
        $('#help-box').hide();
    });
    $('#table-row-actions').on('click', '.show-more-trigger', function () {
        $(this).parent().addClass('hover');
        return false;
    });
    oscTab();
    $(".close-dialog").on("click", function () {
        $(".ui-dialog-content").dialog("close");
        return false;
    });
});

function oscTab(callback) {
    $(".osc-tab").tabs();
}

function tabberAutomatic() {
    $('.tabber:hidden').show();
    $('.tabber h2').remove();
    $(osc.locales.string).parent().hide();
    $('[name*="' + osc.locales.current + '"],.' + osc.locales.current).parent().show();
}