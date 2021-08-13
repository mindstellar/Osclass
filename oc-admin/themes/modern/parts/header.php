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
<header id="header" class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar-wrapper">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a id="osc_toolbar_home" class="navbar-brand me-auto ps-1" target="_blank" href="<?php echo osc_base_url(); ?>">
            <i class="bi bi-house-fill"></i><?php echo osc_page_title() ?></a>
        <ul class="navbar-nav navbar-collapse collapse justify-content-end">
            <?php AdminToolbar::newInstance()->render(); ?>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"><div class="visually-hidden"><?php _e('User Menu'); ?></div></i>
                </a>
                <div class="dropdown-menu-dark dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <a class="dropdown-item"
                       href="<?php echo osc_admin_base_url(true) . '?page=admins&action=edit&id=' . osc_logged_admin_id(); ?>">
                        <i class="bi bi-person-lines-fill"></i> <?php _e('Edit Profile'); ?></a>
                    <a class="dropdown-item" href="<?php echo osc_admin_base_url(true).'?page=settings'?>"><i class="bi bi-gear-fill"></i>
                        Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo osc_admin_base_url(true) . '?action=logout'; ?>">
                        <i class="align-middle bi bi-box-arrow-right"></i> <?php _e('Sign out') ?></a>
                </div>
            </li>
        </ul>
    </div>
</header>
<div id="content" class="container-fluid">
    <div class="row flex-nowrap">
        <nav id="sidebar-wrapper" class="col-auto dashboard-sidebar">
            <?php osc_draw_admin_menu(); ?>
        </nav>
        <main id="content-render" class="col">
            <div id="content-head" class="row">
                <div class="col">
                    <?php osc_run_hook('admin_page_header'); ?>
                </div>
            </div>
            <div id="help-wrapper" class="row">
                <div class="col">
                    <div id="help-box" class="pt-2 collapse">
                        <div class="alert alert-warning alert-dismissible">
                            <?php osc_run_hook('help_box'); ?>
                            <button type="button" class="btn-close" data-bs-toggle="collapse" href="#help-box" aria-label="Close"></button>
                        </div>
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
