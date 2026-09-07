<?php

/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace mindstellar\admin\form\store;

use Preference;

/**
 * The default store: one preference per field, under the page's own section.
 *
 * This is the behaviour the declarative settings page has always had, moved behind the
 * store interface unchanged. A page that declares no store gets it, so nothing already
 * registered has to be edited.
 *
 * @package mindstellar\admin\form\store
 */
final class PreferenceStore implements Store
{
    private string $section;

    public function __construct(string $section)
    {
        $this->section = $section;
    }

    /**
     * @inheritDoc
     */
    public function value(string $name, array $field, $id = null)
    {
        $locales = osc_settings_field_locales($field);
        if ($locales !== array()) {
            // A translated field is not one value but one per locale, keyed by locale code,
            // so a caller reads osc_settings_value(...)[$code] and never has to know how the
            // preference key is spelled.
            $values = array();
            foreach ($locales as $code => $localeName) {
                $stored        = Preference::newInstance()->get($name . $code, $this->section);
                $values[$code] = ($stored === null || $stored === '')
                    ? (string)($field['default'] ?? '')
                    : $stored;
            }

            return $values;
        }

        $stored = Preference::newInstance()->get($name, $this->section);
        if ($stored === null || $stored === '') {
            // A checkbox saved as off stores '0', not '', so an empty read really is
            // "never saved" and the declared default is the right answer.
            return $field['default'] ?? (($field['type'] ?? 'text') === 'checkbox' ? false : '');
        }

        return osc_settings_cast($field['type'] ?? 'text', $stored);
    }

    /**
     * @inheritDoc
     */
    public function load(array $fields, $id = null): array
    {
        $values = array();
        foreach ($fields as $name => $field) {
            $values[$name] = $this->value($name, $field, $id);
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function save(array $fields, array $values, array $locales, $id = null): array
    {
        $updated = 0;
        foreach ($fields as $name => $field) {
            if ($field['type'] === 'custom' || !array_key_exists($name, $values)) {
                // Core does not know what a custom field submitted, so it does not pretend
                // to store it. The plugin owns the value the same way it owns the markup.
                continue;
            }
            if (($locales[$name] ?? array()) !== array()) {
                // Core writes only what it can read back: a before_save listener that
                // replaced the per-locale array with a scalar has nothing to spread over
                // the locales, and the bare name is a key this page never reads.
                if (!is_array($values[$name])) {
                    continue;
                }
                foreach ($locales[$name] as $code => $localeName) {
                    $updated += (int)osc_set_preference(
                        $name . $code,
                        (string)($values[$name][$code] ?? ''),
                        $this->section,
                        'STRING'
                    );
                }
                continue;
            }
            $value = $values[$name];
            if ($field['type'] === 'checkbox') {
                $value = $value ? '1' : '0';
            }
            $updated += (int)osc_set_preference($name, (string)$value, $this->section, 'STRING');
        }

        // Preferences are keyed by name, not by row, so there is no primary key to hand
        // an after_save listener.
        return array('updated' => $updated, 'id' => null);
    }
}
