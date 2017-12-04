<?php

/**
 * Styles enqueue class.
 *
 * @since 3.1.1
 */
class Styles {

    public $styles = array();

    private static $instance;

	/**
	 * @return \Styles
	 */
	public static function newInstance()
    {
        if(!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct()
    {
        $styles = array();
    }

    /**
     * Add style to be loaded
     *
     * @param $id
     * @param $url
     */
    public function addStyle($id, $url)
    {
        $this->styles[$id] = $url;
    }

    /**
     * Remove style to not be loaded
     *
     * @param $id
     */
    public function removeStyle($id)
    {
        unset($this->styles[$id]);
    }

    /**
     * Get the css styles urls
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Print the HTML tags to load the styles
     */
    public function printStyles()
    {
        foreach($this->styles as $css) {
            echo '<link href="' . osc_apply_filter('style_url', $css) . '" rel="stylesheet"="text/css" />' . PHP_EOL;
        }
    }
}
