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
 * Class WebSecBaseModel
 */
class WebSecBaseModel extends SecBaseModel
{
    /**
     * @return bool
     */
    public function isLogged()
    {
        return osc_is_web_user_logged_in();
    }

    //destroying current session
    public function logout()
    {
        //destroying session
        $locale = Session::newInstance()->_get('userLocale');
        Session::newInstance()->session_destroy();
        Session::newInstance()->_drop('userId');
        Session::newInstance()->_drop('userName');
        Session::newInstance()->_drop('userEmail');
        Session::newInstance()->_drop('userPhone');
        Session::newInstance()->session_start();
        Session::newInstance()->_set('userLocale', $locale);

        Cookie::newInstance()->pop('oc_userId');
        Cookie::newInstance()->pop('oc_userSecret');
        Cookie::newInstance()->set();
    }

    public function showAuthFailPage()
    {
        if (Params::getParam('page') === 'ajax') {
            echo json_encode(array('error' => 1, 'msg' => __('Session timed out')));
            exit;
        }

        $this->redirectTo(osc_user_login_url());
        exit;
    }
}

/* file end: ./oc-includes/osclass/core/WebSecBaseModel.php */
