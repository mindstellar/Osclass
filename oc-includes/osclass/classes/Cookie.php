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

/**
 * Class Cookie
 */
class Cookie
{
    private static $instance;
    public $name;
    public $val;
    public $expires;

    public function __construct()
    {
        $this->val     = array();
        $web_path      = WEB_PATH;
        $this->name    = md5($web_path);
        $this->expires = time() + 3600; // 1 hour by default
        if (isset($_COOKIE[$this->name])) {
            $tmp  = explode('&', $_COOKIE[$this->name]);
            $vars = $tmp[0];
            $vals = isset($tmp[1]) ? explode('._.', $tmp[1]) : array();
            $vars = explode('._.', $vars);

            foreach ($vars as $key => $var) {
                if ($var != '' && isset($vals[$key])) {
                    $this->val[(string)$var] = $vals[$key];
                    $_COOKIE[(string)$var]   = $vals[$key];
                } else {
                    $this->val[(string)$var] = '';
                    $_COOKIE[(string)$var]   = '';
                }
            }
        }
    }

    /**
     * @return \Cookie
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param $var
     * @param $value
     */
    public function push($var, $value)
    {
        $this->val[(string)$var] = $value;
        $_COOKIE[(string)$var]   = $value;
    }

    /**
     * @param $var
     */
    public function pop($var)
    {
        unset($this->val[$var], $_COOKIE[$var]);
    }

    public function clear()
    {
        $this->val = array();
    }

    public function set()
    {
        $cookie_val = '';
        if (is_array($this->val) && count($this->val) > 0) {
            $cookie_val = '';
            $vals       = array();
            $vars       = $vals;

            foreach ($this->val as $key => $curr) {
                if ($curr !== '') {
                    $vars[] = $key;
                    $vals[] = $curr;
                }
            }
            if (count($vars) > 0 && count($vals) > 0) {
                $cookie_val = implode('._.', $vars) . '&' . implode('._.', $vals);
            }
        }
        setcookie($this->name, $cookie_val, $this->expires, REL_WEB_URL);
    }

    /**
     * @return int
     */
    public function num_vals()
    {
        return count($this->val);
    }

    /**
     * @param $str
     *
     * @return mixed|string
     */
    public function get_value($str)
    {
        if (isset($this->val[$str])) {
            return $this->val[$str];
        }

        return '';
    }

    //$tm: time in seconds

    /**
     * @param $tm
     */
    public function set_expires($tm)
    {
        $this->expires = time() + $tm;
    }
}
