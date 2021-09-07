<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

use mindstellar\utility\Deprecate;

/**
 * Scripts enqueue class.
 *
 * @since 3.1.1
 */
class Scripts extends Dependencies
{

    private static $instance;
    /**
     * Keep an array of loaded scripts
     *
     * @var array
     */
    private $scriptsLoaded;

    public function __construct()
    {
        parent::__construct();
        $this->scriptsLoaded = array();
    }

    /**
     * Enqueue Script Code to footer_scripts_loaded hook
     *
     * @param string $code         javascript code string with script tag
     * @param array  $dependencies ids array of registered js libraries (not script code) this code depends on
     */
    public static function enqueueScriptCode($code, $dependencies = null)
    {
        $print_code = static function () use ($code) {
            echo $code;
        };
        Plugins::addHook('footer_scripts_loaded', $print_code, 10);
        if ($dependencies !== null && is_array($dependencies)) {
            foreach ($dependencies as $script) {
                self::newInstance()->enqueueScript($script);
            }
        }
    }

    /**
     * Enqueue script to be loaded
     *
     * @param $id
     */
    public function enqueueScript($id)
    {
        $this->queue[$id] = $id;
    }

    /**
     * @return \Scripts
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Initialize Scripts
     */
    public static function init()
    {
        if (defined(OC_ADMIN) && OC_ADMIN) {
            self::initPrintScriptsAdmin();
        } else {
            self::initPrintScripts();
        }
    }

    /**
     * Print enqueued scripts and run a 'scripts_loaded' hook after scripts print
     *
     */
    private static function initPrintScriptsAdmin()
    {
        $printScript = function () {
            Scripts::newInstance()->printScripts();
        };
        if (!Preference::newInstance()->get('enqueue_scripts_in_footer')) {
            Plugins::addHook('admin_header', $printScript, 10);
        }
        Plugins::addHook('admin_footer', $printScript, 10);

        Plugins::addHook('admin_footer',
            function () {
                Plugins::runHook('admin_scripts_loaded');
            }, 20);
    }

    /**
     *  Print the HTML tags to load the scripts
     */
    public function printScripts()
    {
        foreach ($this->getScripts() as $script) {
            if ($script && !in_array($script, $this->scriptsLoaded, false)) {
                echo '<script src="' . Plugins::applyFilter('theme_url', $script) . '"></script>' . PHP_EOL;
                $this->scriptsLoaded[] = $script;
            }
        }
    }

    /**
     *  Get the scripts urls
     */
    public function getScripts()
    {
        $scripts = array();
        $this->order();
        foreach ($this->resolved as $id) {
            if (isset($this->registered[$id]['url'])) {
                $scripts[] = $this->registered[$id]['url'];
            }
        }

        return $scripts;
    }

    private static function initPrintScripts()
    {
        $printScript = function () {
            Scripts::newInstance()->printScripts();
        };
        if (!Preference::newInstance()->get('enqueue_scripts_in_footer')) {
            Plugins::addHook('header', $printScript, 10);
        }
        Plugins::addHook('footer', $printScript, 10);

        Plugins::addHook('footer',
            function () {
                Plugins::runHook('scripts_loaded');
            }, 20);
    }

    /**
     * Print enqueued scripts and run a 'scripts_loaded' hook after scripts print
     *
     * @param string $prefix
     */
    private static function printScriptRunLoadHook($prefix)
    {
        if ($prefix) {
            osc_load_scripts();
        }
        $closure = static function () use ($prefix) {
            Scripts::newInstance()->printScripts();
            Plugins::runHook($prefix . '_scripts_loaded');
        };
        Plugins::addHook($prefix, $closure, 10);
        if (!Preference::newInstance()->get('enqueue_scripts_in_footer')) {
            Plugins::addHook($prefix, $closure, 10);
        }
    }

    /**
     * Add script to be loaded
     *
     * @param $id
     * @param $url
     * @param $dependencies mixed, it could be an array or a string
     */
    public function registerScript($id, $url, $dependencies = null)
    {
        $this->register($id, $url, $dependencies);
    }

    /**
     * Remove script to not be loaded
     *
     * @param $id
     */
    public function unregisterScript($id)
    {
        $this->unregister($id);
    }

    /**
     * Enqueu script to be loaded
     *
     * @param $id
     *
     * @deprecated since 4.0.0
     */
    public function enqueuScript($id)
    {
        Deprecate::deprecatedFunction(__METHOD__, '4.0.0', 'Scripts::enqueueScript()');
        $this->enqueueScript($id);
    }

    /**
     * Remove script to not be loaded
     *
     * @param $id
     */
    public function removeScript($id)
    {
        unset($this->queue[$id]);
    }
}
