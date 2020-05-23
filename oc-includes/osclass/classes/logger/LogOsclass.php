<?php
/**
 * Created by Osclass Community.
 * User: navjottomer
 * Date: 2019-11-15
 * Time: 22:07
 */


/**
 * Class LogOsclass
 *
 * @package osclass
 */
class LogOsclass extends Logger
{
    /**
     * @var LogOsclass
     */
    private static $instance;
    /**
     * $debug_enabled - boolean
     *
     * @var bool
     */
    protected $debug_enabled = false;
    /**
     * $log_file - path and log file name
     *
     * @var string
     */
    protected $log_file;
    /**
     * $file - file
     *
     * @var resource
     */
    protected $file;
    /**
     * $options - settable options - future use - passed through constructor
     *
     * @var array
     */
    protected $options = array(
        'dateFormat' => 'd-M-Y H:i:s'
    );
    /**
     * @var array
     */
    private $params;

    /**
     * Class constructor
     *
     * @param string $log_file - path and filename of log
     * @param array  $params
     *
     */
    public function __construct($log_file = null, $params = array())
    {
        if (defined('OSC_DEBUG')) {
            $this->debug_enabled = OSC_DEBUG;
        }
        if ($log_file === null) {
            $this->log_file = CONTENT_PATH . 'osclass_debug.log';
        } else {
            $this->log_file = $log_file;
        }
        $this->params   = array_merge($this->options, $params);
    }

    /**
     * @return LogOsclass
     */
    public static function newInstance($log_file = null, $params = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($log_file, $params);
        }

        return self::$instance;
    }

    /**
     * Info method (write info message)
     *
     * @param string $message
     *
     * @return void
     */
    public function info($message = '', $caller = null)
    {
        $this->writeLog($message, 'INFO', $caller);
    }

    /**
     * Write to log file
     *
     * @param string $message
     * @param string $severity
     *
     * @return void
     */
    private function writeLog($message, $severity, $caller)
    {
        if ($this->debug_enabled) {
            // open log file
            $this->openLog();

            $path = Params::getServerParam('SERVER_NAME') . Params::getServerParam('REQUEST_URI');
            $time = date($this->params['dateFormat']);
            // Write time, url, & message to end of file
            fwrite($this->file, "[$time] [$path] : [$severity] - $message - $caller" . PHP_EOL);
        }
    }

    /**
     * Open log file
     */
    private function openLog()
    {
        //Create log file if it doesn't exist.
        if (!file_exists($this->log_file)) {
            $this->file = fopen($this->log_file, 'wb') or exit("Can't create " . basename($this->log_file) . '!');
        }
        //Check permissions of file.
        if (!is_writable($this->log_file)) {
            //throw exception if not writable
            throw new RuntimeException('ERROR: Unable to write to file!', 1);
        }
        if ($this->file === null) {
            $openFile = $this->log_file;
            // append new log
            $this->file = fopen($openFile, 'ab') or exit("Can't open $openFile! Check permissions");
        }
    }

    /**
     * Debug method (write debug message)
     *
     * @param string $message
     *
     * @return void
     */
    public function debug($message = '', $caller = null)
    {
        $this->writeLog($message, 'DEBUG', $caller);
    }

    /**
     * Warning method (write warning message)
     *
     * @param string $message
     *
     * @return void
     */
    public function warn($message = '', $caller = null)
    {
        $this->writeLog($message, 'WARNING', $caller);
    }

    /**
     * Error method (write error message)
     *
     * @param string $message
     *
     * @return void
     */
    public function error($message = '', $caller = null)
    {
        $this->writeLog($message, 'ERROR', $caller);
    }

    /**
     * Log a message object with the FATAL level including the caller.
     *
     * @param string $message
     * @param null   $caller
     */
    public function fatal($message = '', $caller = null)
    {
        $this->writeLog($message, 'FATAL', $caller);
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
}
