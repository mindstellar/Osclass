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
 * Class Themes
 */
abstract class Themes
{
    private static $instance;
    protected $theme;
    protected $theme_url;
    protected $theme_path;
    protected $theme_exists;

    protected $scripts;
    protected $queue;
    protected $styles;

    protected $resolved;
    protected $unresolved;

    public function __construct()
    {
        $this->scripts = array();
        $this->queue   = array();
        $this->styles  = array();
    }

    /**
     * @param $theme
     */
    public function setCurrentTheme($theme)
    {
        $this->theme = $theme;
        $this->setCurrentThemePath();
        $this->setCurrentThemeUrl();
    }

    abstract protected function setCurrentThemePath();

    /* PUBLIC */

    abstract protected function setCurrentThemeUrl();

    public function getCurrentTheme()
    {
        return $this->theme;
    }

    public function getCurrentThemeUrl()
    {
        return $this->theme_url;
    }

    public function getCurrentThemePath()
    {
        return $this->theme_path;
    }

    /**
     * @return string
     */
    public function getCurrentThemeStyles()
    {
        return $this->theme_url . 'css/';
    }

    /**
     * @return string
     */
    public function getCurrentThemeJs()
    {
        return $this->theme_url . 'js/';
    }
}

/* file end: ./oc-includes/osclass/Themes.php */
