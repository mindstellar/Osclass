<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2014 Osclass (original work, licensed under the Apache License 2.0)
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. The original
 * Osclass code it derives from was licensed under the Apache License 2.0.
 * See LICENSE (GPL-3.0) and LICENSE-APACHE (Apache-2.0).
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

$rule = __get('rule');

/**
 * @return array
 */
function customFrmText()
{
    $rule   = __get('rule');
    $return = array();

    if (isset($rule['pk_i_id'])) {
        $return['edit']       = true;
        $return['title']      = __('Edit rule');
        $return['action_frm'] = 'edit_ban_rule_post';
        $return['btn_text']   = __('Update rule');
    } else {
        $return['edit']       = false;
        $return['title']      = __('Add new ban rule');
        $return['action_frm'] = 'create_ban_rule_post';
        $return['btn_text']   = __('Add new ban rule');
    }

    return $return;
}

osc_admin_page(array(
    'section' => __('Users'),
));

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    $aux = customFrmText();

    return sprintf('%s &raquo; %s', $aux['title'], $string);
}

osc_add_filter('admin_title', 'customPageTitle');

//customize Head
function customHead()
{
}

osc_add_hook('admin_header', 'customHead', 10);

$aux = customFrmText();
?>
<?php osc_current_admin_theme_path('parts/header.php'); ?>
<?php osc_admin_page_head($aux['title']); ?>
<div class="settings-user">
    <ul id="error_list"></ul>
    <?php osc_admin_form_open(array(
        'name'   => 'register',
        'page'   => 'users',
        'action' => $aux['action_frm'],
        'fields' => array('id' => $rule['pk_i_id'] ?? ''),
    )); ?>
        <?php osc_admin_text(array(
            'name'  => 's_name',
            'label' => __('Ban name / Reason'),
            'value' => $rule['s_name'] ?? '',
        )); ?>
        <?php osc_admin_text(array(
            'name'  => 's_ip',
            'label' => __('IP rule'),
            'value' => $rule['s_ip'] ?? '',
            'help'  => __('(e.g. 192.168.10-20.*)'),
        )); ?>
        <?php osc_admin_text(array(
            'name'  => 's_email',
            'label' => __('E-mail rule'),
            'value' => $rule['s_email'] ?? '',
            'help'  => __('(e.g. *@badsite.com, *@subdomain.badsite.com, *@*badsite.com)'),
        )); ?>
    <?php osc_admin_form_close(array(
        array('label' => $aux['btn_text'], 'type' => 'submit', 'variant' => 'primary'),
    )); ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
