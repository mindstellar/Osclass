<?php
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

use mindstellar\settings\SettingsPageRegistry;

/**
 * Declared admin settings pages.
 *
 * A plugin says what is on its settings page; core owns the page. From the declaration
 * core derives the shell and heading, the form and its osc_csrf_check(), per-field
 * sanitisation chosen by type, validation and error display, persistence into
 * preferences under the plugin's own section, the flash message and the Save row.
 *
 * The field primitives in hAdminUi.php already gave an author one way to draw a field.
 * What they could not do is stop the page around the field being re-invented -- a form,
 * a save handler, a CSRF check, a validation block -- and that re-invention is where the
 * security bugs live. So: no markup and no save handler in the plugin.
 *
 * Loaded with the other helpers, ahead of Plugins::init(), so a plugin can call
 * osc_register_settings_page() at include time. Nothing here renders anything, so it does
 * not want hAdminUi.php's late position; the render path only runs during a request that
 * has already loaded everything.
 */

if (!function_exists('osc_register_settings_page')) {
    /**
     * Declare a settings page. Thin wrapper over SettingsPageRegistry::register().
     *
     * See SettingsPageRegistry::register() for the $spec shape.
     *
     * @param string $id   Namespaced slug, usually the plugin's own.
     * @param array  $spec Page specification.
     *
     * @return void
     */
    function osc_register_settings_page($id, $spec)
    {
        SettingsPageRegistry::instance()->register($id, $spec);
    }
}

if (!function_exists('osc_settings_pages')) {
    /**
     * All registered settings pages, keyed by id.
     *
     * @return array
     */
    function osc_settings_pages()
    {
        return SettingsPageRegistry::instance()->all();
    }
}

if (!function_exists('osc_settings_page')) {
    /**
     * The spec for a registered settings page, or null when it is not registered.
     *
     * @param string $id
     *
     * @return array|null
     */
    function osc_settings_page($id)
    {
        return SettingsPageRegistry::instance()->get($id);
    }
}

if (!function_exists('osc_settings_page_conflicts')) {
    /**
     * Page ids more than one plugin tried to register, and how many times each. Empty on a
     * healthy install; an entry means a plugin's settings page is not the one being shown,
     * and its saved values are under a section nothing reads.
     *
     * @return array
     */
    function osc_settings_page_conflicts()
    {
        return SettingsPageRegistry::instance()->conflicts();
    }
}

if (!function_exists('osc_settings_page_url')) {
    /**
     * The admin URL of a declared settings page.
     *
     * @param string $id
     *
     * @return string
     */
    function osc_settings_page_url($id)
    {
        return osc_admin_base_url(true) . '?page=settings&action=custom&id=' . urlencode($id);
    }
}

if (!function_exists('osc_settings_value')) {
    /**
     * The stored value of one declared field, or the field's declared default when
     * nothing has been saved yet.
     *
     * A checkbox is returned as a bool, a number as an int or float, everything else as
     * the stored string -- so a caller never has to remember what a preference column
     * holds.
     *
     * @param string $pageId
     * @param string $name
     *
     * @return mixed
     */
    function osc_settings_value($pageId, $name)
    {
        $page = osc_settings_page($pageId);
        if ($page === null) {
            return null;
        }

        $fields = SettingsPageRegistry::instance()->fields($pageId);
        $field  = $fields[$name] ?? null;
        if ($field === null) {
            return null;
        }

        $stored = Preference::newInstance()->get($name, $page['section']);
        if ($stored === null || $stored === '') {
            // A checkbox saved as off stores '0', not '', so an empty read really is
            // "never saved" and the declared default is the right answer.
            return $field['default'] ?? ($field['type'] === 'checkbox' ? false : '');
        }

        return osc_settings_cast($field['type'], $stored);
    }
}

if (!function_exists('osc_settings_cast')) {
    /**
     * Turn a stored preference string back into the shape its field type implies.
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return mixed
     */
    function osc_settings_cast($type, $value)
    {
        if ($type === 'checkbox') {
            return (bool)$value;
        }
        if ($type === 'number') {
            return strpos((string)$value, '.') === false ? (int)$value : (float)$value;
        }

        return $value;
    }
}

if (!function_exists('osc_settings_values')) {
    /**
     * Every declared field on a page, keyed by name, resolved the same way
     * osc_settings_value() resolves one.
     *
     * @param string $pageId
     *
     * @return array
     */
    function osc_settings_values($pageId)
    {
        $values = array();
        foreach (SettingsPageRegistry::instance()->fields($pageId) as $name => $field) {
            $values[$name] = osc_settings_value($pageId, $name);
        }

        return $values;
    }
}

if (!function_exists('osc_settings_sanitize')) {
    /**
     * The submitted value of one field, sanitised by its type.
     *
     * Type-driven and deliberately small: this is not FieldValidator, which serves the
     * listing custom-field system (t_meta_fields, FormService) and would tie admin
     * settings to it for no gain. A field wanting more supplies its own `sanitize`.
     *
     * @param array $field
     *
     * @return mixed
     */
    function osc_settings_sanitize(array $field)
    {
        $name = $field['name'];

        if ($field['type'] === 'checkbox') {
            // Presence is the value. An unchecked box submits nothing at all, and
            // comparing against whatever string the browser invents for a checked one is
            // how a setting ends up impossible to switch on.
            return Params::getParam($name) !== '';
        }

        // Raw on purpose: a secret may legitimately contain characters osc_sanitize_string
        // would strip, and every value here is escaped again on output.
        $value = Params::getParam($name, false, false);
        if (is_array($value)) {
            return '';
        }
        $value = trim((string)$value);

        if (isset($field['sanitize'])) {
            return call_user_func($field['sanitize'], $value);
        }

        switch ($field['type']) {
            case 'number':
                return $value === '' ? '' : (strpos($value, '.') === false ? (int)$value : (float)$value);
            case 'email':
                return (string)filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'url':
                return osc_sanitize_url($value);
            default:
                return $value;
        }
    }
}

if (!function_exists('osc_settings_validate')) {
    /**
     * Check one submitted value against what its field declares. Returns an error message,
     * or null when the value is good.
     *
     * @param array $field
     * @param mixed $value
     *
     * @return string|null
     */
    function osc_settings_validate(array $field, $value)
    {
        $label = $field['label'] ?? $field['name'];

        if (!empty($field['required']) && ($value === '' || $value === false || $value === null)) {
            return sprintf(__('%s cannot be left empty'), $label);
        }

        // Everything below describes a value that is there. An optional field left blank
        // has nothing to check and must not be rejected by a min or a pattern.
        if ($value === '' || $value === null) {
            return null;
        }

        switch ($field['type']) {
            case 'number':
                if (!is_int($value) && !is_float($value)) {
                    return sprintf(__('%s must be a number'), $label);
                }
                if (isset($field['min']) && $value < $field['min']) {
                    return sprintf(__('%1$s must be %2$s or more'), $label, $field['min']);
                }
                if (isset($field['max']) && $value > $field['max']) {
                    return sprintf(__('%1$s must be %2$s or less'), $label, $field['max']);
                }
                break;
            case 'email':
                if (!osc_validate_email($value)) {
                    return sprintf(__('%s is not a valid email address'), $label);
                }
                break;
            case 'url':
                if (!osc_validate_url($value)) {
                    return sprintf(__('%s is not a valid URL'), $label);
                }
                break;
            case 'select':
            case 'radio':
                // A <select> only offers what was declared, so anything else was hand-made:
                // reject it rather than store a value the page can never show again.
                if (!array_key_exists((string)$value, $field['options'])) {
                    return sprintf(__('%s is not one of the available options'), $label);
                }
                break;
            default:
                break;
        }

        if (isset($field['maxlength']) && mb_strlen((string)$value) > (int)$field['maxlength']) {
            return sprintf(__('%1$s must be %2$s characters or fewer'), $label, (int)$field['maxlength']);
        }
        if (isset($field['pattern']) && !preg_match($field['pattern'], (string)$value)) {
            return sprintf(__('%s is not in the expected format'), $label);
        }
        if (isset($field['validate'])) {
            $error = call_user_func($field['validate'], $value, $field);
            if (is_string($error) && $error !== '') {
                return $error;
            }
        }

        return null;
    }
}

if (!function_exists('osc_settings_save')) {
    /**
     * Sanitise, validate and store every field on a declared page.
     *
     * Nothing is written unless every field passes: half a saved page is worse than a
     * rejected one, because the half that landed is invisible. The same guarantee covers
     * the lifecycle hooks -- 'admin_form_after_save' and a page's inline 'after_save' never
     * run on a rejected submission, and each runs exactly once on a successful one.
     *
     * The order on a successful save is part of the contract: 'admin_form_before_save'
     * filters the values, the store writes them, the 'admin_form_after_save' hook runs, and
     * the page's own inline 'after_save' runs last -- so a page sees whatever a listener
     * already did rather than racing it.
     *
     * @param string $pageId
     *
     * @return array array('errors' => string[], 'updated' => int, 'values' => array)
     */
    function osc_settings_save($pageId)
    {
        $page = osc_settings_page($pageId);
        if ($page === null) {
            $errors = array(__('That settings page is not registered.'));
            osc_run_hook('admin_form_save_failed', $pageId, $errors, array());

            return array('errors' => $errors, 'updated' => 0, 'values' => array());
        }

        $values = array();
        $errors = array();
        foreach (SettingsPageRegistry::instance()->fields($pageId) as $name => $field) {
            if ($field['type'] === 'custom') {
                // Core does not know what a custom field submitted, so it does not pretend
                // to store it. The plugin owns the value the same way it owns the markup.
                continue;
            }
            $value          = osc_settings_sanitize($field);
            $values[$name]  = $value;
            $error          = osc_settings_validate($field, $value);
            if ($error !== null) {
                $errors[] = $error;
            }
        }

        if ($errors !== array()) {
            osc_run_hook('admin_form_save_failed', $pageId, $errors, $values);

            return array('errors' => $errors, 'updated' => 0, 'values' => $values);
        }

        // A filter, not an action: deriving or normalising a value means handing it back,
        // and a hook would only ever mutate its own copy of the array.
        $filtered = osc_apply_filter('admin_form_before_save', $values, $pageId);
        $values   = is_array($filtered) ? $filtered : $values;

        // Walking the declared fields rather than $values is what keeps a before_save
        // listener from writing a key the page never declared. A custom field stays
        // uncollected here too: core did not read it, so it does not write one back.
        $updated = 0;
        foreach (SettingsPageRegistry::instance()->fields($pageId) as $name => $field) {
            if ($field['type'] === 'custom' || !array_key_exists($name, $values)) {
                continue;
            }
            $value = $values[$name];
            if ($field['type'] === 'checkbox') {
                $value = $value ? '1' : '0';
            }
            $updated += (int)osc_set_preference($name, (string)$value, $page['section'], 'STRING');
        }

        // No entity primary key exists until a table-backed store lands; a preference page
        // passes null where a future model-backed page would pass the row it just wrote.
        osc_run_hook('admin_form_after_save', $pageId, $values, null);
        if (isset($page['after_save']) && is_callable($page['after_save'])) {
            call_user_func($page['after_save'], $values, null);
        }

        osc_run_hook('settings_page_saved', $pageId, $values);

        return array('errors' => array(), 'updated' => $updated, 'values' => $values);
    }
}

if (!function_exists('osc_settings_menu_init')) {
    /**
     * Put every declared page that asked for one into its menu section. Registered on
     * admin_menu_init, which runs after plugins have loaded and therefore after they have
     * declared their pages.
     *
     * @return void
     */
    function osc_settings_menu_init()
    {
        foreach (osc_settings_pages() as $id => $page) {
            if ($page['menu'] === '') {
                continue;
            }
            osc_add_admin_submenu_page(
                $page['menu'],
                $page['menu_title'],
                osc_settings_page_url($id),
                'settings-page-' . $id,
                $page['capability']
            );
        }
    }
}

osc_add_hook('admin_menu_init', 'osc_settings_menu_init');

/* file end: ./oc-includes/osclass/helpers/hSettings.php */
