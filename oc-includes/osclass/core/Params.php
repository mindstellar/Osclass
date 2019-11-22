<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

    /**
     * Class Params
     */
class Params
{
    private static  $_purifier;
    private static  $_config;
    private  $_request;
    private  $_server;
    private static $instance;

    public function __construct()
    {
        $this->_request = array_merge($_GET, $_POST);
        $this->_server  = $_SERVER;
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
     * @param      $param
     * @param bool $htmlencode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return mixed
     */
    public static function getParam($param, $htmlencode = false, $xss_check = true, $quotes_encode = true)
    {
        if ($param === '') {
            return '';
        }
        if (!isset(self::newInstance()->_request[$param])) {
            return '';
        }

        $value = self::_purify(self::newInstance()->_request[$param], $xss_check);

        if ($htmlencode) {
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
     * Get HTML Purifier Instance, create new if not already exists
     * @param array $config_options Loads configuration values from an array with the following structure:
     * Namespace.Directive => Value http://htmlpurifier.org/live/configdoc/plain.html
     */
    private static function setHTMLPurifierInstance($config_options = null ){
        if ($config_options !== null && is_array($config_options)){
            $config = HTMLPurifier_Config::create($config_options);
            self::$_config = $config;
        }elseif(!isset(self::$_config)){
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', '');
            $config->set('Cache.SerializerPath', osc_uploads_path());
            self::$_config = $config;
        }
        if (self::$_purifier === null) {
            self::$_purifier = new HTMLPurifier(self::$_config);
        }
    }

    /**
     * @param $value
     * @param $xss_check
     *
     * @return string
     */
    private static function _purify($value, $xss_check)
    {
        if (!$xss_check) {
            return $value;
        }

        self::setHTMLPurifierInstance();

        if (!isset(self::$_purifier)) {
            self::$_purifier = new HTMLPurifier(self::$_config);
        }

        if (is_array($value)) {
            foreach ($value as $k => &$v) {
                $v = self::_purify($v, $xss_check); // recursive
            }
        } else {
            $value = self::$_purifier->purify($value);
        }

        return $value;
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
        if (!isset(self::newInstance()->_request[$param])) {
            return false;
        }

        return true;
    }

    /**
     * @param      $param
     * @param bool $htmlencode
     * @param bool $xss_check
     * @param bool $quotes_encode
     *
     * @return string
     */
    public static function getServerParam($param, $htmlencode = false, $xss_check = true, $quotes_encode = true)
    {
        if ($param === '') {
            return '';
        }
        if (!isset(self::newInstance()->_server[$param])) {
            return '';
        }

        $value = self::_purify(self::newInstance()->_server[$param], $xss_check);

        if ($htmlencode) {
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
        if (!isset(self::newInstance()->_server[$param])) {
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
        $value = self::_purify(self::newInstance()->_server, $xss_check);

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
        self::newInstance()->_request[$key] = $value;
    }

    /**
     * @param $key
     */
    public static function unsetParam($key)
    {
        unset(self::newInstance()->_request[$key]);
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
     * @param bool   $htmlencode
     * @param bool   $xss_check
     * @param bool   $quotes_encode
     *
     * @return array|string
     */
    public static function getParamsAsArray($what = '', $htmlencode = false, $xss_check = true, $quotes_encode = true)
    {
        switch ($what) {
            case('get'):
                $value = $_GET;
                break;
            case('post'):
                $value = $_POST;
                break;
            case('cookie'):
                return $_COOKIE;
                break;
            case('files'):
                return $_FILES;
                break;
            case('request'): // This should not be called, as it depends on server's configuration
                return $_REQUEST;
                break;
            default:
                $value = self::newInstance()->_request;
                break;
        }

        $value = self::_purify($value, $htmlencode); // $xss_check, $quotes_encode );

        if (get_magic_quotes_gpc()) {
            return strip_slashes_extended($value);
        }

        return $value;
    }
}
