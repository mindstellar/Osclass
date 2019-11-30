<?php

/*
 * Copyright 2014 Osclass
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

    /**
     * Log a message object with the FATAL level including the caller.
     *
     * @param string $message
     * @param null   $caller
     */
    abstract public function fatal($message = '', $caller = null);
}

/* file end: ./oc-includes/osclass/logger/logger.php */
