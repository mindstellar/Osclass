<?php if (!defined('ABS_PATH')) {
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
 * Class Rewrite
 */
class Rewrite
{
    private static $instance;
    private $rules;
    private $routes;
    private $request_uri;
    private $raw_request_uri;
    private $uri;
    private $location;
    private $section;
    private $title;
    private $http_referer;

    public function __construct()
    {
        $this->request_uri     = '';
        $this->raw_request_uri = '';
        $this->uri             = '';
        $this->location        = '';
        $this->section         = '';
        $this->title           = '';
        $this->http_referer    = '';
        $this->routes          = array();
        $this->rules           = $this->getRules();
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return osc_unserialize(osc_rewrite_rules());
    }

    public function setRules()
    {
        osc_set_preference('rewrite_rules', osc_serialize($this->rules));
    }

    /**
     * @return array
     */
    public function listRules()
    {
        return $this->rules;
    }

    /**
     * @param $rules
     */
    public function addRules($rules)
    {
        if (is_array($rules)) {
            foreach ($rules as $rule) {
                if (is_array($rule) && count($rule) > 1) {
                    $this->addRule($rule[0], $rule[1]);
                }
            }
        }
    }

    /**
     * @param $regexp
     * @param $uri
     */
    public function addRule($regexp, $uri)
    {
        $regexp = trim($regexp);
        $uri    = trim($uri);
        if ($regexp != '' && $uri != '' && !in_array($regexp, $this->rules)) {
            $this->rules[$regexp] = $uri;
        }
    }

    /**
     * @param        $id
     * @param        $regexp
     * @param        $url
     * @param        $file
     * @param bool   $user_menu
     * @param string $location
     * @param string $section
     * @param string $title
     */
    public function addRoute(
        $id,
        $regexp,
        $url,
        $file,
        $user_menu = false,
        $location = 'custom',
        $section = 'custom',
        $title = 'Custom'
    ) {
        $regexp = trim($regexp);
        $file   = trim($file);
        if ($regexp != '' && $file != '') {
            $this->routes[$id] = array(
                'regexp'    => $regexp,
                'url'       => $url,
                'file'      => $file,
                'user_menu' => $user_menu,
                'location'  => $location,
                'section'   => $section,
                'title'     => $title
            );
        }
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    public function init()
    {
        if (Params::existServerParam('REQUEST_URI')) {
            $server_params            = Params::getServerParam('REQUEST_URI', false, false);
            $urldecoded_server_params = urldecode($server_params);
            if (preg_match(
                '|[\?&]{1}http_referer=(.*)$|',
                $urldecoded_server_params,
                $ref_match
            )
            ) {
                $this->http_referer     = $ref_match[1];
                $_SERVER['REQUEST_URI'] = preg_replace(
                    '|[\?&]{1}http_referer=(.*)$|',
                    '',
                    $urldecoded_server_params
                );
            }
            $request_uri           =
                preg_replace('@^' . REL_WEB_URL . '@', '', $server_params);
            $this->raw_request_uri = $request_uri;
            $route_used            = false;
            foreach ($this->routes as $id => $route) {
                // UNCOMMENT TO DEBUG
                //echo 'Request URI: '.$request_uri." # Match : ".$route['regexp']."
                // # URI to go : ".$route['url']." <br />";
                if (preg_match('#^' . $route['regexp'] . '#', $request_uri, $m)) {
                    if (!preg_match_all('#\{([^\}]+)\}#', $route['url'], $args)) {
                        $args[1] = array();
                    }
                    $l = count($m);
                    for ($p = 1; $p < $l; $p++) {
                        if (isset($args[1][$p - 1])) {
                            Params::setParam($args[1][$p - 1], $m[$p]);
                        } else {
                            Params::setParam('route_param_' . $p, $m[$p]);
                        }
                    }

                    Params::setParam('page', 'custom');
                    Params::setParam('route', $id);
                    $route_used     = true;
                    $this->location = $route['location'];
                    $this->section  = $route['section'];
                    $this->title    = $route['title'];
                    break;
                }
            }
            if (!$route_used) {
                if (osc_rewrite_enabled()) {
                    $tmp_ar      = explode('?', $request_uri);
                    $request_uri = $tmp_ar[0];

                    // if try to access directly to a php file
                    if (preg_match('#^(.+?)\.php(.*)$#', $request_uri)) {
                        $file = explode('?', $request_uri);
                        if (!file_exists(ABS_PATH . $file[0])) {
                            self::newInstance()->set_location('error');
                            header('HTTP/1.1 404 Not Found');
                            osc_current_web_theme_path('404.php');
                            exit;
                        }
                    }

                    foreach ($this->rules as $match => $uri) {
                        // UNCOMMENT TO DEBUG
                        // echo 'Request URI: '.$request_uri." # Match : ".$match." # URI to go : ".$uri." <br />";
                        if (preg_match('#^' . $match . '#', $request_uri, $m)) {
                            $request_uri = preg_replace('#' . $match . '#', $uri, $request_uri);
                            break;
                        }
                    }
                    $this->extractParams($request_uri);
                }
                $this->request_uri = $request_uri;

                if (Params::getParam('page') != '') {
                    $this->location = Params::getParam('page');
                }
                if (Params::getParam('action') != '') {
                    $this->section = Params::getParam('action');
                }
            }
        }
    }

    /**
     * @return \Rewrite
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param string $uri
     */
    private function extractParams($uri = '')
    {
        $uri_array = explode('?', $uri);
        $length_i  = count($uri_array);
        for ($var_i = 1; $var_i < $length_i; $var_i++) {
            parse_str($uri_array[$var_i], $parsedVars);
            foreach ($parsedVars as $k => $v) {
                Params::setParam($k, urldecode($v));
            }
        }
    }

    /**
     * @param $regexp
     */
    public function removeRule($regexp)
    {
        unset($this->rules[$regexp]);
    }

    public function clearRules()
    {
        unset($this->rules);
        $this->rules = array();
    }

    /**
     * @return string
     */
    public function get_request_uri()
    {
        return $this->request_uri;
    }

    /**
     * @return string
     */
    public function get_raw_request_uri()
    {
        return $this->raw_request_uri;
    }

    /**
     * @return string
     */
    public function get_location()
    {
        return $this->location;
    }

    /**
     * @param $location
     */
    public function set_location($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function get_section()
    {
        return $this->section;
    }

    /**
     * @return string
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function get_http_referer()
    {
        return $this->http_referer;
    }

    /**
     * @param string $uri
     *
     * @return bool|string
     */
    private function extractURL($uri = '')
    {
        $uri_array = explode('?', str_replace('index.php', '', $uri));
        if ($uri_array[0][0] === '/') {
            return substr($uri_array[0], 1);
        }

        return $uri_array[0];
    }
}
