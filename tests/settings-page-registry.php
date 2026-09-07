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
 * Pins the declared settings page: what a spec must contain, what a submission is
 * sanitised and validated into, and what reaches storage.
 *
 * This is the layer a plugin hands its settings to instead of writing a form, a save
 * handler and a CSRF check of its own, so each of these fails quietly on a live site:
 *
 *  - a spec error accepted at registration is a field silently missing from the page;
 *  - a checkbox read by value rather than presence is a setting that cannot be switched
 *    on, which is exactly how the latest-searches one broke;
 *  - a select that stores an option it never offered is a value the page can never show
 *    again, and one nothing downstream expects;
 *  - a partial write on a rejected save leaves half the page stored and no sign of it.
 *
 * DB-free: registration, sanitisation and validation are pure functions over the spec
 * and the request. Only the final write touches preferences, and that is stubbed here.
 *
 * Usage:  php tests/settings-page-registry.php
 */

if (!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

require_once __DIR__ . '/lib/harness.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/settings/SettingsPageRegistry.php';

use mindstellar\settings\SettingsPageRegistry;

// --- the slice of core the helper leans on -----------------------------------------
$GLOBALS['params']      = array();
$GLOBALS['preferences'] = array();
$GLOBALS['hooks']       = array();

class Params
{
    public static function getParam($key, $a = false, $b = true)
    {
        return $GLOBALS['params'][$key] ?? '';
    }
}

class Preference
{
    public static function newInstance()
    {
        return new self();
    }

    public function get($key, $section = 'osclass')
    {
        return $GLOBALS['preferences'][$section . '/' . $key] ?? null;
    }
}

function osc_set_preference($key, $value = '', $section = 'osclass', $type = 'STRING')
{
    $k   = $section . '/' . $key;
    $was = $GLOBALS['preferences'][$k] ?? null;
    $GLOBALS['preferences'][$k] = $value;

    return (int)((string)$was !== (string)$value);
}

function osc_add_hook($hook, $fn, $priority = 5)
{
    $GLOBALS['hooks'][$hook][] = $fn;
}

function osc_run_hook($hook, ...$args)
{
}

function osc_apply_filter($hook, $content = '', ...$args)
{
    return $content;
}

function osc_admin_base_url($index = false)
{
    return 'https://example.test/oc-admin/index.php';
}

function osc_add_admin_submenu_page($menu, $title, $url, $id, $capability = 'administrator')
{
    $GLOBALS['menu'][] = array($menu, $title, $url, $id, $capability);
}

function osc_validate_email($email, $required = true)
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function osc_validate_url($value, $required = false, $headers = false)
{
    return (bool)filter_var($value, FILTER_VALIDATE_URL);
}

function osc_sanitize_url($value)
{
    return filter_var($value, FILTER_SANITIZE_URL);
}

function __($key, $domain = 'core')
{
    return $key;
}

require_once ABS_PATH . 'oc-includes/osclass/helpers/hSettings.php';

/** Register a page, returning the exception message when the spec is refused. */
function register_error(string $id, array $spec): ?string
{
    try {
        SettingsPageRegistry::instance()->register($id, $spec);
    } catch (InvalidArgumentException $e) {
        return $e->getMessage();
    }

    return null;
}

$spec = array(
    'title'   => 'My Plugin',
    'menu'    => 'plugins',
    'intro'   => 'Everything this plugin does.',
    'groups'  => array(
        array(
            'title'  => 'Connection',
            'fields' => array(
                array('type' => 'secret', 'name' => 'api_key', 'label' => 'API key', 'required' => true),
                array('type' => 'number', 'name' => 'batch', 'label' => 'Batch', 'min' => 1, 'max' => 500, 'default' => 50),
                array('type' => 'select', 'name' => 'mode', 'label' => 'Mode',
                      'options' => array('live' => 'Live', 'test' => 'Test'), 'default' => 'test'),
                array('type' => 'checkbox', 'name' => 'verbose', 'label' => 'Verbose'),
                array('type' => 'email', 'name' => 'notify', 'label' => 'Notify'),
            ),
        ),
    ),
);

harness_section('what a spec must contain');
check('a well-formed page registers', register_error('myplugin', $spec) === null);
pin('an unknown id is not registered', null, osc_settings_page('nope'));
check('an id with spaces is refused', register_error('my plugin', $spec) !== null);
check('a page with no title is refused', register_error('x1', array('groups' => $spec['groups'])) !== null);
check('a page with no fields is refused', register_error('x2', array('title' => 'X')) !== null);
check(
    'a menu section core does not have is refused',
    register_error('x3', array('title' => 'X', 'menu' => 'nowhere', 'fields' => array(array('name' => 'a')))) !== null
);
check(
    'an unknown field type is refused',
    register_error('x4', array('title' => 'X', 'fields' => array(array('name' => 'a', 'type' => 'wysiwyg')))) !== null
);
// A select with no options renders an empty dropdown: a page that cannot be used, and
// nothing on screen says why.
check(
    'a select with no options is refused',
    register_error('x5', array('title' => 'X', 'fields' => array(array('name' => 'a', 'type' => 'select')))) !== null
);
check(
    'a custom field with no renderer is refused',
    register_error('x6', array('title' => 'X', 'fields' => array(array('name' => 'a', 'type' => 'custom')))) !== null
);
// Two fields of the same name means one silently overwrites the other on save.
check(
    'a name declared twice is refused',
    register_error('x7', array('title' => 'X', 'fields' => array(
        array('name' => 'a'),
        array('name' => 'a'),
    ))) !== null
);

// A master that is not on the page can never be switched on: the dependent field renders,
// never hides, and has its value discarded on every save with nothing to explain why.
$dependsError = register_error('x10', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'depends' => 'ghost'),
)));
check('a depends naming a field the page does not declare is refused', $dependsError !== null);
pin(
    'and the message names both the field and the master it wanted',
    'SettingsPageRegistry: page "x10" field "a" depends on "ghost", which the page does not declare',
    $dependsError
);
check(
    'a depends that is not a field name is refused',
    register_error('x11', array('title' => 'X', 'fields' => array(array('name' => 'a', 'depends' => true)))) !== null
);
// A master may sit in a later group than the field following it, so the check cannot run
// while the groups are still being walked one at a time.
check('a depends on a field declared in a later group is accepted', register_error('x12', array(
    'title'  => 'X',
    'groups' => array(
        array('fields' => array(array('name' => 'a', 'depends' => 'b'))),
        array('fields' => array(array('name' => 'b', 'type' => 'checkbox'))),
    ),
)) === null);
// Only text and textarea expand over locales. Anywhere else the flag would be read on save
// and ignored at render, so the page would store keys nothing on it can edit.
check(
    'translate on a field that cannot expand over locales is refused',
    register_error('x13', array('title' => 'X', 'fields' => array(
        array('name' => 'a', 'type' => 'checkbox', 'translate' => true),
    ))) !== null
);
check('translate on a text field is accepted', register_error('x14', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'type' => 'text', 'translate' => true),
))) === null);
check('translate on a textarea is accepted', register_error('x15', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'type' => 'textarea', 'translate' => true),
))) === null);

// Core never collects a custom field's value, so a custom master is read as off on every
// save while the plugin's own markup shows the dependent row as though it were on.
$customMaster = register_error('x16', array('title' => 'X', 'fields' => array(
    array('name' => 'widget', 'type' => 'custom', 'render' => static fn () => null),
    array('name' => 'a', 'depends' => 'widget'),
)));
check('a depends on a custom field is refused', $customMaster !== null);
pin(
    'and the message says why core cannot answer for it',
    'SettingsPageRegistry: page "x16" field "a" depends on custom field "widget", '
    . 'whose value core never reads',
    $customMaster
);
// A translated master has one value per locale and its controls are named for the locale,
// so the server would call it on while the client script cannot find it at all.
$transMaster = register_error('x17', array('title' => 'X', 'fields' => array(
    array('name' => 'master', 'type' => 'text', 'translate' => true),
    array('name' => 'a', 'depends' => 'master'),
)));
check('a depends on a translated field is refused', $transMaster !== null);
pin(
    'and the message names the per-locale problem',
    'SettingsPageRegistry: page "x17" field "a" depends on translated field "master", '
    . 'which has one value per locale',
    $transMaster
);
// A cycle resolves to off at save time, so every field on it is discarded on every
// submission: no error, nothing written, and "Nothing to update" as the only symptom.
$selfCycle = register_error('x18', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'depends' => 'a'),
)));
check('a field depending on itself is refused', $selfCycle !== null);
pin(
    'and the message shows the loop',
    'SettingsPageRegistry: page "x18" has a depends cycle: a -> a',
    $selfCycle
);
$pairCycle = register_error('x19', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'depends' => 'b'),
    array('name' => 'b', 'depends' => 'a'),
)));
check('two fields depending on each other are refused', $pairCycle !== null);
pin(
    'and the message walks the whole chain',
    'SettingsPageRegistry: page "x19" has a depends cycle: a -> b -> a',
    $pairCycle
);
check('a three-field cycle is refused too', register_error('x20', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'depends' => 'b'),
    array('name' => 'b', 'depends' => 'c'),
    array('name' => 'c', 'depends' => 'a'),
))) !== null);
// The cycle check must not mistake a long chain for a loop.
check('a three-field chain that ends is accepted', register_error('x21', array('title' => 'X', 'fields' => array(
    array('name' => 'a', 'depends' => 'b'),
    array('name' => 'b', 'depends' => 'c'),
    array('name' => 'c', 'type' => 'checkbox'),
))) === null);
// A custom field may still follow a master: core stores nothing for it either way, and the
// plugin's own markup is what the shared script hides.
check('a custom field may itself depend on something', register_error('x22', array('title' => 'X', 'fields' => array(
    array('name' => 'b_on', 'type' => 'checkbox'),
    array('name' => 'widget', 'type' => 'custom', 'render' => static fn () => null, 'depends' => 'b_on'),
))) === null);

harness_section('after_save');
check(
    'a non-callable after_save is refused',
    register_error('x8', array('title' => 'X', 'fields' => array(array('name' => 'a')), 'after_save' => 'not a function')) !== null
);
$x9AfterSave = static function ($values, $id) {
};
check('a well-formed page with after_save registers', register_error('x9', array(
    'title'      => 'X',
    'fields'     => array(array('name' => 'a')),
    'after_save' => $x9AfterSave,
)) === null);
// Identity, not is_callable(): the page has to run the callable it was handed, and a
// wrapper or a default substituted for it would satisfy is_callable() just as well.
check('its after_save is the very callable that was declared', osc_settings_page('x9')['after_save'] === $x9AfterSave);
pin('a page that declared none has a null after_save', null, osc_settings_page('myplugin')['after_save']);

harness_section('two plugins claiming one id');
// The first keeps it: replacing the page would take the other plugin's settings off the
// menu and leave its saved values under a section nothing reads, both without a sound.
// Throwing is not an option either -- plugins are included unguarded from oc-load.php,
// so an exception here would white-screen the front end over a name collision.
$before = osc_settings_page('myplugin')['title'];
register_error('myplugin', array('title' => 'Impostor', 'fields' => array(array('name' => 'z'))));
pin('the first registration keeps the page', $before, osc_settings_page('myplugin')['title']);
check('the field it declared is not there', !array_key_exists('z', SettingsPageRegistry::instance()->fields('myplugin')));
// Silent would be as bad as replacing; the collision is recorded so it can be found.
pin('and the collision is counted', 1, osc_settings_page_conflicts()['myplugin'] ?? 0);
check('a healthy id is not listed', !array_key_exists('cust', osc_settings_page_conflicts()));

harness_section('defaults');
$page = osc_settings_page('myplugin');
pin('the section defaults to the page id', 'myplugin', $page['section']);
pin('the menu title defaults to the title', 'My Plugin', $page['menu_title']);
pin('the capability defaults to administrator', 'administrator', $page['capability']);
pin('a flat fields list becomes one group', 1, count($page['groups']));

harness_section('values before anything is saved');
pin('a declared default is used', 50, osc_settings_value('myplugin', 'batch'));
pin('a select falls back to its default', 'test', osc_settings_value('myplugin', 'mode'));
// Not '' — a caller writing `if (osc_settings_value(...))` must not have to know that a
// checkbox nobody has touched reads back as an empty string.
pin('an untouched checkbox is false', false, osc_settings_value('myplugin', 'verbose'));
pin('a field the page does not declare is null', null, osc_settings_value('myplugin', 'ghost'));

harness_section('sanitising a submission');
$fields = SettingsPageRegistry::instance()->fields('myplugin');
$GLOBALS['params'] = array('batch' => ' 12 ');
pin('a number arrives as an int', 12, osc_settings_sanitize($fields['batch']));
$GLOBALS['params'] = array('batch' => '2.5');
pin('a decimal stays a float', 2.5, osc_settings_sanitize($fields['batch']));
$GLOBALS['params'] = array('api_key' => '  sk-live-9f2a  ');
pin('a secret is trimmed but otherwise untouched', 'sk-live-9f2a', osc_settings_sanitize($fields['api_key']));
$GLOBALS['params'] = array('verbose' => '1');
pin('a ticked checkbox is true', true, osc_settings_sanitize($fields['verbose']));
$GLOBALS['params'] = array();
pin('an absent checkbox is false', false, osc_settings_sanitize($fields['verbose']));
// A checkbox with no value attribute submits whatever the browser invents; presence is
// the only thing that can be relied on.
$GLOBALS['params'] = array('verbose' => 'on');
pin("a checkbox is read by presence, not by its value", true, osc_settings_sanitize($fields['verbose']));
// An array where a scalar was declared is the shape an attacker reaches for first.
$GLOBALS['params'] = array('api_key' => array('a', 'b'));
pin('an array submitted for a scalar field is dropped', '', osc_settings_sanitize($fields['api_key']));

harness_section('validation');
pin('a required field left empty is rejected', 'API key cannot be left empty', osc_settings_validate($fields['api_key'], ''));
pin('a number under its minimum is rejected', 'Batch must be 1 or more', osc_settings_validate($fields['batch'], 0));
pin('a number over its maximum is rejected', 'Batch must be 500 or less', osc_settings_validate($fields['batch'], 900));
pin('a number in range passes', null, osc_settings_validate($fields['batch'], 50));
pin('an option that was never offered is rejected', 'Mode is not one of the available options', osc_settings_validate($fields['mode'], 'debug'));
pin('a declared option passes', null, osc_settings_validate($fields['mode'], 'live'));
pin('a malformed email is rejected', 'Notify is not a valid email address', osc_settings_validate($fields['notify'], 'not-an-email'));
// An optional field left blank has nothing to check; rejecting it would make every
// optional field on the page required by accident.
pin('an optional field left empty passes', null, osc_settings_validate($fields['notify'], ''));

harness_section('a custom validator and a custom sanitiser');
SettingsPageRegistry::instance()->register('cust', array(
    'title'  => 'Custom',
    'fields' => array(
        array('name' => 'slug', 'label' => 'Slug',
              'sanitize' => static fn ($v) => strtolower(str_replace(' ', '-', $v)),
              'validate' => static fn ($v, $f) => strlen($v) > 8 ? 'Slug is too long' : null),
    ),
));
$cust = SettingsPageRegistry::instance()->fields('cust');
$GLOBALS['params'] = array('slug' => 'My Slug');
pin('the field sanitiser runs instead of the type default', 'my-slug', osc_settings_sanitize($cust['slug']));
pin('the field validator runs', 'Slug is too long', osc_settings_validate($cust['slug'], 'aaaaaaaaaa'));

harness_section('saving');
$GLOBALS['params'] = array('api_key' => 'k', 'batch' => '25', 'mode' => 'live', 'verbose' => '1', 'notify' => 'a@b.com');
$result = osc_settings_save('myplugin');
pin('a good submission reports no errors', 0, count($result['errors']));
pin('and writes every field', 5, $result['updated']);
pin('into the page section', '25', $GLOBALS['preferences']['myplugin/batch']);
pin('a checkbox stores 1', '1', $GLOBALS['preferences']['myplugin/verbose']);

$GLOBALS['preferences'] = array();
$GLOBALS['params'] = array('api_key' => '', 'batch' => '900', 'mode' => 'live', 'notify' => '');
$result = osc_settings_save('myplugin');
pin('a bad submission reports every error', 2, count($result['errors']));
// Half a saved page is worse than a rejected one: the half that landed is invisible.
pin('and writes nothing at all', 0, count($GLOBALS['preferences']));
pin('while handing back what was submitted', 900, $result['values']['batch']);

harness_section('reading back');
$GLOBALS['params'] = array('api_key' => 'k', 'batch' => '25', 'mode' => 'live', 'verbose' => '1', 'notify' => 'a@b.com');
osc_settings_save('myplugin');
pin('a number reads back as a number', 25, osc_settings_value('myplugin', 'batch'));
pin('a checkbox reads back as a bool', true, osc_settings_value('myplugin', 'verbose'));
$GLOBALS['params']['verbose'] = '';
osc_settings_save('myplugin');
pin('and an unticked one as false', false, osc_settings_value('myplugin', 'verbose'));

harness_section('the menu entry');
$GLOBALS['menu'] = array();
osc_settings_menu_init();
$ids = array_column($GLOBALS['menu'], 3);
check('a page that asked for a menu gets one', in_array('settings-page-myplugin', $ids, true), implode(',', $ids));
// A page reached from a plugin's own link should not also plant an entry nobody asked for.
SettingsPageRegistry::instance()->register('hidden', array(
    'title'  => 'Hidden',
    'menu'   => '',
    'fields' => array(array('name' => 'a')),
));
$GLOBALS['menu'] = array();
osc_settings_menu_init();
$ids = array_column($GLOBALS['menu'], 3);
check('a page that asked for none does not', !in_array('settings-page-hidden', $ids, true), implode(',', $ids));
// The default belongs under Plugins: that is where a plugin's own settings live.
$entry = array_values(array_filter($GLOBALS['menu'], static fn ($m) => $m[3] === 'settings-page-cust'));
pin('a page that named no section lands under Plugins', 'plugins', $entry[0][0] ?? '');

exit(harness_result());
