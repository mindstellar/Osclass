<?php
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/**
 * Admin form field primitives.
 *
 * The admin theme has had a component vocabulary for a while, but it lives inside the
 * theme -- so a plugin cannot call it without risking a fatal on an install running a
 * different admin theme, and there was never a primitive for a text input at all. Every
 * settings screen, core and plugin alike, therefore hand-wrote `<input class="…">` against
 * our class names, which is how those class names became an API we never chose to publish.
 *
 * These live in core so a plugin can rely on them existing. They emit the classes the
 * admin theme already styles, plus the `field-*` classes added alongside them, so a screen
 * built from them is indistinguishable from a correct hand-built one.
 *
 * Two things they enforce that hand-written markup cannot:
 *
 *   A suffix is a slot, not concatenation. `'suffix' => __('comments per page')` is a
 *       declared property of the field, so nothing can separate it from its input and the
 *       translator gets two whole strings instead of one sentence split around an opaque
 *       %s they cannot move the field within.
 *   Width comes from the field's type, not the page. Three widths tied to meaning --
 *       field-text (prose), field-num (a number), field-key (a long opaque token) -- plus
 *       intrinsic sizing for choice groups.
 *
 * ESCAPING CONTRACT, the same one parts/ui.php states: every value is escaped by the
 * function that prints it, except keys whose name ends in `_html`. If you add a parameter
 * that must accept markup, name it `*_html` -- the suffix is the whole warning system.
 *
 * LOAD ORDER IS LOAD-BEARING. oc-load.php requires this file *after* the admin theme's
 * functions.php, so a theme shipping its own osc_admin_text() still wins and core only
 * fills the gaps. Move the require up with the other helpers and core silently takes over
 * every theme's copies.
 */

if (!function_exists('osc_admin_field')) {
    /**
     * Render one labelled field. Every other function here is sugar over this one, so this
     * is the only new name a plugin has to depend on.
     *
     * Keys, all optional but `name`:
     *   'type'      => text|email|url|tel|number|select|textarea|radio|checkbox|secret|custom
     *   'name'      => request/preference key
     *   'label'     => the label. For a checkbox it sits beside the control, so the row's
     *                  own label column comes from 'row_label' instead.
     *   'value'     => current value ('selected' is accepted for select and radio)
     *   'help'      => hint under the field. 'help_html' for a hint carrying markup.
     *   'prefix'    => leading words that belong to the field, e.g. "Break comments into"
     *   'suffix'    => trailing words that belong to the field, e.g. "listings at most"
     *   'width'     => text|num|key|select|full, overriding the width the type implies
     *   'options'   => value => label, for select and radio
     *   'required', 'disabled' => bool
     *   'id'        => defaults to a slug of 'name'
     *   'attrs'     => extra attributes, name => value
     *   'row'       => false to emit the control alone, for a caller composing its own row
     *   'render'    => callable, with 'type' => 'custom': emits into the normal row
     *
     * @param array $spec
     *
     * @return void
     */
    function osc_admin_field(array $spec)
    {
        $type = $spec['type'] ?? 'text';
        $id   = osc_admin_field_id($spec);
        $row  = $spec['row'] ?? true;

        if ($row) {
            // A checkbox carries its own label beside the control; anything else labels the
            // row. Passing both would print the label twice.
            $rowLabel = $type === 'checkbox'
                ? ($spec['row_label'] ?? '')
                : ($spec['label'] ?? '');
            // A choice list has no single control to point at -- each option owns its own
            // label -- so the row label stays plain text and the group is named through
            // aria-label instead. A `for` pointing at an id nothing carries reaches nothing.
            osc_admin_form_row_open($rowLabel, $type === 'radio' ? array() : array('for' => $id));
        }

        if ($type === 'checkbox') {
            $spec['id'] = $id;
            osc_admin_checkbox($spec);
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

            osc_admin_field_control($type, $id, $spec);

            if ($suffix !== '') {
                echo '<label class="field-suffix"' . $affixFor . '>' . osc_esc_html($suffix) . '</label>';
            }
            if ($inline) {
                echo '</div>';
            }

            osc_admin_field_help($spec);
        }

        if ($row) {
            osc_admin_form_row_close();
        }
    }
}

if (!function_exists('osc_admin_field_control')) {
    /**
     * The control alone, without its row, label or hint. Split out so every field type
     * shares one escaping path and one width decision.
     *
     * @param string $type
     * @param string $id
     * @param array  $spec
     *
     * @return void
     */
    function osc_admin_field_control($type, $id, array $spec)
    {
        $name  = (string)($spec['name'] ?? '');
        $value = $spec['value'] ?? ($spec['selected'] ?? '');
        $attrs = osc_admin_field_attrs($spec['attrs'] ?? array());

        if (!empty($spec['required'])) {
            $attrs .= ' required';
        }
        if (!empty($spec['disabled'])) {
            $attrs .= ' disabled';
        }
        if (isset($spec['placeholder']) && $type !== 'select') {
            $attrs .= ' placeholder="' . osc_esc_html($spec['placeholder']) . '"';
        }

        $common = ' id="' . osc_esc_html($id) . '" name="' . osc_esc_html($name) . '"';

        switch ($type) {
            case 'custom':
                if (isset($spec['render']) && is_callable($spec['render'])) {
                    call_user_func($spec['render'], $spec);
                }
                break;

            case 'select':
                echo '<select' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"' . $attrs . '>';
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
                echo '<textarea' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"'
                    . ' rows="' . (int)($spec['rows'] ?? 5) . '"' . $attrs . '>'
                    . osc_esc_html((string)$value) . '</textarea>';
                break;

            case 'radio':
                osc_admin_field_choices($id, $name, (string)$value, $spec);
                break;

            case 'number':
                foreach (array('min', 'max', 'step') as $key) {
                    if (isset($spec[$key])) {
                        $attrs .= ' ' . $key . '="' . osc_esc_html($spec[$key]) . '"';
                    }
                }
                echo '<input type="number"' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;

            case 'secret':
                // A stored secret is not echoed back into the DOM when 'masked' is set: the
                // field renders empty over a bullet placeholder, and a blank submission means
                // "unchanged". Callers keep the old value themselves until the save layer lands.
                $masked = !empty($spec['masked']);
                echo '<input type="password"' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"'
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
                    . ' class="' . osc_admin_field_class($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;
        }
    }
}

if (!function_exists('osc_admin_field_choices')) {
    /**
     * The option list of a radio group. Each option is its own label wrapping its own
     * control, so the whole line is a hit target and no id can drift from its label.
     *
     * An option may carry 'custom_html' -- markup rendered inside the label after its text,
     * which is how "Custom: [____]" rows keep the free-text input inside the choice.
     *
     * @param string $id
     * @param string $name
     * @param string $value
     * @param array  $spec
     *
     * @return void
     */
    function osc_admin_field_choices($id, $name, $value, array $spec)
    {
        $label = (string)($spec['label'] ?? '');
        echo '<div class="field-choices"'
            . ($label === '' ? '' : ' role="group" aria-label="' . osc_esc_html($label) . '"')
            . '>';

        $i = 0;
        foreach (($spec['options'] ?? array()) as $optValue => $option) {
            $i++;
            $custom = '';
            if (is_array($option)) {
                $custom = (string)($option['custom_html'] ?? '');
                $option = $option['label'] ?? '';
            }

            // The id counts the options rather than slugging their values: a value is free
            // text ("F j, Y"), and neither spaces nor two values slugging to the same
            // string can be allowed to produce an id two radios share.
            echo '<label class="field-choice">'
                . '<input type="radio" id="' . osc_esc_html($id . '-' . $i) . '"'
                . ' name="' . osc_esc_html($name) . '" value="' . osc_esc_html($optValue) . '"'
                . ((string)$optValue === $value ? ' checked' : '')
                . (!empty($spec['disabled']) ? ' disabled' : '') . ' />'
                . '<span>' . osc_esc_html($option) . '</span>'
                . $custom
                . '</label>';
        }
        echo '</div>';
    }
}

if (!function_exists('osc_admin_field_class')) {
    /**
     * The width class a field gets. Width follows the field's meaning, not the page it
     * happens to sit on; 'width' overrides it when a screen genuinely needs something else.
     *
     * @param string $type
     * @param array  $spec
     *
     * @return string
     */
    function osc_admin_field_class($type, array $spec)
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
        );

        $width = isset($spec['width'])
            ? ($widths[$spec['width']] ?? '')
            : ($byType[$type] ?? 'field-text');

        $classes = $type === 'select' ? array() : array('input-text');
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
}

if (!function_exists('osc_admin_field_help')) {
    /**
     * The hint under a field, in the same help-box the admin already uses.
     *
     * @param array $spec
     *
     * @return void
     */
    function osc_admin_field_help(array $spec)
    {
        if (!empty($spec['help_html'])) {
            echo '<div class="help-box">' . $spec['help_html'] . '</div>';
        } elseif (!empty($spec['help'])) {
            echo '<div class="help-box">' . osc_esc_html((string)$spec['help']) . '</div>';
        }
    }
}

if (!function_exists('osc_admin_field_id')) {
    /**
     * A field's DOM id: its own, or one derived from its name so the label is clickable
     * without every caller having to invent one.
     *
     * @param array $spec
     *
     * @return string
     */
    function osc_admin_field_id(array $spec)
    {
        if (!empty($spec['id'])) {
            return (string)$spec['id'];
        }

        $name = (string)($spec['name'] ?? '');

        return $name === '' ? '' : 'field-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $name);
    }
}

if (!function_exists('osc_admin_field_attrs')) {
    /**
     * Extra attributes as an escaped string. A value of true renders the attribute alone
     * (`readonly`), false and null drop it.
     *
     * @param array $attrs name => value
     *
     * @return string
     */
    function osc_admin_field_attrs(array $attrs)
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

if (!function_exists('osc_admin_text')) {
    /**
     * Single-line text. Keys: the shared set, plus 'placeholder', 'width', 'prefix', 'suffix'.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_text(array $opts)
    {
        $opts['type'] = 'text';
        osc_admin_field($opts);
    }
}

if (!function_exists('osc_admin_number')) {
    /**
     * A number, at number width. Keys: the shared set, plus 'min', 'max', 'step', 'prefix',
     * 'suffix'.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_number(array $opts)
    {
        $opts['type'] = 'number';
        osc_admin_field($opts);
    }
}

if (!function_exists('osc_admin_select')) {
    /**
     * A dropdown. Keys: the shared set, plus 'options', 'selected', 'placeholder'.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_select(array $opts)
    {
        $opts['type'] = 'select';
        osc_admin_field($opts);
    }
}

if (!function_exists('osc_admin_textarea')) {
    /**
     * Multi-line text. Keys: the shared set, plus 'rows', 'monospace'.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_textarea(array $opts)
    {
        $opts['type'] = 'textarea';
        osc_admin_field($opts);
    }
}

if (!function_exists('osc_admin_radio_group')) {
    /**
     * A choice list. Keys: the shared set, plus 'options', 'selected'. An option may be a
     * string label or array('label' => …, 'custom_html' => …).
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_radio_group(array $opts)
    {
        $opts['type'] = 'radio';
        osc_admin_field($opts);
    }
}

if (!function_exists('osc_admin_secret')) {
    /**
     * An API key or password. Keys: the shared set, plus 'reveal', 'masked'.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_secret(array $opts)
    {
        $opts['type'] = 'secret';
        osc_admin_field($opts);
    }
}

/*
 * Fallbacks for the three theme components the primitives above build on. The active admin
 * theme defines these already and loads first, so on a stock install nothing below runs --
 * they exist so a plugin calling osc_admin_field() still renders on a theme that ships
 * neither. Markup is identical to themes/modern/parts/ui.php by design; the class names are
 * consumed directly by third-party plugins and cannot drift between the two copies.
 */

if (!function_exists('osc_admin_form_row_open')) {
    /**
     * One labelled row of a form.
     *
     * @param string $label
     * @param array  $opts 'for' => input id, so the label is clickable
     *
     * @return void
     */
    function osc_admin_form_row_open($label = '', array $opts = array())
    {
        echo '<div class="form-row">';

        if ($label !== '') {
            $for = $opts['for'] ?? '';
            echo '<div class="form-label">';
            echo $for !== ''
                ? '<label for="' . osc_esc_html($for) . '">' . osc_esc_html($label) . '</label>'
                : osc_esc_html($label);
            echo '</div>';
        }

        echo '<div class="form-controls">';
    }
}

if (!function_exists('osc_admin_form_row_close')) {
    /**
     * @return void
     */
    function osc_admin_form_row_close()
    {
        echo '</div></div>';
    }
}

if (!function_exists('osc_admin_checkbox')) {
    /**
     * A checkbox with its label on one line and its hint underneath.
     *
     * Keys: name, label, checked, value (default '1'), help, help_html, id, attrs.
     * `label_html` is the raw-markup form of `label`, for the label that has to carry a link.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_checkbox(array $opts)
    {
        echo '<div class="form-label-checkbox"><label>'
            . '<input type="checkbox" name="' . osc_esc_html($opts['name'] ?? '') . '"'
            . (!empty($opts['id']) ? ' id="' . osc_esc_html($opts['id']) . '"' : '')
            . ' value="' . osc_esc_html($opts['value'] ?? '1') . '"'
            . (!empty($opts['checked']) ? ' checked="checked"' : '')
            . osc_admin_field_attrs($opts['attrs'] ?? array()) . ' /> '
            . (!empty($opts['label_html']) ? $opts['label_html'] : osc_esc_html($opts['label'] ?? ''))
            . '</label>';
        osc_admin_field_help($opts);
        echo '</div>';
    }
}

/* file end: ./oc-includes/osclass/helpers/hAdminUi.php */
