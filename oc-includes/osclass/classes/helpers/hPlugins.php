<?php

/*
 * Copyright 2014 Osclass
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace mindstellar\osclass\classes\helpers;

use Plugins;

/**
 * Helper Plugins
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */
class Plugins
{
    /**
     * Run a hook
     *
     * @param       $hook
     * @param mixed ...$args
     */
    public static function osc_run_hook($hook, ...$args)
    {
        Plugins::runHook($hook, ...$args);
    }


    /**
     * Apply a filter to a text
     *
     * @param       $hook
     * @param       $content
     * @param mixed ...$args
     *
     * @return mixed
     */
    public static function osc_apply_filter($hook, $content, ...$args)
    {
        return Plugins::applyFilter($hook, $content, ...$args);
    }


    /**
     * Add a hook
     *
     * @param string   $hook
     * @param callable $function
     * @param int      $priority
     *
     * @return void
     */
    public static function osc_add_hook($hook, $function, $priority = 5)
    {
        Plugins::addHook($hook, $function, $priority);
    }


    /**
     * Add a filter
     *
     * @param string   $hook
     * @param callable $function
     * @param int      $priority
     *
     * @return void
     */
    public static function osc_add_filter($hook, $function, $priority = 5)
    {
        Plugins::addHook($hook, $function, $priority);
    }


    /**
     * Remove a hook's function
     *
     * @param string   $hook
     * @param callable $function
     *
     * @return void
     */
    public static function osc_remove_hook($hook, $function)
    {
        Plugins::removeHook($hook, $function);
    }


    /**
     * Remove a filter's function
     *
     * @param string   $hook
     * @param callable $function
     *
     * @return void
     */
    public static function osc_remove_filter($hook, $function)
    {
        Plugins::removeHook($hook, $function);
    }


    /**
     * If the plugin is attached to the category
     *
     * @param string $name
     * @param int    $id
     *
     * @return boolean
     */
    public static function osc_is_this_category($name, $id)
    {
        return Plugins::isThisCategory($name, $id);
    }


    /**
     * Returns plugin's information
     *
     * @param $plugin
     *
     * @return array
     */
    public static function osc_plugin_get_info($plugin)
    {
        return Plugins::getInfo($plugin);
    }


    /**
     * Check if there's a new version of the plugin
     *
     * @param string $plugin
     *
     * @return boolean
     */
    public static function osc_plugin_check_update($plugin)
    {
        return Plugins::checkUpdate($plugin);
    }


    /**
     * Register a plugin file to be loaded
     *
     * @param string   $path
     * @param callable $function
     *
     * @return void
     */
    public static function osc_register_plugin($path, $function)
    {
        Plugins::register($path, $function);
    }


    /**
     * Get list of the plugins
     *
     * @return array
     */
    public static function osc_get_plugins()
    {
        return Plugins::getActive();
    }


    /**
     * Gets if a plugin is installed or not
     *
     * @param string $plugin
     *
     * @return bool
     */
    public static function osc_plugin_is_installed($plugin)
    {
        return Plugins::isInstalled($plugin);
    }


    /**
     * Gets if a plugin is enabled or not
     *
     * @param string $plugin
     *
     * @return bool
     */
    public static function osc_plugin_is_enabled($plugin)
    {
        return Plugins::isEnabled($plugin);
    }


    /**
     * Show the default configure view for plugins (attach them to categories)
     *
     * @param string $plugin
     *
     * @return void
     */
    public static function osc_plugin_configure_view($plugin)
    {
        Plugins::configureView($plugin);
    }


    /**
     * Gets the path to a plugin's resource
     *
     * @param string $file
     *
     * @return string
     */
    public static function osc_plugin_resource($file)
    {
        return Plugins::resource($file);
    }


    /**
     * Gets plugin's configure url
     *
     * @param string $plugin
     *
     * @return string
     */
    public static function osc_plugin_configure_url($plugin)
    {
        return osc_admin_base_url(true) . '?page=plugins&action=configure&plugin=' . $plugin;
    }


    /**
     * Gets the ajax url
     *
     * @param string $hook
     * @param array  $params
     *
     * @return string
     * @since 3.1
     */
    public static function osc_admin_ajax_hook_url($hook = '', $params = array())
    {
        return self::_osc_ajax_hook_url(true, $hook, $params);
    }

    /**
     * Gets the ajax url
     *
     * @param string $admin
     * @param string $hook
     * @param array  $params
     *
     * @return string
     * @since 3.1
     */
    private static function _osc_ajax_hook_url($admin, $hook, $params)
    {
        if ($admin) {
            $url = osc_admin_base_url(true);
        } else {
            $url = osc_base_url(true);
        }

        $url .= '?page=ajax&action=runhook';

        if ($hook != '') {
            $url .= '&hook=' . $hook;
        }

        if (is_array($params)) {
            $url_params = array();
            foreach ($params as $k => $v) {
                $url_params[] = sprintf('%s=%s', $k, $v);
            }
            $url .= '&' . implode('&', $url_params);
        }

        return $url;
    }

    /**
     * Gets the ajax url
     *
     * @param string $hook
     * @param array  $params
     *
     * @return string
     * @since 3.0
     */
    public static function osc_ajax_hook_url($hook = '', $params = array())
    {
        return self::_osc_ajax_hook_url(false, $hook, $params);
    }

    /**
     * Gets the path for ajax
     *
     * @param string $file
     *
     * @return string
     */
    public static function osc_ajax_plugin_url($file = '')
    {
        $file        = preg_replace('|/+|', '/', str_replace('\\', '/', $file));
        $plugin_path = str_replace('\\', '/', osc_plugins_path());
        $file        = str_replace($plugin_path, '', $file);

        return (osc_base_url(true) . '?page=ajax&action=custom&ajaxfile=' . $file);
    }


    /**
     * Gets the configure admin's url
     *
     * @param string $file
     *
     * @return string
     */
    public static function osc_admin_configure_plugin_url($file = '')
    {
        $file        = preg_replace('|/+|', '/', str_replace('\\', '/', $file));
        $plugin_path = str_replace('\\', '/', osc_plugins_path());
        $file        = str_replace($plugin_path, '', $file);

        return osc_admin_base_url(true) . '?page=plugins&action=configure&plugin=' . $file;
    }


    /**
     * Gets urls for custom plugin administrations options
     *
     * @param string $file
     *
     * @return string
     */
    public static function osc_admin_render_plugin_url($file = '')
    {
        $file        = preg_replace('|/+|', '/', str_replace('\\', '/', $file));
        $plugin_path = str_replace('\\', '/', osc_plugins_path());
        $file        = str_replace($plugin_path, '', $file);

        return osc_admin_base_url(true) . '?page=plugins&action=renderplugin&file=' . $file;
    }


    /**
     * Show custom plugin administrationfile
     *
     * @param string $file
     *
     * @return void
     */
    public static function osc_admin_render_plugin($file = '')
    {
        osc_redirect_to(osc_admin_render_plugin_url($file));
    }
}
