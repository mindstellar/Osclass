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
 * Class LogOsclassInstaller
 */
class LogOsclassInstaller extends Logger
{
    private static $instance;

    private $os;
    private $component = 'INSTALLER';

    public function __construct()
    {
        $this->os = PHP_OS;
    }

    /**
     * @return \LogOsclassInstaller
     */
    public static function newInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Log a message with the INFO level.
     *
     * @param string $message
     * @param null   $caller
     */
    public function info($message = '', $caller = null)
    {
        $this->sendOsclass('INFO', $message, $caller);
    }

    /**
     * @param $type
     * @param $message
     * @param $caller
     *
     * @return bool
     * @todo Creating another target to receive logs.
     */
    private function sendOsclass($type, $message, $caller)
    {
        return true;
        /** TODO
         * osc_doRequest(
         * 'http://admin.osclass.org/logger.php',
         * array(
         * 'type' => $type
         * ,'component' => $this->component
         * ,'os' => $this->os
         * ,'message' => base64_encode($message)
         * ,'fileLine' => base64_encode($caller)
         * )
         * );
         *
         */
    }

    /**
     * Log a message with the WARN level.
     *
     * @param string $message
     * @param null   $caller
     */
    public function warn($message = '', $caller = null)
    {
        $this->sendOsclass('WARN', $message, $caller);
    }

    /**
     * Log a message with the ERROR level.
     *
     * @param string $message
     * @param null   $caller
     */
    public function error($message = '', $caller = null)
    {
        $this->sendOsclass('ERROR', $message, $caller);
    }

    /**
     * Log a message with the DEBUG level.
     *
     * @param string $message
     * @param null   $caller
     */
    public function debug($message = '', $caller = null)
    {
        $this->sendOsclass('DEBUG', $message, $caller);
    }

    /**
     * Log a message object with the FATAL level including the caller.
     *
     * @param string $message
     * @param null   $caller
     */
    public function fatal($message = '', $caller = null)
    {
        $this->sendOsclass('FATAL', $message, $caller);
    }
}

/* file end: ./oc-includes/osclass/logger/LogOsclassInstaller.php */
