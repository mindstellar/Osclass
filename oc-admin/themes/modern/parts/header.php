<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
} ?>
<!DOCTYPE html>
<html lang="<?php echo substr(osc_current_admin_locale(), 0, 2); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo osc_apply_filter('admin_title', osc_page_title() . ' - Osclass'); ?></title>
    <meta name="title" content="<?php echo osc_apply_filter('admin_title', osc_page_title() . ' - Osclass'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="<?php echo osc_current_admin_locale(); ?>"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <?php osc_run_hook('admin_header'); ?>
</head>

<body class="<?php echo implode(' ', osc_apply_filter('admin_body_class', array())); ?>">
<?php AdminToolbar::newInstance()->render(); ?>
<div id="content" class="container-fluid">
    <div class="row flex-nowrap">
        <div class="col-auto dashboard-sidebar">
            <?php osc_draw_admin_menu(); ?>
        </div>
        <div id="content-render" class="col">
            <div id="content-head" class="row">
                <div class="col">
                    <div>
                        <?php osc_run_hook('admin_page_header'); ?>
                    </div>
                </div>
            </div>
            <div id="help-wrapper" class="row">
                <div class="col">
                    <div id="help-box" class="alert alert-dismissible shadow-sm fade collapse">
                        <?php osc_run_hook('help_box'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <div id="message-wrapper" class="row">
                <div class="col">
                    <?php osc_show_flash_message('admin'); ?>
                    <div id="jsMessage" class="jsMessage flashmessage flashmessage-info hide shadow-sm">
                        <a class="btn ico btn-mini ico-close">×</a>
                        <p></p>
                    </div>
                </div>
            </div>
            <div id="content-page" class="row">
                <div class="col">
                        <div class="row-wrapper <?php echo osc_apply_filter('render-wrapper', ''); ?>">
