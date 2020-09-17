<?php

/*
 *  Copyright 2020 Mindstellar Osclass
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
