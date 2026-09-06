<?php use mindstellar\utility\Utils;

if (!defined('OC_ADMIN')) {
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

$dateFormats = array('F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y');
$timeFormats = array('g:i a', 'g:i A', 'H:i');

$aLanguages  = __get('aLanguages');
$aCurrencies = __get('aCurrencies');

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            oscValidateForm(document.querySelector('form[name=settings_form]'), {
                rules: {
                    pageTitle: { required: true, minlength: 1 },
                    contactEmail: { required: true, email: true },
                    num_rss_items: { required: true, digits: true },
                    max_latest_items_at_home: { required: true, digits: true },
                    default_results_per_page: { required: true, digits: true }
                },
                messages: {
                    pageTitle: {
                        required: '<?php echo osc_esc_js(__('Page title: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Page title: this field is required')); ?>.'
                    },
                    contactEmail: {
                        required: '<?php echo osc_esc_js(__('Email: this field is required')); ?>.',
                        email: '<?php echo osc_esc_js(__('Invalid email address')); ?>.'
                    },
                    num_rss_items: {
                        required: '<?php echo osc_esc_js(__('Listings shown in RSS feed: this field is required')); ?>.',
                        digits: '<?php echo osc_esc_js(__('Listings shown in RSS feed: this field must only contain numeric characters')); ?>.'
                    },
                    max_latest_items_at_home: {
                        required: '<?php echo osc_esc_js(__('Latest listings shown: this field is required')); ?>.',
                        digits: '<?php echo osc_esc_js(__('Latest listings shown: this field must only contain numeric characters')); ?>.'
                    },
                    default_results_per_page: {
                        required: '<?php echo osc_esc_js(__('The search page shows: this field is required')); ?>.',
                        digits: '<?php echo osc_esc_js(__('The search page shows: this field must only contain numeric characters')); ?>.'
                    }
                },
                errorContainer: '#error_list',
                onInvalid: function () {
                    var h1 = document.querySelector('h1');
                    if (h1) { h1.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
            });
        });

        // Date and time format. The radios and the free-text box both write the hidden
        // field the controller reads (dateFormat / timeFormat); the free-text box also asks
        // the server what its format renders as, since PHP's date() is not JS's.
        function previewFormat(format, target) {
            if (!target) {
                return;
            }
            if (format === '') {
                target.textContent = '';

                return;
            }
            fetch("<?php echo osc_admin_base_url(true); ?>?page=ajax&action=date_format&format=" + encodeURIComponent(format), {
                credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                target.textContent = data.str_formatted !== '' ? ' <?php echo osc_esc_js(__('Preview')); ?>: ' + data.str_formatted : '';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            [['date', 'df'], ['time', 'tf']].forEach(function (pair) {
                var kind    = pair[0];
                var group   = pair[1];
                var hidden  = document.getElementById(kind === 'date' ? 'dateFormat' : 'timeFormat');
                var custom  = document.getElementById(group + '_custom_text');
                var preview = document.getElementById('custom_' + kind);
                var radios  = document.querySelectorAll('input[name="' + group + '"]');
                if (!hidden || !radios.length) {
                    return;
                }

                function sync() {
                    var picked = document.querySelector('input[name="' + group + '"]:checked');
                    if (!picked) {
                        return;
                    }
                    var isCustom = picked.value === group + '_custom';
                    hidden.value = isCustom ? (custom ? custom.value : '') : picked.value;
                    previewFormat(isCustom ? hidden.value : '', preview);
                }

                radios.forEach(function (radio) {
                    radio.addEventListener('change', sync);
                });

                if (custom) {
                    // Typing a format is choosing it: without this the value is entered and
                    // silently discarded because another radio is still selected.
                    custom.addEventListener('input', function () {
                        var radio = document.querySelector('input[name="' + group + '"][value="' + group + '_custom"]');
                        if (radio) {
                            radio.checked = true;
                        }
                        sync();
                    });
                }
            });
        });
    </script>
    <?php
}

osc_add_hook('admin_header', 'customHead', 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('General Settings'),
    'help'    => __("Change the basic configuration of your Shopclass. From here, you can modify variables such as the site’s name, "
                    . "the default currency or how lists of listings are displayed. <strong>Be careful</strong> when modifying default "
                    . "values if you're not sure what you're doing!"),
));

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="general-setting">
    <!-- settings form -->
    <div id="general-settings">
        <?php osc_admin_page_head(__('General Settings')); ?>
        <ul id="error_list"></ul>
        <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="update"/>
            <fieldset>
                <div class="form-horizontal">
                    <?php
                    osc_admin_text(array(
                        'name'  => 'pageTitle',
                        'label' => __('Page title'),
                        'value' => osc_page_title(),
                    ));
                    osc_admin_text(array(
                        'name'  => 'pageDesc',
                        'label' => __('Page description'),
                        'value' => osc_page_description(),
                    ));
                    osc_admin_field(array(
                        'type'  => 'email',
                        'name'  => 'contactEmail',
                        'label' => __('Contact e-mail'),
                        'value' => osc_contact_email(),
                    )); ?>
                    <?php
                    $languageOptions = array();
                    foreach ($aLanguages as $lang) {
                        $languageOptions[$lang['pk_c_code']] = $lang['s_name'];
                    }
                    osc_admin_select(array(
                        'name'     => 'language',
                        'label'    => __('Default language'),
                        'selected' => osc_language(),
                        'options'  => $languageOptions,
                    ));

                    $currencyOptions = array();
                    foreach ($aCurrencies as $currency) {
                        $currencyOptions[$currency['pk_c_code']] = $currency['pk_c_code'];
                    }
                    osc_admin_select(array(
                        'id'       => 'currency_admin',
                        'name'     => 'currency',
                        'label'    => __('Default currency'),
                        'selected' => osc_currency(),
                        'options'  => $currencyOptions,
                    ));

                    osc_admin_select(array(
                        'id'       => 'weekStart',
                        'name'     => 'weekStart',
                        'label'    => __('Week starts on'),
                        'selected' => (string)osc_week_starts_at(),
                        'options'  => array(
                            '0' => __('Sunday'),
                            '1' => __('Monday'),
                            '2' => __('Tuesday'),
                            '3' => __('Wednesday'),
                            '4' => __('Thursday'),
                            '5' => __('Friday'),
                            '6' => __('Saturday'),
                        ),
                    ));

                    $timezoneOptions = array();
                    foreach (Utils::timezoneList() as $tz) {
                        $timezoneOptions[$tz] = $tz;
                    }
                    osc_admin_select(array(
                        'name'     => 'timezone',
                        'label'    => __('Timezone'),
                        'selected' => osc_timezone(),
                        'options'  => $timezoneOptions,
                        'width'    => 'text',
                    )); ?>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Date & time format'); ?></div>
                        <div class="form-controls">
                            <div class="custom-date-time">
                                <div id="date">
                                    <div class="form-sublabel"><?php _e('Date'); ?></div>
                                    <?php
                                    $dfOptions = array();
foreach ($dateFormats as $df) {
    $dfOptions[$df] = date($df);
}
                                    $dfCustom            = !in_array(osc_date_format(), $dateFormats, true);
                                    $dfOptions['df_custom'] = array(
                                        'label'       => __('Custom'),
                                        'custom_html' => '<input type="text" name="df_custom_text" id="df_custom_text"'
                                            . ' class="input-text field-inline-text"'
                                            . ' aria-label="' . osc_esc_html(__('Custom date format')) . '"'
                                            . ' value="' . ($dfCustom ? osc_esc_html(osc_date_format()) : '') . '" />',
                                    );
                                    osc_admin_radio_group(array(
                                        'row'      => false,
                                        'name'     => 'df',
                                        'label'    => __('Date'),
                                        'selected' => $dfCustom ? 'df_custom' : osc_date_format(),
                                        'options'  => $dfOptions,
                                    )); ?>
                                    <span id="custom_date"></span>
                                    <input type="hidden" name="dateFormat" id="dateFormat"
                                           value="<?php echo osc_esc_html(osc_date_format()); ?>"/>
                                </div>
                                <div id="time">
                                    <div class="form-sublabel"><?php _e('Time'); ?></div>
                                    <?php
                                    $tfOptions = array();
foreach ($timeFormats as $tf) {
    $tfOptions[$tf] = date($tf);
}
                                    $tfCustom            = !in_array(osc_time_format(), $timeFormats, true);
                                    $tfOptions['tf_custom'] = array(
                                        'label'       => __('Custom'),
                                        'custom_html' => '<input type="text" name="tf_custom_text" id="tf_custom_text"'
                                            . ' class="input-text field-inline-text"'
                                            . ' aria-label="' . osc_esc_html(__('Custom time format')) . '"'
                                            . ' value="' . ($tfCustom ? osc_esc_html(osc_time_format()) : '') . '" />',
                                    );
                                    osc_admin_radio_group(array(
                                        'row'      => false,
                                        'name'     => 'tf',
                                        'label'    => __('Time'),
                                        'selected' => $tfCustom ? 'tf_custom' : osc_time_format(),
                                        'options'  => $tfOptions,
                                    )); ?>
                                    <span id="custom_time"></span>
                                    <input type="hidden" name="timeFormat" id="timeFormat"
                                           value="<?php echo osc_esc_html(osc_time_format()); ?>"/>
                                </div>
                            </div>
                            <div class="help-box">
                                <a href="https://php.net/date"
                                   target="_blank" rel="noopener"><?php _e('Documentation on date and time formatting'); ?></a>
                            </div>
                        </div>
                    </div>
                    <?php
                    osc_admin_number(array(
                        'name'   => 'num_rss_items',
                        'label'  => __('RSS shows'),
                        'value'  => osc_num_rss_items(),
                        'min'    => 0,
                        'suffix' => __('listings at most'),
                    ));
                    osc_admin_number(array(
                        'name'   => 'max_latest_items_at_home',
                        'label'  => __('Latest listings shown'),
                        'value'  => osc_max_latest_items_at_home(),
                        'min'    => 0,
                        'suffix' => __('at most'),
                    ));
                    osc_admin_number(array(
                        'name'   => 'default_results_per_page',
                        'label'  => __('Search page shows'),
                        'value'  => osc_default_results_per_page_at_search(),
                        'min'    => 0,
                        'suffix' => __('listings at most'),
                    )); ?>
                    <?php osc_admin_page_head(__('Category settings')); ?>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Parent categories'); ?></div>
                        <div class="form-controls">
                            <?php osc_admin_checkbox(array(
                                'name'    => 'selectable_parent_categories',
                                'label'   => __('Allow users to select a parent category as a category
                                    when inserting or editing a listing '),
                                'checked' => osc_selectable_parent_categories(),
                            )); ?>
                        </div>
                    </div>
                    <?php osc_admin_page_head(__('Contact Settings')); ?>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Attachments'); ?></div>
                        <div class="form-controls">
                            <?php osc_admin_checkbox(array(
                                'name'    => 'enabled_attachment',
                                'label'   => __('Allow people to attach a file to the contact form'),
                                'checked' => osc_contact_attachment(),
                            )); ?>
                        </div>
                    </div>
                    <?php osc_admin_page_head(__('Cron Settings')); ?>
                    <?php osc_admin_field(array(
                        'type'       => 'checkbox',
                        'row_label'  => __('Automatic cron process'),
                        'name'       => 'auto_cron',
                        'checked'    => osc_auto_cron(),
                        'label_html' => sprintf(
                            __('Allow Shopclass to run a built-in <a href="%s" target="_blank" rel="noopener">cron</a>'
                               . ' automatically without setting crontab'),
                            'https://en.wikipedia.org/wiki/Cron'
                        ),
                        'help_html'  => __('It is <b>recommended</b> to have this option enabled, because some features require it.'),
                    )); ?>
                    <?php osc_admin_page_head(__('Maps')); ?>
                    <?php
                    // Not osc_admin_secret(): a Maps JavaScript key is public by design — it
                    // ships in the page source — so masking it would only hide it from the
                    // administrator who has to check it.
                    osc_admin_text(array(
                        'name'  => 'googlemaps_api_key',
                        'label' => __('Google Maps key'),
                        'value' => osc_google_maps_api_key(),
                        'width' => 'key',
                        'help'  => __('Add your Google Maps JavaScript API key.'),
                    ));
                    osc_admin_text(array(
                        'name'  => 'openstreet_api_key',
                        'label' => __('OpenStreetMaps key'),
                        'value' => osc_openstreet_api_key(),
                        'width' => 'key',
                        'help'  => __('Add your Mapquest Consumer key.'),
                    )); ?>
                    <?php osc_admin_page_head(__('Software updates')); ?>
                    <?php
                    /**
                     *
                     * <div class="form-row">
                     * <div class="form-label"><?php _e('Core updates'); ?></div>
                     * <div class="form-controls">
                     * <select name="auto_update[]" id="auto_update_core">
                     * <option value="disabled" ><?php _e('Disabled'); ?></option>
                     * <option value="branch" <?php if(strpos(osc_auto_update(),'branch')!==false) {
                     * ?>selected="selected"<?php } ?>><?php _e('Branch - big changes'); ?></option>
                     * <option value="major" <?php if(strpos(osc_auto_update(),'major')!==false) {
                     * ?>selected="selected"<?php } ?>><?php _e('Major - new features'); ?></option>
                     * <option value="minor" <?php if(strpos(osc_auto_update(),'minor')!==false) {
                     * ?>selected="selected"<?php } ?>><?php _e('Minor - bug fixes'); ?></option>
                     * </select>
                     * </div>
                     * </div>
                     * <div class="form-row">
                     * <div class="form-label"><?php _e('Plugin updates'); ?></div>
                     * <div class="form-controls">
                     * <div class="form-label-checkbox">
                     * <label>
                     * <input type="checkbox" <?php echo ( (strpos(osc_auto_update(),'plugins')!==false) ?
                     * 'checked="checked"' : '' ); ?> name="auto_update[]" value="plugins" />
                     * <?php _e('Allow auto-updates plugins'); ?>
                     * </label>
                     * </div>
                     * </div>
                     * </div>
                     * <div class="form-row">
                     * <div class="form-label"><?php _e('Theme updates'); ?></div>
                     * <div class="form-controls">
                     * <div class="form-label-checkbox">
                     * <label>
                     * <input type="checkbox" <?php echo ( (strpos(osc_auto_update(),'themes')!==false) ?
                     * 'checked="checked"' : '' ); ?> name="auto_update[]" value="themes" />
                     * <?php _e('Allow auto-updates of themes'); ?>
                     * </label>
                     * </div>
                     * </div>
                     * </div>
                     * <div class="form-row">
                     * <div class="form-label"><?php _e('Language updates'); ?></div>
                     * <div class="form-controls">
                     * <div class="form-label-checkbox">
                     * <label>
                     * <input type="checkbox" <?php echo ( (strpos(osc_auto_update(),'languages')!==false) ?
                     * 'checked="checked"' : '' ); ?> name="auto_update[]" value="languages" />
                     * <?php _e('Allow auto-updates of languages'); ?>
                     * </label>
                     * </div>
                     * </div>
                     * </div>
                     */
?>
                    <div class="form-row">
                        <div class="form-label"></div>
                        <div class="form-controls">
                            <span id="last-version-check">
                                <?php
            $last_version_check = (int) osc_get_preference('last_version_check');
echo __('Last checked on ') . ($last_version_check > 0
    ? osc_format_date(date('d-m-Y h:i:s', $last_version_check))
    : __('never')); ?></span>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="checkOsclassUpdate()"><?php _e('Check updates'); ?></button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Allow Prerelease'); ?></div>
                        <div class="form-controls">
                            <?php osc_admin_checkbox(array(
                                'name'    => 'allow_update_prerelease',
                                'label'   => __('Allow prerelease update'),
                                'checked' => osc_get_preference('allow_update_prerelease'),
                            )); ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php osc_admin_form_actions(); ?>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /settings form -->
</div>
<script>
    function checkOsclassUpdate() {
        var lastVersionElement = document.getElementById("last-version-check");
        fetch('<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_version')
            .then(response => response.json())
            .then(data => {
                lastVersionElement.textContent = '<?php echo osc_esc_js(__('Last checked on ')); ?>' + new Date().toLocaleString();
                setJsMessage('info', data.msg);
            })
            .catch(error => setJsMessage('error', error));
    }
</script>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
