<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Sitemap'),
    'help'    => __('Configure the XML sitemap: how many URLs go in each file, which extra location pages to '
                    . 'include, any custom URLs to append, and the robots.txt that advertises it to search engines.'),
));

$prefs           = __get('prefs');
$custom_urls     = __get('custom_urls');
$robots_content  = __get('robots_content');
$robots_writable = __get('robots_writable');
$sitemap_index_url = __get('sitemap_index_url');

$sitemapChecks = array(
    'sitemap_categories'  => __('Include categories'),
    'sitemap_pages'       => __('Include pages'),
    'sitemap_cities'      => __('Include cities'),
    'sitemap_regions'     => __('Include regions'),
    'sitemap_countries'   => __('Include countries'),
    'sitemap_cat_regions' => __('Include categories with regions'),
    'sitemap_cat_city'    => __('Include categories with cities'),
);

$freqOptions = array(
    'hourly'  => __('Hourly'),
    'daily'   => __('Daily'),
    'weekly'  => __('Weekly'),
    'monthly' => __('Monthly'),
    'yearly'  => __('Yearly'),
);

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="sitemap-setting">
    <?php osc_admin_page_head(__('Sitemap')); ?>
    <p>
        <?php printf(
            __('The sitemap index is served at %s and always reflects the current site content.'),
            '<code>' . osc_esc_html($sitemap_index_url) . '</code>'
        ); ?>
    </p>

    <div id="sitemap-general-settings">
        <h3 class="render-title"><?php _e('Sitemap settings'); ?></h3>
        <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="sitemap_settings_post"/>
            <fieldset class="form-horizontal">
                <?php
                osc_admin_number(array(
                    'id'     => 'sitemap_number',
                    'name'   => 'sitemap_number',
                    'label'  => __('URLs per sitemap file'),
                    'value'  => $prefs['sitemap_number'],
                    'min'    => 1,
                    'suffix' => __('URLs'),
                    'help'   => __('Number of URLs per XML item sitemap file. Extra listings roll into additional '
                                   . 'sitemaps automatically. Keep this low if you hit memory or timeout errors.'),
                ));
                // One checkbox per row, each labelled twice — once as the row and once beside
                // the box — is what the old markup did; the row label alone is enough.
                foreach ($sitemapChecks as $key => $label) {
                    osc_admin_field(array(
                        'type'    => 'checkbox',
                        'id'      => $key,
                        'name'    => $key,
                        'label'   => $label,
                        'checked' => !empty($prefs[$key]),
                    ));
                } ?>
                <?php osc_admin_form_actions(array(
                    array('label' => __('Save changes'), 'type' => 'submit', 'attrs' => array('id' => 'submit_sitemap_settings')),
                )); ?>
            </fieldset>
        </form>
    </div>

    <div id="sitemap-custom-urls" class="separate-top">
        <h3 class="render-title"><?php _e('Custom sitemap URLs'); ?></h3>
        <p><?php _e('Add URLs the sitemap would not otherwise discover on its own, such as pages served by a plugin.'); ?></p>
        <form name="sitemap_url_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="sitemap_custom_url_add"/>
            <fieldset class="form-horizontal">
                <?php
                osc_admin_field(array(
                    'type'        => 'url',
                    'name'        => 'sitemap_url',
                    'label'       => __('URL'),
                    'placeholder' => 'https://www.example.com/page',
                ));
                osc_admin_select(array(
                    'name'     => 'sitemap_freq',
                    'label'    => __('Frequency'),
                    'selected' => 'weekly',
                    'options'  => $freqOptions,
                ));
                osc_admin_text(array(
                    'name'        => 'sitemap_lastmod',
                    'label'       => __('Last modified'),
                    'placeholder' => 'YYYY-MM-DD',
                    'width'       => 'num',
                    'attrs'       => array('inputmode' => 'numeric'),
                    'help'        => __('Optional. Leave blank to use today\'s date.'),
                )); ?>
                <?php osc_admin_form_actions(array(
                    array('label' => __('Add URL'), 'type' => 'submit'),
                )); ?>
            </fieldset>
        </form>

        <?php if (!empty($custom_urls)) { ?>
            <table class="table" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Frequency'); ?></th>
                    <th><?php _e('Last modified'); ?></th>
                    <th class="text-end"><?php _e('Remove'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($custom_urls as $index => $custom) { ?>
                    <tr>
                        <td data-col-name="<?php echo osc_esc_html(__('URL')); ?>">
                            <?php echo osc_esc_html($custom['url'] ?? ''); ?>
                        </td>
                        <td data-col-name="<?php echo osc_esc_html(__('Frequency')); ?>">
                            <?php echo osc_esc_html($custom['freq'] ?? ''); ?>
                        </td>
                        <td data-col-name="<?php echo osc_esc_html(__('Last modified')); ?>">
                            <?php echo osc_esc_html($custom['lastmod'] ?? ''); ?>
                        </td>
                        <td class="text-end">
                            <form name="sitemap_url_remove_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
                                <input type="hidden" name="page" value="settings"/>
                                <input type="hidden" name="action" value="sitemap_custom_url_remove"/>
                                <input type="hidden" name="sitemap_url_index" value="<?php echo osc_esc_html($index); ?>"/>
                                <input type="submit" value="<?php echo osc_esc_html(__('Remove')); ?>" class="btn btn-mini"/>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>

    <div id="sitemap-robots" class="separate-top">
        <h3 class="render-title"><?php _e('robots.txt'); ?></h3>
        <?php if (!$robots_writable) { ?>
            <div class="flashmessage flashmessage-error">
                <?php _e('robots.txt is not writable by the web server. Fix the file or folder permissions before saving changes here.'); ?>
            </div>
        <?php } ?>
        <form name="sitemap_robots_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="sitemap_robots_post"/>
            <fieldset class="form-horizontal">
                <?php osc_admin_textarea(array(
                    'id'        => 'sitemap_robots',
                    'name'      => 'sitemap_robots',
                    'label'     => __('robots.txt contents'),
                    'value'     => $robots_content,
                    'rows'      => 10,
                    'width'     => 'key',
                    'monospace' => true,
                    'help_html' => '<span class="text-danger">'
                        . osc_esc_html(__('Make a backup before changing your robots.txt file.')) . '</span>',
                )); ?>
                <?php osc_admin_form_actions(array(
                    array(
                        'label' => __('Save robots.txt'),
                        'type'  => 'submit',
                        'attrs' => $robots_writable ? array() : array('disabled' => 'disabled'),
                    ),
                )); ?>
            </fieldset>
        </form>
    </div>

    <div id="sitemap-regenerate" class="separate-top">
        <h3 class="render-title"><?php _e('Regenerate'); ?></h3>
        <p><?php _e('The sitemap is cached for a few hours after it is first requested. Use this if you need '
                            . 'search engines to see fresh content immediately.'); ?></p>
        <form name="sitemap_regenerate_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="sitemap_regenerate"/>
            <input type="submit" value="<?php echo osc_esc_html(__('Regenerate / clear cache')); ?>" class="btn btn-submit"/>
        </form>
    </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
