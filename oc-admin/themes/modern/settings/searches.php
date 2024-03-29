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

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // Code for form validation

            $.validator.addMethod('customrule', function (value, element) {
                if ($('input:radio[name=purge_searches]:checked').val() === 'custom') {
                    if ($("#custom_queries").val() == '') {
                        return false;
                    }
                }
                return true;
            });

            $("form[name=searches_form]").validate({
                rules: {
                    custom_queries: {
                        digits: true,
                        customrule: true
                    }
                },
                messages: {
                    custom_queries: {
                        digits: '<?php
                            echo osc_esc_js(__('Custom number: this field must only contain numeric characters'));
                        ?>.',
                        customrule: '<?php echo osc_esc_js(__('Custom number: this field cannot be left empty')); ?>.'
                    }
                },
                wrapper: "li",
                errorLabelContainer: "#error_list",
                invalidHandler: function (form, validator) {
                    $('html,body').animate({scrollTop: $('h1').offset().top}, {duration: 250, easing: 'swing'});
                },
                submitHandler: function (form) {
                    $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
                    form.submit();
                }
            });
        });
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

/**
 * @return string
 */
function render_offset()
{
    return 'row-offset';
}


function addHelp()
{
    echo '<p>'
         . __("Save the searches users do on your site. In this way, you can get information on what they're most "
              . 'interested in. From here, you can manage the options on how much information you want to save.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Settings'); ?>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"
           href="#help-box"></a>
    </h1>
    <?php
}


/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Latest searches Settings &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="general-setting">
    <!-- settings form -->
    <div id="general-settings">
        <h2 class="render-title"><?php _e('Latest searches Settings'); ?></h2>
        <ul id="error_list"></ul>
        <form name="searches_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="latestsearches_post"/>
            <fieldset>
                <div class="form-horizontal">
                    <div class="form-row">
                        <div class="form-label"><?php _e('Latest searches'); ?></div>
                        <div class="form-controls">
                            <div class="form-label-checkbox">
                                <input type="checkbox" <?php echo (osc_save_latest_searches()) ? 'checked="checked"'
                                    : ''; ?> name="save_latest_searches"/>
                                <?php _e('Save the latest user searches'); ?>
                                <div class="help-box">
                                    <?php _e('It may be useful to know what queries users make.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-label"><?php _e('How long queries are stored'); ?></div>
                        <div class="form-controls">
                            <div>
                                <input type="radio" name="purge_searches"
                                       value="hour" <?php echo((osc_purge_latest_searches() === 'hour')
                                        ? 'checked="checked"' : ''); ?>
                                       onclick="document.getElementById('customPurge').value = 'hour';"/>
                                <?php _e('One hour'); ?>
                            </div>
                            <div>
                                <input type="radio" name="purge_searches"
                                       value="day" <?php echo((osc_purge_latest_searches() === 'day')
                                        ? 'checked="checked"' : ''); ?>
                                       onclick="document.getElementById('customPurge').value = 'day';"/>
                                <?php _e('One day'); ?>
                            </div>
                            <div>
                                <input type="radio" name="purge_searches"
                                       value="week" <?php echo((osc_purge_latest_searches() === 'week')
                                        ? 'checked="checked"' : ''); ?>
                                       onclick="document.getElementById('customPurge').value = 'week';"/>
                                <?php _e('One week'); ?>
                            </div>
                            <div>
                                <input type="radio" name="purge_searches"
                                       value="forever" <?php echo((osc_purge_latest_searches() === 'forever')
                                        ? 'checked="checked"' : ''); ?>
                                       onclick="document.getElementById('customPurge').value = 'forever';"/>
                                <?php _e('Forever'); ?>
                            </div>
                            <div>
                                <input type="radio" name="purge_searches"
                                       value="1000" <?php echo((osc_purge_latest_searches() == '1000')
                                        ? 'checked="checked"' : ''); ?>
                                       onclick="document.getElementById('customPurge').value = '1000';"/>
                                <?php _e('Store 1000 queries'); ?>
                            </div>
                            <div>
                                <input type="radio" name="purge_searches" id="purge_searches"
                                       value="custom"
                                    <?php echo(!in_array(
                                        osc_purge_latest_searches(),
                                        array('hour', 'day', 'week', 'forever', '1000')
                                    ) ? 'checked="checked"' : ''); ?>
                                />
                                <?php printf(
                                    __('Store %s queries'),
                                    '<input name="custom_queries" id="custom_queries" type="number" class="input-medium" '
                                    . (!in_array(
                                        osc_purge_latest_searches(),
                                        array('hour', 'day', 'week', 'forever', '1000')
                                    ) ? 'value="'
                                        . osc_esc_html(osc_purge_latest_searches()) . '"' : '')
                                    . ' onchange="javascript:document.getElementById(\'customPurge\').value = this.value;" />'
                                ); ?>
                                <div class="help-box">
                                    <?php _e(
                                        "This feature can generate a lot of data. It's recommended to purge this data periodically."
                                    ); ?>
                                </div>
                            </div>
                            <input type="hidden" id="customPurge" name="customPurge"
                                   value="<?php echo osc_esc_html(osc_purge_latest_searches()); ?>"/>

                        </div>
                    </div>
                    <div class="form-actions">
                        <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>"
                               class="btn btn-submit"/>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
