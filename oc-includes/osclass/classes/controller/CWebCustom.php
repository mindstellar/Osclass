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
 * Class CWebCustom
 */
class CWebCustom extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        //specific things for this class
        osc_run_hook('init_custom');
    }

    //Business Layer...
    public function doModel()
    {
        $user_menu = false;
        if (Params::existParam('route')) {
            $routes = Rewrite::newInstance()->getRoutes();
            $rid    = Params::getParam('route');
            $file   = '../';
            if (isset($routes[$rid]['file'])) {
                $file      = $routes[$rid]['file'];
                $user_menu = $routes[$rid]['user_menu'];
            }
        } else {
            // DEPRECATED: Disclosed path in URL is deprecated, use routes instead
            // This will be REMOVED in 3.4
            $file = Params::getParam('file');
        }

        // valid file?
        if (strpos($file, '../') !== false || strpos($file, '..\\') !== false
            || stripos($file, '/admin/') !== false
        ) { //If the file is inside an "admin" folder, it should NOT be opened in frontend
            $this->do404();

            return;
        }

        // check if the file exists
        if (!file_exists(osc_plugins_path() . $file)
            && !file_exists(osc_themes_path() . osc_theme() . '/plugins/' . $file)
        ) {
            $this->do404();

            return;
        }

        osc_run_hook('custom_controller');

        $this->_exportVariableToView('file', $file);
        if ($user_menu) {
            if (osc_is_web_user_logged_in()) {
                Params::setParam('in_user_menu', true);
                $this->doView('user-custom.php');
            } else {
                $this->redirectTo(osc_user_login_url());
            }
        } else {
            $this->doView('custom.php');
        }
    }

    //hopefully generic...

    /**
     * @param $file
     *
     * @return void
     */
    public function doView($file)
    {
        osc_run_hook('before_html');
        osc_current_web_theme_path($file);
        Session::newInstance()->_clearVariables();
        osc_run_hook('after_html');
    }
}

/* file end: ./CWebCustom.php */
