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

/**
 * The rendering behind the osc_admin_field() family. Those functions are the public,
 * plugin-facing API and stay procedural; this class is the implementation they delegate to,
 * so the field logic lives in one testable object instead of a spread of free functions.
 *
 * A Field is built from the same spec array the helpers take (see osc_admin_field()); the
 * static methods back the lower-level helpers that pass a type or id in explicitly.
 */
class Field
{
    /** The types a browser applies maxlength to; on the rest the attribute is ignored. */
    private const LENGTH_HINT_TYPES = array('text', 'email', 'url', 'tel', 'secret', 'textarea');

    /**
     * The types whose stored value is escaped on save, so its length is not the one the
     * browser counted. Mirrors SettingsPageRegistry::PURIFIED_TYPES, copied rather than
     * imported so a field primitive need not know about the settings registry.
     */
    private const PURIFIED_TYPES = array('text', 'textarea', 'tel', 'color');

    /** @var array */
    private $spec;

    /** @var string */
    private $type;

    /** @var string */
    private $id;

    /**
     * @param array $spec
     */
    public function __construct(array $spec)
    {
        $this->spec = $spec;
        $this->type = $spec['type'] ?? 'text';
        $this->id   = self::idFor($spec);
    }

    /**
     * One labelled field, row and all. The body of osc_admin_field().
     *
     * @return void
     */
    public function render()
    {
        $type    = $this->type;
        $id      = $this->id;
        $spec    = $this->spec;
        $row     = $spec['row'] ?? true;
        $locales = self::localesFor($spec);

        if ($row) {
            // A checkbox carries its own label beside the control; anything else labels the
            // row. Passing both would print the label twice.
            $rowLabel = $type === 'checkbox'
                ? ($spec['row_label'] ?? '')
                : ($spec['label'] ?? '');
            // A choice list has no single control to point at -- each option owns its own
            // label -- so the row label stays plain text and the group is named through
            // aria-label instead. A `for` pointing at an id nothing carries reaches nothing.
            $opts = $type === 'radio'
                ? array()
                : array('for' => $locales === array() ? $id : self::idFor(array(
                    'name' => (string)($spec['name'] ?? '') . array_key_first($locales),
                )));
            // The row is what the shared script shows and hides, so the relationship is
            // declared on it. Which way it resolves is decided again on save: this is a
            // convenience, not the rule.
            if (!empty($spec['depends'])) {
                $opts['data'] = array('osc-depends' => (string)$spec['depends']);
            }
            osc_admin_form_row_open($rowLabel, $opts);
        }

        if ($type === 'checkbox') {
            $spec['id'] = $id;
            osc_admin_checkbox($spec);
        } elseif ($locales !== array()) {
            self::translated($id, $spec, $locales);
            self::help($spec);
        } else {
            // The words either side of a field, and a secret's reveal button, sit on the
            // control's own line. The inputs are display:block, so without this they drop
            // underneath. Each affix is a whole phrase the translator can read, which is the
            // point: the sentence is never split around an opaque %s they cannot move.
            $prefix = (string)($spec['prefix'] ?? '');
            $suffix = (string)($spec['suffix'] ?? '');
            $inline = $prefix !== '' || $suffix !== ''
                || ($type === 'secret' && !empty($spec['reveal']));
            if ($inline) {
                echo '<div class="field-inline">';
            }
            // A choice list has no single control to name, so its affixes stay plain text.
            $affixFor = $type === 'radio' ? '' : ' for="' . osc_esc_html($id) . '"';
            if ($prefix !== '') {
                echo '<label class="field-prefix"' . $affixFor . '>' . osc_esc_html($prefix) . '</label>';
            }

            self::control($type, $id, $spec);

            if ($suffix !== '') {
                echo '<label class="field-suffix"' . $affixFor . '>' . osc_esc_html($suffix) . '</label>';
            }
            if ($inline) {
                echo '</div>';
            }

            self::help($spec);
        }

        if ($row) {
            osc_admin_form_row_close();
        }
    }

    /**
     * The control alone, without its row, label or hint. One escaping path and one width
     * decision for every field type.
     *
     * @param string $type
     * @param string $id
     * @param array  $spec
     *
     * @return void
     */
    public static function control($type, $id, array $spec)
    {
        $name  = (string)($spec['name'] ?? '');
        $value = $spec['value'] ?? ($spec['selected'] ?? '');
        $extra = $spec['attrs'] ?? array();
        // Two spellings for one cap. The declared key is the one the save enforces, so it
        // is the number the control shows; the attrs copy is dropped rather than emitted
        // beside it.
        if (isset($spec['maxlength'], $extra['maxlength'])) {
            unset($extra['maxlength']);
        }
        $attrs = self::attrsString($extra);

        if (!empty($spec['required'])) {
            $attrs .= ' required';
        }
        if (!empty($spec['disabled'])) {
            $attrs .= ' disabled';
        }
        if (isset($spec['placeholder']) && $type !== 'select') {
            $attrs .= ' placeholder="' . osc_esc_html($spec['placeholder']) . '"';
        }
        // Emitted only where the number is honest. A purified value is escaped on the way
        // in and escaping lengthens -- one typed "&" reaches the column as five characters
        // -- so on those types the attribute would promise a cap the save does not apply.
        $maxlength = isset($spec['maxlength']) ? (int)$spec['maxlength'] : 0;
        if ($maxlength > 0
            && in_array($type, self::LENGTH_HINT_TYPES, true)
            && !(($spec['purify'] ?? true) && in_array($type, self::PURIFIED_TYPES, true))
        ) {
            $attrs .= ' maxlength="' . $maxlength . '"';
        }

        // A control the page drives from script and never submits has no name; emitting an
        // empty one would put it in the request as a blank key.
        $common = ' id="' . osc_esc_html($id) . '"'
            . ($name === '' ? '' : ' name="' . osc_esc_html($name) . '"');

        switch ($type) {
            case 'custom':
                if (isset($spec['render']) && is_callable($spec['render'])) {
                    call_user_func($spec['render'], $spec);
                }
                break;

            case 'select':
                echo '<select' . $common . ' class="' . self::cssClass($type, $spec) . '"' . $attrs . '>';
                if (isset($spec['placeholder'])) {
                    echo '<option value="">' . osc_esc_html($spec['placeholder']) . '</option>';
                }
                foreach (($spec['options'] ?? array()) as $optValue => $optLabel) {
                    echo '<option value="' . osc_esc_html($optValue) . '"'
                        . ((string)$optValue === (string)$value ? ' selected' : '') . '>'
                        . osc_esc_html($optLabel) . '</option>';
                }
                echo '</select>';
                break;

            case 'textarea':
                echo '<textarea' . $common . ' class="' . self::cssClass($type, $spec) . '"'
                    . ' rows="' . (int)($spec['rows'] ?? 5) . '"' . $attrs . '>'
                    . osc_esc_html((string)$value) . '</textarea>';
                break;

            case 'radio':
                self::choices($id, $name, (string)$value, $spec);
                break;

            case 'color':
                echo '<input type="color"' . $common . ' class="' . self::cssClass($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;

            case 'file':
                // No value: a file input's value cannot be set, and a browser would refuse it.
                echo '<input type="file"' . $common . ' class="' . self::cssClass($type, $spec) . '"'
                    . $attrs . ' />';
                break;

            case 'number':
                foreach (array('min', 'max', 'step') as $key) {
                    if (isset($spec[$key])) {
                        $attrs .= ' ' . $key . '="' . osc_esc_html($spec[$key]) . '"';
                    }
                }
                echo '<input type="number"' . $common . ' class="' . self::cssClass($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;

            case 'secret':
                // A stored secret is not echoed back into the DOM when 'masked' is set: the
                // field renders empty over a bullet placeholder, and a blank submission means
                // "unchanged". Callers keep the old value themselves until the save layer lands.
                $masked = !empty($spec['masked']);
                echo '<input type="password"' . $common . ' class="' . self::cssClass($type, $spec) . '"'
                    . ' value="' . ($masked ? '' : osc_esc_html((string)$value)) . '"'
                    . ' autocomplete="off" spellcheck="false"'
                    . ($masked && (string)$value !== '' ? ' placeholder="••••••••"' : '')
                    . $attrs . ' />';
                if (!empty($spec['reveal'])) {
                    echo '<button type="button" class="btn btn-sm btn-secondary" data-osc-reveal="'
                        . osc_esc_html($id) . '" aria-controls="' . osc_esc_html($id) . '"'
                        . ' aria-pressed="false" data-label-show="' . osc_esc_html(__('Show')) . '"'
                        . ' data-label-hide="' . osc_esc_html(__('Hide')) . '">'
                        . osc_esc_html(__('Show')) . '</button>';
                }
                break;

            default:
                // email/url/tel are text fields wearing a keyboard and a validator. Anything
                // else falls back to text rather than emitting a type the caller invented.
                $htmlType = in_array($type, array('email', 'url', 'tel'), true) ? $type : 'text';
                echo '<input type="' . $htmlType . '"' . $common
                    . ' class="' . self::cssClass($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;
        }
    }

    /**
     * The locales a field expands over: what 'locales' carries when 'translate' is set on
     * a text or textarea, and nothing otherwise. The caller supplies the list -- core's
     * declared settings page from osc_settings_field_locales(), a plugin from whatever it
     * already has -- so drawing a field never queries anything.
     *
     * @param array $spec
     *
     * @return array<string,string> code => locale name
     */
    public static function localesFor(array $spec)
    {
        if (empty($spec['translate'])
            || !in_array($spec['type'] ?? 'text', array('text', 'textarea'), true)
            || empty($spec['locales'])
            || !is_array($spec['locales'])
        ) {
            return array();
        }

        return $spec['locales'];
    }

    /**
     * One control per locale, in the tab widget the rest of the admin's multilang editors
     * use. Each control is named for its locale (the field name with the locale code
     * appended), which is the key the value is stored under.
     *
     * One enabled locale gets no tabs: a single tab is a label pretending to be a choice.
     *
     * @param string $id      the field's own id, which the panels are named from
     * @param array  $spec
     * @param array  $locales code => locale name
     *
     * @return void
     */
    public static function translated($id, array $spec, array $locales)
    {
        $type   = $spec['type'] ?? 'text';
        $name   = (string)($spec['name'] ?? '');
        $stored = is_array($spec['value'] ?? null) ? $spec['value'] : array();
        $label  = (string)($spec['label'] ?? '');
        $tabs   = count($locales) > 1;

        echo '<div class="field-translate">';
        if ($tabs) {
            echo '<div class="osc-tab"><ul>';
            foreach ($locales as $code => $localeName) {
                echo '<li><a href="#' . osc_esc_html(self::localePanelId($id, $code)) . '">'
                    . osc_esc_html($localeName) . '</a></li>';
            }
            echo '</ul></div>';
        }

        $first = true;
        foreach ($locales as $code => $localeName) {
            $sub          = $spec;
            $sub['name']  = $name . $code;
            $sub['value'] = (string)($stored[$code] ?? '');
            unset($sub['id']);
            if ($tabs && $label !== '') {
                // Only the tab strip says which locale a control belongs to, and a tab is
                // not the control's label. A caller's own aria-label still wins.
                $sub['attrs'] = (array)($spec['attrs'] ?? array())
                    + array('aria-label' => $label . ' (' . $localeName . ')');
            }
            if ($tabs) {
                echo '<div class="field-translate-panel" id="'
                    . osc_esc_html(self::localePanelId($id, $code)) . '"'
                    . ($first ? '' : ' hidden') . '>';
            }
            self::control($type, self::idFor($sub), $sub);
            if ($tabs) {
                echo '</div>';
            }
            $first = false;
        }
        echo '</div>';
    }

    /**
     * The id of one locale's panel. Kept off the control's own id: they sit on different
     * elements and an id used twice is a tab that focuses the wrong thing.
     *
     * @param string $id
     * @param string $code
     *
     * @return string
     */
    public static function localePanelId($id, $code)
    {
        return $id . '-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string)$code);
    }

    /**
     * The option list of a radio group. Each option is its own label wrapping its own
     * control, so the whole line is a hit target and no id can drift from its label.
     *
     * @param string $id
     * @param string $name
     * @param string $value
     * @param array  $spec
     *
     * @return void
     */
    public static function choices($id, $name, $value, array $spec)
    {
        $label = (string)($spec['label'] ?? '');
        echo '<div class="field-choices"'
            . ($label === '' ? '' : ' role="group" aria-label="' . osc_esc_html($label) . '"')
            . '>';

        $i = 0;
        foreach (($spec['options'] ?? array()) as $optValue => $option) {
            $i++;
            $custom      = '';
            $optId       = $id . '-' . $i;
            $optDisabled = false;
            if (is_array($option)) {
                $custom      = (string)($option['custom_html'] ?? '');
                $optId       = (string)($option['id'] ?? $optId);
                $optDisabled = !empty($option['disabled']);
                $option      = $option['label'] ?? '';
            }

            // The id counts the options rather than slugging their values: a value is free
            // text ("F j, Y"), and neither spaces nor two values slugging to the same
            // string can be allowed to produce an id two radios share.
            echo '<label class="field-choice">'
                . '<input type="radio" id="' . osc_esc_html($optId) . '"'
                . ' name="' . osc_esc_html($name) . '" value="' . osc_esc_html($optValue) . '"'
                . ((string)$optValue === $value ? ' checked' : '')
                . (!empty($spec['disabled']) || $optDisabled ? ' disabled' : '') . ' />'
                . '<span>' . osc_esc_html($option) . '</span>'
                . $custom
                . '</label>';
        }
        echo '</div>';
    }

    /**
     * The width class a field gets. Width follows the field's meaning, not the page it
     * happens to sit on; 'width' overrides it when a screen genuinely needs something else.
     *
     * @param string $type
     * @param array  $spec
     *
     * @return string
     */
    public static function cssClass($type, array $spec)
    {
        $widths = array(
            'num'    => 'field-num',
            'text'   => 'field-text',
            'key'    => 'field-key',
            'select' => 'field-select',
            'full'   => '',
        );

        $byType = array(
            'number'   => 'field-num',
            'select'   => 'field-select',
            'secret'   => 'field-key',
            'textarea' => 'field-text',
            'color'    => 'field-color',
            'file'     => '',
        );

        $width = isset($spec['width'])
            ? ($widths[$spec['width']] ?? '')
            : ($byType[$type] ?? 'field-text');

        // input-text is what the admin styles a text control with; a select, a colour well and
        // a file picker are not text controls and the class would fight their own sizing.
        $classes = in_array($type, array('select', 'color', 'file'), true) ? array() : array('input-text');

        // field-select is a select's appearance, not just its width, so it stays on even when
        // the caller asks for a different width -- the width classes carry !important and win
        // that part on their own.
        if ($type === 'select' && $width !== 'field-select') {
            $classes[] = 'field-select';
        }
        if ($width !== '') {
            $classes[] = $width;
        }
        if ($type === 'textarea' && !empty($spec['monospace'])) {
            $classes[] = 'field-mono';
        }
        if (!empty($spec['class'])) {
            $classes[] = $spec['class'];
        }

        return osc_esc_html(implode(' ', $classes));
    }

    /**
     * The hint under a field, in the same help-box the admin already uses.
     *
     * @param array $spec
     *
     * @return void
     */
    public static function help(array $spec)
    {
        if (!empty($spec['help_html'])) {
            echo '<div class="help-box">' . $spec['help_html'] . '</div>';
        } elseif (!empty($spec['help'])) {
            echo '<div class="help-box">' . osc_esc_html((string)$spec['help']) . '</div>';
        }
    }

    /**
     * A field's DOM id: its own, or one derived from its name so the label is clickable
     * without every caller having to invent one.
     *
     * @param array $spec
     *
     * @return string
     */
    public static function idFor(array $spec)
    {
        if (!empty($spec['id'])) {
            return (string)$spec['id'];
        }

        $name = (string)($spec['name'] ?? '');

        return $name === '' ? '' : 'field-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $name);
    }

    /**
     * Extra attributes as an escaped string. A value of true renders the attribute alone
     * (`readonly`), false and null drop it.
     *
     * @param array $attrs name => value
     *
     * @return string
     */
    public static function attrsString(array $attrs)
    {
        $out = '';
        foreach ($attrs as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }
            $out .= $value === true
                ? ' ' . osc_esc_html($name)
                : ' ' . osc_esc_html($name) . '="' . osc_esc_html($value) . '"';
        }

        return $out;
    }
}
