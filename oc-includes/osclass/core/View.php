<?php if ( ! defined( 'ABS_PATH' ) ) {
    exit( 'ABS_PATH is not loaded. Direct access is not allowed.' );
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
     * Class View
     */
class View
{
    private $aExported;
    private $aCurrent;
    private static $instance;

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

    public function __construct()
    {
        $this->aExported = array();
    }

    //to export variables at the business layer

    /**
     * @param $key
     * @param $value
     */
    public function _exportVariableToView($key, $value)
    {
        $this->aExported[$key] = $value;
    }

    //to get the exported variables for the view

    /**
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
     * @return bool
     */
    public function _next($key)
    {
        if (is_array($this->aExported[$key])) {
            $this->aCurrent[$key] = current( $this->aExported[$key] );
            if ( $this->aCurrent[$key] ) {
                next( $this->aExported[$key] );
                return true;
            }
        }
        return false;
    }

    /**
     * @param $key
     *
     * @return string|array
     */
    public function _current($key)
    {
        if (is_array($this->aExported[$key])) {
            if (!isset($this->aCurrent[$key]) ) {
                $this->aCurrent[$key] = current( $this->aExported[$key] );
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
        if ( is_array($this->aExported[$key]) ) {
            $_key = key( $this->aExported[$key] ) -1;
            if ($_key==-1) {
                $_key = count($this->aExported[$key]) -1;
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
        if ( is_array($this->aExported[$key]) ) {
            $this->_reset($key);
            for ($k = 0; $k<=$position; $k++) {
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
        if ( !array_key_exists($key, $this->aExported) ) {
            return array();
        }
        if ( !is_array( $this->aExported[$key] ) ) {
            return array();
        }
        return reset($this->aExported[$key]);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function _exists($key)
    {
        return ( isset($this->aExported[$key]) ? true : false );
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
        unset( $this->aExported[ $key ], $this->aCurrent[ $key ] );
    }
}


