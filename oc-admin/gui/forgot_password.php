<?php
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
if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}
?>
    <div class="alert alert-info">
        <?php _e('Type your new password'); ?>.
    </div>
    <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="login"/>
        <input type="hidden" name="action" value="forgot_post"/>
        <input type="hidden" name="adminId" value="<?php echo Params::getParam('adminId', true); ?>"/>
        <input type="hidden" name="code" value="<?php echo Params::getParam('code', true); ?>"/>
        <div class="form-floating mb-3">
            <input id="new_password" type="password" name="new_password" class="form-control"
                   placeholder="<?php _e('New password'); ?>"
                   autocomplete="off">
            <label for="user_pass"><?php _e('New password'); ?></label>
        </div>
        <div class="form-floating mb-3">
            <input id="new_password2" type="password" name="new_password2" class="form-control"
                   placeholder="<?php _e('Repeat new password'); ?>"
                   autocomplete="off">
            <label for="user_pass"><?php _e('Repeat new password'); ?></label>
        </div>
        <?php osc_run_hook('admin_forgot_form'); ?>
        <button class="w-100 btn btn-lg btn-primary" type="submit" name="submit"
                id="submit"><?php echo osc_esc_html(__('Change password')); ?></button>
        <div class="mt-5 mb-3"><a href="<?php echo osc_base_url(); ?>"
                                  title="<?php echo osc_esc_html(sprintf(__('Back to %s'), osc_page_title())); ?>">
                <i class="text-dark bi bi-arrow-left"></i> <?php printf(__('Back to %s'), osc_page_title()); ?></a>
        </div>
    </form>
    <p id="nav">
        <a title="<?php _e('Log in'); ?>" href="<?php echo osc_admin_base_url(); ?>"><?php _e('Log in'); ?></a>
    </p>
<?php $login_js = static function () { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".ico-close").click(function () {
                $(this).parent().hide();
            });
            $("#new_password").focus();
        });
<?php };
osc_add_hook('admin_login_footer', $login_js); ?>