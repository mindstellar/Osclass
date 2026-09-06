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
 * Pins what osc_admin_field() and its per-type sugar emit.
 *
 * These are the primitives a plugin builds an admin settings page from, so the
 * markup is a contract in both directions and every break here is silent:
 *
 *  - an unescaped value is stored XSS on a page only an administrator sees, which
 *    is exactly where it does the most damage;
 *  - a dropped field-num/field-text/field-key and width stops following the field's
 *    meaning -- the number goes back to being a full-width block that orphans the
 *    words after it;
 *  - a suffix emitted outside .field-inline and the sentence breaks again, which is
 *    the whole reason the slot exists;
 *  - a lost id and the row's <label> points at nothing.
 *
 * DB-free: the helpers are pure markup over the spec array they are handed.
 *
 * Usage:  php tests/admin-field-primitives.php
 */

if (!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

require_once __DIR__ . '/lib/harness.php';
require_once ABS_PATH . 'oc-includes/osclass/helpers/hSanitize.php';

if (!function_exists('__')) {
    function __($key, $domain = 'core')
    {
        return $key;
    }
}

require_once ABS_PATH . 'oc-includes/osclass/helpers/hAdminUi.php';

/** Capture what a helper prints. */
function render(callable $fn): string
{
    ob_start();
    $fn();

    return (string)ob_get_clean();
}

/** Assert $needle appears in $html, reporting the markup when it does not. */
function emits(string $label, string $html, string $needle): void
{
    check($label, strpos($html, $needle) !== false, $needle . ' not in: ' . $html);
}

harness_section('the row every field sits in');
$html = render(static function () {
    osc_admin_text(array('name' => 'site_title', 'label' => 'Site title', 'value' => 'Shopclass'));
});
emits('opens a form-row', $html, '<div class="form-row">');
emits('labels the row', $html, '<div class="form-label"><label for="field-site_title">Site title</label>');
emits('puts the control in form-controls', $html, '<div class="form-controls">');
emits('derives an id from the name', $html, 'id="field-site_title"');
emits('carries the value', $html, 'value="Shopclass"');
check('closes both wrappers', substr($html, -12) === '</div></div>', $html);

// A caller composing its own row (a plugin inside an existing screen) needs the
// control alone, or it nests a form-row inside a form-row.
$bare = render(static function () {
    osc_admin_text(array('name' => 'x', 'label' => 'X', 'row' => false));
});
check("'row' => false emits no row", strpos($bare, 'form-row') === false, $bare);

harness_section('width follows the field type');
$widths = array(
    'text'     => 'field-text',
    'number'   => 'field-num',
    'secret'   => 'field-key',
    'textarea' => 'field-text',
    'select'   => 'field-select',
);
foreach ($widths as $type => $class) {
    $out = render(static function () use ($type) {
        osc_admin_field(array('type' => $type, 'name' => 'f', 'label' => 'F', 'options' => array()));
    });
    emits("a {$type} field is {$class}", $out, $class);
}
// The override exists for the screen that genuinely needs something else.
$wide = render(static function () {
    osc_admin_field(array('type' => 'number', 'name' => 'f', 'width' => 'text'));
});
check("'width' overrides the type's default", strpos($wide, 'field-text') !== false
    && strpos($wide, 'field-num') === false, $wide);

harness_section('a suffix is a slot, not concatenation');
$html = render(static function () {
    osc_admin_number(array(
        'name'   => 'comments_per_page',
        'label'  => 'Other comment settings',
        'value'  => 10,
        'suffix' => 'comments per page',
        'min'    => 0,
    ));
});
emits('wraps control and suffix together', $html, '<div class="field-inline">');
emits('renders the suffix in its own label', $html, '<label class="field-suffix" for="field-comments_per_page">comments per page</label>');
emits('passes min through', $html, 'min="0"');
check('the suffix follows the input', strpos($html, 'field-suffix') > strpos($html, '<input'), $html);
// The words in front of a field are a slot too: the sentence they came from is never
// split around a %s the translator cannot move the field within.
$both = render(static function () {
    osc_admin_number(array(
        'name'   => 'comments_per_page',
        'value'  => 10,
        'prefix' => 'Break comments into pages with',
        'suffix' => 'comments per page',
    ));
});
emits('renders a prefix', $both, '<label class="field-prefix" for="field-comments_per_page">Break comments into pages with</label>');
check('the prefix comes before the input', strpos($both, 'field-prefix') < strpos($both, '<input'), $both);
// Without a suffix there is nothing to keep on one line, so no wrapper.
$plain = render(static function () {
    osc_admin_number(array('name' => 'n', 'label' => 'N'));
});
check('no wrapper without a suffix', strpos($plain, 'field-inline') === false, $plain);

harness_section('choice lists');
$html = render(static function () {
    osc_admin_radio_group(array(
        'name'     => 'date_format',
        'label'    => 'Date format',
        'selected' => 'F j, Y',
        'options'  => array(
            'F j, Y' => 'August 25, 2026',
            'custom' => array('label' => 'Custom', 'custom_html' => '<input class="field-inline-text" />'),
        ),
    ));
});
emits('opens a choices list', $html, '<div class="field-choices"');
emits('each option is its own label', $html, '<label class="field-choice">');
// The id counts options: a value is free text, so slugging it can produce a space,
// or two options that slug to the same id.
emits('each radio has its own id', $html, 'id="field-date_format-1"');
emits('the second option too', $html, 'id="field-date_format-2"');
emits('the group is named', $html, 'role="group" aria-label="Date format"');
check('the row label points at no control', strpos($html, '<label for=') === false, $html);
emits('marks the selected option', $html, 'value="F j, Y" checked');
emits('renders per-option markup', $html, '<input class="field-inline-text" />');
check('only one option is checked', substr_count($html, ' checked') === 1, $html);

harness_section('escaping');
$html = render(static function () {
    osc_admin_text(array(
        'name'  => 'evil',
        'label' => '<script>alert(1)</script>',
        'value' => '" onfocus="alert(1)',
        'help'  => '<b>hi</b>',
        'attrs' => array('data-x' => '"><script>'),
    ));
});
check('no raw script tag survives', strpos($html, '<script') === false, $html);
emits('escapes the value out of its attribute', $html, 'value="&quot; onfocus=&quot;alert(1)"');
emits('escapes extra attributes', $html, 'data-x="&quot;&gt;&lt;script&gt;"');
emits('escapes help text', $html, '<div class="help-box">&lt;b&gt;hi&lt;/b&gt;</div>');
// The _html suffix is the whole warning system: only those keys are raw sinks.
$raw = render(static function () {
    osc_admin_text(array('name' => 'h', 'help_html' => '<b>bold</b>'));
});
emits('help_html is a markup slot', $raw, '<div class="help-box"><b>bold</b></div>');

harness_section('select');
$html = render(static function () {
    osc_admin_select(array(
        'name'        => 'mode',
        'label'       => 'Mode',
        'selected'    => 'test',
        'placeholder' => 'Choose…',
        'options'     => array('live' => 'Live', 'test' => 'Test'),
    ));
});
emits('renders a select', $html, '<select id="field-mode" name="mode"');
emits('a placeholder is an empty first option', $html, '<option value="">Choose…</option>');
emits('marks the selected option', $html, '<option value="test" selected>Test</option>');
check('the placeholder is not an input attribute', strpos($html, 'placeholder="') === false, $html);

harness_section('secret');
$html = render(static function () {
    osc_admin_secret(array('name' => 'api_key', 'label' => 'API key', 'value' => 's3cret', 'reveal' => true));
});
emits('is a password field', $html, '<input type="password"');
emits('does not autocomplete', $html, 'autocomplete="off"');
emits('the reveal button names its input', $html, 'data-osc-reveal="field-api_key"');
emits('and reports its state', $html, 'aria-pressed="false"');
check('the reveal button sits on the input line', strpos($html, 'field-inline') !== false, $html);
// masked keeps a stored secret out of the DOM entirely.
$masked = render(static function () {
    osc_admin_secret(array('name' => 'api_key', 'value' => 's3cret', 'masked' => true));
});
check('masked never echoes the secret', strpos($masked, 's3cret') === false, $masked);
emits('masked shows bullets instead', $masked, 'placeholder="••••••••"');

harness_section('colour and file');
$html = render(static function () {
    osc_admin_field(array('type' => 'color', 'name' => 'tint', 'label' => 'Tint', 'value' => '#112233'));
});
emits('a colour field is a colour well', $html, '<input type="color" id="field-tint" name="tint" class="field-color"');
check('and is not styled as a text control', strpos($html, 'input-text') === false, $html);
$html = render(static function () {
    osc_admin_field(array('type' => 'file', 'name' => 'logo', 'label' => 'Logo', 'value' => '/etc/passwd'));
});
emits('a file field is a file picker', $html, '<input type="file"');
// A file input's value cannot be set from markup, and echoing one back would be a
// path disclosure for nothing.
check('a file field carries no value', strpos($html, 'value=') === false, $html);

harness_section('an option can be disabled or keep its own id');
$html = render(static function () {
    osc_admin_radio_group(array(
        'name'     => 'watermark_type',
        'selected' => 'none',
        'options'  => array(
            'none' => array('label' => 'None', 'id' => 'watermark_none'),
            'text' => array('label' => 'Text', 'id' => 'watermark_text', 'disabled' => true),
        ),
    ));
});
emits('an option keeps the id a script already reaches for', $html, 'id="watermark_none"');
emits('and one option can be disabled alone', $html, 'id="watermark_text" name="watermark_type" value="text" disabled');
check('without disabling the rest', substr_count($html, 'disabled') === 1, $html);

harness_section('checkbox keeps its published shape');
$html = render(static function () {
    osc_admin_field(array(
        'type'      => 'checkbox',
        'name'      => 'enabled',
        'label'     => 'Enable the thing',
        'row_label' => 'Comments',
        'checked'   => true,
    ));
});
emits('labels the row separately', $html, '<div class="form-label"><label for="field-enabled">Comments</label>');
emits('the control label sits beside the box', $html, 'class="form-label-checkbox"');
emits('is checked', $html, 'checked="checked"');
check('the label is printed once', substr_count($html, 'Enable the thing') === 1, $html);
// A checkbox label sometimes has to carry a link; label_html is that slot, named so the
// _html suffix still marks every raw sink.
$html = render(static function () {
    osc_admin_field(array(
        'type'       => 'checkbox',
        'name'       => 'cron',
        'label_html' => 'Run <a href="#">cron</a>',
    ));
});
emits('label_html is a markup slot', $html, 'Run <a href="#">cron</a>');

harness_section('custom');
$html = render(static function () {
    osc_admin_field(array(
        'type'   => 'custom',
        'name'   => 'thing',
        'label'  => 'Thing',
        'render' => static function (array $spec) {
            echo '<em>' . $spec['name'] . '</em>';
        },
    ));
});
emits('a custom field renders into the normal row', $html, '<div class="form-row">');
emits('and its callable owns the control', $html, '<em>thing</em>');

exit(harness_result());
