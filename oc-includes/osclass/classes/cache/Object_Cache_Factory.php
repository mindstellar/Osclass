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
 * Class Object_Cache_Factory
 */
class Object_Cache_Factory
{

    private static $instance;

    /**
     * @return \Object_Cache_default
     */
    public static function newInstance()
    {
        if (self::$instance === null) {
            self::$instance = self::getCache();
        }

        return self::$instance;
    }

    /**
     * @return null|\Object_Cache_default
     */
    public static function getCache()
    {
        if (self::$instance === null) {
            $cache = 'default';
            if (defined('OSC_CACHE')) {
                $cache = OSC_CACHE;
            }

            $cache_class = 'Object_Cache_' . $cache;

            if (class_exists($cache_class, true)) {
                // all correct ?
                if (call_user_func(array($cache_class, 'is_supported'))) {
                    self::$instance = new $cache_class();
                } else {
                    self::$instance = new Object_Cache_default();
                    trigger_error('Cache ' . $cache . ' NOT SUPPORTED - loaded Object_Cache_default cache',
                        E_USER_NOTICE);
                }

                return self::$instance;
            }

            throw new RuntimeException('Unknown cache');
        }

        return self::$instance;
    }
}
