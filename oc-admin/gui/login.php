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
<form name="loginform" id="loginform" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="login"/>
    <input type="hidden" name="action" value="login_post"/>
    <div class="form-floating mb-3">
        <input type="text" name="user" class="form-control" id="user_login"
               value="<?php if (defined('DEMO')) {
                    echo 'admin';
                      } ?>" size="20" placeholder="Enter your username">
        <label for="user_login"><?php _e('Username'); ?></label>
    </div>
    <div class="form-floating mb-3">
        <input type="password" name="password" id="user_pass" class="form-control" placeholder="Password"
               value="<?php if (defined('DEMO')) {
                    echo 'admin';
                      } ?>" size="20" autocomplete="off">
        <label for="user_pass"><?php _e('Password'); ?></label>
    </div>
    <?php osc_run_hook('login_admin_form'); ?>
    <?php $locales = osc_all_enabled_locales_for_admin(); ?>
    <?php if (count($locales) > 1) { ?>
        <div class="form-floating mb-3">
            <select class="form-select" name="locale" id="user_language">
                <?php foreach ($locales as $locale) { ?>
                    <option value="<?php echo $locale ['pk_c_code']; ?>" <?php
                    if (osc_admin_language() === $locale['pk_c_code']
                    ) {
                        echo 'selected="selected"';
                    } ?>><?php echo $locale['s_name']; ?></option>
                <?php } ?>
            </select>
            <label for="locale"><i class="bi bi-globe"></i> <?php _e('Choose Language'); ?></label>
        </div>
    <?php } else { ?>
        <input type="hidden" name="locale" value="<?php echo $locales[0]['pk_c_code']; ?>"/>
    <?php } ?>
    <div class="checkbox mb-3">
        <label>
            <input name="remember" type="checkbox" id="remember" value="1" checked> <?php _e('Remember me'); ?>
        </label>
    </div>
    <div class="mb-3">
        <a href="<?php echo osc_admin_base_url(true); ?>?page=login&amp;action=recover"
           title="<?php echo osc_esc_html(__('Forgot your password?')); ?>"><?php _e('Forgot your password?'); ?></a>
    </div>
    <?php osc_run_hook('admin_login_form'); ?>
    <button class="w-100 btn btn-lg btn-primary" type="submit" name="submit"
            id="submit"><?php echo osc_esc_html
            (__('Log in')); ?></button>
    <div class="mt-5 mb-3"><a href="<?php echo osc_base_url(); ?>"
                              title="<?php echo osc_esc_html(sprintf(__('Back to %s'), osc_page_title())); ?>">
            <i class="text-dark bi bi-arrow-left"></i> <?php printf(__('Back to %s'), osc_page_title()); ?></a>
    </div>
</form>
<?php $login_js = static function () { ?><script type="text/javascript">
    $(document).ready(function () {
        $(".ico-close").click(function () {
            $(this).parent().hide();
        });
    });
</script>
<?php };
osc_add_hook('admin_login_footer', $login_js); ?>
