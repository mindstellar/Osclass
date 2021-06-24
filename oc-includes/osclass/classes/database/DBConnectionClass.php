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
 * Database connection object
 *
 * @package    Osclass
 * @subpackage Database
 * @since      2.3
 */
class DBConnectionClass
{
    /**
     * DBConnectionClass should be instanced one, so it's DBConnectionClass object is set
     *
     * @access private
     * @since  2.3
     * @var DBConnectionClass
     */
    private static $instance;

    /** A list of incompatible SQL modes.
     *
     * @since  2.3
     * @access protected
     * @var array
     */
    protected $incompatible_modes = array(
        'NO_ZERO_DATE',
        'ONLY_FULL_GROUP_BY',
        'STRICT_TRANS_TABLES',
        'STRICT_ALL_TABLES',
        'TRADITIONAL'
    );
    /**
     * Host name or IP address where it is located the database
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $dbHost;
    /**
     * Database name where it's installed Osclass
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $dbName;
    /**
     * Database user
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $dbUser;
    /**
     * Database user password
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $dbPassword;
    /**
     * Database connection object to Osclass database
     *
     * @access private
     * @since  2.3
     * @var mysqli
     */
    private $db = 0;
    /**
     * Database connection object to metadata database
     *
     * @access private
     * @since  2.3
     * @var mysqli
     */
    private $metadataDb = 0;
    /**
     * Database error number
     *
     * @access private
     * @since  2.3
     * @var int
     */
    private $errorLevel = 0;
    /**
     * Database error description
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $errorDesc = '';
    /**
     * Database connection error number
     *
     * @access private
     * @since  2.3
     * @var int
     */
    private $connErrorLevel = 0;
    /**
     * Database connection error description
     *
     * @access private
     * @since  2.3
     * @var string
     */
    private $connErrorDesc = 0;

    /**
     * Initialize database connection
     *
     * @param string $server   Host name where it's located the mysql server
     * @param string $user     MySQL user name
     * @param string $password MySQL password
     * @param string $database Default database to be used when performing queries
     */
    public function __construct($server, $user, $password, $database)
    {
        $this->dbHost     = $server;
        $this->dbName     = $database;
        $this->dbUser     = $user;
        $this->dbPassword = $password;
        $this->connectToOsclassDb();
    }

    /**
     * Connect to Osclass database
     *
     * @access public
     * @return boolean It returns true if the connection has been successful or false if not
     * @since  2.3
     */
    public function connectToOsclassDb()
    {
        $conn = $this->connectToDb($this->dbHost, $this->dbUser, $this->dbPassword, $this->db);

        if ($conn == false) {
            $this->errorConnection();
            $this->releaseOsclassDb();
            $this->handleDbError(
                'Osclass &raquo; Error',
                'Osclass database server is not available. <a href="https://osclass.discourse.group/">Need more help?</a></p>'
            );
        }

        $this->setCharset('utf8', $this->db);


        if (!$this->dbName) {
            return true;
        }

        $selectDb = $this->selectOsclassDb();
        if ($selectDb == false) {
            $this->errorReport();
            $this->releaseOsclassDb();
            $this->handleDbError(
                'Osclass &raquo; Error',
                'Osclass database is not available. <a href="https://osclass.discourse.group/">Need more help?</a></p>'
            );
        }

        return true;
    }

    /**
     * Connect to the database passed per parameter
     *
     * @param string $host     Database host
     * @param string $user     Database user
     * @param string $password Database user password
     * @param mysqli $connId   Database connector link
     *
     * @return boolean It returns true if the connection
     */
    private function connectToDb($host, $user, $password, &$connId)
    {
        if (OSC_DEBUG) {
            $connId = new mysqli($host, $user, $password);
        } else {
            $connId = @new mysqli($host, $user, $password);
        }
        if ($connId->connect_errno) {
            return false;
        }
        $this->setSQLMode($connId, array());
        return true;
    }

    /**
     * Set sql_mode
     *
     * @param array $modes
     * @param       $connId
     */
    public function setSQLMode(&$connId, $modes = array())
    {
        if (empty($modes)) {
            $res = mysqli_query($connId, 'SELECT @@SESSION.sql_mode');

            if (empty($res)) {
                return;
            }

            $modes_array = mysqli_fetch_array($res);
            if (empty($modes_array[0])) {
                return;
            }
            $modes_str = $modes_array[0];


            if (empty($modes_str)) {
                return;
            }

            $modes = explode(',', $modes_str);
        }

        $modes              = array_change_key_case($modes, CASE_UPPER);
        $incompatible_modes = $this->incompatible_modes;
        foreach ($modes as $i => $mode) {
            if (in_array($mode, $incompatible_modes)) {
                unset($modes[$i]);
            }
        }

        $modes_str = implode(',', $modes);
        mysqli_query($connId, "SET SESSION sql_mode='$modes_str'");
    }

    /**
     * Set connection error num error and connection error description
     *
     * @access private
     * @since  2.3
     */
    private function errorConnection()
    {
        if (OSC_DEBUG) {
            $this->connErrorLevel = $this->db->connect_errno;
            $this->connErrorDesc  = $this->db->connect_error;
        } else {
            $this->connErrorLevel = @$this->db->connect_errno;
            $this->connErrorDesc  = @$this->db->connect_error;
        }
    }

    /**
     * Release the Osclass database connection
     *
     * @access private
     * @return boolean
     * @since  2.3
     */
    private function releaseOsclassDb()
    {
        $release = $this->releaseDb($this->db);

        if (!$release) {
            $this->errorReport();
        }

        return $release;
    }

    /**
     * Release the database connection passed per parameter
     *
     * @param mysqli $connId Database connection to be released
     *
     * @return boolean It returns true if the database connection is released and false
     * if the database connection couldn't be closed
     * @since  2.3
     * @access private
     */
    private function releaseDb(&$connId)
    {
        if (!$connId) {
            return true;
        }

        return @$connId->close();
    }

    /**
     * Set error num error and error description
     *
     * @access private
     * @since  2.3
     */
    public function errorReport()
    {
        if (OSC_DEBUG) {
            $this->errorLevel = $this->db->errno;
            $this->errorDesc  = $this->db->error;
        } else {
            $this->errorLevel = @$this->db->errno;
            $this->errorDesc  = @$this->db->error;
        }
    }

    /**
     * Set charset of the database passed per parameter
     *
     * @param string $charset The charset to be set
     * @param mysqli $connId  Database link connector
     *
     * @since  2.3
     * @access private
     */
    private function setCharset($charset, &$connId)
    {
        if (OSC_DEBUG) {
            $connId->set_charset($charset);
        }

        @$connId->set_charset($charset);
    }

    /**
     * Select Osclass database in $db var
     *
     * @access private
     * @return boolean It returns true if the database has been selected sucessfully or false if not
     * @since  2.3
     */
    private function selectOsclassDb()
    {
        return $this->selectDb($this->dbName, $this->db);
    }

    /**
     * It selects the database of a connector database link
     *
     * @param string $dbName Database name. If you leave blank this field, it will
     *                       select the database set in the init method
     * @param mysqli $connId Database connector link
     *
     * @return boolean It returns true if the database has been selected or false if not
     * @since  2.3
     * @access private
     */
    private function selectDb($dbName, &$connId)
    {
        if ($connId->connect_errno) {
            return false;
        }

        if (OSC_DEBUG) {
            return $connId->select_db($dbName);
        }

        return @$connId->select_db($dbName);
    }

    /**
     * It creates a new DBConnection object class or if it has been created before, it
     * returns the previous object
     *
     * @access public
     *
     * @param string $server   Host name where it's located the mysql server
     * @param string $user     MySQL user name
     * @param string $password MySQL password
     * @param string $database Default database to be used when performing queries
     *
     * @return DBConnectionClass
     * @since  2.3
     */
    public static function newInstance($server = DB_HOST, $user = DB_USER, $password = DB_PASSWORD, $database = DB_NAME)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($server, $user, $password, $database);
        }

        return self::$instance;
    }

    /**
     * Connection destructor and print debug
     */
    public function __destruct()
    {
        $printFrontend = OSC_DEBUG_DB ? osc_is_admin_user_logged_in() : false;
        $this->releaseOsclassDb();
        $this->releaseMetadataDb();
        $this->debug($printFrontend);
    }

    /**
     * Release the metadata database connection
     *
     * @access private
     * @return boolean
     * @since  2.3
     */
    public function releaseMetadataDb()
    {
        return $this->releaseDb($this->metadataDb);
    }

    /**
     * At the end of the execution it prints the database debug if it's necessary
     *
     * @param bool $printFrontend
     *
     * @return bool
     * @since  2.3
     * @access private
     *
     */
    public function debug($printFrontend = true)
    {
        $log = LogDatabase::newInstance();

        if (OSC_DEBUG_DB_EXPLAIN) {
            $log->writeExplainMessages();
        }

        if (!OSC_DEBUG_DB) {
            return false;
        }

        if (defined('IS_AJAX') && !OSC_DEBUG_DB_LOG) {
            return false;
        }

        if (OSC_DEBUG_DB_LOG) {
            $log->writeMessages();
        } elseif ($printFrontend) {
            $log->printMessages();
        } else {
            return false;
        }

        unset($log);

        return true;
    }

    /**
     * Return the mysqli connection error number
     *
     * @access public
     * @return int
     * @since  2.3
     */
    public function getErrorConnectionLevel()
    {
        return $this->connErrorLevel;
    }

    /**
     * Return the mysqli connection error description
     *
     * @access public
     * @return string
     * @since  2.3
     */
    public function getErrorConnectionDesc()
    {
        return $this->connErrorDesc;
    }

    /**
     * Return the mysqli error number
     *
     * @access public
     * @return int
     * @since  2.3
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * Return the mysqli error description
     *
     * @access public
     * @return string
     * @since  2.3
     */
    public function getErrorDesc()
    {
        return $this->errorDesc;
    }

    /**
     * It returns the osclass database link connection
     *
     * @access public
     * @since  2.3
     */
    public function getOsclassDb()
    {
        return $this->getDb($this->db);
    }

    /**
     * It returns database link connection
     *
     * @param mysqli $connId Database connector link
     *
     * @return \mysqli|bool mysqli link connector if it's correct, or false if the dabase connection
     * hasn't been done.
     */
    private function getDb(&$connId)
    {
        if ($connId) {
            return $connId;
        }

        return false;
    }

    /**
     * It returns the metadata database link connection
     *
     * @access public
     * @since  2.3
     */
    public function getMetadataDb()
    {
        return $this->getDb($this->metadataDb);
    }

    /**
     * This handle database error and show error page with given title,message.
     * @param $title
     * @param $message
     */
    private function handleDbError($title, $message)
    {
        if (OSC_INSTALLING !== 1) {
            require_once LIB_PATH . 'osclass/helpers/hErrors.php';
            osc_die($title, $message);
        }
    }

    /**
     * It reconnects to Osclass database. First, it releases the database link connection and it connects again
     *
     * @access private
     * @since  2.3
     */
    private function reconnectOsclassDb()
    {
        $this->releaseOsclassDb();
        $this->connectToOsclassDb();
    }

    /**
     * It reconnects to metadata database. First, it releases the database link connection and it connects again
     *
     * @access private
     * @since  2.3
     */
    private function reconnectMetadataDb()
    {
        $this->releaseMetadataDb();
        $this->connectToMetadataDb();
    }

    /**
     * Connect to metadata database
     *
     * @access public
     * @return boolean It returns true if the connection has been successful or false if not
     * @since  2.3
     */
    public function connectToMetadataDb()
    {
        $conn = $this->connectToDb(DB_HOST, DB_USER, DB_PASSWORD, $this->metadataDb);

        if ($conn == false) {
            $this->releaseMetadataDb();

            return false;
        }

        $this->setCharset('utf8', $this->metadataDb);

        if (!DB_NAME) {
            return true;
        }

        $selectDb = $this->selectMetadataDb();
        if ($selectDb == false) {
            $this->releaseMetadataDb();

            return false;
        }

        return true;
    }

    /**
     * Select metadata database in $metadata_db var
     *
     * @access private
     * @return boolean It returns true if the database has been selected sucessfully or false if not
     * @since  2.3
     */
    private function selectMetadataDb()
    {
        return $this->selectDb(DB_NAME, $this->metadataDb);
    }
}

/* file end: ./oc-includes/osclass/classes/database/DBConnectionClass.php */
