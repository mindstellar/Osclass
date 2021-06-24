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
 * Interface iObject_Cache
 */
interface iObject_Cache
{
    public static function is_supported();

    /**
     * @param     $key
     * @param     $data
     * @param int $expire
     *
     * @return mixed
     */
    public function add($key, $data, $expire = 0);

    /**
     * @param     $key
     * @param     $data
     * @param int $expire
     *
     * @return mixed
     */
    public function set($key, $data, $expire = 0);

    /**
     * @param      $key
     * @param null $found
     *
     * @return mixed
     */
    public function get($key, &$found = null);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function delete($key);

    public function flush();

    public function stats(); // return string

    public function _get_cache();

    public function __destruct();
}
