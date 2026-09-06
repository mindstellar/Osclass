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
    'title'   => __('Edit language'),
));

//customize Head
function customHead()
{
    LanguageForm::js_validation();
}

osc_add_hook('admin_header', 'customHead', 10);

$aLocale = __get('aLocale');

osc_current_admin_theme_path('parts/header.php'); ?>
    <?php osc_admin_page_head(__('Edit language')); ?>
    <div id="language-form" class="col-lg-6">
        <ul id="error_list"></ul>
        <form name="language_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="languages"/>
            <input type="hidden" name="action" value="edit_post"/>
            <?php LanguageForm::primary_input_hidden($aLocale); ?>

            <div class="form-horizontal">
                <?php osc_admin_form_row_open(__('Current version')); ?>
                        <?php echo $aLocale['s_version']; ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Name')); ?>
                        <?php LanguageForm::name_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Short name')); ?>
                        <?php LanguageForm::short_name_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Description')); ?>
                        <?php LanguageForm::description_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Direction')); ?>
                        <?php LanguageForm::text_direction_select($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Currency format')); ?>
                        <?php LanguageForm::currency_format_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Number of decimals')); ?>
                        <?php LanguageForm::num_dec_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Decimal point')); ?>
                        <?php LanguageForm::dec_point_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Thousands separator')); ?>
                        <?php LanguageForm::thousands_sep_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Date format')); ?>
                        <?php LanguageForm::date_format_input_text($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Stopwords')); ?>
                        <?php LanguageForm::description_textarea($aLocale); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(''); ?>
                        <div class="form-label-checkbox">
                            <?php LanguageForm::enabled_input_checkbox($aLocale); ?>
                            <?php _e('Enabled for the public website'); ?>
                        </div>
                        <div class="form-label-checkbox">
                            <?php LanguageForm::enabled_bo_input_checkbox($aLocale); ?>
                            <?php _e('Enabled for the backoffice (oc-admin)'); ?>
                        </div>
                <?php osc_admin_form_row_close(); ?>
            </div>
            <?php osc_admin_form_actions(); ?>
        </form>
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>