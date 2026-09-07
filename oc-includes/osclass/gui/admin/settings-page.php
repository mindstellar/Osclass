<?php
if (!defined('OC_ADMIN')) {
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

/**
 * The page core draws for a declared settings page (osc_register_settings_page()).
 *
 * Core owns this file, not the admin theme: a plugin that declares a page must get one on
 * every install, including those running an admin theme nobody here has seen. It is built
 * from the same helpers a hand-written screen uses, so it is indistinguishable from one.
 *
 * The values rendered are the submitted ones when a save was rejected, so a validation
 * error never costs the administrator what they typed.
 */

$page   = __get('settings_page');
$values = __get('settings_values');

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => $page['title'],
    'help'    => $page['help'],
));

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="settings-page-<?php echo osc_esc_html($page['id']); ?>">
    <?php osc_admin_page_head($page['title']); ?>

    <?php if ($page['intro'] !== '') { ?>
        <p class="form-intro"><?php echo osc_esc_html($page['intro']); ?></p>
    <?php } ?>

    <form action="<?php echo osc_esc_html(osc_admin_base_url(true)); ?>" method="post">
        <input type="hidden" name="page" value="settings"/>
        <input type="hidden" name="action" value="custom_post"/>
        <input type="hidden" name="id" value="<?php echo osc_esc_html($page['id']); ?>"/>
        <fieldset>
            <div class="form-horizontal">
                <?php foreach ($page['groups'] as $index => $group) {
                    if ($group['title'] !== '') {
                        osc_admin_page_head($group['title']);
                    }
                    if ($group['intro'] !== '') {
                        echo '<p class="form-intro">' . osc_esc_html($group['intro']) . '</p>';
                    }

                    foreach ($group['fields'] as $field) {
                        $name = $field['name'];
                        if ($field['type'] === 'checkbox') {
                            $field['row_label'] = $field['row_label'] ?? '';
                            $field['checked']   = !empty($values[$name]);
                        } else {
                            $field['value'] = $values[$name] ?? '';
                            // Resolved here, not inside the field: drawing a control must
                            // not query anything, and render and store have to expand a
                            // translated field over the same list.
                            $locales = osc_settings_field_locales($field);
                            if ($locales !== array()) {
                                $field['locales'] = $locales;
                            }
                        }
                        // A filter over the field spec: a listener can add a hint, retitle a
                        // field or mark it readonly. It cannot suppress the control or put a
                        // different one in its place -- the field below renders regardless.
                        $filtered = osc_apply_filter('admin_form_render_field', $field, $page['id'], $values);
                        $field    = is_array($filtered) ? $filtered : $field;
                        osc_admin_field($field);
                    }

                    // A schema that cannot express something must not force a plugin back to
                    // raw HTML for the whole page: this appends to a declared group without
                    // owning the page it sits on.
                    osc_run_hook('settings_page_after_group', $page['id'], $group, $index);
                } ?>
                <?php osc_admin_form_actions(); ?>
            </div>
        </fieldset>
    </form>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
