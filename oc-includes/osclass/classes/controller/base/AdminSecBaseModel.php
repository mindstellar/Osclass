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
 * Class AdminSecBaseModel
 */
class AdminSecBaseModel extends SecBaseModel
{
    public function __construct()
    {
        parent::__construct();

        // check if is moderator and can enter to this page
        if ($this->isModerator()
            && !in_array($this->page, osc_apply_filter('moderator_access', array(
                'items',
                'comments',
                'media',
                'login',
                'admins',
                'ajax',
                'stats',
                ''
            )), false)
        ) {
            osc_add_flash_error_message(_m("You don't have enough permissions"), 'admin');
            $this->redirectTo(osc_admin_base_url());
        }
        osc_run_hook('init_admin');

        $config_version = OSCLASS_VERSION;
        $installed_version = osc_get_preference('version');
        if (strlen($installed_version) === 3) {
            // It's a legacy osclass version i.e. below 390 make it compatible with new methods
            $installed_version = implode('.', str_split($installed_version));
        }
        if (!defined('IS_AJAX')
            && !$this instanceof CAdminUpgrade
            && !$this instanceof CAdminTools
            && Utils::versionCompare($config_version, $installed_version, 'gt')
        ) {
            $this->redirectTo(osc_admin_base_url(true) . '?page=upgrade');
        }

        // show donation successful
        if (Params::getParam('donation') === 'successful') {
            osc_add_flash_ok_message(_m('Thank you very much for your donation'), 'admin');
        }

        // enqueue scripts
        osc_enqueue_script('jquery');
        osc_enqueue_script('jquery-ui');
        osc_enqueue_script('admin-osc');
        osc_enqueue_script('admin-ui-osc');
    }

    /**
     * @return bool
     */
    public function isModerator()
    {
        return osc_is_moderator();
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        return osc_is_admin_user_logged_in();
    }

    public function logout()
    {
        //destroying session
        $locale = Session::newInstance()->_get('oc_adminLocale');
        Session::newInstance()->session_destroy();
        Session::newInstance()->_drop('adminId');
        Session::newInstance()->_drop('adminUserName');
        Session::newInstance()->_drop('adminName');
        Session::newInstance()->_drop('adminEmail');
        Session::newInstance()->_drop('adminLocale');
        Session::newInstance()->session_start();
        Session::newInstance()->_set('oc_adminLocale', $locale);

        Cookie::newInstance()->pop('oc_adminId');
        Cookie::newInstance()->pop('oc_adminSecret');
        Cookie::newInstance()->pop('oc_adminLocale');
        Cookie::newInstance()->set();
    }

    /**
     * @param $file
     */
    public function doView($file)
    {
        osc_run_hook('before_admin_html');
        osc_current_admin_theme_path($file);
        Session::newInstance()->_clearVariables();
        osc_run_hook('after_admin_html');
    }

    public function showAuthFailPage()
    {
        if (Params::getParam('page') === 'ajax') {
            echo json_encode(array('error' => 1, 'msg' => __('Session timed out')));
            exit;
        }

        Session::newInstance()->_setReferer(
            osc_base_url()
            . Params::getRequestURI(false, false, false)
        );
        header('Location: ' . osc_admin_base_url(true) . '?page=login');
        exit;
    }
}

/* file end: ./oc-includes/osclass/core/AdminSecBaseModel.php */
