<?php
/**
 * The base configuration for Osclass
 *
 * The config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Database table prefix
 * * Optional settings
 *
 * @package Osclass
 */

/** MySQL database name for Osclass */
define('DB_NAME', 'database_name');

/** MySQL database username */
define('DB_USER', 'username');

/** MySQL database password */
define('DB_PASSWORD', 'password');

/** MySQL hostname */
define('DB_HOST', 'db_host'); // i.e localhost,

/** Database Table prefix */
define('DB_TABLE_PREFIX', 'oc_');

/** Website relative root path */
define('REL_WEB_URL', 'rel_here');

/** Website base url */
defined('WEB_PATH') or define('WEB_PATH', 'web_path_here'); // i.e http://localhost/

// Below are optional settings and should only be enabled for debugging purposes

/** Enable osclass debug */
//define('OSC_DEBUG', false); //default is false

/** Enable osclass debugging to oc-content/debug.log */
//define('OSC_DEBUG_LOG', false); //default is false

/** Enable osclass database debug */
//define('OSC_DEBUG_DB', false); //default is false

/** Enable osclass db query logging */
//define('OSC_DEBUG_DB_LOG', false); //default is false

/** Enable osclass db query explain logging */
//define('OSC_DEBUG_DB_EXPLAIN', false); //default is false





