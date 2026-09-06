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
            oscValidateForm(document.querySelector('form[name=searches_form]'), {
                rules: {
                    custom_queries: {
                        digits: true,
                        // Required only when the "custom" purge option is selected.
                        custom: function (value, form) {
                            var picked = form.querySelector('input[name=purge_searches]:checked');
                            if (picked && picked.value === 'custom') {
                                return value !== '';
                            }
                            return true;
                        }
                    }
                },
                messages: {
                    custom_queries: {
                        digits: '<?php echo osc_esc_js(__('Custom number: this field must only contain numeric characters')); ?>.',
                        custom: '<?php echo osc_esc_js(__('Custom number: this field cannot be left empty')); ?>.'
                    }
                },
                errorContainer: '#error_list',
                onInvalid: function () {
                    var h1 = document.querySelector('h1');
                    if (h1) { h1.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
            });

            // The radios and the free-text box both write the hidden field the controller
            // reads. Typing a number is choosing it, or the value is entered and discarded.
            var purge  = document.getElementById('customPurge');
            var custom = document.getElementById('custom_queries');
            if (purge) {
                var sync = function () {
                    var picked = document.querySelector('input[name=purge_searches]:checked');
                    if (!picked) {
                        return;
                    }
                    purge.value = picked.value === 'custom' ? (custom ? custom.value : '') : picked.value;
                };
                document.querySelectorAll('input[name=purge_searches]').forEach(function (radio) {
                    radio.addEventListener('change', sync);
                });
                if (custom) {
                    custom.addEventListener('input', function () {
                        var radio = document.querySelector('input[name=purge_searches][value=custom]');
                        if (radio) {
                            radio.checked = true;
                        }
                        sync();
                    });
                }
            }
        });
    </script>
    <?php
}

osc_add_hook('admin_header', 'customHead', 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Latest searches Settings'),
    'help'    => __("Save the searches users do on your site. In this way, you can get information on what they're most "
                    . 'interested in. From here, you can manage the options on how much information you want to save.'),
));

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="general-setting">
    <!-- settings form -->
    <div id="general-settings">
        <?php osc_admin_page_head(__('Latest searches Settings')); ?>
        <ul id="error_list"></ul>
        <form name="searches_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="latestsearches_post"/>
            <fieldset>
                <div class="form-horizontal">
                    <?php
                    osc_admin_field(array(
                        'type'      => 'checkbox',
                        'row_label' => __('Latest searches'),
                        'name'      => 'save_latest_searches',
                        'label'     => __('Save the latest user searches'),
                        'checked'   => osc_save_latest_searches(),
                        'help'      => __('It may be useful to know what queries users make.'),
                    ));

                    $presets     = array('hour', 'day', 'week', 'forever', '1000');
                    $isCustom    = !in_array(osc_purge_latest_searches(), $presets, true);
                    osc_admin_radio_group(array(
                        'name'     => 'purge_searches',
                        'label'    => __('How long queries are stored'),
                        'selected' => $isCustom ? 'custom' : (string)osc_purge_latest_searches(),
                        'help'     => __("This feature can generate a lot of data. It's recommended to purge this data periodically."),
                        'options'  => array(
                            'hour'    => __('One hour'),
                            'day'     => __('One day'),
                            'week'    => __('One week'),
                            'forever' => __('Forever'),
                            '1000'    => __('Store 1000 queries'),
                            'custom'  => array(
                                'label'       => __('Store'),
                                'custom_html' => '<input type="number" min="0" name="custom_queries" id="custom_queries"'
                                    . ' class="input-text field-inline-text"'
                                    . ' value="' . ($isCustom ? osc_esc_html(osc_purge_latest_searches()) : '') . '" />'
                                    . '<span class="field-suffix">' . osc_esc_html(__('queries')) . '</span>',
                            ),
                        ),
                    )); ?>
                    <input type="hidden" id="customPurge" name="customPurge"
                           value="<?php echo osc_esc_html(osc_purge_latest_searches()); ?>"/>
                    <?php osc_admin_form_actions(); ?>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
