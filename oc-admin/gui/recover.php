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
    <?php _e('Please enter your username or e-mail address'); ?>.<br/>
    <?php _e('You will receive a new password via e-mail'); ?>.
</div>
<form id="recoverform" name="recoverform" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="login"/>
    <input type="hidden" name="action" value="recover_post"/>
    <div class="form-floating mb-3">
        <input type="text" name="email" class="form-control" id="user_email" value="" size="20" tabindex="10"
               placeholder="Enter your E-mail">
        <label for="user_email"><?php _e('E-mail'); ?></label>
    </div>
    <?php osc_show_recaptcha(); ?>
    <?php osc_run_hook('admin_forgot_password_form'); ?>
    <button class="w-100 btn btn-lg btn-primary" type="submit" name="submit" id="submit"><?php
        echo osc_esc_html(__('Get new password')); ?></button>
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
            $("#user_email").focus();
        });
    </script>
<?php };
osc_add_hook('admin_login_footer', $login_js); ?>