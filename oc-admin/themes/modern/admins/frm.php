<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

osc_enqueue_script('jquery-validate');

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


function customPageHeader()
{
    ?>
    <h1><?php _e('Admins'); ?></h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');

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
    <h2 class="render-title"><?php echo $aux['title']; ?></h2>
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
                    <div class="form-row">
                        <div class="form-label"><?php _e('Name <em>(required)</em>'); ?></div>
                        <div class="form-controls">
                            <?php AdminForm::name_text($admin); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Username <em>(required)</em>'); ?></div>
                        <div class="form-controls"><?php AdminForm::username_text($admin); ?></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('E-mail <em>(required)</em>'); ?></div>
                        <div class="form-controls"><?php AdminForm::email_text($admin); ?></div>
                    </div>
                    <?php if (!$aux['admin_edit']
                              || ($aux['admin_edit']
                                  && Params::getParam('id') != osc_logged_admin_id()
                                  && Params::getParam('id') != '')
                    ) { ?>
                        <div class="form-row">
                            <div class="form-label"><?php _e('Admin type <em>(required)</em>'); ?></div>
                            <div class="form-controls">
                                                       <?php AdminForm::type_select($admin); ?>
                                <p class="help-inline">
                                    <em><?php _e('Administrators have total control over all aspects of your installation, '
                                                 . 'while moderators are only allowed to moderate listings, comments and media files');
                                        ?></em>
                                </p>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-row">
                        <div class="form-label"><?php _e('New password'); ?></div>
                        <div class="form-controls">
                            <?php AdminForm::password_text($admin); ?>
                        </div>
                        <?php if ($aux['admin_edit']) { ?>
                            <div class="form-controls">
                                <?php AdminForm::check_password_text($admin); ?>
                                <p class="help-inline"><em><?php _e('Type your new password again'); ?></em></p>
                            </div>
                        <?php } ?>
                    </div>

                    <hr/>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Your current password'); ?></div>
                        <div class="form-controls">
                            <?php AdminForm::old_password_text(); ?>
                            <p class="help-inline">
                                <em><?php _e('For security, type <b>your current password</b>'); ?></em></p>
                        </div>
                    </div>


                    <?php osc_run_hook('admin_profile_form', $admin); ?>
                    <div class="clear"></div>
                    <div class="form-actions">
                        <?php if ($aux['admin_edit']) { ?>
                            <a href="javascript:history.go(-1)" class="btn btn-dim"><?php _e('Cancel'); ?></a>
                        <?php } ?>
                        <button class="btn btn-submit" type="submit"><?php echo osc_esc_html($aux['btn_text']); ?></button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /add user form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>