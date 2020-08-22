<?php
/*
 *  Copyright 2020 Osclass
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
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

if (PHP_SAPI === 'cli') {
    define('CLI', true);
}

require_once __DIR__ . '/oc-load.php';

if (CLI) {
    $cli_params = getopt('p:t:');
    if ($cli_params) {
        Params::setParam('page', $cli_params['p']);
        Params::setParam('cron-type', $cli_params['t']);
    }
    if (Params::getParam('page') === 'upgrade') {
        echo \mindstellar\osclass\classes\upgrade\Osclass::upgradeDB();

        exit(1);
    }

    if (Params::getParam('page') !== 'cron'
        && !in_array(Params::getParam('cron-type'), array('hourly', 'daily', 'weekly'))
    ) {
        exit(1);
    }
}

if (file_exists(ABS_PATH . '.maintenance')) {
    if (osc_is_admin_user_logged_in()) {
        define('__OSC_MAINTENANCE__', true);
    } else {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        header('Status: 503 Service Temporarily Unavailable');
        header('Retry-After: 900');

        if (file_exists(WebThemes::newInstance()->getCurrentThemePath() . 'maintenance.php')) {
            osc_current_web_theme_path('maintenance.php');
            die();
        }

        require_once LIB_PATH . 'osclass/helpers/hErrors.php';

        $title   = sprintf(__('Maintenance &raquo; %s'), osc_page_title());
        $message = sprintf(
            __('We are sorry for any inconvenience. %s is undergoing maintenance.') . '.',
            osc_page_title()
        );
        osc_die($title, $message);
    }
}

if (!osc_users_enabled() && osc_is_web_user_logged_in()) {
    Session::newInstance()->_drop('userId');
    Session::newInstance()->_drop('userName');
    Session::newInstance()->_drop('userEmail');
    Session::newInstance()->_drop('userPhone');

    Cookie::newInstance()->pop('oc_userId');
    Cookie::newInstance()->pop('oc_userSecret');
    Cookie::newInstance()->set();
}

if (osc_is_web_user_logged_in()) {
    User::newInstance()->lastAccess(
        osc_logged_user_id(),
        date('Y-m-d H:i:s'),
        Params::getServerParam('REMOTE_ADDR'),
        3600
    );
}

switch (Params::getParam('page')) {
    case ('cron'):      // cron system
        define('__FROM_CRON__', true);
        require_once(LIB_PATH . 'osclass/cron.php');
        break;
    case ('user'):      // user pages (with security)
        $osclass_action = Params::getParam('action');
        if ($osclass_action === 'change_email_confirm'
            || $osclass_action === 'activate_alert'
            || $osclass_action === 'contact_post'
            || $osclass_action === 'pub_profile'
            || ($osclass_action === 'unsub_alert' && !osc_is_web_user_logged_in())

        ) {
            $do = new CWebUserNonSecure();
            $do->doModel();
        } else {
            $do = new CWebUser();
            $do->doModel();
        }
        break;
    case ('item'):      // item pages
        $do = new CWebItem();
        $do->doModel();
        break;
    case ('search'):    // search pages
        $do = new CWebSearch();
        $do->doModel();
        break;
    case ('page'):      // static pages
        $do = new CWebPage();
        $do->doModel();
        break;
    case ('register'):  // register page
        $do = new CWebRegister();
        $do->doModel();
        break;
    case ('ajax'):      // ajax
        $do = new CWebAjax();
        $do->doModel();
        break;
    case ('login'):     // login page
        $do = new CWebLogin();
        $do->doModel();
        break;
    case ('language'):  // set language
        $do = new CWebLanguage();
        $do->doModel();
        break;
    case ('contact'):   //contact
        $do = new CWebContact();
        $do->doModel();
        break;
    case ('custom'):   //custom
        $do = new CWebCustom();
        $do->doModel();
        break;
    default:            // home and static pages that are mandatory...
        $do = new CWebMain();
        $do->doModel();
        break;
}

if (!defined('__FROM_CRON__') && osc_auto_cron()) {
    \mindstellar\osclass\classes\utility\Utils::doRequest(osc_base_url(), array('page' => 'cron'));
}

/* file end: ./index.php */
