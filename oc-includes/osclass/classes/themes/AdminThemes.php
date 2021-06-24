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
 * Class AdminThemes
 */
class AdminThemes extends Themes
{
    private static $instance;

    public function __construct()
    {
        parent::__construct();
        $this->setCurrentTheme(osc_admin_theme());
    }

    /**
     * @return \AdminThemes
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function setCurrentThemeUrl()
    {
        if ($this->theme_exists) {
            $this->theme_url = osc_admin_base_url() . 'themes/' . $this->theme . '/';
        } else {
            $this->theme_url = osc_admin_base_url() . 'gui/';
        }
    }

    public function setCurrentThemePath()
    {
        if (file_exists(osc_admin_base_path() . 'themes/' . $this->theme . '/')) {
            $this->theme_exists = true;
            $this->theme_path   = osc_admin_base_path() . 'themes/' . $this->theme . '/';
        } else {
            $this->theme_exists = false;
            $this->theme_path   = osc_admin_base_path() . 'gui/';
        }
    }
}

/* file end: ./oc-includes/osclass/AdminThemes.php */
