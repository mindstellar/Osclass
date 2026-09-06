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

$admin = __get('admin');
/**
 * @return array
 */
function customFrmText()
{
    $admin  = __get('admin');
    $return = array();
    if (isset($admin['pk_i_id'])) {
        $return['admin_edit'] = true;
        $return['title']      = __('Edit admin');
        $return['action_frm'] = 'edit_post';
        $return['btn_text']   = __('Save');
    } else {
        $return['admin_edit'] = false;
        $return['title']      = __('Add admin');
        $return['action_frm'] = 'add_post';
        $return['btn_text']   = __('Add');
    }

    return $return;
}

osc_admin_page(array(
    'section' => __('Users'),
));

$aux = customFrmText();

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

osc_current_admin_theme_path('parts/header.php'); ?>
    <?php osc_admin_page_head($aux['title']); ?>
    <!-- add/edit admin form -->
    <div class="settings-user">
        <ul id="error_list"></ul>
        <?php osc_admin_form_open(array(
            'name'   => 'admin_form',
            'page'   => 'admins',
            'action' => $aux['action_frm'],
            'fields' => array('id' => $admin['pk_i_id'] ?? ''),
        )); ?>
            <?php AdminForm::js_validation(); ?>
            <?php osc_admin_form_row_open('', array('label_html' => __('Name <em>(required)</em>'))); ?>
                <?php osc_admin_text(array('row' => false, 'name' => 's_name', 'value' => $admin['s_name'] ?? '')); ?>
            <?php osc_admin_form_row_close(); ?>
            <?php osc_admin_form_row_open('', array('label_html' => __('Username <em>(required)</em>'))); ?>
                <?php osc_admin_text(array('row' => false, 'name' => 's_username', 'value' => $admin['s_username'] ?? '')); ?>
            <?php osc_admin_form_row_close(); ?>
            <?php osc_admin_form_row_open('', array('label_html' => __('E-mail <em>(required)</em>'))); ?>
                <?php osc_admin_text(array('row' => false, 'type' => 'email', 'name' => 's_email', 'value' => $admin['s_email'] ?? '')); ?>
            <?php osc_admin_form_row_close(); ?>
            <?php if (!$aux['admin_edit']
                      || ($aux['admin_edit']
                          && Params::getParam('id') != osc_logged_admin_id()
                          && Params::getParam('id') != '')
            ) { ?>
                <?php osc_admin_form_row_open('', array('label_html' => __('Admin type <em>(required)</em>'))); ?>
                    <?php osc_admin_select(array(
                        'row'      => false,
                        'name'     => 'b_moderator',
                        'selected' => (string)($admin['b_moderator'] ?? '0'),
                        'options'  => array('0' => __('Administrator'), '1' => __('Moderator')),
                        'help'     => __('Administrators have total control over all aspects of your installation, '
                                         . 'while moderators are only allowed to moderate listings, comments and media files'),
                    )); ?>
                <?php osc_admin_form_row_close(); ?>
            <?php } ?>
            <?php osc_admin_secret(array('name' => 's_password', 'label' => __('New password'), 'width' => 'text')); ?>
            <?php if ($aux['admin_edit']) {
                osc_admin_secret(array(
                    'name'  => 's_password2',
                    'label' => __('Confirm new password'),
                    'width' => 'text',
                    'help'  => __('Type your new password again'),
                ));
            } ?>

            <hr/>
            <?php osc_admin_secret(array(
                'name'      => 'old_password',
                'label'     => __('Your current password'),
                'width'     => 'text',
                'help_html' => __('For security, type <b>your current password</b>'),
            )); ?>

            <?php osc_run_hook('admin_profile_form', $admin); ?>
            <?php
            $formActions = array();
            if ($aux['admin_edit']) {
                $formActions[] = array('label' => __('Cancel'), 'url' => 'javascript:history.go(-1)', 'variant' => 'dim');
            }
            $formActions[] = array('label' => $aux['btn_text'], 'type' => 'submit', 'variant' => 'primary');
            osc_admin_form_close($formActions);
            ?>
    </div>
    <!-- /add user form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>