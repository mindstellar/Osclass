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
 * Class logger
 */
abstract class Logger
{

    /**
     * Log a message with the INFO level.
     *
     * @param string $message
     *
     * @param null   $caller
     *
     */
    abstract public function info($message = '', $caller = null);

    /**
     * Log a message with the WARN level.
     *
     * @param string $message
     *
     * @param null   $caller
     *
     */
    abstract public function warn($message = '', $caller = null);

    /**
     * Log a message with the ERROR level.
     *
     * @param string $message
     *
     * @param null   $caller
     */
    abstract public function error($message = '', $caller = null);

    /**
     * Log a message with the DEBUG level.
     *
     * @param string $message
     * @param null   $caller
     */
    abstract public function debug($message = '', $caller = null);
}

/* file end: ./oc-includes/osclass/logger/logger.php */
