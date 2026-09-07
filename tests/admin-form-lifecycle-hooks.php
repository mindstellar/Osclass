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
 * Pins the declared save path's lifecycle hooks: the point that unblocks side effects like
 * Permalinks rewriting .htaccess or Sitemap writing robots.txt on a declared page, without
 * ever running them on a rejected submission.
 *
 * What silently breaks without this:
 *
 *  - a side effect wired to 'admin_form_after_save' (or a page's inline 'after_save')
 *    running on a *rejected* save, which is exactly the bug this contract exists to rule
 *    out -- a form that failed validation must not rewrite a file or queue a job;
 *  - the same effect running twice on one successful save, e.g. because a future change
 *    moves the write loop without noticing a hook sits either side of it;
 *  - 'admin_form_before_save' seeing the raw submission instead of the sanitised and
 *    validated values, which is the whole reason it exists over reading $_POST directly;
 *  - 'admin_form_before_save' or 'admin_form_render_field' degrading from a filter to an
 *    action: both exist to transform something, and a hook hands a listener a copy, so the
 *    derived value or the edited spec would be dropped without a word;
 *  - an existing declared page with no hooks at all changing behaviour just because the
 *    hook points now exist.
 *
 * DB-free: osc_settings_save() is exercised with a stubbed preference store, the same
 * technique tests/settings-page-registry.php already uses.
 *
 * Usage: php tests/admin-form-lifecycle-hooks.php
 */

if (!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}
if (!defined('OC_ADMIN')) {
    define('OC_ADMIN', true);
}

require_once __DIR__ . '/lib/harness.php';
require_once ABS_PATH . 'oc-includes/osclass/helpers/hSanitize.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/settings/SettingsPageRegistry.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/form/store/Store.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/form/store/PreferenceStore.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/form/store/TableStore.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/form/store/StoreFactory.php';

use mindstellar\settings\SettingsPageRegistry;

// --- the slice of core the helper leans on -----------------------------------------
$GLOBALS['params']      = array();
$GLOBALS['preferences'] = array();
$GLOBALS['hookLog']     = array();
$GLOBALS['filters']     = array();
$GLOBALS['viewVars']    = array();

class Params
{
    public static function getParam($key, $a = false, $b = true)
    {
        return $GLOBALS['params'][$key] ?? '';
    }

    // The save path purifies declared text through this. Left as a pass-through: what it
    // strips is pinned against the real one in tests/admin-form-text-purify.php, and these
    // tests are about what reaches it.
    public static function purifyText($value)
    {
        return $value;
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
}

/** Every call is logged in order, so ordering and counts are both pinnable. */
function osc_run_hook($hook, ...$args)
{
    $GLOBALS['hookLog'][] = array($hook, $args);
}

/**
 * The real filter runner in miniature: listeners registered for the name are chained and
 * the last return value wins, which is the behaviour a hook cannot give.
 */
function osc_apply_filter($hook, $content = '', ...$args)
{
    $GLOBALS['hookLog'][] = array($hook, array_merge(array($content), $args));
    foreach ($GLOBALS['filters'][$hook] ?? array() as $fn) {
        $content = $fn($content, ...$args);
    }

    return $content;
}

function listen(string $hook, callable $fn): void
{
    $GLOBALS['filters'][$hook][] = $fn;
}

function __get($key)
{
    return $GLOBALS['viewVars'][$key] ?? null;
}

function osc_admin_page(array $opts = array())
{
}

function osc_current_admin_theme_path($path)
{
}

function osc_admin_base_url($index = false)
{
    return 'https://example.test/oc-admin/index.php';
}

function osc_add_admin_submenu_page($menu, $title, $url, $id, $capability = 'administrator')
{
}

function osc_validate_email($email, $required = true)
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function osc_validate_url($value, $required = false, $headers = false)
{
    return (bool)filter_var($value, FILTER_VALIDATE_URL);
}

function __($key, $domain = 'core')
{
    return $key;
}

require_once ABS_PATH . 'oc-includes/osclass/helpers/hSettings.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/ui/Field.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/ui/Form.php';
require_once ABS_PATH . 'oc-includes/osclass/classes/admin/ui/SettingsForm.php';
require_once ABS_PATH . 'oc-includes/osclass/helpers/hAdminUi.php';

/** Draw a declared page through the view core owns, and hand back the markup. */
function render_settings_page(string $pageId, array $values = array()): string
{
    $GLOBALS['viewVars'] = array(
        'settings_page'   => osc_settings_page($pageId),
        'settings_values' => $values,
    );
    ob_start();
    require ABS_PATH . 'oc-includes/osclass/gui/admin/settings-page.php';

    return (string)ob_get_clean();
}

/** Hook names logged, in order, since the log was last reset. */
function hook_names(): array
{
    return array_column($GLOBALS['hookLog'], 0);
}

function reset_log(): void
{
    $GLOBALS['hookLog']     = array();
    $GLOBALS['preferences'] = array();
    $GLOBALS['filters']     = array();
}

// --- a page with no hooks at all: the regression guard -----------------------------
SettingsPageRegistry::instance()->register('plain', array(
    'title'  => 'Plain',
    'fields' => array(
        array('type' => 'text', 'name' => 's_name', 'label' => 'Name', 'required' => true),
    ),
));

harness_section('a page declaring no hooks behaves exactly as before');
reset_log();
$GLOBALS['params'] = array('s_name' => 'ok');
$result = osc_settings_save('plain');
pin('a good save still reports no errors', 0, count($result['errors']));
pin('and still writes the field', 1, $result['updated']);
// The lifecycle hooks still fire (any page can be listened to from outside), but nothing
// about the page's own declared behaviour changes because it named none of them.
check(
    'settings_page_saved still fires, exactly once',
    array_count_values(hook_names())['settings_page_saved'] === 1
);

reset_log();
$GLOBALS['params'] = array('s_name' => '');
$result = osc_settings_save('plain');
pin('a bad save still reports its error', 1, count($result['errors']));
// The store itself, not the reported count: 'updated' counts values that changed, so it
// reads 0 for a save that wrote every field back unchanged as well as for one that wrote
// nothing at all.
pin('and still writes nothing', 0, count($GLOBALS['preferences']));
check('settings_page_saved does not fire on a rejected save', !in_array('settings_page_saved', hook_names(), true));

// --- a page with an after_save hook AND an inline after_save -----------------------
$inlineCalls = array();
SettingsPageRegistry::instance()->register('withhooks', array(
    'title'      => 'With hooks',
    'fields'     => array(
        array('type' => 'text', 'name' => 's_name', 'label' => 'Name', 'required' => true),
    ),
    // Logged into the shared log as well as counted: an ordering claim about the inline
    // callable is only testable if the callable writes to the log the order is read from.
    'after_save' => static function ($values, $id) use (&$inlineCalls) {
        $inlineCalls[]        = array($values, $id);
        $GLOBALS['hookLog'][] = array('inline:after_save', array($values, $id));
    },
));

harness_section('admin_form_before_save');
reset_log();
$GLOBALS['params'] = array('s_name' => '  Alice  ');
osc_settings_save('withhooks');
$before = array_values(array_filter($GLOBALS['hookLog'], static fn ($h) => $h[0] === 'admin_form_before_save'));
pin('fires exactly once on a valid submission', 1, count($before));
pin('filters the values, so they are the first argument', 'Alice', $before[0][1][0]['s_name'] ?? null);
pin('and carries the page id after them', 'withhooks', $before[0][1][1] ?? null);
// Both positions have to be checked for presence first: array_search answers a miss with
// false, and false is less than any offset, so an unguarded ordering comparison passes
// loudest exactly when the hook it is about was never fired.
$beforeAt = array_search('admin_form_before_save', hook_names(), true);
$savedAt  = array_search('settings_page_saved', hook_names(), true);
check(
    'fires before settings_page_saved',
    $beforeAt !== false && $savedAt !== false && $beforeAt < $savedAt,
    'admin_form_before_save at ' . var_export($beforeAt, true) . ', settings_page_saved at ' . var_export($savedAt, true)
);

// Ordering against another hook only proves an ordering between hooks. This proves the
// claim that matters: nothing has reached the store yet when the filter runs.
reset_log();
$storeWhenFilterRan = 'the listener never ran';
listen('admin_form_before_save', static function ($values, $pageId) use (&$storeWhenFilterRan) {
    $storeWhenFilterRan = $GLOBALS['preferences'];

    return $values;
});
$GLOBALS['params'] = array('s_name' => 'Alice');
osc_settings_save('withhooks');
pin('and the store is still untouched when it runs', array(), $storeWhenFilterRan);

// The whole point of the filter: a listener deriving s_slug from s_title has to be able to
// hand the change back. Run as an action it would mutate a copy and the store would write
// the original with nothing to show for it.
reset_log();
listen('admin_form_before_save', static function ($values, $pageId) {
    $values['s_name'] = strtoupper($values['s_name']);

    return $values;
});
$GLOBALS['params'] = array('s_name' => 'Alice');
$result = osc_settings_save('withhooks');
pin('a listener returning modified values changes what is persisted', 'ALICE', $GLOBALS['preferences']['withhooks/s_name'] ?? null);
pin('and what the caller is handed back for re-render', 'ALICE', $result['values']['s_name'] ?? null);

// A listener that forgets to return is a plugin bug, not a white screen on the save path.
reset_log();
listen('admin_form_before_save', static function ($values, $pageId) {
});
$GLOBALS['params'] = array('s_name' => 'Alice');
osc_settings_save('withhooks');
pin('a listener returning nothing leaves the values intact', 'Alice', $GLOBALS['preferences']['withhooks/s_name'] ?? null);

// --- what the write loop is allowed to write --------------------------------------
// admin_form_before_save hands a listener the whole value array, so the write loop has to
// walk the *declared* fields and not the array it was handed. Walking $values instead would
// let any listener write an arbitrary preference under any page's section, on every page.
SettingsPageRegistry::instance()->register('writeset', array(
    'title'  => 'Write set',
    'fields' => array(
        array('type' => 'text', 'name' => 's_one', 'label' => 'One'),
        array('type' => 'text', 'name' => 's_two', 'label' => 'Two'),
        array(
            'type'   => 'custom',
            'name'   => 's_custom',
            'label'  => 'Custom',
            'render' => static function ($field, $value) {
            },
        ),
    ),
));

harness_section('the write set is the declared fields, nothing else');
reset_log();
$GLOBALS['params'] = array('s_one' => '1', 's_two' => '2');
osc_settings_save('writeset');
// A custom field is never collected, so it is never written: the plugin owns that value.
pin(
    'a save writes exactly the declared non-custom fields',
    array('writeset/s_one', 'writeset/s_two'),
    array_keys($GLOBALS['preferences'])
);

reset_log();
listen('admin_form_before_save', static function ($values, $pageId) {
    $values['s_undeclared'] = 'injected';

    return $values;
});
$GLOBALS['params'] = array('s_one' => '1', 's_two' => '2');
$result = osc_settings_save('writeset');
check(
    'a listener may still add a key -- the filter contract is honoured',
    ($result['values']['s_undeclared'] ?? null) === 'injected'
);
check('but an undeclared key is not written', !array_key_exists('writeset/s_undeclared', $GLOBALS['preferences']));
pin(
    'and the write set is unchanged by it',
    array('writeset/s_one', 'writeset/s_two'),
    array_keys($GLOBALS['preferences'])
);

// The edge the two rules meet at. Core deliberately skips collecting a custom field, so it
// also declines to write one a listener injected: core does not know what a custom field
// submitted, and a value it never read is not one it can be responsible for storing.
reset_log();
listen('admin_form_before_save', static function ($values, $pageId) {
    $values['s_custom'] = 'injected';

    return $values;
});
$GLOBALS['params'] = array('s_one' => '1', 's_two' => '2');
$result = osc_settings_save('writeset');
check(
    'a value injected for a declared custom field reaches the caller',
    ($result['values']['s_custom'] ?? null) === 'injected'
);
check('but core does not write it', !array_key_exists('writeset/s_custom', $GLOBALS['preferences']));

harness_section('admin_form_after_save and the inline after_save: success');
reset_log();
$inlineCalls       = array();
$GLOBALS['params'] = array('s_name' => 'Bob');
osc_settings_save('withhooks');
$after = array_values(array_filter($GLOBALS['hookLog'], static fn ($h) => $h[0] === 'admin_form_after_save'));
pin('the hook fires exactly once on success', 1, count($after));
// Guarded so that a hook or callable which never ran fails this assertion and lets the rest
// of the file run, rather than fataling on a missing offset and taking the tally with it.
pin(
    'with a null id, because a preference page has no row to name one',
    null,
    isset($after[0]) && array_key_exists(2, $after[0][1]) ? $after[0][1][2] : 'missing'
);
pin('the inline after_save fires exactly once on success', 1, count($inlineCalls));
pin('the inline after_save sees the saved values', 'Bob', $inlineCalls[0][0]['s_name'] ?? null);
pin(
    'the inline after_save sees the same null id',
    null,
    isset($inlineCalls[0]) && array_key_exists(1, $inlineCalls[0]) ? $inlineCalls[0][1] : 'missing'
);
$hookAt   = array_search('admin_form_after_save', hook_names(), true);
$inlineAt = array_search('inline:after_save', hook_names(), true);
check('the hook is in the log', $hookAt !== false);
check('the inline callable is in the log', $inlineAt !== false);
check(
    'firing order is deterministic: hook before the inline callable',
    $hookAt !== false && $inlineAt !== false && $hookAt < $inlineAt,
    'admin_form_after_save at ' . var_export($hookAt, true) . ', inline at ' . var_export($inlineAt, true)
);

harness_section('re-saving identical values');
reset_log();
$inlineCalls       = array();
$GLOBALS['params'] = array('s_name' => 'Eve');
$first = osc_settings_save('withhooks');
pin('the first save changes one value', 1, $first['updated']);

// 'updated' is a changed-count, not a success flag. Re-submitting the same values changes
// nothing and that is a successful save, not a failed one -- reading a zero here as failure
// is the exact bug CAdminSettingsKeywordBlock carries a comment about.
$GLOBALS['hookLog'] = array();
$inlineCalls        = array();
$second = osc_settings_save('withhooks');
pin('re-saving identical values reports no errors', 0, count($second['errors']));
pin('and reports zero changed, which is success and not an error', 0, $second['updated']);
pin('the value is still in the store afterwards', 'Eve', $GLOBALS['preferences']['withhooks/s_name'] ?? null);
// The effects do fire: the save succeeded. A listener that only wants real changes has to
// compare values itself, because core does not suppress the hook for an unchanged save.
check('admin_form_after_save still fires on an unchanged save', in_array('admin_form_after_save', hook_names(), true));
pin('and the inline after_save still runs exactly once', 1, count($inlineCalls));
check('admin_form_save_failed does not fire for it', !in_array('admin_form_save_failed', hook_names(), true));

harness_section('admin_form_after_save and the inline after_save: rejected submission');
reset_log();
$inlineCalls       = array();
$GLOBALS['params'] = array('s_name' => '');
$result = osc_settings_save('withhooks');
pin('the submission is in fact rejected', 1, count($result['errors']));
check('admin_form_after_save does not fire on a rejected save', !in_array('admin_form_after_save', hook_names(), true));
check('the inline after_save does not run on a rejected save', $inlineCalls === array());
check('settings_page_saved does not fire either', !in_array('settings_page_saved', hook_names(), true));

harness_section('admin_form_save_failed');
reset_log();
$GLOBALS['params'] = array('s_name' => '');
osc_settings_save('withhooks');
$failed = array_values(array_filter($GLOBALS['hookLog'], static fn ($h) => $h[0] === 'admin_form_save_failed'));
pin('fires exactly once on the rejected path', 1, count($failed));
pin('carries the page id', 'withhooks', $failed[0][1][0] ?? null);
check('carries the validation errors', count($failed[0][1][1] ?? array()) === 1);

reset_log();
$GLOBALS['params'] = array('s_name' => 'Carol');
osc_settings_save('withhooks');
check('admin_form_save_failed does not fire on a successful save', !in_array('admin_form_save_failed', hook_names(), true));

// A page id nobody registered is still a failed save. A listener counting them (or clearing
// a draft) would otherwise under-count exactly the submissions that went nowhere.
reset_log();
$GLOBALS['params'] = array('s_name' => 'Dave');
$result = osc_settings_save('no-such-page');
pin('an unregistered page is rejected', 1, count($result['errors']));
$failed = array_values(array_filter($GLOBALS['hookLog'], static fn ($h) => $h[0] === 'admin_form_save_failed'));
pin('and admin_form_save_failed fires for it too', 1, count($failed));
pin('carrying the page id that was asked for', 'no-such-page', $failed[0][1][0] ?? null);
check('with the error it was rejected with', count($failed[0][1][1] ?? array()) === 1);
check('nothing is written', $GLOBALS['preferences'] === array());

harness_section('admin_form_render_field');
reset_log();
$html = render_settings_page('withhooks');
$rendered = array_values(array_filter($GLOBALS['hookLog'], static fn ($h) => $h[0] === 'admin_form_render_field'));
pin('fires once per declared field', 1, count($rendered));
pin('filters the field spec, so it is the first argument', 's_name', $rendered[0][1][0]['name'] ?? null);
pin('and carries the page id after it', 'withhooks', $rendered[0][1][1] ?? null);
check('the declared label renders when nothing listens', strpos($html, '>Name</label>') !== false, $html);

// A listener can edit the spec -- add a hint, retitle, mark readonly. It cannot suppress or
// replace the control, and this is what proves the edit survives to the markup.
reset_log();
listen('admin_form_render_field', static function ($field, $pageId, $values) {
    $field['label'] = 'Renamed by a plugin';
    $field['help']  = 'Added by a plugin';

    return $field;
});
$html = render_settings_page('withhooks');
check('a listener editing the label changes the rendered markup', strpos($html, 'Renamed by a plugin') !== false, $html);
check('the declared label is gone', strpos($html, '>Name</label>') === false, $html);
check('a hint the listener added is rendered too', strpos($html, 'Added by a plugin') !== false, $html);
check('the control itself still renders', strpos($html, 'id="field-s_name"') !== false, $html);

// Same plugin bug as on the save path: a listener that returns nothing must not take the
// admin page down with a TypeError.
reset_log();
listen('admin_form_render_field', static function ($field, $pageId, $values) {
});
$html = render_settings_page('withhooks');
check('a listener returning nothing leaves the declared field rendering', strpos($html, '>Name</label>') !== false, $html);

exit(harness_result());
