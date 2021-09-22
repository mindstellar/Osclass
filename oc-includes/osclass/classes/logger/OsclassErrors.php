<?php
/*
 * OSClass – software for creating and publishing online classified advertising platforms
 *
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
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 07-09-2021
 * Time: 01:04
 * License is provided in root directory.
 */

namespace mindstellar\logger;

use Exception;

/**
 * Class LogErrors
 *
 * @desc    PHP error handler class
 * @package mindstellar\logger
 * @author  Mindstellar Community
 * @version 1.0
 */
class OsclassErrors
{
    private static $instance;
    private $logEnabled = false;
    private $debugEnabled = false;
    private $logFile = '';

    public function __construct()
    {
        // check if OSC_DEBUG is defined and is true
        if (defined('OSC_DEBUG') && OSC_DEBUG) {
            $this->debugEnabled = true;
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
            if (defined('OSC_DEBUG_LOG') && OSC_DEBUG_LOG) {
                ini_set('display_errors', 0);
                $this->logEnabled = true;
                $this->logFile    = CONTENT_PATH . 'debug.log';
            }
        } else {
            error_reporting(
                E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE
                | E_USER_ERROR | E_USER_WARNING
            );
        }
    }

    /**
     * Return previous instance or create a new one
     */
    public static function newInstance()
    : OsclassErrors
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * register the error handler
     *
     * @return bool
     */
    public function register()
    : bool
    {
        if ($this->debugEnabled === true) {
            // register the error handler
            set_error_handler(array($this, 'log'));

            // register exception handler
            set_exception_handler(array($this, 'logException'));

            // register shutdown function for fatal errors
            register_shutdown_function(array($this, 'logFatalErrors'));
        }

        return true;
    }

    /**
     * Logs a fatal error
     *
     */
    public function logFatalErrors()
    {
        $error = error_get_last();
        if (!empty($error)) {
            if ($this->logEnabled) {
                $message =
                    $this->formattedError($error['message'], $error['type'], $error['file'], $error['line'], var_export($error, true));
                $this->writeToFile($message);
            } elseif (PHP_SAPI === 'cli') {
                printf($this->formattedError($error['message'], $error['type'], $error['file'], $error['line'],
                                             var_export($error, true)));
                exit(1);
            } else {
                echo sprintf('<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="../../../../favicon.ico">
<title>OSClass Error</title>
<link href="%soc-admin/themes/modern/css/main.css" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body style="background:var(--bs-gray-dark);">
<div class="container">
<div class="row">
    <div class="col-lg-12">
        <h1 class="display-4 text-center text-primary mt-5"><i class="fa fa-warning text-warning"></i> OSClass Error</h1>
        <hr>
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-5 bg-dark text-light shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <h2 class="mb-3 p-1">Error Message</h2>
                                <p class="lead text-primary font-monospace">%s</p>
                            </div>
                            <div class="col-lg-8">
                                <h2 class="mb-1 p-1">Error Details</h2>
                                <div class="p-2 font-monospace">
                                    <div class="p-1 text-info"><strong class="">File: </strong>%s</div>
                                    <div class="p-1 text-info"><strong>Line: </strong>%s</div>
                                    <div class="p-1 text-info"><strong>Type: </strong>%s</div>
                                    <pre style="background:var(--bs-gray-dark);" class="mt-4 text-warning border-0">%s</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>', WEB_PATH, $error['message'], $error['file'], $error['line'], $this->errorType($error['type']), var_export($error, true));
            }
        }
    }

    /**
     * Formats a message
     *
     * @param string       $message
     * @param int          $errorCode
     * @param string       $file
     * @param int          $lineNo
     * @param string|array $context
     *
     * @return string
     */
    private function formattedError(string $message, int $errorCode, string $file, int $lineNo, $context)
    : string {
        $message = $this->errorType($errorCode) . ': ' . $message;
        $message .= ' in ' . $file . ' on line no ' . $lineNo. ' Error Code:'.$errorCode;

        if (!empty($context)) {
            $message .= ' with context: '. PHP_EOL . var_export($context, true);
        }

        return $message;
    }

    /**
     * Formats a errorCode
     *
     * @param int $errorCode
     *
     * @return string
     */
    private function errorType(int $errorCode)
    : string {
        switch ($errorCode) {
            case E_WARNING:
                return 'WARNING';
            case E_PARSE:
                return 'PARSE';
            case E_NOTICE:
                return 'NOTICE';
            case E_CORE_ERROR:
                return 'CORE_ERROR';
            case E_CORE_WARNING:
                return 'CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'COMPILE_WARNING';
            case E_USER_ERROR:
                return 'USER_ERROR';
            case E_USER_WARNING:
                return 'USER_WARNING';
            case E_USER_NOTICE:
                return 'USER_NOTICE';
            case E_STRICT:
                return 'STRICT';
            case E_RECOVERABLE_ERROR:
                return 'RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'DEPRECATED';
            case E_USER_DEPRECATED:
                return 'USER_DEPRECATED';
            default:
                return 'ERROR';
        }
    }

    /**
     * Writes a message to the log file
     *
     * @param string $message
     *
     * @return void
     */
    private function writeToFile(string $message)
    {
        if (!file_exists($this->logFile)) {
            // try to create the log file or throw an exception with the error
            try {
                $this->createLogFile();
            } catch (Exception $e) {
                $this->logFile = ini_get('error_log');
                $this->log($e->getCode(), $e->getTraceAsString());
            }
        }

        $message = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;

        file_put_contents($this->logFile, $message, FILE_APPEND);
    }

    /**
     * Creates the log file
     *
     * @return void
     * @throws \Exception
     */
    private function createLogFile()
    {
        $logFile = CONTENT_PATH . 'debug.log';

        if (file_exists($logFile)) {
            return;
        }

        if (!is_writable(CONTENT_PATH)) {
            throw new Exception('The content directory is not writable');
        }

        touch($logFile);
    }

    /**
     * Logs a message
     *
     * @param int    $type
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param string $context
     *
     * @return bool
     */
    public function log(
        int    $type = E_USER_NOTICE,
        string $message = '',
        string $file = __FILE__,
        int    $line = __LINE__,
        array $context = []
    )
    : bool {
        if ($this->logEnabled) {
            $message = $this->formattedError($message, $type, $file, $line, $context);
            $this->writeToFile($message);
        } else {
            // it's PHP CLI do not use html
            if (PHP_SAPI === 'cli') {
                printf($this->formattedError($message, $type, $file, $line, $context));
            }
            $message = $this->htmlFormattedError($message, $type, $file, $line, $context);
            $this->writeToScreen($message);
        }

        return true;
    }

    /**
     * Format message in html for screen
     *
     * @param string $message
     * @param int    $type
     * @param string $file
     * @param int    $line
     * @param string $context
     *
     * @return string
     */
    private function htmlFormattedError(string $message, int $type, string $file, int $line, string $context)
    : string {
        // return html formatted message
        $errorTrace = '';
        if($context){
            $errorTrace = '<pre>' . $context . '</pre>';
        }
        return sprintf('<style>
              .error-container {
                  width: 100%%;
                  left: 0;
                  right: 0;
                  margin: auto;
                  z-index: 999;
              }
              .error-container .error {
                  border-radius: .25rem;
                  font-size: 1.2rem;
                  font-weight: normal;
                  padding: 1rem;
                  margin-top: 10px;
                  margin-bottom: 10px;
                  clear: both;
                  text-align: initial;
                  color: #231c1c;
              }
              .error-container .error-info {
                  color: #055160;
                  background-color: #cff4fc;
              }
              .error-container .error-warning {
                  color: #5a5a00;
                  background-color: #fff7bd;
              }
              .error-container .error-danger {
                  color: #720505;
                  background-color: #fcd5d1;
              }
              .error-container pre {
                  background: #343a40;
                  color: #ffc107;
                  padding: 1rem;
                  font-size: 1rem;
                  border-radius: 0.25rem;
                  margin-top: 2rem;
                  border:0;
              }
            </style>
            <div class="error-container">
                <div class="error error-%s">
                    <strong>%s:</strong> %s
                    <br>
                    <strong>Error File:</strong> %s
                    <br>
                    <strong>Error Line:</strong> %d
                    <br>
                    <strong>Error Code:</strong> %s
                    <br>
                    %s
                </div>
           </div>', $this->errorClass($type), $this->errorType($type), $message, $file, $line, $type, $errorTrace );
    }

    /**
     * Get the class for the alert type
     *
     * @param int $errorCode
     *
     * @return string
     */
    private function errorClass(int $errorCode)
    : string {
        switch ($errorCode) {
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_WARNING:
                return 'warning';
            case E_USER_NOTICE:
            case E_STRICT:
            case E_NOTICE:
                return 'info';
            default:
                return 'danger';
        }
    }

    /**
     * Writes a message to the screen
     *
     * @param string $message
     *
     * @return void
     */
    private function writeToScreen(string $message)
    {
        echo $message;
    }

    /**
     * Logs an exception
     *
     * @param $exception object
     *
     * @return bool
     */
    public function logException($exception)
    : bool {

        $this->log($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(),
                   $exception->getTraceAsString());

        return true;
    }
}
