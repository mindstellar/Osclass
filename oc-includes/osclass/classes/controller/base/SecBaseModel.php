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
 * Description of BaseModel
 *
 * @author danielo
 */
class SecBaseModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();

        //Checking granting...
        $this->init();
    }

    protected function init()
    {
        if (!$this->isLogged()) {
            //If we are not logged or we do not have permissions -> go to the login page
            $this->logout();
            $this->showAuthFailPage();
        }
    }


    public function logout()
    {
        //destroying session
        Session::newInstance()->session_destroy();
    }

    //destroying current session

    /**
     * @param $grant
     */
    public function setGranting($grant)
    {
        $this->grant = $grant;
    }

    public function doModel()
    {
    }

    /**
     * @param $file
     */
    public function doView($file)
    {
    }
}

/* file end: ./oc-includes/osclass/core/SecBaseModel.php */
