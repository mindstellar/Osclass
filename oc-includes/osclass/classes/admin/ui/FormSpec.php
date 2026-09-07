<?php

/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace mindstellar\admin\ui;

use LogicException;

/**
 * The builder behind osc_admin_form(). It is sugar and nothing else: every method writes
 * into a spec array, toArray() hands that array back, and register() passes it to the
 * registry unchanged. No storage, no rendering and no validation of its own -- a bad spec
 * still fails at registration, in the one place that already knows what a good one is.
 *
 * The array form stays fully supported. Anything the builder cannot express can be added
 * with field() or set(), so a page never has to drop back to a hand-written form.
 *
 * Three names that do not mean what a reader might assume:
 *   section()  sets the *preference section* the values are stored in -- the page-level
 *              'section' key. The visual sections of a page are groups: use group().
 *   default()  sets the field's 'default', the value used until something is saved.
 *   hint()     sets the field's 'help' text, because help() is taken by the page-level
 *              'help' key. A help() chained onto a field writes the page, not the field.
 *
 * Modifiers apply to the field most recently added, so they read as a suffix on it:
 * ->text('s_name', $label)->required(). Calling one before any field is a programming
 * error and throws.
 *
 * @package mindstellar\admin\ui
 */
final class FormSpec
{
    /**
     * Emission order for page keys, so builder output does not depend on the order the
     * author happened to chain in. Keys never set are not emitted at all.
     */
    private const PAGE_KEY_ORDER = array(
        'title',
        'menu',
        'menu_title',
        'section',
        'capability',
        'help',
        'intro',
        'after_save',
    );

    /** Emission order for field keys. Anything else follows, in the order it was set. */
    private const FIELD_KEY_ORDER = array(
        'type',
        'name',
        'label',
        'help',
        'default',
        'required',
        'disabled',
        'options',
        'row_label',
        'prefix',
        'suffix',
        'width',
        'attrs',
        'render',
        'sanitize',
        'validate',
    );

    private string $id;

    /** @var array<string,mixed> page-level keys, only those actually set */
    private array $page = array();

    /** @var array<int,array{title?:string,intro?:string,fields:array<int,array>}> */
    private array $groups = array();

    private ?int $groupIndex = null;

    private ?int $fieldIndex = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * The page id this spec registers under. Not part of the spec array -- the registry
     * takes it as its own argument.
     */
    public function id(): string
    {
        return $this->id;
    }

    // --- page-level keys ------------------------------------------------------------

    public function title(string $title): self
    {
        return $this->setPage('title', $title);
    }

    /** One of SettingsPageRegistry::MENUS, or '' for a page with no menu entry. */
    public function menu(string $menu): self
    {
        return $this->setPage('menu', $menu);
    }

    public function menuTitle(string $menuTitle): self
    {
        return $this->setPage('menu_title', $menuTitle);
    }

    /** The preference section the values are stored in. For a visual section, see group(). */
    public function section(string $section): self
    {
        return $this->setPage('section', $section);
    }

    public function capability(string $capability): self
    {
        return $this->setPage('capability', $capability);
    }

    /** The page's own help text. For a field's, see hint(). */
    public function help(string $help): self
    {
        return $this->setPage('help', $help);
    }

    public function intro(string $intro): self
    {
        return $this->setPage('intro', $intro);
    }

    /**
     * The page's own effect, run once after a successful save and never after a rejected
     * one. Alternative to hooking 'admin_form_after_save' when the effect belongs to this
     * page alone.
     *
     * @param mixed $callback callable(array $values, string $id): void
     */
    public function onAfterSave($callback): self
    {
        return $this->setPage('after_save', $callback);
    }

    // --- groups ---------------------------------------------------------------------

    /**
     * Start a new group. Fields added before the first group() land in an untitled one,
     * which is the single-group page most plugins want.
     */
    public function group(string $title = '', string $intro = ''): self
    {
        $group = array();
        if ($title !== '') {
            $group['title'] = $title;
        }
        if ($intro !== '') {
            $group['intro'] = $intro;
        }
        $group['fields'] = array();

        $this->groups[]   = $group;
        $this->groupIndex = count($this->groups) - 1;
        $this->fieldIndex = null;

        return $this;
    }

    // --- fields ---------------------------------------------------------------------

    public function text(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('text', $name, $label, $help));
    }

    public function email(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('email', $name, $label, $help));
    }

    public function url(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('url', $name, $label, $help));
    }

    public function tel(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('tel', $name, $label, $help));
    }

    public function number(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('number', $name, $label, $help));
    }

    public function color(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('color', $name, $label, $help));
    }

    public function secret(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('secret', $name, $label, $help));
    }

    public function textarea(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('textarea', $name, $label, $help));
    }

    /** @param array<string,string> $options value => label */
    public function select(string $name, string $label = '', array $options = array(), string $help = ''): self
    {
        $field = $this->base('select', $name, $label, $help);
        if ($options !== array()) {
            $field['options'] = $options;
        }

        return $this->field($field);
    }

    /** @param array<string,string> $options value => label */
    public function radio(string $name, string $label = '', array $options = array(), string $help = ''): self
    {
        $field = $this->base('radio', $name, $label, $help);
        if ($options !== array()) {
            $field['options'] = $options;
        }

        return $this->field($field);
    }

    /** The label sits beside the control; the row's own label comes from rowLabel(). */
    public function checkbox(string $name, string $label = '', string $help = ''): self
    {
        return $this->field($this->base('checkbox', $name, $label, $help));
    }

    /**
     * A field core has no type for. The callable emits the control into the normal row.
     *
     * @param mixed $render callable(array $field, array $values): void
     */
    public function custom(string $name, $render = null, string $label = ''): self
    {
        $field = $this->base('custom', $name, $label, '');
        if ($render !== null) {
            $field['render'] = $render;
        }

        return $this->field($field);
    }

    /**
     * Append a field spec as written. The escape hatch for anything the typed methods do
     * not cover; the array form of a field is what the registry stores either way.
     */
    public function field(array $field): self
    {
        if ($this->groupIndex === null) {
            $this->group();
        }

        $this->groups[$this->groupIndex]['fields'][] = $field;
        $this->fieldIndex                            = count($this->groups[$this->groupIndex]['fields']) - 1;

        return $this;
    }

    // --- field modifiers ------------------------------------------------------------

    public function required(bool $required = true): self
    {
        return $this->set('required', $required);
    }

    public function disabled(bool $disabled = true): self
    {
        return $this->set('disabled', $disabled);
    }

    /**
     * The value used until something is saved.
     *
     * @param mixed $value
     */
    public function default($value): self
    {
        return $this->set('default', $value);
    }

    /** @param array<string,string> $options */
    public function options(array $options): self
    {
        return $this->set('options', $options);
    }

    /** The row label of a checkbox, whose own label sits beside the control. */
    public function rowLabel(string $label): self
    {
        return $this->set('row_label', $label);
    }

    /** The field's 'help' text. The page-level help() is a different key. */
    public function hint(string $help): self
    {
        return $this->set('help', $help);
    }

    public function prefix(string $prefix): self
    {
        return $this->set('prefix', $prefix);
    }

    public function suffix(string $suffix): self
    {
        return $this->set('suffix', $suffix);
    }

    public function width(string $width): self
    {
        return $this->set('width', $width);
    }

    /** @param array<string,string> $attrs */
    public function attrs(array $attrs): self
    {
        return $this->set('attrs', $attrs);
    }

    /** @param mixed $render callable(array $field, array $values): void */
    public function render($render): self
    {
        return $this->set('render', $render);
    }

    /** @param mixed $callback callable(mixed $value): mixed, run before validation */
    public function sanitize($callback): self
    {
        return $this->set('sanitize', $callback);
    }

    /** @param mixed $callback callable(mixed $value, array $field): ?string */
    public function validate($callback): self
    {
        return $this->set('validate', $callback);
    }

    /**
     * Set any key on the field most recently added. Covers everything osc_admin_field()
     * understands that has no method of its own.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        if ($this->groupIndex === null || $this->fieldIndex === null) {
            throw new LogicException('FormSpec: "' . $key . '" was set before any field was added');
        }

        $this->groups[$this->groupIndex]['fields'][$this->fieldIndex][$key] = $value;

        return $this;
    }

    // --- output ---------------------------------------------------------------------

    /**
     * The spec array, exactly as osc_register_settings_page() takes it. Inspectable
     * without registering, which is how builder output is held equal to a hand-written
     * array.
     */
    public function toArray(): array
    {
        $spec = self::ordered($this->page, self::PAGE_KEY_ORDER);

        $groups = array();
        foreach ($this->groups as $group) {
            $fields = array();
            foreach ($group['fields'] as $field) {
                $fields[] = self::ordered($field, self::FIELD_KEY_ORDER);
            }
            $group['fields'] = $fields;
            $groups[]        = $group;
        }
        $spec['groups'] = $groups;

        return $spec;
    }

    /**
     * Hand the spec to the registry. Goes through osc_register_settings_page() so the
     * builder and the array form enter by the same door.
     *
     * @throws \InvalidArgumentException on an invalid id or spec.
     */
    public function register(): void
    {
        osc_register_settings_page($this->id, $this->toArray());
    }

    // --- internals ------------------------------------------------------------------

    /**
     * @param mixed $value
     */
    private function setPage(string $key, $value): self
    {
        $this->page[$key] = $value;

        return $this;
    }

    private function base(string $type, string $name, string $label, string $help): array
    {
        $field = array('type' => $type, 'name' => $name);
        if ($label !== '') {
            $field['label'] = $label;
        }
        if ($help !== '') {
            $field['help'] = $help;
        }

        return $field;
    }

    /**
     * Reorder an array onto a known key order, keeping anything unlisted after it in the
     * order it was set. Key order is part of what toArray() promises: two specs that say
     * the same thing have to compare equal.
     *
     * @param array<string,mixed> $values
     * @param array<int,string>   $order
     *
     * @return array<string,mixed>
     */
    private static function ordered(array $values, array $order): array
    {
        $out = array();
        foreach ($order as $key) {
            if (array_key_exists($key, $values)) {
                $out[$key] = $values[$key];
                unset($values[$key]);
            }
        }

        return $out + $values;
    }
}
