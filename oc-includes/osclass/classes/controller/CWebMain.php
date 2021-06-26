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
 * Class CWebMain
 */
class CWebMain extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_main');
    }

    //Business Layer...
    public function doModel()
    {
        $i = $this->action;
        if ($i === 'logout') {         // unset only the required parameters in Session
            osc_run_hook('logout');

            Session::newInstance()->_drop('userId');
            Session::newInstance()->_drop('userName');
            Session::newInstance()->_drop('userEmail');
            Session::newInstance()->_drop('userPhone');

            Cookie::newInstance()->pop('oc_userId');
            Cookie::newInstance()->pop('oc_userSecret');
            Cookie::newInstance()->set();

            $this->redirectTo(osc_base_url());
        } else {
            $this->doView('main.php');
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

/* file end: ./CWebMain.php */
