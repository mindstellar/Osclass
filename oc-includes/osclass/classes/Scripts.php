<?php

use mindstellar\osclass\classes\utility\Deprecate;

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
        $print_header_scripts = static function () {
            self::newInstance()->printScripts();
            Plugins::runHook('scripts_loaded');
        };

        $print_footer_scripts = static function () {
            self::newInstance()->printScripts();
            Plugins::runHook('footer_scripts_loaded');
        };

        if (OC_ADMIN) {
            if (Preference::newInstance()->get('enqueue_scripts_in_footer')) {
                Plugins::addHook('admin_header', $print_header_scripts, 10);
            }
            Plugins::addHook('admin_footer', $print_footer_scripts, 10);
        } else {
            if (Preference::newInstance()->get('enqueue_scripts_in_footer')) {
                Plugins::addHook('header', $print_header_scripts, 10);
            }
            Plugins::addHook('footer', $print_footer_scripts, 10);
        }
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
     */
    public function enqueuScript($id)
    {
        Deprecate::deprecatedFunction(__METHOD__, '4.0.0', 'Scripts::enqueueScript()');
        $this->enqueueScript($id);
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
     * Remove script to not be loaded
     *
     * @param $id
     */
    public function removeScript($id)
    {
        unset($this->queue[$id]);
    }
}
