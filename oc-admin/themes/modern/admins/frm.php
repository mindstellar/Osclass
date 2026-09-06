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
        <form name="admin_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="action" value="<?php echo $aux['action_frm']; ?>"/>
            <input type="hidden" name="page" value="admins"/>
            <?php AdminForm::primary_input_hidden($admin); ?>
            <?php AdminForm::js_validation(); ?>
            <fieldset>
                <div class="form-horizontal">
                    <?php osc_admin_form_row_open(__('Name <em>(required)</em>')); ?>
                        <?php AdminForm::name_text($admin); ?>
                    <?php osc_admin_form_row_close(); ?>
                    <?php osc_admin_form_row_open(__('Username <em>(required)</em>')); ?>
                        <?php AdminForm::username_text($admin); ?>
                    <?php osc_admin_form_row_close(); ?>
                    <?php osc_admin_form_row_open(__('E-mail <em>(required)</em>')); ?>
                        <?php AdminForm::email_text($admin); ?>
                    <?php osc_admin_form_row_close(); ?>
                    <?php if (!$aux['admin_edit']
                              || ($aux['admin_edit']
                                  && Params::getParam('id') != osc_logged_admin_id()
                                  && Params::getParam('id') != '')
                    ) { ?>
                        <?php osc_admin_form_row_open(__('Admin type <em>(required)</em>')); ?>
                            <?php AdminForm::type_select($admin); ?>
                                <p class="help-inline">
                                    <em><?php _e('Administrators have total control over all aspects of your installation, '
                                                 . 'while moderators are only allowed to moderate listings, comments and media files');
                        ?></em>
                                </p>
                        <?php osc_admin_form_row_close(); ?>
                    <?php } ?>
                    <?php osc_admin_form_row_open(__('New password')); ?>
                        <?php AdminForm::password_text($admin); ?>
                    <?php osc_admin_form_row_close(); ?>
                    <?php if ($aux['admin_edit']) { ?>
                        <?php osc_admin_form_row_open(__('Confirm new password')); ?>
                            <?php AdminForm::check_password_text($admin); ?>
                                <p class="help-inline"><em><?php _e('Type your new password again'); ?></em></p>
                        <?php osc_admin_form_row_close(); ?>
                    <?php } ?>

                    <hr/>
                    <?php osc_admin_form_row_open(__('Your current password')); ?>
                        <?php AdminForm::old_password_text(); ?>
                            <p class="help-inline">
                                <em><?php _e('For security, type <b>your current password</b>'); ?></em></p>
                    <?php osc_admin_form_row_close(); ?>


                    <?php osc_run_hook('admin_profile_form', $admin); ?>
                    <div class="clear"></div>
                    <?php
                    $formActions = array();
                    if ($aux['admin_edit']) {
                        $formActions[] = array('label' => __('Cancel'), 'url' => 'javascript:history.go(-1)', 'variant' => 'dim');
                    }
                    $formActions[] = array('label' => $aux['btn_text'], 'type' => 'submit', 'variant' => 'primary');
                    osc_admin_form_actions($formActions);
                    ?>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /add user form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>