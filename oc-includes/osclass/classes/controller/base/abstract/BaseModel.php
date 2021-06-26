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

use mindstellar\utility\Utils;

/**
 * Class BaseModel
 */
abstract class BaseModel
{
    protected $page;
    protected $action;
    protected $ajax;
    protected $time;

    public function __construct()
    {
        // this is necessary because if HTTP_HOST doesn't have the PORT the parse_url is null
        $current_host = parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_HOST);
        if ($current_host === null) {
            $current_host = Params::getServerParam('HTTP_HOST');
        }

        if (parse_url(osc_base_url(), PHP_URL_HOST) !== $current_host) {
            // first check if it's http or https
            $url = 'http://';
            if (Utils::isSsl()) {
                $url = 'https://';
            }
            // append the domain
            $url .= parse_url(osc_base_url(), PHP_URL_HOST);
            // append the port number if it's necessary
            $http_port = parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_PORT);
            if ($http_port !== 80) {
                $url .= ':' . parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_PORT);
            }
            // append the request
            $url .= Params::getServerParam('REQUEST_URI', false, false);
            $this->redirectTo($url);
        }

        $this->subdomain_params($current_host);
        $this->setParams();
        $this->ajax = false;
        $this->time = microtime(true);
        WebThemes::newInstance();
        osc_run_hook('init');
    }

    /**
     * @param      $url
     * @param null $code
     */
    public function redirectTo($url, $code = null)
    {
        Utils::redirectTo($url, $code);
    }

    /**
     * @param $host
     */
    private function subdomain_params($host)
    {
        $subdomain_type = osc_subdomain_type();
        $subhost        = osc_subdomain_host();
        // strpos is used to check if the domain is different, useful when accessing the website by diferent domains
        if ($subdomain_type != '' && $subhost != '' && strpos($host, $subhost) !== false
            && preg_match('|^(www\.)?(.+)\.' . $subhost . '$|i', $host, $match)
        ) {
            $subdomain = $match[2];
            if ($subdomain != '' && $subdomain !== 'www') {
                if ($subdomain_type === 'category') {
                    $category = Category::newInstance()->findBySlug($subdomain);
                    if (isset($category['pk_i_id'])) {
                        $this->_exportVariableToView('subdomain_name', $category['s_name']);
                        $this->_exportVariableToView('subdomain_slug', $category['s_slug']);
                        Params::setParam('sCategory', $category['pk_i_id']);
                        if (Params::getParam('page') == '') {
                            Params::setParam('page', 'search');
                        }
                    } else {
                        $this->do400();
                    }
                } elseif ($subdomain_type === 'country') {
                    $country = Country::newInstance()->findBySlug($subdomain);
                    if (isset($country['pk_c_code'])) {
                        $this->_exportVariableToView('subdomain_name', $country['s_name']);
                        $this->_exportVariableToView('subdomain_slug', $country['s_slug']);
                        Params::setParam('sCountry', $country['pk_c_code']);
                    } else {
                        $this->do400();
                    }
                } elseif ($subdomain_type === 'region') {
                    $region = Region::newInstance()->findBySlug($subdomain);
                    if (isset($region['pk_i_id'])) {
                        $this->_exportVariableToView('subdomain_name', $region['s_name']);
                        $this->_exportVariableToView('subdomain_slug', $region['s_slug']);
                        Params::setParam('sRegion', $region['pk_i_id']);
                    } else {
                        $this->do400();
                    }
                } elseif ($subdomain_type === 'city') {
                    $city = City::newInstance()->findBySlug($subdomain);
                    if (isset($city['pk_i_id'])) {
                        $this->_exportVariableToView('subdomain_name', $city['s_name']);
                        $this->_exportVariableToView('subdomain_slug', $city['s_slug']);
                        Params::setParam('sCity', $city['pk_i_id']);
                    } else {
                        $this->do400();
                    }
                } elseif ($subdomain_type === 'user') {
                    $user = User::newInstance()->findByUsername($subdomain);
                    if (isset($user['pk_i_id'])) {
                        $this->_exportVariableToView('subdomain_name', $user['s_name']);
                        $this->_exportVariableToView('subdomain_slug', $user['s_username']);
                        Params::setParam('sUser', $user['pk_i_id']);
                    } else {
                        $this->do400();
                    }
                } else {
                    $this->do400();
                }
            }
        }
    }

    //to export variables at the business layer

    public function do400()
    {
        Rewrite::newInstance()->set_location('error');
        header('HTTP/1.1 400 Bad Request');
        osc_current_web_theme_path('404.php');
        exit;
    }

    //only for debug (deprecated, all inside View.php)

    /**
     *
     * @since 3.9.0 -develop
     */
    protected function setParams()
    {
        $this->page   = Params::getParam('page');
        $this->action = Params::getParam('action');
    }

    // Functions that will have to be rewritten in the class that extends from this

    public function __destruct()
    {
        if (!$this->ajax && OSC_DEBUG) {
            echo '<!-- ' . $this->getTime() . ' seg. -->';
        }
    }

    /**
     * @return float
     */
    public function getTime()
    {
        $timeEnd = microtime(true);

        return $timeEnd - $this->time;
    }

    /**
     * @param $key
     * @param $value
     */
    public function _exportVariableToView($key, $value)
    {
        View::newInstance()->_exportVariableToView($key, $value);
    }

    /**
     * @param null $key
     */
    public function _view($key = null)
    {
        View::newInstance()->_view($key);
    }

    public function do404()
    {
        Rewrite::newInstance()->set_location('error');
        header('HTTP/1.1 404 Not Found');
        osc_current_web_theme_path('404.php');
        exit;
    }

    public function do410()
    {
        Rewrite::newInstance()->set_location('error');
        header('HTTP/1.1 410 Gone');
        osc_current_web_theme_path('404.php');
        exit;
    }
    abstract protected function doModel();

    /**
     * @param $file
     *
     * @return mixed
     */
    abstract protected function doView($file);
}

/* file end: ./oc-includes/osclass/core/BaseModel.php */
