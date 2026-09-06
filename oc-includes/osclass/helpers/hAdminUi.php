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
     *   'type'      => text|email|url|tel|number|color|file|select|textarea|radio|checkbox|secret|custom
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

            case 'color':
                echo '<input type="color"' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"'
                    . ' value="' . osc_esc_html((string)$value) . '"' . $attrs . ' />';
                break;

            case 'file':
                // No value: a file input's value cannot be set, and a browser would refuse it.
                echo '<input type="file"' . $common . ' class="' . osc_admin_field_class($type, $spec) . '"'
                    . $attrs . ' />';
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
     * An option may be a plain label, or an array carrying 'label' plus any of 'custom_html'
     * (markup rendered inside the label after its text, which is how "Custom: [____]" rows keep
     * the free-text input inside the choice), 'disabled', and 'id' where an existing script
     * already reaches for one.
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

if (!function_exists('osc_admin_field_row')) {
    /**
     * One labelled row holding several fields.
     *
     * osc_admin_field() draws its own row, which covers a label with a control beside it.
     * It does not cover the shape most settings screens are actually made of -- one label
     * against a stack of related checkboxes ("Default comment settings", "Notifications",
     * "Optional fields") -- and every one of those was still opening `<div class="form-row">`
     * and its two inner divs by hand.
     *
     * Each entry is an ordinary field spec. A callable is invoked instead, for the row that
     * has to drop to markup for one of its parts without hand-writing the row around it.
     *
     * @param string $label  The row's label. Empty for a row that continues the one above.
     * @param array  $fields Field specs, or callables emitting into the controls column.
     * @param array  $opts   'for' => id the label points at
     *
     * @return void
     */
    function osc_admin_field_row($label, array $fields, array $opts = array())
    {
        osc_admin_form_row_open($label, $opts);

        foreach ($fields as $field) {
            if (is_callable($field)) {
                $field();
                continue;
            }
            $field['row'] = false;
            if (($field['type'] ?? 'text') === 'checkbox') {
                osc_admin_checkbox($field);
                continue;
            }
            osc_admin_field($field);
        }

        osc_admin_form_row_close();
    }
}

if (!function_exists('osc_admin_form_open')) {
    /**
     * Open an admin form: the element, the hidden route fields it posts to, and the
     * wrappers the row layout needs.
     *
     * The fields on a settings screen were primitives already; everything around them was
     * not. Every screen wrote its own `<form>`, its own `page`/`action` hidden inputs and
     * its own `<fieldset><div class="form-horizontal">` -- 93 forms and 158 hidden route
     * fields across the admin, each a chance to post to the wrong action or to lose the
     * layout wrapper and with it the label column.
     *
     * Keys:
     *   'action' => string  The `action` this form posts to. Emitted as a hidden field.
     *   'page'   => string  The `page` it posts to; defaults to the current one, which is
     *                       almost always right -- a form usually posts back to its own
     *                       screen.
     *   'url'    => string  The form's action attribute; defaults to the admin base URL.
     *   'method' => string  Defaults to post. A GET form is never given a CSRF token.
     *   'fields' => array   Extra hidden fields, name => value.
     *   'name', 'id', 'class' => string
     *   'upload' => bool    multipart/form-data, for a form carrying a file field.
     *   'horizontal' => bool  Wrap in fieldset + .form-horizontal (default true). False for
     *                       a stacked form in a dialog or a panel.
     *   'csrf'   => bool    False marks the form `nocsrf`, which is only ever right for a
     *                       form that changes nothing.
     *
     * @param array $opts
     *
     * @return void
     */
    function osc_admin_form_open(array $opts = array())
    {
        $method     = strtolower((string)($opts['method'] ?? 'post'));
        $horizontal = $opts['horizontal'] ?? true;

        echo '<form action="' . osc_esc_html($opts['url'] ?? osc_admin_base_url(true)) . '"'
            . ' method="' . osc_esc_html($method) . '"'
            . (!empty($opts['name']) ? ' name="' . osc_esc_html($opts['name']) . '"' : '')
            . (!empty($opts['id']) ? ' id="' . osc_esc_html($opts['id']) . '"' : '')
            . (!empty($opts['class']) ? ' class="' . osc_esc_html($opts['class']) . '"' : '')
            . (!empty($opts['upload']) ? ' enctype="multipart/form-data"' : '')
            . (isset($opts['csrf']) && !$opts['csrf'] ? ' nocsrf' : '')
            . '>';

        $hidden = $opts['fields'] ?? array();
        if ($method === 'post' || array_key_exists('page', $opts) || array_key_exists('action', $opts)) {
            $hidden = array_merge(
                array(
                    'page'   => $opts['page'] ?? Params::getParam('page'),
                    'action' => $opts['action'] ?? null,
                ),
                $hidden
            );
        }
        foreach ($hidden as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            echo '<input type="hidden" name="' . osc_esc_html($name) . '"'
                . ' value="' . osc_esc_html($value) . '"/>';
        }

        if ($horizontal) {
            echo '<fieldset><div class="form-horizontal">';
        }
    }
}

if (!function_exists('osc_admin_form_close')) {
    /**
     * Close what osc_admin_form_open() opened, optionally with the submit row.
     *
     * Pass an array -- empty for the plain "Save changes" every settings screen ends on --
     * to emit osc_admin_form_actions() inside the wrappers, where the row's own indent
     * lines it up with the controls above it. Pass nothing for a form whose buttons are
     * somewhere else.
     *
     * @param array|null $actions
     * @param array      $opts 'horizontal' => false when the form was opened that way
     *
     * @return void
     */
    function osc_admin_form_close($actions = null, array $opts = array())
    {
        if (is_array($actions)) {
            osc_admin_form_actions($actions);
        }
        if ($opts['horizontal'] ?? true) {
            echo '</div></fieldset>';
        }
        echo '</form>';
    }
}

if (!function_exists('osc_admin_form_section')) {
    /**
     * A titled group of fields within a screen, with an optional explanatory paragraph.
     *
     * Sections were headings written by hand: 29 `<h3 class="render-title">` in the admin,
     * plus screens calling osc_admin_page_head() a second time for a section, which gives a
     * page two competing `<h2>`s. This is one heading level for one meaning -- the page has
     * a head, a section is inside it.
     *
     * @param string $title
     * @param array  $opts 'intro' => paragraph under the heading, 'intro_html' for markup,
     *                     'spaced' => true to separate it from the block above,
     *                     'level' => 2 to render the section as a page head instead.
     *
     * @return void
     */
    function osc_admin_form_section($title, array $opts = array())
    {
        $level = (int)($opts['level'] ?? 3) === 2 ? 2 : 3;
        $class = 'render-title' . (!empty($opts['spaced']) ? ' separate-top' : '');

        if ($title !== '') {
            echo '<h' . $level . ' class="' . $class . '">' . osc_esc_html($title) . '</h' . $level . '>';
        }
        if (!empty($opts['intro_html'])) {
            echo '<p class="form-intro">' . $opts['intro_html'] . '</p>';
        } elseif (!empty($opts['intro'])) {
            echo '<p class="form-intro">' . osc_esc_html($opts['intro']) . '</p>';
        }
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
 * Fallbacks for the theme components the primitives above build on. The active admin
 * theme defines these already and loads first, so on a stock install nothing below runs --
 * they exist so a plugin calling osc_admin_field() still renders on a theme that ships
 * neither -- and core's own declared-settings-page view calls them too, which would fatal
 * on such a theme. Markup is identical to themes/modern/parts/ui.php by design; the class
 * names are consumed directly by third-party plugins and cannot drift between the two copies.
 */

if (!function_exists('osc_admin_page_head')) {
    /**
     * The heading a screen opens on.
     *
     * @param string $title
     * @param array  $actions Action specs rendered to its right
     * @param array  $opts    'class' => extra classes on the heading
     *
     * @return void
     */
    function osc_admin_page_head($title, array $actions = array(), array $opts = array())
    {
        $class = 'render-title' . (!empty($opts['class']) ? ' ' . $opts['class'] : '');

        if ($actions === array()) {
            echo '<h2 class="' . osc_esc_html($class) . '">' . osc_esc_html($title) . '</h2>';

            return;
        }

        echo '<div class="page-head"><h2 class="' . osc_esc_html($class) . '">'
            . osc_esc_html($title) . '</h2><div class="page-head-actions">';
        foreach ($actions as $action) {
            osc_admin_action_button($action);
        }
        echo '</div></div>';
    }
}

if (!function_exists('osc_admin_action_button')) {
    /**
     * One action, as a link or a button.
     *
     * Keys: label, url, variant (primary|secondary|danger|dim), icon, title, attrs, type.
     *
     * @param array $action
     *
     * @return void
     */
    function osc_admin_action_button(array $action)
    {
        $variant   = $action['variant'] ?? 'secondary';
        $classes   = 'btn btn-sm btn-' . ($variant === 'primary' ? 'submit' : $variant);
        $attrs     = osc_admin_field_attrs($action['attrs'] ?? array());
        if (!empty($action['title'])) {
            $attrs .= ' title="' . osc_esc_html($action['title']) . '"';
        }

        $inner = '';
        if (!empty($action['icon'])) {
            $inner .= '<i class="bi ' . osc_esc_html($action['icon']) . '" aria-hidden="true"></i> ';
        }
        $inner .= osc_esc_html($action['label'] ?? '');

        if (!empty($action['url'])) {
            echo '<a class="' . osc_esc_html($classes) . '" href="' . osc_esc_html($action['url']) . '"'
                . $attrs . '>' . $inner . '</a>';

            return;
        }

        echo '<button type="' . osc_esc_html($action['type'] ?? 'button') . '"'
            . ' class="' . osc_esc_html($classes) . '"' . $attrs . '>' . $inner . '</button>';
    }
}

if (!function_exists('osc_admin_form_actions')) {
    /**
     * The submit row at the foot of a form. With no arguments this is a lone "Save
     * changes" -- the case that covers most settings screens.
     *
     * @param array $actions Action specs; the first defaults to variant 'primary'
     *
     * @return void
     */
    function osc_admin_form_actions(array $actions = array())
    {
        if ($actions === array()) {
            $actions = array(array('label' => __('Save changes'), 'type' => 'submit', 'variant' => 'primary'));
        }

        echo '<div class="form-actions">';
        foreach ($actions as $i => $action) {
            $action['variant'] = $action['variant'] ?? ($i === 0 ? 'primary' : 'secondary');
            $action['type']    = $action['type'] ?? 'submit';
            osc_admin_action_button($action);
        }
        echo '</div>';
    }
}

if (!function_exists('osc_admin_form_row_open')) {
    /**
     * One labelled row of a form.
     *
     * @param string $label Empty for a row with no label column of its own.
     * @param array  $opts  'for' => input id, so the label is clickable;
     *                      'id'/'class' => on the row, for a row a script shows and hides;
     *                      'controls_class' => extra classes on the controls column;
     *                      'data' => assoc array rendered as data-* attrs on the row;
     *                      'label_html' => raw markup for the label, instead of the escaped $label;
     *                      'layout' => 'stacked' to stack the label above a full-width control
     *
     * @return void
     */
    function osc_admin_form_row_open($label = '', array $opts = array())
    {
        $class = 'form-row';
        if (!empty($opts['class'])) {
            $class .= ' ' . osc_esc_html($opts['class']);
        }
        if (($opts['layout'] ?? '') === 'stacked') {
            $class .= ' form-row-stacked';
        }

        $data_attrs = '';
        if (!empty($opts['data']) && is_array($opts['data'])) {
            foreach ($opts['data'] as $data_key => $data_value) {
                if (preg_match('/^[a-z0-9_-]+$/i', $data_key)) {
                    $data_attrs .= ' data-' . $data_key . '="' . osc_esc_html($data_value) . '"';
                }
            }
        }

        echo '<div class="' . $class . '"'
            . (!empty($opts['id']) ? ' id="' . osc_esc_html($opts['id']) . '"' : '')
            . (!empty($opts['style']) ? ' style="' . osc_esc_html($opts['style']) . '"' : '')
            . $data_attrs
            . '>';

        $label_html = $opts['label_html'] ?? '';
        if ($label !== '' || $label_html !== '') {
            $for = $opts['for'] ?? '';
            echo '<div class="form-label">';
            if ($label_html !== '') {
                echo $for !== ''
                    ? '<label for="' . osc_esc_html($for) . '">' . $label_html . '</label>'
                    : $label_html;
            } else {
                echo $for !== ''
                    ? '<label for="' . osc_esc_html($for) . '">' . osc_esc_html($label) . '</label>'
                    : osc_esc_html($label);
            }
            echo '</div>';
        }

        echo '<div class="form-controls'
            . (!empty($opts['controls_class']) ? ' ' . osc_esc_html($opts['controls_class']) : '')
            . '">';
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
