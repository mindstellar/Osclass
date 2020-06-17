<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 18/05/20
 * Time: 4:40 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\utility;

use Plugins;

/**
 * Class Deprecated
 * Provides common static method to deprecate functions,class,file,hooks,filters
 *
 * @package mindstellar\osclass\classes\utility
 */
class Deprecate
{
    /**
     * Deprecate Function
     * Fire a 'd_function_run' hook when deprecated function is called.
     *
     * @param string      $function    Deprecated function name
     * @param string      $version     The version of Osclass that deprecated the file.
     * @param string|null $replacement The function that should have been used.
     */
    public static function deprecatedFunction(
        $function,
        $version,
        $replacement = null
    ) {

        $debug_backtrace = debug_backtrace();
        $caller = next($debug_backtrace);
        /**
         * Fires when a deprecated function is called.
         */
        Plugins::runHook('d_function_run', $function, $replacement, $version);

        if (OSC_DEBUG) {
            if ($replacement !== null) {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'
                        .'</strong> in <strong>'
                        .$caller['file'].'</strong> on line <strong>'
                        .$caller['line'].'</strong>'."\n<br /> error handled",
                        $function,
                        $version,
                        $replacement
                    ),
                    E_USER_DEPRECATED
                );
            } else {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'
                        .'</strong> in <strong>'
                        .$caller['file'].'</strong> on line <strong>'
                        .$caller['line'].'</strong>'."\n<br /> error handled",
                        $function,
                        $version
                    ),
                    E_USER_DEPRECATED
                );
            }
        }
    }

    /**
     * Deprecate Hook
     *
     * @param string      $hook        Hook name to run
     * @param string      $version     The version of Osclass that deprecated this Hook
     * @param string|null $replacement Replacement Hook name if available
     * @param string|null $message     A message regarding the change.
     * @param mixed       $args,...    hook arguments
     */
    public static function deprecatedRunHook(
        $hook,
        $version,
        $replacement = null,
        $message = null,
        ...$args
    ) {
        if (!Plugins::hasHook($hook)) {
            return;
        }

        self::deprecatedHook($hook, $version, $replacement, $message);

        Plugins::runHook($hook, ...$args);
    }

    /**
     * For internal use only
     * run when a deprecated hook/filter is used.
     *
     * @param      $hook
     * @param      $version
     * @param null $replacement
     * @param null $message
     */
    private static function deprecatedHook(
        $hook,
        $version,
        $replacement = null,
        $message = null
    ) {
        /**
         * Fires when a deprecated hook/filter is called.
         */
        Plugins::runHook('d_hook_run', $hook, $replacement, $version, $message);

        if (OSC_DEBUG) {
            $message = empty($message) ? '' : ' ' . $message;

            if ($replacement !== null) {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.',
                        $hook,
                        $version,
                        $replacement
                    ) . $message,
                    E_USER_DEPRECATED
                );
            } else {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.',
                        $hook,
                        $version
                    ) . $message,
                    E_USER_DEPRECATED
                );
            }
        }
    }

    /**
     * Deprecate Filter
     *
     * @param string      $filter      Filter name to run
     * @param mixed       $content     Content to filter
     * @param string      $version     The version of Osclass that deprecated this Filter
     * @param null|string $replacement Replacement Filter name if available.
     * @param null|string $message     A messaged regarding the change.
     * @param mixed       $args,...    filter arguments.
     */
    public static function deprecatedApplyFilter(
        $filter,
        $content,
        $version,
        $replacement = null,
        $message = null,
        ...$args
    ) {
        if (Plugins::hadRun($filter)) {
            return;
        }

        self::deprecatedHook($filter, $version, $replacement, $message);

        Plugins::applyFilter($filter, $content, ...$args);
    }

    /**
     * Deprecate File
     *
     * @param string $file        The file that was called.
     * @param string $replacement The file that should have been included based on ABS_PATH.
     * @param string $version     The version of Osclass that deprecated the file.
     * @param string $message     A message regarding the change.
     */
    public static function deprecatedFile(
        $file,
        $version,
        $replacement = null,
        $message = ''
    ) {

        /**
         * Fires when a deprecated file is called.
         */
        Plugins::runHook('d_file_included', $file, $replacement, $version, $message);

        if (OSC_DEBUG) {
            $message = empty($message) ? '' : ' ' . $message;

            if ($replacement !== null) {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.',
                        $file,
                        $version,
                        $replacement
                    ) . $message,
                    E_USER_DEPRECATED
                );
            } else {
                trigger_error(
                    sprintf(
                        '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.',
                        $file,
                        $version
                    ) . $message,
                    E_USER_DEPRECATED
                );
            }
        }
    }
}
