<?php

/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace mindstellar\settings;

use InvalidArgumentException;
use mindstellar\admin\form\store\TableStore;

/**
 * Class SettingsPageRegistry
 *
 * Registry of declared admin settings pages. A page is a spec -- a title, a menu to
 * appear under, and groups of fields -- and core does the rest: the page shell, the
 * form and its CSRF token, sanitisation and validation per field type, persistence,
 * the flash message and the save row.
 *
 * The field primitives in hAdminUi.php gave an author one way to draw a field. They
 * did not stop anyone re-inventing the page around them -- a form, a save handler, a
 * CSRF check, a validation block -- and that re-invention is where the security bugs
 * live. This is the layer that removes the need for it.
 *
 * Deliberately mirrors mindstellar\fields\FieldTypeRegistry and
 * mindstellar\pages\PageTemplateRegistry: one spec array, one register(), the same
 * optional sanitize/validate callables, so there is one mental model for all three.
 *
 * @package mindstellar\settings
 */
final class SettingsPageRegistry
{
    /** Field types a declared page may use. Anything else is a spec error, not a fallback. */
    public const FIELD_TYPES = array(
        'text',
        'email',
        'url',
        'tel',
        'number',
        'color',
        'secret',
        'textarea',
        'select',
        'radio',
        'checkbox',
        'custom',
    );

    /** Anything used as a table, column or key name. Matches what QueryBuilder will accept. */
    private const IDENTIFIER = '/^[A-Za-z0-9_]+$/';

    /** Core menu sections a page may ask to appear under. */
    public const MENUS = array(
        'settings',
        'plugins',
        'appearance',
        'tools',
        'items',
        'users',
        'pages',
        'stats',
    );

    private static ?SettingsPageRegistry $instance = null;

    /** @var array<string,array> normalised page specs, keyed by id */
    private array $pages = array();

    /** @var array<string,int> ids a second registration tried to claim */
    private array $conflicts = array();

    private function __construct()
    {
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register a settings page.
     *
     * $spec keys:
     *   'title'      => string    Page heading and browser title. Required.
     *   'menu'       => string    One of MENUS, or '' for a page with no menu entry
     *                             (reached from a link the plugin puts somewhere else).
     *                             Defaults to 'plugins'.
     *   'menu_title' => string    Menu label; defaults to 'title'.
     *   'section'    => string    Preference section the values are stored in.
     *                             Defaults to the page id, which is what keeps one
     *                             plugin's keys out of core's 'osclass' section.
     *   'capability' => string    'administrator' (default) or 'moderator'.
     *   'store'      => string|array Where the values live. 'preference' (the default)
     *                             writes one preference per field under 'section'.
     *                             array('table' => 't_ban_rule', 'pk' => 'pk_i_id') writes
     *                             one row of that table -- unprefixed, core applies
     *                             DB_TABLE_PREFIX. Rows are addressed by an integer key,
     *                             and the key comes from the controller rather than from
     *                             the request: no key inserts a row, and a key that is not
     *                             a positive integer is refused. The generic settings
     *                             controller therefore does not serve a table-backed page
     *                             -- one needs a controller that supplies a row id it has
     *                             validated for this admin.
     *   'help'       => string    Help-box body for the "?" beside the page title.
     *   'intro'      => string    Explanatory paragraph above the first group.
     *   'groups'     => array[]   array('title' =>, 'intro' =>, 'fields' => array[]).
     *   'fields'     => array[]   Sugar for a single untitled group.
     *   'validate'   => callable callable(array $values, string $pageId, $id):
     *                             string|string[]|null returning the error, or the errors, a
     *                             rule spanning more than one field found. $id is the row
     *                             being saved, null on an insert and on a preference page,
     *                             so "that name is taken" can exclude the row being edited.
     *                             Runs after every field has been checked, so a page reports
     *                             all its problems at once. A field discarded with its
     *                             'depends' master off is not in $values at all.
     *   'after_save' => callable callable(array $values, $id): void, run once after a
     *                             successful save, never on a rejected one. An alternative
     *                             to hooking 'admin_form_after_save' when the effect only
     *                             belongs to this one page; it runs after that hook, not
     *                             before it.
     *
     * A field spec is what osc_admin_field() takes, plus:
     *   'default'  => mixed     Value used until something is saved.
     *   'required' => bool      Rejected as empty on save.
     *   'sanitize' => callable  callable(mixed $value): mixed, run before validation.
     *   'validate' => callable  callable(mixed $value, array $field): ?string returning
     *                           an error message, or null when the value is good.
     *   'depends'  => string    Another field on this page -- not a custom or translated
     *                           one, and not a cycle. While that field is off, this one is
     *                           hidden, is not required, and its submitted value is
     *                           discarded rather than stored.
     *   'translate' => bool     text and textarea only: one control per enabled locale,
     *                           each stored under the field name plus the locale code.
     *   'column'   => string    Table stores only: the column this field maps to, when it
     *                           is not the field's own name.
     *
     * @param string $id   Namespaced slug, [a-z0-9_.-]{1,60}. Usually the plugin's own.
     * @param array  $spec Page specification (see above).
     *
     * @throws InvalidArgumentException on an invalid id or spec.
     */
    public function register(string $id, array $spec): void
    {
        if (!self::isValidId($id)) {
            throw new InvalidArgumentException('SettingsPageRegistry: invalid page id "' . $id . '"');
        }

        // Two plugins claiming one id: the first keeps it. Replacing it would take the other
        // plugin's page off the menu *and* point its saved values at a section it no longer
        // reads, both silently. Throwing is not the answer either -- plugins are included
        // unguarded from oc-load.php, so an exception here white-screens the whole site,
        // front end included, over a name collision. See conflicts().
        if (isset($this->pages[$id])) {
            $this->conflicts[$id] = ($this->conflicts[$id] ?? 0) + 1;

            return;
        }
        if (empty($spec['title']) || !is_string($spec['title'])) {
            throw new InvalidArgumentException('SettingsPageRegistry: page "' . $id . '" needs a string title');
        }

        $menu = (string)($spec['menu'] ?? 'plugins');
        if ($menu !== '' && !in_array($menu, self::MENUS, true)) {
            throw new InvalidArgumentException(
                'SettingsPageRegistry: page "' . $id . '" menu "' . $menu . '" is not a core menu section'
            );
        }

        $groups = $spec['groups'] ?? array();
        if (isset($spec['fields'])) {
            array_unshift($groups, array('fields' => $spec['fields']));
        }
        if ($groups === array()) {
            throw new InvalidArgumentException('SettingsPageRegistry: page "' . $id . '" declares no fields');
        }
        if (isset($spec['after_save']) && !is_callable($spec['after_save'])) {
            throw new InvalidArgumentException('SettingsPageRegistry: page "' . $id . '" after_save must be callable');
        }
        if (isset($spec['validate']) && !is_callable($spec['validate'])) {
            throw new InvalidArgumentException('SettingsPageRegistry: page "' . $id . '" validate must be callable');
        }

        $store = $this->normaliseStore($id, $spec['store'] ?? 'preference');

        $this->pages[$id] = array(
            'id'         => $id,
            'title'      => $spec['title'],
            'menu'       => $menu,
            'menu_title' => isset($spec['menu_title']) && is_string($spec['menu_title'])
                ? $spec['menu_title']
                : $spec['title'],
            'section'    => isset($spec['section']) && is_string($spec['section']) && $spec['section'] !== ''
                ? $spec['section']
                : $id,
            'store'      => $store,
            'capability' => ($spec['capability'] ?? '') === 'moderator' ? 'moderator' : 'administrator',
            'help'       => isset($spec['help']) && is_string($spec['help']) ? $spec['help'] : '',
            'intro'      => isset($spec['intro']) && is_string($spec['intro']) ? $spec['intro'] : '',
            'groups'     => $this->normaliseGroups($id, $groups, $store),
            'validate'   => isset($spec['validate']) && is_callable($spec['validate']) ? $spec['validate'] : null,
            'after_save' => isset($spec['after_save']) && is_callable($spec['after_save']) ? $spec['after_save'] : null,
        );
    }

    /**
     * The spec for a registered page, or null when the id is not registered (e.g. its
     * plugin is deactivated -- which is exactly when a bookmarked URL is still requested).
     */
    public function get(string $id): ?array
    {
        return $this->pages[$id] ?? null;
    }

    /**
     * All registered pages, keyed by id, in registration order.
     *
     * @return array<string,array>
     */
    public function all(): array
    {
        return $this->pages;
    }

    /**
     * Every field on a page, flattened out of its groups and keyed by name. The save
     * path walks this rather than the groups: what a field is grouped with is a layout
     * decision and has nothing to do with what gets stored.
     *
     * @return array<string,array>
     */
    public function fields(string $id): array
    {
        $page = $this->get($id);
        if ($page === null) {
            return array();
        }

        $fields = array();
        foreach ($page['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                $fields[$field['name']] = $field;
            }
        }

        return $fields;
    }

    /**
     * Ids more than one registration tried to claim, and how many times. Empty on a healthy
     * install; non-empty means a plugin's settings page is not the one on screen.
     *
     * @return array<string,int>
     */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    /**
     * Whether $id is a well-formed page id.
     */
    public static function isValidId(string $id): bool
    {
        return (bool)preg_match('/^[a-z0-9_.-]{1,60}$/', $id);
    }

    /**
     * Normalise the page's 'store' into array('type' => 'preference') or
     * array('type' => 'table', 'table' =>, 'pk' =>).
     *
     * A store nobody implements has to be refused here: accepted, it would fall back to
     * preferences and the page would look saved while its table stayed empty.
     *
     * @param mixed $store
     *
     * @throws InvalidArgumentException
     */
    private function normaliseStore(string $id, $store): array
    {
        $prefix = 'SettingsPageRegistry: page "' . $id . '" store ';

        if (is_string($store)) {
            if ($store !== 'preference') {
                throw new InvalidArgumentException($prefix . '"' . $store . '" is not a store core has');
            }

            return array('type' => 'preference');
        }
        if (!is_array($store)) {
            throw new InvalidArgumentException(
                $prefix . 'must be "preference" or an array naming a table and its pk'
            );
        }

        foreach (array('table', 'pk') as $key) {
            if (!isset($store[$key]) || !is_string($store[$key]) || $store[$key] === '') {
                throw new InvalidArgumentException($prefix . 'needs a ' . $key);
            }
            if (!preg_match(self::IDENTIFIER, $store[$key])) {
                throw new InvalidArgumentException(
                    $prefix . $key . ' "' . $store[$key] . '" is not an identifier'
                );
            }
        }

        return array('type' => 'table', 'table' => $store['table'], 'pk' => $store['pk']);
    }

    /**
     * Hold one field against the store the page writes through.
     *
     * Every refusal here is something that registers cleanly and then goes wrong at save
     * time: a 'column' on a preference page is a mapping nothing applies, a field mapped
     * onto the primary key is the store overwriting the row it is addressing, and a
     * translated field has one value per locale where a column holds one.
     *
     * @throws InvalidArgumentException
     */
    private function checkFieldStorage(string $id, array $field, string $type, array $store): void
    {
        $prefix = 'SettingsPageRegistry: page "' . $id . '" field "' . $field['name'] . '" ';
        $table  = $store['type'] === 'table';

        if (isset($field['column'])) {
            if (!is_string($field['column']) || $field['column'] === '') {
                throw new InvalidArgumentException($prefix . 'column must name a column');
            }
            if (!$table) {
                throw new InvalidArgumentException(
                    $prefix . 'declares a column, which only a table store writes'
                );
            }
        }
        if (!$table || $type === 'custom') {
            return;
        }

        $column = TableStore::column($field['name'], $field);
        if (!preg_match(self::IDENTIFIER, $column)) {
            throw new InvalidArgumentException($prefix . 'maps to "' . $column . '", which is not an identifier');
        }
        if ($column === $store['pk']) {
            throw new InvalidArgumentException(
                $prefix . 'maps to "' . $column . '", the primary key the store addresses the row by'
            );
        }
        if (!empty($field['translate'])) {
            throw new InvalidArgumentException(
                $prefix . 'cannot be translated on a table store: a column holds one value, not one per locale'
            );
        }
    }

    /**
     * Check the shape of every group and field up front, so a typo in a spec is an
     * exception at registration time rather than a silently missing field on a page.
     *
     * @throws InvalidArgumentException
     */
    private function normaliseGroups(string $id, array $groups, array $store): array
    {
        $out     = array();
        $seen    = array();
        $depends = array();

        foreach ($groups as $group) {
            if (!is_array($group) || !isset($group['fields']) || !is_array($group['fields'])) {
                throw new InvalidArgumentException('SettingsPageRegistry: page "' . $id . '" has a group with no fields');
            }

            $fields = array();
            foreach ($group['fields'] as $field) {
                if (!is_array($field) || empty($field['name']) || !is_string($field['name'])) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" has a field with no name'
                    );
                }

                $type = (string)($field['type'] ?? 'text');
                if (!in_array($type, self::FIELD_TYPES, true)) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" field "' . $field['name']
                        . '" has unknown type "' . $type . '"'
                    );
                }
                if (in_array($type, array('select', 'radio'), true)
                    && (empty($field['options']) || !is_array($field['options']))
                ) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" field "' . $field['name'] . '" needs options'
                    );
                }
                if ($type === 'custom' && (!isset($field['render']) || !is_callable($field['render']))) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" field "' . $field['name']
                        . '" is custom and needs a render callable'
                    );
                }
                foreach (array('sanitize', 'validate') as $cb) {
                    if (isset($field[$cb]) && !is_callable($field[$cb])) {
                        throw new InvalidArgumentException(
                            'SettingsPageRegistry: page "' . $id . '" field "' . $field['name']
                            . '" ' . $cb . ' must be callable'
                        );
                    }
                }
                if (isset($field['translate']) && $field['translate']
                    && !in_array($type, array('text', 'textarea'), true)
                ) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" field "' . $field['name']
                        . '" cannot be translated: only text and textarea expand over locales'
                    );
                }
                $this->checkFieldStorage($id, $field, $type, $store);
                if (isset($field['depends'])) {
                    if (!is_string($field['depends']) || $field['depends'] === '') {
                        throw new InvalidArgumentException(
                            'SettingsPageRegistry: page "' . $id . '" field "' . $field['name']
                            . '" depends must name a field'
                        );
                    }
                    $depends[$field['name']] = $field['depends'];
                }
                if (isset($seen[$field['name']])) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" declares "' . $field['name'] . '" twice'
                    );
                }

                $seen[$field['name']] = array('type' => $type, 'translate' => !empty($field['translate']));
                $field['type']        = $type;
                $fields[]             = $field;
            }

            $out[] = array(
                'title'  => isset($group['title']) && is_string($group['title']) ? $group['title'] : '',
                'intro'  => isset($group['intro']) && is_string($group['intro']) ? $group['intro'] : '',
                'fields' => $fields,
            );
        }

        // Checked once every group is in: a master may be declared in a later group than
        // the field depending on it. Every refusal here is a relationship that would
        // register cleanly and then discard the dependent's value on every save.
        foreach ($depends as $name => $master) {
            $prefix = 'SettingsPageRegistry: page "' . $id . '" field "' . $name . '" depends on ';
            if (!isset($seen[$master])) {
                throw new InvalidArgumentException(
                    $prefix . '"' . $master . '", which the page does not declare'
                );
            }
            if ($seen[$master]['type'] === 'custom') {
                throw new InvalidArgumentException(
                    $prefix . 'custom field "' . $master . '", whose value core never reads'
                );
            }
            if ($seen[$master]['translate']) {
                throw new InvalidArgumentException(
                    $prefix . 'translated field "' . $master . '", which has one value per locale'
                );
            }
        }

        foreach ($depends as $name => $master) {
            $path  = array($name);
            $chain = array($name => true);
            for ($at = $master; ; $at = $depends[$at]) {
                $path[] = $at;
                if (isset($chain[$at])) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" has a depends cycle: ' . implode(' -> ', $path)
                    );
                }
                $chain[$at] = true;
                if (!isset($depends[$at])) {
                    break;
                }
            }
        }

        return $out;
    }
}
