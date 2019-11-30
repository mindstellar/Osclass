<?php

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
 * Class Session
 */
class Session
{
    //attributes
    private static $instance;
    private $session;

    /**
     * @return \Session
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function session_start()
    {
        $currentCookieParams = session_get_cookie_params();
        if (defined('COOKIE_DOMAIN')) {
            $currentCookieParams['domain'] = COOKIE_DOMAIN;
        }
        if (isset($_SERVER['HTTPS'])) {
            $currentCookieParams["secure"] = true;
        }
        session_set_cookie_params(
            $currentCookieParams['lifetime'],
            $currentCookieParams['path'],
            $currentCookieParams['domain'],
            $currentCookieParams['secure'],
            true
        );

        if (!isset($_SESSION)) {
            session_name('osclass');
            if (!$this->_session_start()) {
                session_id(uniqid('', true));
                session_start();
                session_regenerate_id();
            }
        }

        $this->session = $_SESSION;
        if ($this->_get('messages') == '') {
            $this->_set('messages', array());
        }
        if ($this->_get('keepForm') == '') {
            $this->_set('keepForm', array());
        }
        if ($this->_get('form') == '') {
            $this->_set('form', array());
        }
    }

    /**
     * @return bool
     */
    public function _session_start()
    {
        $sn = session_name();
        if (isset($_COOKIE[$sn])) {
            $sessid = $_COOKIE[$sn];
        } elseif (isset($_GET[$sn])) {
            $sessid = $_GET[$sn];
        } else {
            return session_start();
        }

        if (!preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $sessid)) {
            return false;
        }

        return session_start();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function _get($key)
    {
        if (!isset($this->session[$key])) {
            return '';
        }

        return $this->session[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function _set($key, $value)
    {
        $_SESSION[$key]      = $value;
        $this->session[$key] = $value;
    }

    public function session_destroy()
    {
        session_destroy();
    }

    /**
     * @param $key
     */
    public function _drop($key)
    {
        unset($_SESSION[$key], $this->session[$key]);
    }

    /**
     * @param $value
     */
    public function _setReferer($value)
    {
        $_SESSION['osc_http_referer']            = $value;
        $this->session['osc_http_referer']       = $value;
        $_SESSION['osc_http_referer_state']      = 0;
        $this->session['osc_http_referer_state'] = 0;
    }

    /**
     * @return string
     */
    public function _getReferer()
    {
        if (isset($this->session['osc_http_referer'])) {
            return $this->session['osc_http_referer'];
        }

        return '';
    }

    public function _view()
    {
        print_r($this->session);
    }

    /**
     * @param $key
     * @param $value
     * @param $type
     */
    public function _setMessage($key, $value, $type)
    {
        $messages         = $this->_get('messages');
        $messages[$key][] = array('msg' => str_replace(PHP_EOL, '<br />', $value), 'type' => $type);
        $this->_set('messages', $messages);
    }

    /**
     * @param $key
     *
     * @return string|array
     */
    public function _getMessage($key)
    {
        $messages = $this->_get('messages');
        if (isset($messages[$key])) {
            return $messages[$key];
        }

        return '';
    }

    /**
     * @param $key
     */
    public function _dropMessage($key)
    {
        $messages = $this->_get('messages');
        unset($messages[$key]);
        $this->_set('messages', $messages);
    }

    /**
     * @param $key
     */
    public function _keepForm($key)
    {
        $aKeep       = $this->_get('keepForm');
        $aKeep[$key] = 1;
        $this->_set('keepForm', $aKeep);
    }

    /**
     * @param string $key
     */
    public function _dropKeepForm($key = '')
    {
        $aKeep = $this->_get('keepForm');
        if ($key != '') {
            unset($aKeep[$key]);
            $this->_set('keepForm', $aKeep);
        } else {
            $this->_set('keepForm', array());
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function _setForm($key, $value)
    {
        $form       = $this->_get('form');
        $form[$key] = $value;
        $this->_set('form', $form);
    }

    /**
     * @param string $key
     *
     * @return string|array
     */
    public function _getForm($key = '')
    {
        $form = $this->_get('form');
        if ($key != '') {
            if (isset($form[$key])) {
                return $form[$key];
            }

            return '';
        }

        return $form;
    }

    /**
     * @return string|array
     */
    public function _getKeepForm()
    {
        return $this->_get('keepForm');
    }

    public function _viewMessage()
    {
        print_r($this->session['messages']);
    }

    public function _viewForm()
    {
        print_r($_SESSION['form']);
    }

    public function _viewKeep()
    {
        print_r($_SESSION['keepForm']);
    }

    public function _clearVariables()
    {
        $form  = $this->_get('form');
        $aKeep = $this->_get('keepForm');
        if (is_array($form)) {
            foreach ($form as $key => $value) {
                if (!isset($aKeep[$key])) {
                    unset($_SESSION['form'][$key], $this->session['form'][$key]);
                }
            }
        }

        if (isset($this->session['osc_http_referer_state'])) {
            $this->session['osc_http_referer_state']++;
            $_SESSION['osc_http_referer_state']++;
            if ((int)$this->session['osc_http_referer_state'] >= 2) {
                $this->_dropReferer();
            }
        }
    }

    public function _dropReferer()
    {
        unset($_SESSION['osc_http_referer'], $this->session['osc_http_referer'], $_SESSION['osc_http_referer_state'], $this->session['osc_http_referer_state']);
    }
}
