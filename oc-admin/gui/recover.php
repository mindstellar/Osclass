<?php
/*
 *  Copyright 2020 Mindstellar Osclass
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
<?php };
osc_add_hook('admin_login_footer', $login_js); ?>