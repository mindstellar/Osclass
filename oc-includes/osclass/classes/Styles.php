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
 * Styles enqueue class.
 *
 * @since 3.1.1
 */
class Styles
{

    private static $instance;
    public $styles = array();

    public function __construct()
    {
        $styles = array();
    }

    /**
     * @return \Styles
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Add style to be loaded
     *
     * @param $id
     * @param $url
     */
    public function addStyle($id, $url)
    {
        $this->styles[$id] = $url;
    }

    /**
     * Remove style to not be loaded
     *
     * @param $id
     */
    public function removeStyle($id)
    {
        unset($this->styles[$id]);
    }

    /**
     * Get the css styles urls
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Print the HTML tags to load the styles
     */
    public function printStyles()
    {
        foreach ($this->styles as $css) {
            echo '<link href="' . Plugins::applyFilter('style_url', $css) . '" rel="stylesheet" type="text/css" />'
                . PHP_EOL;
        }
    }

    /**
     * Initialize Scripts class
     */
    public static function init()
    {
        $print_styles = static function () {
            self::newInstance()->printStyles();
        };

        if (OC_ADMIN) {
            Plugins::addHook('admin_header', $print_styles, 9);
        } else {
            Plugins::addHook('header', $print_styles, 9);
        }
    }
}
