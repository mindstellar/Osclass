<?php
/**
 * Class Params
 */
class Params
{
    private static $purifier;
    private static $config;
    private static $instance;
    private $request;
    private $server;

    public function __construct()
    {
        $this->request = array_merge($_GET, $_POST);
        $this->server  = $_SERVER;
    }

    /**
     * @param      $param
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return mixed
     */
    public static function getParam(
        $param,
        $html_encode = false,
        $xss_check = true,
        $quotes_encode = true
    ) {
        if ($param === '') {
            return '';
        }
        if (!isset(self::newInstance()->request[$param])) {
            return '';
        }

        $value = self::purify(self::newInstance()->request[$param], $xss_check);

        if ($html_encode) {
            if ($quotes_encode) {
                return htmlspecialchars(stripslashes($value), ENT_QUOTES);
            }

            return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
        }

        if (get_magic_quotes_gpc()) {
            $value = strip_slashes_extended($value);
        }

        return $value;
    }

    /**
     * @return \Params Singleton
     */
    public static function newInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param $value
     * @param $xss_check
     *
     * @return string
     */
    private static function purify($value, $xss_check)
    {
        if (!$xss_check) {
            return $value;
        }

        self::setHTMLPurifierInstance();

        if (!isset(self::$purifier)) {
            self::$purifier = new HTMLPurifier(self::$config);
        }

        if (is_array($value)) {
            foreach ($value as $k => &$v) {
                $v = self::purify($v, $xss_check); // recursive
            }
        } else {
            $value = self::$purifier->purify($value);
        }

        return $value;
    }

    /**
     * Get HTML Purifier Instance, create new if not already exists
     *
     * @param array $config_options Loads configuration values from an array with the following
     *                              structure: Namespace.Directive => Value
     *                              http://htmlpurifier.org/live/configdoc/plain.html
     */
    private static function setHTMLPurifierInstance($config_options = null)
    {
        if ($config_options !== null && is_array($config_options)) {
            $config       = HTMLPurifier_Config::create($config_options);
            self::$config = $config;
        } elseif (!isset(self::$config)) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', '');
            $config->set('Cache.SerializerPath', osc_uploads_path());
            self::$config = $config;
        }
        if (self::$purifier === null) {
            self::$purifier = new HTMLPurifier(self::$config);
        }
    }

    /**
     * @param $param
     *
     * @return bool
     */
    public static function existParam($param)
    {
        if ($param === '') {
            return false;
        }
        if (!isset(self::newInstance()->request[$param])) {
            return false;
        }

        return true;
    }

    /**
     * @param      $param
     * @param bool $html_encode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return string
     */
    public static function getServerParam(
        $param,
        $html_encode = false,
        $xss_check = true,
        $quotes_encode = true
    ) {
        if ($param === '') {
            return '';
        }
        if (!isset(self::newInstance()->server[$param])) {
            return '';
        }

        $value = self::purify(self::newInstance()->server[$param], $xss_check);

        if ($html_encode) {
            if ($quotes_encode) {
                return htmlspecialchars(stripslashes($value), ENT_QUOTES);
            }

            return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
        }

        if (get_magic_quotes_gpc()) {
            $value = strip_slashes_extended($value);
        }

        return $value;
    }

    /**
     * @param $param
     *
     * @return bool
     */
    public static function existServerParam($param)
    {
        if ($param === '') {
            return false;
        }
        if (!isset(self::newInstance()->server[$param])) {
            return false;
        }

        return true;
    }

    /**
     * @param bool $xss_check
     *
     * @return string
     */
    public static function getServerParamsAsArray($xss_check = true)
    {
        $value = self::purify(self::newInstance()->server, $xss_check);

        if (get_magic_quotes_gpc()) {
            return strip_slashes_extended($value);
        }

        return $value;
    }

    /**
     * @param $param
     *
     * @return array
     */
    public static function getFiles($param)
    {
        if (isset($_FILES[$param])) {
            return $_FILES[$param];
        }

        return array();
    }

    /**
     * @param $key
     * @param $value
     */
    public static function setParam($key, $value)
    {
        self::newInstance()->request[$key] = $value;
    }

    /**
     * @param $key
     */
    public static function unsetParam($key)
    {
        unset(self::newInstance()->request[$key]);
    }

    /**
     * return void
     */
    public static function _view()
    {
        print_r(self::getParamsAsArray());
    }

    /**
     * @param string $what
     * @param bool   $html_encode
     * @param bool   $xss_check
     * @param bool   $quotes_encode
     *
     * @return array|string
     */
    public static function getParamsAsArray($what = '', $xss_check = true)
    {
        switch ($what) {
            case ('get'):
                $value = $_GET;
                break;
            case ('post'):
                $value = $_POST;
                break;
            case ('cookie'):
                return $_COOKIE;
                break;
            case ('files'):
                return $_FILES;
                break;
            case ('request'): // This should not be called, as it depends on server's configuration
                return $_REQUEST;
                break;
            default:
                $value = self::newInstance()->request;
                break;
        }

        $value = self::purify($value, $xss_check);

        if (get_magic_quotes_gpc()) {
            return strip_slashes_extended($value);
        }

        return $value;
    }
}
