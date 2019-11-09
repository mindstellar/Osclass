<?php  if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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

/**
 * Class Plugins
 */
class Plugins {

    private static $hooks;
    static $plugins_infos = array();
    private static $installed;
    private static $enabled;
    public function __construct()
    {
        
    }

    /**
     * @param $hook
     */
    public static function runHook($hook)
    {
        $args = func_get_args();
        array_shift($args);
        if (isset(self::$hooks[$hook])) {
            for ($priority = 0; $priority <= 10; $priority++) {
                if (isset(self::$hooks[$hook][$priority]) && is_array(self::$hooks[$hook][$priority])) {
                    foreach (self::$hooks[$hook][$priority] as $fxName) {
                        if (is_callable($fxName)) {
                            call_user_func_array($fxName, $args);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $hook
     *
     * @return mixed|string
     */
    public static function applyFilter($hook)
    {
        $args = func_get_args();
        $hook = array_shift($args);
        $content = '';
        if (isset($args[0])) {
            $content = $args[0];
        }

        if (isset(self::$hooks[$hook])) {
            for ($priority = 0; $priority <= 10; $priority++) {
                if (isset(self::$hooks[$hook][$priority]) && is_array(self::$hooks[$hook][$priority])) {
                    foreach (self::$hooks[$hook][$priority] as $fxName) {
                        if (is_callable($fxName)) {
                            $content = call_user_func_array($fxName, $args);
                            $args[0] = $content;
                        }
                    }
                }
            }
        }
        return $content;
    }

    /**
     * @param $plugin
     *
     * @return bool
     */
    static function isInstalled($plugin)
    {
        if (!isset(self::$installed)) {
            self::$installed = self::listInstalled();
        }
        if (in_array($plugin, self::$installed)) {
            return true;
        }

        return false;
    }

    /**
     * @param $plugin
     *
     * @return bool
     */
    static function isEnabled($plugin)
    {
        if (!isset(self::$enabled)) {
            self::$enabled = self::listEnabled();
        }
        if (in_array($plugin, self::$enabled)) {
            return true;
        }

        return false;
    }

    /**
     * @param bool $sort
     *
     * @return array
     */
    public static function listAll($sort = true)
    {
        $plugins = array();
        $pluginsPath = osc_plugins_path();
        $dir = opendir($pluginsPath);
        while ($file = readdir($dir)) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $file, $matches)) {
                // This has to change in order to catch any .php file
                $pluginPath = $pluginsPath . "$file/index.php";
                if (file_exists($pluginPath)) {
                    $plugins[] = $file . '/index.php';
                } else {
                    trigger_error(sprintf(__('Plugin %s is missing the index.php file %s'), $file, $pluginPath));
                }
            }
        }
        closedir($dir);

        if ($sort) {
            $enabled = self::listEnabled();
            $installed = self::listInstalled();
            $extended_list = array();
            foreach ($plugins as $p) {
                $extended_list[$p] = self::getInfo($p);
            }
            uasort($extended_list, array('self', 'strnatcmpCustom'));
            $plugins = array();
            // Enabled
            foreach ($extended_list as $k => $v) {
                if (in_array($k, $enabled)) {
                    $plugins[] = $k;
                    unset($extended_list[$k]);
                }
            }
            // Installed but disabled
            foreach ($extended_list as $k => $v) {
                if (in_array($k, $installed)) {
                    $plugins[] = $k;
                    unset($extended_list[$k]);
                }
            }
            // Not installed
            foreach ($extended_list as $k => $v) {
                $plugins[] = $k;
            }
        }

        return $plugins;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    public static function strnatcmpCustom($a, $b)
    {
        return strnatcasecmp($a['plugin_name'], $b['plugin_name']);
    }

    static function loadActive()
    {

        $plugins_list = self::listEnabled();
        
        if (is_array($plugins_list) && count($plugins_list)>0) {
            foreach ($plugins_list as $plugin_name) {
                $pluginPath = PLUGINS_PATH . $plugin_name;
                if (file_exists($pluginPath)) {
                    //This should include the file and adds the hooks
                    include_once $pluginPath;
                }
            }
        }
    }

    /**
     * @return array
     */
    static function listInstalled()
    {
        if (isset(self::$installed)) {
            return self::$installed;
        }
        $plugins_list = unserialize(osc_installed_plugins);
        if (is_array($plugins_list)) {
            self::$installed = $plugins_list;
            return self::$installed;
        }

        return array();
    }

    /**
     * @return array
     */
    static function listEnabled()
    {
        if (isset(self::$enabled)) {
            return self::$enabled;
        }
        $plugins_list = unserialize(osc_active_plugins());
        if (is_array($plugins_list)) {
            self::$enabled = $plugins_list;
            return self::$enabled;
        }

        return array();
    }

    /**
     * @param $uri
     *
     * @return bool|mixed
     */
    public static function findByUpdateURI($uri)
    {
        $plugins = self::listAll();
        foreach ($plugins as $p) {
            $info = self::getInfo($p);
            if ($info['plugin_update_uri'] == $uri) {
                return $p;
            }
        }
        return false;
    }

    /**
     * @param $path
     *
     * @return bool|string
     */
    public static function resource($path)
    {
        $fullPath = PLUGINS_PATH . $path;
        return file_exists($fullPath) ? $fullPath : false;
    }

    /**
     * @param $path
     * @param $function
     */
    public static function register($path, $function)
    {
        $path = str_replace(PLUGINS_PATH, '', $path);
        $tmp = explode('oc-content/plugins/', $path);
        if (count($tmp) == 2) {
            $path = $tmp[1];
        }
        self::addHook('install_' . $path, $function);
    }

    /**
     * @param $path
     *
     * @return array|bool
     */
    public static function install($path)
    {
        osc_run_hook('before_plugin_install');

        $plugins_list = unserialize(osc_installed_plugins());

        if (is_array($plugins_list) && in_array($path, $plugins_list)) {
            return array('error_code' => 'error_installed');
        }

        if (!file_exists(PLUGINS_PATH . $path)) {
            return array('error_code' => 'error_file');
        }

        try {
            include_once PLUGINS_PATH . $path;

            self::runHook('install_' . $path);
        } catch (Exception $e) {
            return array('error_code' => 'custom_error', 'msg' => $e->getMessage());
        }

        if (!self::activate($path)) {
            return array('error_code' => '');
        }

        $plugins_list[] = $path;
        osc_set_preference('installed_plugins', serialize($plugins_list));

        // Check if something failed
        if (ob_get_length() > 0) {
            return array('error_code' => 'error_output', 'output' => ob_get_clean());
        }

        osc_run_hook('after_plugin_install');

        return true;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function uninstall($path)
    {
        osc_run_hook('before_plugin_uninstall');

        $plugins_list = unserialize(osc_installed_plugins());

        $path = str_replace(PLUGINS_PATH, '', $path);
        if (!is_array($plugins_list)) {
            return false;
        }

        include_once PLUGINS_PATH . $path;

        self::deactivate($path);
        /* if( !self::deactivate($path) ) {
          return false;
          } */

        self::runHook($path . '_uninstall');

        foreach ($plugins_list as $k => $v) {
            if ($v == $path) {
                unset($plugins_list[$k]);
            }
        }

        osc_set_preference('installed_plugins', serialize($plugins_list));

        $plugin = self::getInfo($path);
        self::cleanCategoryFromPlugin($plugin['short_name']);

        osc_run_hook('after_plugin_uninstall');

        return true;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function activate($path)
    {
        osc_run_hook('before_plugin_activate');

        $plugins_list = unserialize(osc_active_plugins());

        if (is_array($plugins_list) && in_array($path, $plugins_list)) {
            return false;
        }

        $plugins_list[] = $path;
        osc_set_preference('active_plugins', serialize($plugins_list));

        self::reload();

        self::runHook($path . '_enable');

        osc_run_hook('after_plugin_activate');

        return true;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function deactivate($path)
    {
        osc_run_hook('before_plugin_deactivate');

        $plugins_list = unserialize(osc_active_plugins());

        $path = str_replace(osc_plugins_path(), '', $path);
        // check if there is some plugin enabled
        if (!is_array($plugins_list)) {
            return false;
        }

        // remove $path from the active plugins list
        foreach ($plugins_list as $k => $v) {
            if ($v == $path) {
                unset($plugins_list[$k]);
            }
        }

        self::runHook($path . '_disable');

        // update t_preference field for active plugins
        osc_set_preference('active_plugins', serialize($plugins_list));

        self::reload();

        osc_run_hook('after_plugin_deactivate');

        return true;
    }

    /**
     * @param $name
     * @param $id
     *
     * @return mixed
     */
    public static function isThisCategory($name, $id)
    {
        return PluginCategory::newInstance()->isThisCategory($name, $id);
    }

    /**
     * @param $plugin
     *
     * @return array
     */
    public static function getInfo($plugin)
    {
        if (isset(self::$plugins_infos[$plugin])) {
             return self::$plugins_infos[$plugin];
        }
        $s_info = file_get_contents(osc_plugins_path() . $plugin);
        $info = array();
        if (preg_match('|Plugin Name:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['plugin_name'] = trim($match[1]);
        } else {
            $info['plugin_name'] = $plugin;
        }

        if (preg_match('|Plugin URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['plugin_uri'] = trim($match[1]);
        } else {
            $info['plugin_uri'] = '';
        }

        if (preg_match('|Plugin update URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['plugin_update_uri'] = trim($match[1]);
        } else {
            $info['plugin_update_uri'] = '';
        }

        if (preg_match('|Support URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['support_uri'] = trim($match[1]);
        } else {
            $info['support_uri'] = '';
        }

        if (preg_match('|Description:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['description'] = trim($match[1]);
        } else {
            $info['description'] = '';
        }

        if (preg_match('|Version:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['version'] = trim($match[1]);
        } else {
            $info['version'] = '';
        }

        if (preg_match('|Author:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['author'] = trim($match[1]);
        } else {
            $info['author'] = '';
        }

        if (preg_match('|Author URI:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['author_uri'] = trim($match[1]);
        } else {
            $info['author_uri'] = '';
        }

        if (preg_match('|Short Name:([^\\r\\t\\n]*)|i', $s_info, $match)) {
            $info['short_name'] = trim($match[1]);
        } else {
            $info['short_name'] = $info['plugin_name'];
        }

        $info['filename'] = $plugin;
        self::$plugins_infos[$plugin] =  $info;
        return $info;
    }

    /**
     * @param $plugin
     *
     * @return bool
     */
    public static function checkUpdate($plugin)
    {
        $info = self::getInfo($plugin);
        return osc_check_plugin_update($info['plugin_update_uri'], $info['version']);
    }

    /**
     * @param $path
     */
    public static function configureView($path)
    {
        $plugin = str_replace(PLUGINS_PATH, '', $path);
        if (stripos($plugin, '.php') === false) {
            $plugins_list = unserialize(osc_active_plugins());
            if (is_array($plugins_list)) {
                foreach ($plugins_list as $p) {
                    $data = self::getInfo($p);
                    if ($plugin == $data['plugin_name']) {
                        $plugin = $p;
                        break;
                    }
                }
            }
        }
        osc_redirect_to(osc_plugin_configure_url($plugin));
    }

    /**
     * @param $plugin
     */
    public static function cleanCategoryFromPlugin($plugin)
    {
        $dao_pluginCategory = new PluginCategory();
        $dao_pluginCategory->delete(array('s_plugin_name' => $plugin));
        unset($dao_pluginCategory);
    }

    /**
     * @param $categories
     * @param $plugin
     */
    public static function addToCategoryPlugin($categories, $plugin)
    {
        $dao_pluginCategory = new PluginCategory();
        $dao_category = new Category();
        if (!empty($categories)) {
            foreach ($categories as $catId) {
                $result = $dao_pluginCategory->isThisCategory($plugin, $catId);
                if ($result == 0) {
                    $fields = array();
                    $fields['s_plugin_name'] = $plugin;
                    $fields['fk_i_category_id'] = $catId;
                    $dao_pluginCategory->insert($fields);

                    $subs = $dao_category->findSubcategories($catId);
                    if (is_array($subs) && count($subs) > 0) {
                        $cats = array();
                        foreach ($subs as $sub) {
                            $cats[] = $sub['pk_i_id'];
                        }
                        self::addToCategoryPlugin($cats, $plugin);
                    }
                }
            }
        }
        unset($dao_pluginCategory, $dao_category);
    }

    // Add a hook

    /**
     * @param     $hook
     * @param     $function
     * @param int $priority
     */
    public static function addHook($hook, $function, $priority = 5)
    {
        $hook = preg_replace('|/+|', '/', str_replace('\\', '/', $hook));
        $plugin_path = str_replace('\\', '/', osc_plugins_path());
        $hook = str_replace($plugin_path, '', $hook);
        $found_plugin = false;
        if (isset(self::$hooks[$hook])) {
            for ($_priority = 0; $_priority <= 10; $_priority++) {
                if (isset(self::$hooks[$hook][$_priority])) {
                    foreach (self::$hooks[$hook][$_priority] as $fxName) {
                        if ($fxName == $function) {
                            $found_plugin = true;
                            break;
                        }
                    }
                }
            }
        }
        if (!$found_plugin) {
            self::$hooks[$hook][$priority][] = $function;
        }
    }

    /**
     * @param $hook
     * @param $function
     */
    public static function removeHook($hook, $function)
    {
        for ($priority = 0; $priority <= 10; $priority++) {
            if (isset(self::$hooks[$hook][$priority])) {
                foreach (self::$hooks[$hook][$priority] as $k => $v) {
                    if ($v == $function) {
                        unset(self::$hooks[$hook][$priority][$k]);
                    }
                }
            }
        }
    }

    public static function getActive()
    {
        return self::$hooks;
    }

    public static function reload()
    {
        osc_reset_preferences();
        self::init();
    }

    public static function init()
    {
        self::loadActive();
    }

}
