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

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Spam and bots'),
    'help'    => __('Keep spammers from publishing on your site by configuring reCAPTCHA and Akismet. '
                    . 'Be careful: in order to use these services, you must register on their sites first and follow their instructions.'),
));

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="spam-setting">
    <?php osc_admin_page_head(__('Spam and bots')); ?>
    <div id="akismet-settings">
        <?php osc_admin_form_section(__('Akismet')); ?>
        <p><?php _e("Akismet is a hosted web service that saves you time by automatically detecting comment and trackback spam. "
                    . "It's hosted on our servers, but we give you access to it through plugins and our API."); ?></p>
        <?php osc_admin_form_open(array(
    'name'   => 'settings_form',
    'page'   => 'settings',
    'action' => 'akismet_post',
)); ?>
            <?php
                    $akismet_status = View::newInstance()->_get('akismet_status');
$alert_msg      = '';
$alert_type     = 'error';
switch ($akismet_status) {
    case 1:
$alert_type = 'ok';
$alert_msg  = __('This key is valid');
break;
    case 2:
$alert_type = 'error';
$alert_msg  = __('The key you entered is invalid. Please double-check it');
break;
    case 3:
$alert_type = 'warning';
$alert_msg  =
    sprintf(
        __('Akismet is disabled, please enter an API key. <a href="%s" target="_blank">(Get your key)</a>'),
        'http://akismet.com/get/'
    );
break;
}

            // Not masked: an empty key is how Akismet is switched off, so a blank
            // submission has to mean "clear it" rather than "leave it alone".
            osc_admin_text(array(
                'name'      => 'akismetKey',
                'label'     => __('Akismet API Key'),
                'value'     => osc_akismet_key() ?: '',
                'width'     => 'key',
                'help_html' => $alert_msg === ''
                    ? ''
                    : '<span class="callout-' . osc_esc_html($alert_type) . '">' . $alert_msg . '</span>',
            ));

            osc_admin_form_actions(array(
                array('label' => __('Save changes'), 'type' => 'submit', 'attrs' => array('id' => 'submit_akismet')),
            )); ?>
                <?php osc_admin_form_close(); ?>
    </div>
    <div id="recaptcha-settings" class="separate-top">
        <?php osc_admin_form_section(__('Captcha')); ?>
        <p><?php printf(
            __('Protect your site from automated abuse with Google reCAPTCHA or Cloudflare Turnstile. '
                           . '<a href="%1$s" target="_blank">Get a reCAPTCHA key</a> or '
                           . '<a href="%2$s" target="_blank">get a Turnstile key</a>.'),
            'https://www.google.com/recaptcha/admin#whyrecaptcha',
            'https://dash.cloudflare.com/?to=/:account/turnstile'
        ); ?></p>
        <?php osc_admin_form_open(array(
            'name'   => 'settings_form',
            'page'   => 'settings',
            'action' => 'recaptcha_post',
            'fields' => array('recaptchaVersion' => 2),
        )); ?>
                <?php
                $captcha_provider_pref = osc_captcha_provider_pref();
                // A forced provider (Turnstile/reCAPTCHA) whose keys are blank resolves to
                // 'none', silently disabling captcha site-wide. Warn rather than leave the
                // admin to work it out from the help text.
                $captcha_warning = '';
                if (osc_captcha_provider() === 'none'
                    && ($captcha_provider_pref === 'turnstile' || $captcha_provider_pref === 'recaptcha')
                ) {
                    $captcha_warning = '<span class="callout-warning">' . osc_esc_html(sprintf(
                        __('Captcha is currently off: you forced a provider but its keys are empty. '
                           . 'Enter the %s below to turn captcha on.'),
                        $captcha_provider_pref === 'turnstile'
                            ? __('Turnstile site key and Turnstile secret key')
                            : __('reCAPTCHA site key and reCAPTCHA secret key')
                    )) . '</span>';
                }
                osc_admin_select(array(
                    'name'      => 'captchaProvider',
                    'label'     => __('Captcha provider'),
                    'selected'  => $captcha_provider_pref,
                    'options'   => array(
                        'auto'      => __('Automatic'),
                        'turnstile' => __('Cloudflare Turnstile'),
                        'recaptcha' => __('Google reCAPTCHA'),
                        'none'      => __('None'),
                    ),
                    'help_html' => osc_esc_html(__('Automatic prefers reCAPTCHA whenever its site and secret keys below are set; clear '
                        . 'both to let Turnstile take over instead. Pick a provider to force it, or None to '
                        . 'turn captchas off.')) . $captcha_warning,
                ));

                // None of the four is masked: clearing a key is how a provider is turned off,
                // so a blank submission has to mean "clear it".
                osc_admin_text(array(
                    'name'  => 'recaptchaPubKey',
                    'label' => __('reCAPTCHA site key'),
                    'value' => osc_recaptcha_public_key() ?: '',
                    'width' => 'key',
                ));
                osc_admin_text(array(
                    'name'  => 'recaptchaPrivKey',
                    'label' => __('reCAPTCHA secret key'),
                    'value' => osc_recaptcha_private_key() ?: '',
                    'width' => 'key',
                ));
                osc_admin_text(array(
                    'name'  => 'turnstileSiteKey',
                    'label' => __('Turnstile site key'),
                    'value' => osc_turnstile_site_key() ?: '',
                    'width' => 'key',
                    'help'  => __('From the Cloudflare dashboard &raquo; Turnstile.'),
                ));
                osc_admin_text(array(
                    'name'  => 'turnstileSecretKey',
                    'label' => __('Turnstile secret key'),
                    'value' => osc_turnstile_secret_key() ?: '',
                    'width' => 'key',
                    'help'  => __('From the Cloudflare dashboard &raquo; Turnstile.'),
                )); ?>
                <?php if (osc_captcha_enabled()) { ?>
                    <?php osc_admin_form_row_open(__('If you see a captcha widget below, the active provider is configured correctly')); ?>
                            <?php osc_show_captcha(); ?>
                    <?php osc_admin_form_row_close(); ?>
                <?php } ?>
                <?php osc_admin_form_close(array(
                    array('label' => __('Save changes'), 'type' => 'submit', 'attrs' => array('id' => 'submit_recaptcha')),
                )); ?>
    </div>
    <div id="alerts-settings" class="separate-top">
        <?php osc_admin_form_section(__('Search alerts')); ?>
        <p><?php _e('Search alerts email visitors when new listings match a saved search. Requiring login before '
                    . 'subscribing prevents anonymous email harvesting and confirmation-email abuse through the alert endpoint.'); ?></p>
        <?php osc_admin_form_open(array(
            'name'   => 'settings_form',
            'page'   => 'settings',
            'action' => 'alerts_post',
        )); ?>
                <?php osc_admin_field(array(
                    'type'      => 'checkbox',
                    'row_label' => __('Require login for alerts'),
                    'name'      => 'alerts_require_login',
                    'label'     => __('Only logged-in users can subscribe to search alerts'),
                    'checked'   => osc_get_preference('alerts_require_login'),
                )); ?>
                <?php osc_admin_form_close(array(
                    array('label' => __('Save changes'), 'type' => 'submit', 'attrs' => array('id' => 'submit_alerts')),
                )); ?>
    </div>
    <div id="login-throttle-settings" class="separate-top">
        <?php osc_admin_form_section(__('Sign-in protection')); ?>
        <p><?php _e('Failed sign-ins and password-reset requests are counted per visitor address and per account '
                    . 'name. Passing a limit refuses further attempts until the older ones age out of the window, '
                    . 'which is what stops a stolen password list being tried one guess at a time.'); ?></p>
        <p><?php _e('The account limit is skipped while a captcha provider is configured above, because every '
                    . 'attempt already has to solve one. Without that, an attacker could hold someone else\'s '
                    . 'account shut simply by failing against it.'); ?></p>
        <?php osc_admin_form_open(array(
            'name'   => 'settings_form',
            'page'   => 'settings',
            'action' => 'login_throttle_post',
        )); ?>
                <?php
                osc_admin_field(array(
                    'type'      => 'checkbox',
                    'row_label' => __('Limit sign-in attempts'),
                    'name'      => 'login_throttle_enabled',
                    'label'     => __('Count failed attempts and refuse further ones past the limits below'),
                    'checked'   => osc_login_throttle_enabled(),
                ));
                osc_admin_number(array(
                    'name'   => 'login_throttle_window',
                    'label'  => __('Window'),
                    'value'  => osc_login_throttle_window(),
                    'min'    => 1,
                    'suffix' => __('minutes'),
                    'help'   => __('How far back failures are counted, and so how long a refusal lasts.'),
                ));
                osc_admin_number(array(
                    'name'  => 'login_throttle_max_ip',
                    'label' => __('Attempts per address'),
                    'value' => osc_login_throttle_max_ip(),
                    'min'   => 1,
                    'help'  => __('Failures from one visitor address, across every account it tried. Keep this '
                                  . 'generous: an office or mobile network is many people behind one address.'),
                ));
                osc_admin_number(array(
                    'name'  => 'login_throttle_max_account',
                    'label' => __('Attempts per account'),
                    'value' => osc_login_throttle_max_account(),
                    'min'   => 1,
                    'help'  => __('Failures against one account name, from anywhere. This is what catches guessing '
                                  . 'spread across many addresses.'),
                ));
                osc_admin_number(array(
                    'name'   => 'login_attempt_retention_days',
                    'label'  => __('Keep records for'),
                    'value'  => osc_login_attempt_retention_days(),
                    'min'    => 0,
                    'suffix' => __('days'),
                    'help'   => __('Pruned by the daily cron. Only the window above affects the limits; the rest is '
                                   . 'history. 0 keeps everything.'),
                )); ?>
                <?php osc_admin_form_close(array(
                    array('label' => __('Save changes'), 'type' => 'submit', 'attrs' => array('id' => 'submit_login_throttle')),
                )); ?>
        <?php osc_admin_form_open(array(
            'name'   => 'settings_form',
            'page'   => 'settings',
            'action' => 'login_throttle_reset',
        )); ?>
                <?php osc_admin_field(array(
                    'type'   => 'custom',
                    'label'  => __('Clear recorded attempts'),
                    'help'   => __('Lets anyone currently refused try again straight away, including you.'),
                    'render' => static function () {
                        osc_admin_action_button(array(
                            'label' => __('Clear now'),
                            'type'  => 'submit',
                            'attrs' => array('id' => 'submit_login_throttle_reset'),
                        ));
                    },
                )); ?>
            <?php osc_admin_form_close(); ?>
    </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
