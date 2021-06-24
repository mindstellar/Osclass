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
 * Class View
 */
class View
{
    private static $instance;
    private $aExported;
    private $aCurrent;

    public function __construct()
    {
        $this->aExported = array();
    }

    /**
     * @return \View
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * to export variables at the business layer
     *
     * @param $key
     * @param $value
     */
    public function _exportVariableToView($key, $value)
    {
        $this->aExported[$key] = $value;
    }

    /**
     * to get the exported variables for the view
     *
     * @param $key
     *
     * @return mixed|string|array
     */
    public function _get($key)
    {
        if ($this->_exists($key)) {
            return $this->aExported[$key];
        }

        return '';
    }

    //only for debug

    /**
     * @param $key
     *
     * @return bool
     */
    public function _exists($key)
    {
        return (isset($this->aExported[$key]) ? true : false);
    }

    /**
     * @param null $key
     */
    public function _view($key = null)
    {
        if ($key) {
            print_r($this->aExported[$key]);
        } else {
            print_r($this->aExported);
        }
    }

    /**
     * @param $key
     *
     * @return string|array
     */
    public function _current($key)
    {
        if (is_array($this->aExported[$key])) {
            if (!isset($this->aCurrent[$key])) {
                $this->aCurrent[$key] = current($this->aExported[$key]);
            }

            return $this->aCurrent[$key];
        }

        return '';
    }

    /**
     * @param $key
     *
     * @return bool|int|null|string
     */
    public function _key($key)
    {
        if (is_array($this->aExported[$key])) {
            $_key = key($this->aExported[$key]) - 1;
            if ($_key == -1) {
                $_key = count($this->aExported[$key]) - 1;
            }

            return $_key;
        }

        return false;
    }

    /**
     * @param $key
     * @param $position
     *
     * @return bool
     */
    public function _seek($key, $position)
    {
        if (is_array($this->aExported[$key])) {
            $this->_reset($key);
            for ($k = 0; $k <= $position; $k++) {
                $res = $this->_next($key);
                if (!$res) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $key
     *
     * @return array|mixed
     */
    public function _reset($key)
    {
        if (!array_key_exists($key, $this->aExported)) {
            return array();
        }
        if (!is_array($this->aExported[$key])) {
            return array();
        }

        return reset($this->aExported[$key]);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function _next($key)
    {
        if (is_array($this->aExported[$key])) {
            $this->aCurrent[$key] = current($this->aExported[$key]);
            if ($this->aCurrent[$key]) {
                next($this->aExported[$key]);

                return true;
            }
        }

        return false;
    }

    /**
     * @param $key
     *
     * @return int
     */
    public function _count($key)
    {
        if (isset($this->aExported[$key]) && is_array($this->aExported[$key])) {
            return count($this->aExported[$key]);
        }

        return -1; // @TOFIX @FIXME ?? why ? why not 0 ?
    }

    /**
     * @param $key
     */
    public function _erase($key)
    {
        unset($this->aExported[$key], $this->aCurrent[$key]);
    }
}
