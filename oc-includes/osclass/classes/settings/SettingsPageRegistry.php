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
     *   'help'       => string    Help-box body for the "?" beside the page title.
     *   'intro'      => string    Explanatory paragraph above the first group.
     *   'groups'     => array[]   array('title' =>, 'intro' =>, 'fields' => array[]).
     *   'fields'     => array[]   Sugar for a single untitled group.
     *
     * A field spec is what osc_admin_field() takes, plus:
     *   'default'  => mixed     Value used until something is saved.
     *   'required' => bool      Rejected as empty on save.
     *   'sanitize' => callable  callable(mixed $value): mixed, run before validation.
     *   'validate' => callable  callable(mixed $value, array $field): ?string returning
     *                           an error message, or null when the value is good.
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
            'capability' => ($spec['capability'] ?? '') === 'moderator' ? 'moderator' : 'administrator',
            'help'       => isset($spec['help']) && is_string($spec['help']) ? $spec['help'] : '',
            'intro'      => isset($spec['intro']) && is_string($spec['intro']) ? $spec['intro'] : '',
            'groups'     => $this->normaliseGroups($id, $groups),
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
     * Whether $id is a well-formed page id.
     */
    public static function isValidId(string $id): bool
    {
        return (bool)preg_match('/^[a-z0-9_.-]{1,60}$/', $id);
    }

    /**
     * Check the shape of every group and field up front, so a typo in a spec is an
     * exception at registration time rather than a silently missing field on a page.
     *
     * @throws InvalidArgumentException
     */
    private function normaliseGroups(string $id, array $groups): array
    {
        $out  = array();
        $seen = array();

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
                if (isset($seen[$field['name']])) {
                    throw new InvalidArgumentException(
                        'SettingsPageRegistry: page "' . $id . '" declares "' . $field['name'] . '" twice'
                    );
                }

                $seen[$field['name']] = true;
                $field['type']        = $type;
                $fields[]             = $field;
            }

            $out[] = array(
                'title'  => isset($group['title']) && is_string($group['title']) ? $group['title'] : '',
                'intro'  => isset($group['intro']) && is_string($group['intro']) ? $group['intro'] : '',
                'fields' => $fields,
            );
        }

        return $out;
    }
}
