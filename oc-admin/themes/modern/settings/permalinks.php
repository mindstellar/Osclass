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
            oscValidateForm(document.querySelector('form[name=settings_form]'), {
                rules: {
                    rewrite_item_url: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_page_url: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_cat_url: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_url: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_country: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_region: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_city: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_city_area: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_category: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_user: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_search_pattern: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_contact: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_feed: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_language: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_mark: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_send_friend: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_contact: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_activate: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_edit: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_delete: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_item_resource_delete: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_login: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_dashboard: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_logout: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_register: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_activate: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_activate_alert: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_profile: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_items: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_alerts: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_recover: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_forgot: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_change_password: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_change_email: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_change_username: {
                        required: true,
                        minlength: 1
                    },
                    rewrite_user_change_email_confirm: {
                        required: true,
                        minlength: 1
                    }
                },
                messages: {
                    rewrite_item_url: {
                        required: '<?php echo osc_esc_js(__('Listings url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Listings url: this field is required')); ?>.'
                    },
                    rewrite_page_url: {
                        required: '<?php echo osc_esc_js(__('Page url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Page url: this field is required')); ?>.'
                    },
                    rewrite_cat_url: {
                        required: '<?php echo osc_esc_js(__('Categories url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Categories url: this field is required')); ?>.'
                    },
                    rewrite_search_url: {
                        required: '<?php echo osc_esc_js(__('Search url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search url: this field is required')); ?>.'
                    },
                    rewrite_search_country: {
                        required: '<?php echo osc_esc_js(__('Search country: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search country: this field is required')); ?>.'
                    },
                    rewrite_search_region: {
                        required: '<?php echo osc_esc_js(__('Search region: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search region: this field is required')); ?>.'
                    },
                    rewrite_search_city: {
                        required: '<?php echo osc_esc_js(__('Search city: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search city: this field is required')); ?>.'
                    },
                    rewrite_search_city_area: {
                        required: '<?php echo osc_esc_js(__('Search city area: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search city area: this field is required')); ?>.'
                    },
                    rewrite_search_category: {
                        required: '<?php echo osc_esc_js(__('Search category: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search category: this field is required')); ?>.'
                    },
                    rewrite_search_user: {
                        required: '<?php echo osc_esc_js(__('Search user: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search user: this field is required')); ?>.'
                    },
                    rewrite_search_pattern: {
                        required: '<?php echo osc_esc_js(__('Search pattern: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Search pattern: this field is required')); ?>.'
                    },
                    rewrite_contact: {
                        required: '<?php echo osc_esc_js(__('Contact url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Contact url: this field is required')); ?>.'
                    },
                    rewrite_feed: {
                        required: '<?php echo osc_esc_js(__('Feed url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Feed url: this field is required')); ?>.'
                    },
                    rewrite_language: {
                        required: '<?php echo osc_esc_js(__('Language url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Language url: this field is required')); ?>.'
                    },
                    rewrite_item_mark: {
                        required: '<?php echo osc_esc_js(__('Listing mark url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Listing mark url: this field is required')); ?>.'
                    },
                    rewrite_item_send_friend: {
                        required: '<?php echo osc_esc_js(__('Listing send friend url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Listing send friend url: this field is required')); ?>.'
                    },
                    rewrite_item_contact: {
                        required: '<?php echo osc_esc_js(__('Listing contact url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Listing contact url: this field is required')); ?>.'
                    },
                    rewrite_item_new: {
                        required: '<?php echo osc_esc_js(__('New listing url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('New listing url: this field is required')); ?>.'
                    },
                    rewrite_item_activate: {
                        required: '<?php echo osc_esc_js(__('Activate listing url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Activate listing url: this field is required')); ?>.'
                    },
                    rewrite_item_edit: {
                        required: '<?php echo osc_esc_js(__('Edit listing url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Edit listing url: this field is required')); ?>.'
                    },
                    rewrite_item_delete: {
                        required: '<?php echo osc_esc_js(__('Delete listing url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Delete listing url: this field is required')); ?>.'
                    },
                    rewrite_item_resource_delete: {
                        required: '<?php echo osc_esc_js(__('Delete listing resource url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Delete listing resource url: this field is required')); ?>.'
                    },
                    rewrite_user_login: {
                        required: '<?php echo osc_esc_js(__('Login url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Login url: this field is required')); ?>.'
                    },
                    rewrite_user_dashboard: {
                        required: '<?php echo osc_esc_js(__('User dashboard url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User dashboard url: this field is required')); ?>.'
                    },
                    rewrite_user_logout: {
                        required: '<?php echo osc_esc_js(__('Logout url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Logout url: this field is required')); ?>.'
                    },
                    rewrite_user_register: {
                        required: '<?php echo osc_esc_js(__('User register url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User register url: this field is required')); ?>.'
                    },
                    rewrite_user_activate: {
                        required: '<?php echo osc_esc_js(__('Activate user url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Activate user url: this field is required')); ?>.'
                    },
                    rewrite_user_activate_alert: {
                        required: '<?php echo osc_esc_js(__('Activate alert url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Activate alert url: this field is required')); ?>.'
                    },
                    rewrite_user_profile: {
                        required: '<?php echo osc_esc_js(__('User profile url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User profile url: this field is required')); ?>.'
                    },
                    rewrite_user_items: {
                        required: '<?php echo osc_esc_js(__('User listings url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User listings url: this field is required')); ?>.'
                    },
                    rewrite_user_alerts: {
                        required: '<?php echo osc_esc_js(__('User alerts url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User alerts url: this field is required')); ?>.'
                    },
                    rewrite_user_recover: {
                        required: '<?php echo osc_esc_js(__('Recover user url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Recover user url: this field is required')); ?>.'
                    },
                    rewrite_user_forgot: {
                        required: '<?php echo osc_esc_js(__('User forgot url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('User forgot url: this field is required')); ?>.'
                    },
                    rewrite_user_change_password: {
                        required: '<?php echo osc_esc_js(__('Change password url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Change password url: this field is required')); ?>.'
                    },
                    rewrite_user_change_email: {
                        required: '<?php echo osc_esc_js(__('Change email url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Change email url: this field is required')); ?>.'
                    },
                    rewrite_user_change_username: {
                        required: '<?php echo osc_esc_js(__('Change username url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Change username url: this field is required')); ?>.'
                    },
                    rewrite_user_change_email_confirm: {
                        required: '<?php echo osc_esc_js(__('Change email confirm url: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Change email confirm url: this field is required')); ?>.'
                    }
                },
                errorContainer: '#error_list',
                onInvalid: function () {
                    var h1 = document.querySelector('h1');
                    if (h1) { h1.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
            });

            var re = document.getElementById('rewrite_enabled');
            var customRules = document.getElementById('custom_rules');
            if (re && customRules) {
                re.addEventListener('change', function () {
                    customRules.classList.toggle('hide', !re.checked);
                });
            }
        });
    </script>
    <?php
}

osc_add_hook('admin_header', 'customHead', 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Permalinks'),
    'help'    => __("Activate this option if you want your site's URLs to be more attractive to search engines and intelligible for users."
                    . " <strong>Be careful</strong>: depending on your hosting service, this might not work correctly."),
));

osc_current_admin_theme_path('parts/header.php'); ?>
    <div id="mail-setting">
        <!-- settings form -->
        <div id="mail-settings">
            <?php osc_admin_page_head(__('Permalinks')); ?>
            <p class="settings-lead"><?php _e('By default Shopclass uses web URLs which have question marks and lots of numbers in them. '
                     . 'However, Shopclass offers you friendly urls. This can improve the aesthetics, usability, '
                     . 'and forward-compatibility of your links'); ?></p>
            <ul id="error_list"></ul>
            <?php osc_admin_form_open(array(
                'name'   => 'settings_form',
                'page'   => 'settings',
                'action' => 'permalinks_post',
            )); ?>
                        <?php osc_admin_field(array(
                            'type'      => 'checkbox',
                            'row_label' => __('Friendly URLs'),
                            'id'        => 'rewrite_enabled',
                            'name'      => 'rewrite_enabled',
                            'label'     => __('Enable friendly urls'),
                            'checked'   => osc_rewrite_enabled(),
                            'help'      => __('Turns links like index.php?page=item&id=42 into readable ones like '
                                              . '/listing/blue-bicycle-42. Your web server needs URL rewriting enabled '
                                              . 'for this to work.'),
                        )); ?>
                        <div id="custom_rules" <?php if (!osc_rewrite_enabled()) {
                            echo 'class="hide"';
                        } ?>>
                            <details class="rules-disclosure">
                                <summary><?php _e('Advanced: customize URL structure'); ?></summary>
                                <?php osc_admin_form_section(__('Listings, pages &amp; categories')); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_url',
                                    'label' => __('Listing URL:'),
                                    'value' => osc_get_preference('rewrite_item_url'),
                                    'width' => 'key',
                                    'help'  => sprintf(__('Accepted keywords: %s'), '{ITEM_ID},{ITEM_TITLE},{ITEM_CITY},{CATEGORIES}'),
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_page_url',
                                    'label' => __('Page URL:'),
                                    'value' => osc_get_preference('rewrite_page_url'),
                                    'width' => 'key',
                                    'help'  => sprintf(__('Accepted keywords: %s'), '{PAGE_ID}, {PAGE_SLUG}'),
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_cat_url',
                                    'label' => __('Category URL:'),
                                    'value' => osc_get_preference('rewrite_cat_url'),
                                    'width' => 'key',
                                    'help'  => sprintf(__('Accepted keywords: %s'), '{CATEGORY_ID},{CATEGORY_NAME},{CATEGORIES}'),
                                )); ?>
                                <?php osc_admin_form_section(__('Search'), array('spaced' => true)); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'seo_url_search_prefix',
                                    'label' => __('Search prefix URL:'),
                                    'value' => osc_get_preference('seo_url_search_prefix'),
                                    'width' => 'key',
                                    'help'  => __('It always appear before the category, region or city url.'),
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_url',
                                    'label' => __('Search URL:'),
                                    'value' => osc_get_preference('rewrite_search_url'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_country',
                                    'label' => __('Search keyword country'),
                                    'value' => osc_get_preference('rewrite_search_country'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_region',
                                    'label' => __('Search keyword region'),
                                    'value' => osc_get_preference('rewrite_search_region'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_city',
                                    'label' => __('Search keyword city'),
                                    'value' => osc_get_preference('rewrite_search_city'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_city_area',
                                    'label' => __('Search keyword city area'),
                                    'value' => osc_get_preference('rewrite_search_city_area'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_category',
                                    'label' => __('Search keyword category'),
                                    'value' => osc_get_preference('rewrite_search_category'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_user',
                                    'label' => __('Search keyword user'),
                                    'value' => osc_get_preference('rewrite_search_user'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_search_pattern',
                                    'label' => __('Search keyword pattern'),
                                    'value' => osc_get_preference('rewrite_search_pattern'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_form_section(__('Contact, feed &amp; language'), array('spaced' => true)); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_contact',
                                    'label' => __('Contact'),
                                    'value' => osc_get_preference('rewrite_contact'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_feed',
                                    'label' => __('Feed'),
                                    'value' => osc_get_preference('rewrite_feed'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_language',
                                    'label' => __('Language'),
                                    'value' => osc_get_preference('rewrite_language'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_form_section(__('Listing actions'), array('spaced' => true)); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_mark',
                                    'label' => __('Listing mark'),
                                    'value' => osc_get_preference('rewrite_item_mark'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_send_friend',
                                    'label' => __('Listing send friend'),
                                    'value' => osc_get_preference('rewrite_item_send_friend'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_contact',
                                    'label' => __('Listing contact'),
                                    'value' => osc_get_preference('rewrite_item_contact'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_new',
                                    'label' => __('Listing new'),
                                    'value' => osc_get_preference('rewrite_item_new'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_activate',
                                    'label' => __('Listing activate'),
                                    'value' => osc_get_preference('rewrite_item_activate'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_edit',
                                    'label' => __('Listing edit'),
                                    'value' => osc_get_preference('rewrite_item_edit'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_delete',
                                    'label' => __('Listing delete'),
                                    'value' => osc_get_preference('rewrite_item_delete'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_item_resource_delete',
                                    'label' => __('Listing resource delete'),
                                    'value' => osc_get_preference('rewrite_item_resource_delete'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_form_section(__('User account'), array('spaced' => true)); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_login',
                                    'label' => __('User login'),
                                    'value' => osc_get_preference('rewrite_user_login'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_dashboard',
                                    'label' => __('User dashboard'),
                                    'value' => osc_get_preference('rewrite_user_dashboard'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_logout',
                                    'label' => __('User logout'),
                                    'value' => osc_get_preference('rewrite_user_logout'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_register',
                                    'label' => __('User register'),
                                    'value' => osc_get_preference('rewrite_user_register'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_activate',
                                    'label' => __('User activate'),
                                    'value' => osc_get_preference('rewrite_user_activate'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_activate_alert',
                                    'label' => __('User activate alert'),
                                    'value' => osc_get_preference('rewrite_user_activate_alert'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_profile',
                                    'label' => __('User profile'),
                                    'value' => osc_get_preference('rewrite_user_profile'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_items',
                                    'label' => __('User listings'),
                                    'value' => osc_get_preference('rewrite_user_items'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_alerts',
                                    'label' => __('User alerts'),
                                    'value' => osc_get_preference('rewrite_user_alerts'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_recover',
                                    'label' => __('User recover'),
                                    'value' => osc_get_preference('rewrite_user_recover'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_forgot',
                                    'label' => __('User forgot'),
                                    'value' => osc_get_preference('rewrite_user_forgot'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_change_password',
                                    'label' => __('User change password'),
                                    'value' => osc_get_preference('rewrite_user_change_password'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_change_email',
                                    'label' => __('User change email'),
                                    'value' => osc_get_preference('rewrite_user_change_email'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_change_email_confirm',
                                    'label' => __('User change email confirm'),
                                    'value' => osc_get_preference('rewrite_user_change_email_confirm'),
                                    'width' => 'key',
                                )); ?>
                                <?php osc_admin_text(array(
                                    'name'  => 'rewrite_user_change_username',
                                    'label' => __('User change username'),
                                    'value' => osc_get_preference('rewrite_user_change_username'),
                                    'width' => 'key',
                                )); ?>
                            </details>
                        </div>
                        <?php if (osc_rewrite_enabled()) {
                            $rewrite_base = REL_WEB_URL;
                            $is_nginx     = osc_server_is_nginx();
                            if ($is_nginx) {
                                $nginx_conf = osc_server_rewrite_rules();
                                ?>
                                <div class="server-config">
                                    <?php osc_admin_form_section(__('Server rules (nginx)'), array('spaced' => true)); ?>
                                    <p class="settings-lead"><?php _e('nginx does not read .htaccess files. Add the '
                                             . 'block below to your site\'s nginx server configuration, then reload '
                                             . 'nginx.'); ?></p>
                                    <div class="server-config-grid">
                                        <div class="server-config-block">
                                            <pre><?php echo htmlentities($nginx_conf, ENT_COMPAT, 'UTF-8'); ?></pre>
                                        </div>
                                    </div>
                                </div>
                            <?php } else {
                                $htaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$rewrite_base}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$rewrite_base}index.php [L]
</IfModule>
HTACCESS;
                                $htaccess_exists = file_exists(osc_base_path() . '.htaccess');
                                ?>
                                <div class="server-config">
                                    <?php osc_admin_form_section(__('Server rules (.htaccess)'), array('spaced' => true)); ?>
                                    <div class="server-config-grid">
                                        <?php if ($htaccess_exists) { ?>
                                            <div class="server-config-block">
                                                <h4><?php _e('Your current .htaccess file'); ?></h4>
                                                <pre><?php echo htmlentities(
                                                    file_get_contents(osc_base_path() . '.htaccess'),
                                                    ENT_COMPAT,
                                                    'UTF-8'
                                                ); ?></pre>
                                            </div>
                                        <?php } ?>
                                        <div class="server-config-block">
                                            <h4><?php echo $htaccess_exists
                                                                    ? osc_esc_html(__('What it should look like'))
                                                                    : osc_esc_html(__('What your .htaccess file should look like')); ?></h4>
                                            <pre><?php echo htmlentities($htaccess, ENT_COMPAT, 'UTF-8'); ?></pre>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            } ?>
                        <?php osc_admin_form_close(array()); ?>
        </div>
        <!-- /settings form -->
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>