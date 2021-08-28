$(function () {
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