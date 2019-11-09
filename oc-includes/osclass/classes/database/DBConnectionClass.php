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
     * Database connection object
     *
     * @package Osclass
     * @subpackage Database
     * @since 2.3
     */
class DBConnectionClass
{
    /**
     * DBConnectionClass should be instanced one, so it's DBConnectionClass object is set
     *
     * @access private
     * @since 2.3
     * @var DBConnectionClass
     */
    private static $instance;

    /**
     * Host name or IP address where it is located the database
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $dbHost;
    /**
     * Database name where it's installed Osclass
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $dbName;
    /**
     * Database user
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $dbUser;
    /**
     * Database user password
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $dbPassword;

    /**
     * Database connection object to Osclass database
     *
     * @access private
     * @since 2.3
     * @var mysqli
     */
    private $db             = 0;
    /**
     * Database connection object to metadata database
     *
     * @access private
     * @since 2.3
     * @var mysqli
     */
    private $metadataDb     = 0;
    /**
     * Database error number
     *
     * @access private
     * @since 2.3
     * @var int
     */
    private $errorLevel     = 0;
    /**
     * Database error description
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $errorDesc      = '';
    /**
     * Database connection error number
     *
     * @access private
     * @since 2.3
     * @var int
     */
    private $connErrorLevel = 0;
    /**
     * Database connection error description
     *
     * @access private
     * @since 2.3
     * @var string
     */
    private $connErrorDesc  = 0;


    /** A list of incompatible SQL modes.
     *
     * @since @TODO <-----
     * @access protected
     * @var array
     */
    protected $incompatible_modes = array( 'NO_ZERO_DATE', 'ONLY_FULL_GROUP_BY',
                'STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'TRADITIONAL' );

    /**
     * It creates a new DBConnection object class or if it has been created before, it
     * returns the previous object
     *
     * @access public
     * @since 2.3
     * @param string $server Host name where it's located the mysql server
     * @param string $user MySQL user name
     * @param string $password MySQL password
     * @param string $database Default database to be used when performing queries
     * @return DBConnectionClass
     */
    public static function newInstance($server = '', $user = '', $password = '', $database = '')
    {
        $server      = ($server == '') ? osc_db_host() : $server;
        $user        = ($user == '') ? osc_db_user() : $user;
        $password    = ($password == '') ? osc_db_password() : $password;
        $database    = ($database == '') ? osc_db_name() : $database;

        if (!self::$instance instanceof self) {
            self::$instance = new self ($server, $user, $password, $database);
        }
        return self::$instance;
    }

    /**
     * Initializate database connection
     *
     * @param string $server Host name where it's located the mysql server
     * @param string $user MySQL user name
     * @param string $password MySQL password
     * @param string $database Default database to be used when performing queries
     */
    public function __construct($server, $user, $password, $database)
    {
        $this->dbHost       = $server;
        $this->dbName       = $database;
        $this->dbUser       = $user;
        $this->dbPassword   = $password;

        $this->connectToOsclassDb();
    }

    /**
     * Connection destructor and print debug
     */
    public function __destruct()
    {
        $printFrontend = OSC_DEBUG_DB?osc_is_admin_user_logged_in():false;
        $this->releaseOsclassDb();
        $this->releaseMetadataDb();
        $this->debug($printFrontend);
    }

    /**
     * Set error num error and error description
     *
     * @access private
     * @since 2.3
     */
    public function errorReport()
    {
        if ( OSC_DEBUG ) {
            $this->errorLevel = $this->db->errno;
            $this->errorDesc  = $this->db->error;
        } else {
            $this->errorLevel = @$this->db->errno;
            $this->errorDesc  = @$this->db->error;
        }
    }

    /**
     * Set connection error num error and connection error description
     *
     * @access private
     * @since 2.3
     */
    public function errorConnection()
    {
        if ( OSC_DEBUG ) {
            $this->connErrorLevel = $this->db->connect_errno;
            $this->connErrorDesc  = $this->db->connect_error;
        } else {
            $this->connErrorLevel = @$this->db->connect_errno;
            $this->connErrorDesc  = @$this->db->connect_error;
        }
    }

    /**
     * Return the mysqli connection error number
     *
     * @access public
     * @since  2.3
     * @return int
     */
    public function getErrorConnectionLevel()
    {
        return $this->connErrorLevel;
    }

    /**
     * Return the mysqli connection error description
     *
     * @access public
     * @since 2.3
     * @return string
     */
    public function getErrorConnectionDesc()
    {
        return $this->connErrorDesc;
    }

    /**
     * Return the mysqli error number
     *
     * @access public
     * @since  2.3
     * @return int
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * Return the mysqli error description
     *
     * @access public
     * @since 2.3
     * @return string
     */
    public function getErrorDesc()
    {
        return $this->errorDesc;
    }

    /**
     * Connect to Osclass database
     *
     * @access public
     * @since 2.3
     * @return boolean It returns true if the connection has been successful or false if not
     */
    public function connectToOsclassDb()
    {
        $conn = $this->_connectToDb($this->dbHost, $this->dbUser, $this->dbPassword, $this->db);

        if ( $conn == false ) {
            $this->errorConnection();
            $this->releaseOsclassDb();
                
            if (MULTISITE) {
                return false;
            }

            require_once LIB_PATH . 'osclass/helpers/hErrors.php';
            $title    = 'Osclass &raquo; Error';
            $message  = 'Osclass database server is not available. <a href="https://osclass.discourse.group/">Need more help?</a></p>';
            osc_die($title, $message);
        }

        $this->_setCharset('utf8', $this->db);



        if ( $this->dbName == '' ) {
            return true;
        }

        $selectDb = $this->selectOsclassDb();
        if ( $selectDb == false ) {
            $this->errorReport();
            $this->releaseOsclassDb();

            if (MULTISITE) {
                return false;
            }

            require_once LIB_PATH . 'osclass/helpers/hErrors.php';
            $title    = 'Osclass &raquo; Error';
            $message  = 'Osclass database is not available. <a href="https://osclass.discourse.group/">Need more help?</a></p>';
            osc_die($title, $message);
        }

        return true;
    }

    /**
     * Connect to metadata database
     *
     * @access public
     * @since 2.3
     * @return boolean It returns true if the connection has been successful or false if not
     */
    public function connectToMetadataDb()
    {
        $conn = $this->_connectToDb(DB_HOST, DB_USER, DB_PASSWORD, $this->metadataDb);

        if ( $conn == false ) {
            $this->releaseMetadataDb();
            return false;
        }

        $this->_setCharset('utf8', $this->metadataDb);

        if ( DB_NAME == '' ) {
            return true;
        }

        $selectDb = $this->selectMetadataDb();
        if ( $selectDb == false ) {
            $this->releaseMetadataDb();
            return false;
        }

        return true;
    }

    /**
     * Select Osclass database in $db var
     *
     * @access private
     * @since 2.3
     * @return boolean It returns true if the database has been selected sucessfully or false if not
     */
    public function selectOsclassDb()
    {
        return $this->_selectDb($this->dbName, $this->db);
    }

    /**
     * Select metadata database in $metadata_db var
     *
     * @access private
     * @since 2.3
     * @return boolean It returns true if the database has been selected sucessfully or false if not
     */
    public function selectMetadataDb()
    {
        return $this->_selectDb(DB_NAME, $this->metadataDb);
    }

    /**
     * It reconnects to Osclass database. First, it releases the database link connection and it connects again
     *
     * @access private
     * @since 2.3
     */
    public function reconnectOsclassDb()
    {
        $this->releaseOsclassDb();
        $this->connectToOsclassDb();
    }

    /**
     * It reconnects to metadata database. First, it releases the database link connection and it connects again
     *
     * @access private
     * @since 2.3
     */
    public function reconnectMetadataDb()
    {
        $this->releaseMetadataDb();
        $this->connectToMetadataDb();
    }

    /**
     * Release the Osclass database connection
     *
     * @access private
     * @since 2.3
     * @return boolean
     */
    public function releaseOsclassDb()
    {
        $release = $this->_releaseDb($this->db);

        if ( !$release ) {
            $this->errorReport();
        }

        return $release;
    }

    /**
     * Release the metadata database connection
     *
     * @access private
     * @since 2.3
     * @return boolean
     */
    public function releaseMetadataDb()
    {
        return $this->_releaseDb($this->metadataDb);
    }

    /**
     * It returns the osclass database link connection
     *
     * @access public
     * @since 2.3
     */
    public function getOsclassDb()
    {
        return $this->_getDb($this->db);
    }

    /**
     * It returns the metadata database link connection
     *
     * @access public
     * @since 2.3
     */
    public function getMetadataDb()
    {
        return $this->_getDb($this->metadataDb);
    }

    /**
     * Connect to the database passed per parameter
     *
     * @param string $host Database host
     * @param string $user Database user
     * @param string $password Database user password
     * @param mysqli $connId Database connector link
     * @return boolean It returns true if the connection
     */
    public function _connectToDb($host, $user, $password, &$connId)
    {
        if ( OSC_DEBUG ) {
            $connId = new mysqli($host, $user, $password);
        } else {
            $connId = @new mysqli($host, $user, $password);
        }

        if ( $connId->connect_errno ) {
            return false;
        }
        $this->set_sql_mode(array(), $connId);
        return true;
    }

    /**
     *
     *
     * @param array $modes
     * @param       $connId
     */
    public function set_sql_mode($modes = array(), &$connId)
    {
        if ( empty( $modes ) ) {
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

        $modes = array_change_key_case( $modes, CASE_UPPER );
        $incompatible_modes = $this->incompatible_modes;
        foreach ( $modes as $i => $mode ) {
            if ( in_array( $mode, $incompatible_modes ) ) {
                unset( $modes[ $i ] );
            }
        }

        $modes_str = implode( ',', $modes );
        mysqli_query($connId, "SET SESSION sql_mode='$modes_str'" );
    }

    /**
     * At the end of the execution it prints the database debug if it's necessary
     *
     * @since  2.3
     * @access private
     *
     * @param bool $printFrontend
     *
     * @return bool
     */
    public function debug($printFrontend = true)
    {
        $log = LogDatabase::newInstance();

        if ( OSC_DEBUG_DB_EXPLAIN ) {
            $log->writeExplainMessages();
        }

        if ( !OSC_DEBUG_DB ) {
            return false;
        }

        if ( defined('IS_AJAX') && !OSC_DEBUG_DB_LOG ) {
            return false;
        }

        if ( OSC_DEBUG_DB_LOG ) {
            $log->writeMessages();
        } else if ($printFrontend) {
            $log->printMessages();
        } else {
            return false;
        }

        unset($log);
        return true;
    }

    /**
     * It selects the database of a connector database link
     *
     * @since 2.3
     * @access private
     * @param string $dbName Database name. If you leave blank this field, it will
     * select the database set in the init method
     * @param mysqli $connId Database connector link
     * @return boolean It returns true if the database has been selected or false if not
     */
    public function _selectDb($dbName, &$connId)
    {
        if ( $connId->connect_errno ) {
            return false;
        }

        if ( OSC_DEBUG ) {
            return $connId->select_db($dbName);
        }

        return @$connId->select_db($dbName);
    }

    /**
     * Set charset of the database passed per parameter
     *
     * @since 2.3
     * @access private
     * @param string $charset The charset to be set
     * @param mysqli $connId Database link connector
     */
    public function _setCharset($charset, &$connId)
    {
        if ( OSC_DEBUG ) {
            $connId->set_charset($charset);
        }

        @$connId->set_charset($charset);
    }

    /**
     * Release the database connection passed per parameter
     *
     * @since 2.3
     * @access private
     * @param mysqli $connId Database connection to be released
     * @return boolean It returns true if the database connection is released and false
     * if the database connection couldn't be closed
     */
    public function _releaseDb(&$connId)
    {
        if ( !$connId ) {
            return true;
        }

        return @$connId->close();
    }

    /**
     * It returns database link connection
     *
     * @param mysqli $connId Database connector link
     * @return mixed mysqli link connector if it's correct, or false if the dabase connection
     * hasn't been done.
     */
    public function _getDb(&$connId)
    {
        if ( $connId != false ) {
            return $connId;
        }

        return false;
    }
}

    /* file end: ./oc-includes/osclass/classes/database/DBConnectionClass.php */
