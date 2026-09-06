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

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            function fld(name) { return document.querySelector('[name="' + name + '"]'); }

            var typeSel = document.querySelector('select[name="mailserver_type"]');
            if (typeSel) {
                typeSel.addEventListener('change', function () {
                    var host = fld('mailserver_host');
                    var port = fld('mailserver_port');
                    if (typeSel.value === 'gmail') {
                        if (host) { host.value = 'smtp.gmail.com'; host.readOnly = true; }
                        if (port) { port.value = '465'; port.readOnly = true; }
                        var u = fld('mailserver_username'); if (u) { u.value = ''; }
                        var pw = fld('mailserver_password'); if (pw) { pw.value = ''; }
                        var ssl = fld('mailserver_ssl'); if (ssl) { ssl.value = 'ssl'; }
                        var auth = fld('mailserver_auth'); if (auth) { auth.checked = true; }
                        var pop = fld('mailserver_pop'); if (pop) { pop.checked = false; }
                    } else {
                        if (host) { host.readOnly = false; }
                        if (port) { port.readOnly = false; }
                    }
                });
            }

            var testBtn = document.getElementById('testMail');
            if (testBtn) {
                testBtn.addEventListener('click', function () {
                    fetch("<?php echo osc_admin_base_url(true)?>?page=ajax&action=test_mail", {
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (r) {
                        return r.json();
                    }).then(function (data) {
                        var msg = document.getElementById('testMail_message');
                        if (!msg) { return; }
                        var pEl = msg.querySelector('p');
                        if (pEl) { pEl.innerHTML = data.html; }
                        msg.style.display = 'block';
                        msg.classList.add(data.status == 1 ? 'ok' : 'error');
                    });
                });
            }
        });
    </script>
    <?php
}

osc_add_hook('admin_header', 'customHead', 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Mail Settings'),
    'help'    => __("Modify the settings of the mail server from which your site's emails are sent. <strong>Be careful</strong>"
                    . ": these settings can vary depending on your hosting or server. If you run into any issues"
                    . ", check your hosting's help section."),
));

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="mail-setting">
    <!-- settings form -->
    <div id="mail-settings">
        <?php osc_admin_page_head(__('Mail Settings')); ?>
        <ul id="error_list"></ul>
        <?php osc_admin_form_open(array(
    'name'   => 'settings_form',
    'page'   => 'settings',
    'action' => 'mailserver_post',
)); ?>
            <?php
            osc_admin_select(array(
                'name'     => 'mailserver_type',
                'label'    => __('Server type'),
                'selected' => osc_mailserver_type(),
                'options'  => array(
                    'custom' => __('Custom Server'),
                    'gmail'  => __('GMail Server'),
                ),
            ));
            osc_admin_text(array(
                'name'  => 'mailserver_host',
                'label' => __('Hostname'),
                'value' => osc_mailserver_host(),
            ));
            osc_admin_field(array(
                'type'  => 'email',
                'name'  => 'mailserver_mail_from',
                'label' => __('Mail from'),
                'value' => osc_mailserver_mail_from(),
            ));
            osc_admin_text(array(
                'name'  => 'mailserver_name_from',
                'label' => __('Name from'),
                'value' => osc_mailserver_name_from(),
            ));
            osc_admin_number(array(
                'name'  => 'mailserver_port',
                'label' => __('Server port'),
                'value' => osc_mailserver_port(),
                'min'   => 0,
                'max'   => 65535,
            ));
            osc_admin_text(array(
                'name'  => 'mailserver_username',
                'label' => __('Username'),
                'value' => osc_mailserver_username(),
            ));
            // Masked: the stored password is never written into the page. Submitting the
            // field blank leaves it as it was; see CAdminSettingsMailserver.
            osc_admin_secret(array(
                'name'   => 'mailserver_password',
                'label'  => __('Password'),
                'value'  => osc_mailserver_password(),
                'masked' => true,
                'reveal' => true,
                'help'   => __('Leave blank to keep the current password.'),
            ));

            $sslWarning = '';
            if (PHP_SAPI === 'cgi-fcgi' || PHP_SAPI === 'cgi') {
                $sslWarning = __('Cannot be sure that Apache Module <b>mod_ssl</b> is loaded.');
            } elseif (!@apache_mod_loaded('mod_ssl')) {
                $sslWarning = __('Apache Module <b>mod_ssl</b> is not loaded');
            }
            osc_admin_select(array(
                'name'      => 'mailserver_ssl',
                'label'     => __('Encryption'),
                'selected'  => (string)osc_mailserver_ssl(),
                'options'   => array(
                    ''    => __('None'),
                    'ssl' => 'SSL',
                    'tls' => 'TLS',
                ),
                'help_html' => $sslWarning === ''
                    ? ''
                    : '<span class="callout-warning">' . $sslWarning . '</span>',
            ));

            osc_admin_field(array(
                'type'      => 'checkbox',
                'row_label' => __('SMTP'),
                'name'      => 'mailserver_auth',
                'label'     => __('SMTP authentication enabled'),
                'checked'   => osc_mailserver_auth(),
            ));
            osc_admin_field(array(
                'type'      => 'checkbox',
                'row_label' => __('POP'),
                'name'      => 'mailserver_pop',
                'label'     => __('Use POP before SMTP'),
                'checked'   => osc_mailserver_pop(),
            )); ?>
                    <?php osc_admin_form_close(array()); ?>
    </div>
    <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
