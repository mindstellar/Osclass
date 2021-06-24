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

use mindstellar\utility\Utils;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @param $value
 * @param $xss_check
 *
 * @return string
 */
function _purify($value, $xss_check)
{
    if (!$xss_check) {
        return $value;
    }

    $_config = HTMLPurifier_Config::createDefault();
    $_config->set('HTML.Allowed', '');
    $_config->set(
        'Cache.SerializerPath',
        dirname(dirname(__DIR__)) . '/oc-content/uploads/'
    );

    $_purifier = new HTMLPurifier($_config);


    if (is_array($value)) {
        foreach ($value as $k => &$v) {
            $v = _purify($v, $xss_check); // recursive
        }
    } else {
        $value = $_purifier->purify($value);
    }

    return $value;
}


/**
 * @param      $param
 * @param bool $htmlencode
 * @param bool $xss_check
 * @param bool $quotes_encode
 *
 * @return string
 */
function getServerParam($param, $htmlencode = false, $xss_check = true, $quotes_encode = true)
{
    if ($param == "") {
        return '';
    }
    if (!isset($_SERVER[$param])) {
        return '';
    }
    $value = _purify($_SERVER[$param], $xss_check);
    if ($htmlencode) {
        if ($quotes_encode) {
            return htmlspecialchars(stripslashes($value), ENT_QUOTES);
        }

        return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
    }

    if (get_magic_quotes_gpc()) {
        $value = strip_slashes_extended($value);
    }

    return ($value);
}


/**
 * The url of the site
 *
 * @return string The url of the site
 * @since 1.2
 *
 */

function get_absolute_url()
{
    $protocol =
        (getServerParam('HTTPS') === 'on' || getServerParam('HTTP_X_FORWARDED_PROTO') === 'https')
            ? 'https' : 'http';
    $pos      = strpos(getServerParam('REQUEST_URI'), 'oc-includes');
    $URI      = rtrim(substr(getServerParam('REQUEST_URI'), 0, $pos), '/') . '/';

    return $protocol . '://' . getServerParam('HTTP_HOST') . $URI;
}


/**
 * The relative url on the domain url
 *
 * @return string The relative url on the domain url
 * @since 1.2
 *
 */
function get_relative_url()
{
    $url = Params::getServerParam('REQUEST_URI', false, false);

    return substr($url, 0, strpos($url, '/oc-includes')) . "/";
}


/**
 * Get the requirements to install Osclass
 *
 * @return array Requirements
 * @since 1.2
 *
 */
function get_requirements()
{
    $array = array(
        'PHP version >= 5.6.x' => array(
            'requirement' => __('PHP version >= 5.6.x'),
            'fn'          => version_compare(PHP_VERSION, '5.6.0', '>='),
            'solution'    => __('At least PHP5.6 (PHP 7.0 or higher recommended) is required to run Osclass. '
                . 'You may talk with your hosting to upgrade your PHP version.')
        ),

        'MySQLi extension for PHP' => array(
            'requirement' => __('MySQLi extension for PHP'),
            'fn'          => extension_loaded('mysqli'),
            'solution'    => __('MySQLi extension is required. How to '
                . '<a target="_blank" href="http://www.php.net/manual/en/mysqli.setup.php">install/configure</a>.')
        ),

        'GD extension for PHP' => array(
            'requirement' => __('GD extension for PHP'),
            'fn'          => extension_loaded('gd'),
            'solution'    => __('GD extension is required. How to '
                . '<a target="_blank" href="http://www.php.net/manual/en/image.setup.php">install/configure</a>.')
        ),
        'cURL extension for PHP' => array(
            'requirement' => __('cURL extension for PHP'),
            'fn'          => extension_loaded('curl'),
            'solution'    => __('cURL extension is required. How to '
                . '<a target="_blank" href="https://www.php.net/manual/en/curl.setup.php">install/configure</a>.')
        ),
        'Folder <code>oc-content/uploads</code> exists' => array(
            'requirement' => __('Folder <code>oc-content/uploads</code> exists'),
            'fn'          => file_exists(ABS_PATH . 'oc-content/uploads/'),
            'solution'    => sprintf(
                __('You have to create <code>uploads</code> folder, i.e.: <code>mkdir %soc-content/uploads/</code>'),
                ABS_PATH
            )
        ),

        'Folder <code>oc-content/uploads</code> is writable' => array(
            'requirement' => __('<code>oc-content/uploads</code> folder is writable'),
            'fn'          => is_writable(ABS_PATH . 'oc-content/uploads/'),
            'solution'    => sprintf(
                __('<code>uploads</code> folder has to be writable, i.e.: '
                    . '<code>chmod 0755 %soc-content/uploads/</code>'),
                ABS_PATH
            )
        ),
        // oc-content/downlods
        'Folder <code>oc-content/downloads</code> exists'    => array(
            'requirement' => __('Folder <code>oc-content/downloads</code> exists'),
            'fn'          => file_exists(ABS_PATH . 'oc-content/downloads/'),
            'solution'    => sprintf(
                __('You have to create <code>downloads</code> folder, i.e.: '
                    . '<code>mkdir %soc-content/downloads/</code>'),
                ABS_PATH
            )
        ),

        'Folder <code>oc-content/downloads</code> is writable' => array(
            'requirement' => __('<code>oc-content/downloads</code> folder is writable'),
            'fn'          => is_writable(ABS_PATH . 'oc-content/downloads/'),
            'solution'    => sprintf(
                __('<code>downloads</code> folder has to be writable, i.e.: '
                    . '<code>chmod 0755 %soc-content/downloads/</code>'),
                ABS_PATH
            )
        ),
        // oc-content/languages
        'Folder <code>oc-content/languages</code> exists'      => array(
            'requirement' => __('Folder <code>oc-content/languages</code> folder exists'),
            'fn'          => file_exists(ABS_PATH . 'oc-content/languages/'),
            'solution'    => sprintf(
                __('You have to create the <code>languages</code> folder, i.e.: '
                    . '<code>mkdir %soc-content/languages/</code>'),
                ABS_PATH
            )
        ),

        'Folder <code>oc-content/languages</code> is writable' => array(
            'requirement' => __('<code>oc-content/languages</code> folder is writable'),
            'fn'          => is_writable(ABS_PATH . 'oc-content/languages/'),
            'solution'    => sprintf(
                __('<code>languages</code> folder has to be writable, i.e.: '
                    . '<code>chmod 0755 %soc-content/languages/</code>'),
                ABS_PATH
            )
        ),
    );

    $config_writable = false;
    $root_writable   = false;
    $config_sample   = false;
    if (file_exists(ABS_PATH . 'config.php')) {
        if (is_writable(ABS_PATH . 'config.php')) {
            $config_writable = true;
        }
        $array['File <code>config.php</code> is writable'] = array(
            'requirement' => __('<code>config.php</code> file is writable'),
            'fn'          => $config_writable,
            'solution'    => sprintf(
                __('<code>config.php</code> file has to be writable, i.e.: <code>chmod 0755 %sconfig.php</code>'),
                ABS_PATH
            )
        );
    } else {
        if (is_writable(ABS_PATH)) {
            $root_writable = true;
        }
        $array['Root directory is writable'] = array(
            'requirement' => __('Root directory is writable'),
            'fn'          => $root_writable,
            'solution'    => sprintf(
                __('Root folder has to be writable, i.e.: <code>chmod 0755 %s</code>'),
                ABS_PATH
            )
        );

        if (file_exists(ABS_PATH . 'config-sample.php')) {
            $config_sample = true;
        }
        $array['File <code>config-sample.php</code> exists'] = array(
            'requirement' => __('<code>config-sample.php</code> file exists'),
            'fn'          => $config_sample,
            'solution'    => __('<code>config-sample.php</code> file is required, you should re-download Osclass.')
        );
    }

    return $array;
}


/**
 * Check if some of the requirements to install Osclass are correct or not
 *
 * @param $array
 *
 * @return boolean Check if all the requirements are correct
 * @since 1.2
 */
function check_requirements($array)
{
    foreach ($array as $k => $v) {
        if (!$v['fn']) {
            return true;
        }
    }

    return false;
}


/**
 * Check if allowed to send stats to Osclass
 *
 * @return boolean Check if allowed to send stats to Osclass
 */
function reportToOsclass()
{
    return $_COOKIE['osclass_save_stats'];
}


/**
 * insert/update preference allow_report_osclass
 *
 * @param $value
 */
function set_allow_report_osclass($value)
{
    $values = array(
        's_section' => 'osclass',
        's_name'    => 'allow_report_osclass',
        's_value'   => $value,
        'e_type'    => 'BOOLEAN'
    );

    Preference::newInstance()->insert($values);
}


/**
 * Install Osclass database
 *
 * @return mixed Error messages of the installation
 * @since 1.2
 *
 */
function oc_install()
{
    $dbhost      = Params::getParam('dbhost');
    $dbname      = Params::getParam('dbname');
    $username    = Params::getParam('username');
    $password    = Params::getParam('password', false, false);
    $tableprefix = Params::getParam('tableprefix');
    $createdb    = false;
    require_once LIB_PATH . 'osclass/helpers/hSecurity.php';

    if (!$tableprefix) {
        $tableprefix = 'oc_';
    }

    if (Params::getParam('createdb') != '') {
        $createdb = true;
    }

    if ($createdb) {
        $adminuser = Params::getParam('admin_username');
        $adminpwd  = Params::getParam('admin_password', false, false);

        $master_conn = new DBConnectionClass($dbhost, $adminuser, $adminpwd, '');
        $error_num   = $master_conn->getErrorConnectionLevel();

        if ($error_num > 0) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()
                    ->error(sprintf(
                        __('Cannot connect to the database. Error number: %s'),
                        $error_num
                    ), __FILE__ . '::' . __LINE__);
            }

            switch ($error_num) {
                case 1049:
                    return array(
                        'error' => __("The database doesn't exist. You should check the \"Create DB\" "
                            . 'checkbox and fill in a username and password with the right privileges')
                    );
                case 1045:
                    return array('error' => __('Cannot connect to the database. Check if the user has privileges.'));
                case 1044:
                    return array(
                        'error' => __('Cannot connect to the database. Check if the username and password are correct.')
                    );
                case 2005:
                    return array('error' => __("Can't resolve MySQL host. Check if the host is correct."));
                default:
                    return array(
                        'error' => sprintf(__('Cannot connect to the database. Error number: %s'), $error_num),
                    );
            }
        }

        $m_db = $master_conn->getOsclassDb();
        $comm = new DBCommandClass($m_db);
        $comm->query(sprintf(
            "CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI'",
            $dbname
        ));

        $error_num = $comm->getErrorLevel();

        if ($error_num > 0) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()
                    ->error(
                        sprintf(__("Can't create the database. Error number: %s"), $error_num),
                        __FILE__ . "::" . __LINE__
                    );
            }

            if (in_array($error_num, array(1006, 1044, 1045))) {
                return array(
                    'error' => __("Can't create the database. Check if the admin username "
                        . 'and password are correct.')
                );
            }

            return array(
                'error' => sprintf(__("Can't create the database. Error number: %s"), $error_num)
            );
        }

        unset($conn, $comm, $master_conn);
    }

    $conn      = new DBConnectionClass($dbhost, $username, $password, $dbname);
    $error_num = $conn->getErrorConnectionLevel();

    if ($error_num == 0) {
        $error_num = $conn->getErrorLevel();
    }

    if ($error_num > 0) {
        if (reportToOsclass()) {
            LogOsclassInstaller::newInstance()
                ->error(
                    sprintf(__('Cannot connect to the database. Error number: %s'), $error_num),
                    __FILE__ . '::' . __LINE__
                );
        }

        switch ($error_num) {
            case 1049:
                return array(
                    'error' => __("The database doesn't exist. You should check the \"Create DB\" "
                        . "checkbox and fill in a username and password with the right privileges")
                );
                break;
            case 1045:
                return array('error' => __('Cannot connect to the database. Check if the user has privileges.'));
                break;
            case 1044:
                return array(
                    'error' => __('Cannot connect to the database. Check if the username and password '
                        . 'are correct.')
                );
                break;
            case 2005:
                return array('error' => __("Can't resolve MySQL host. Check if the host is correct."));
                break;
            default:
                return array(
                    'error' => sprintf(
                        __('Cannot connect to the database. Error number: %s'),
                        $error_num
                    )
                );
                break;
        }
    }

    if (file_exists(ABS_PATH . 'config.php')) {
        if (!is_writable(ABS_PATH . 'config.php')) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()
                    ->error(
                        __("Can't write in config.php file. Check if the file is writable."),
                        __FILE__ . '::' . __LINE__
                    );
            }

            return array('error' => __("Can't write in config.php file. Check if the file is writable."));
        }
        create_config_file($dbname, $username, $password, $dbhost, $tableprefix);
    } else {
        if (!file_exists(ABS_PATH . 'config-sample.php')) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()
                    ->error(
                        __("config-sample.php doesn't exist. Check if everything is decompressed correctly."),
                        __FILE__ . '::' . __LINE__
                    );
            }

            return array(
                'error' => __("config-sample.php doesn't exist. Check if everything is "
                    . 'decompressed correctly.')
            );
        }
        if (!is_writable(ABS_PATH)) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()
                    ->error(
                        __('Can\'t copy config-sample.php. Check if the root directory is writable.'),
                        __FILE__ . '::' . __LINE__
                    );
            }

            return array('error' => __('Can\'t copy config-sample.php. Check if the root directory is writable.'));
        }
        copy_config_file($dbname, $username, $password, $dbhost, $tableprefix);
    }

    require_once ABS_PATH . 'config.php';

    $sql = file_get_contents(ABS_PATH . 'oc-includes/osclass/installer/struct.sql');

    $c_db = $conn->getOsclassDb();
    $comm = new DBCommandClass($c_db);
    $comm->importSQL($sql);

    $error_num = $comm->getErrorLevel();

    if ($error_num > 0) {
        if (reportToOsclass()) {
            LogOsclassInstaller::newInstance()
                ->error(sprintf(
                    __("Can't create the database structure. Error number: %s"),
                    $error_num
                ), __FILE__ . '::' . __LINE__);
        }

        if ($error_num === 1050) {
            return array(
                'error' => __('There are tables with the same name in the database. '
                    . 'Change the table prefix or the database and try again.')
            );
        }

        return array(
            'error' => sprintf(
                __("Can't create the database structure. Error number: %s"),
                $error_num
            )
        );
    }

    $localeManager = OSCLocale::newInstance();

    $locales = osc_listLocales();
    $values  = array(
        'pk_c_code'         => $locales[osc_current_admin_locale()]['code'],
        's_name'            => $locales[osc_current_admin_locale()]['name'],
        's_short_name'      => $locales[osc_current_admin_locale()]['short_name'],
        's_description'     => $locales[osc_current_admin_locale()]['description'],
        's_version'         => $locales[osc_current_admin_locale()]['version'],
        's_author_name'     => $locales[osc_current_admin_locale()]['author_name'],
        's_author_url'      => $locales[osc_current_admin_locale()]['author_url'],
        's_currency_format' => $locales[osc_current_admin_locale()]['currency_format'],
        's_date_format'     => $locales[osc_current_admin_locale()]['date_format'],
        'b_enabled'         => 1,
        'b_enabled_bo'      => 1
    );

    if (isset($locales[osc_current_admin_locale()]['stop_words'])) {
        $values['s_stop_words'] = $locales[osc_current_admin_locale()]['stop_words'];
    }
    $localeManager->insert($values);

    $required_files = array(
        ABS_PATH . 'oc-includes/osclass/installer/basic_data.sql',
        ABS_PATH . 'oc-includes/osclass/installer/pages.sql',

    );

    $install_lang_sql = ABS_PATH . 'oc-content/languages/' . osc_current_admin_locale() . '/mail.sql';
    $default_lang_sql = ABS_PATH . 'oc-includes/osclass/installer/mail.sql';

    if (file_exists($install_lang_sql)) {
        $required_files[] = $install_lang_sql;
    } else {
        $required_files[] = $default_lang_sql;
    }

    $sql = '';
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            if (reportToOsclass()) {
                LogOsclassInstaller::newInstance()->error(sprintf(
                    __('The file %s doesn\'t exist'),
                    $file
                ), __FILE__ . '::' . __LINE__);
            }

            return array('error' => sprintf(__('The file %s doesn\'t exist'), $file));
        }

        $sql .= file_get_contents($file);
    }

    $comm->importSQL($sql);

    $error_num = $comm->getErrorLevel();

    if ($error_num > 0) {
        if (reportToOsclass()) {
            LogOsclassInstaller::newInstance()
                ->error(sprintf(
                    __("Can't insert basic configuration. Error number: %s"),
                    $error_num
                ), __FILE__ . '::' . __LINE__);
        }

        if ($error_num === 1471) {
            return array(
                'error' => __("Can't insert basic configuration. "
                    . "This user has no privileges to 'INSERT' into the database.")
            );
        }

        return array(
            'error' => sprintf(
                __("Can't insert basic configuration. Error number: %s"),
                $error_num
            )
        );
    }

    osc_set_preference('language', osc_current_admin_locale());
    osc_set_preference('admin_language', osc_current_admin_locale());
    osc_set_preference('csrf_name', 'CSRF' . mt_rand(0, mt_getrandmax()));

    oc_install_example_data();

    if (reportToOsclass()) {
        set_allow_report_osclass(true);
    } else {
        set_allow_report_osclass(false);
    }

    return false;
}


/**
 * Insert the example data (categories and emails) on all available locales
 *
 * @return mixed Error messages of the installation
 * @since 2.4
 */
function oc_install_example_data()
{
    require_once LIB_PATH . 'osclass/formatting.php';
    require LIB_PATH . 'osclass/installer/basic_data.php';
    require_once LIB_PATH . 'osclass/helpers/hSecurity.php';
    require_once LIB_PATH . 'osclass/helpers/hValidate.php';
    require_once LIB_PATH . 'osclass/helpers/hUsers.php';
    $mCat = Category::newInstance();

    if (!function_exists('osc_apply_filter')) {
        /**
         * @param $dummyfilter
         * @param $str
         *
         * @return mixed
         */
        function osc_apply_filter($dummyfilter, $str)
        {
            return $str;
        }
    }


    foreach ($categories as $category) {
        $fields['pk_i_id']           = $category['pk_i_id'];
        $fields['fk_i_parent_id']    = $category['fk_i_parent_id'];
        $fields['i_position']        = $category['i_position'];
        $fields['i_expiration_days'] = 0;
        $fields['b_enabled']         = 1;

        $aFieldsDescription[osc_current_admin_locale()]['s_name'] = $category['s_name'];

        $mCat->insert($fields, $aFieldsDescription);
    }

    $mItem = new ItemActions(true);

    foreach ($item as $k => $v) {
        if ($k === 'description' || $k === 'title') {
            Params::setParam($k, array(osc_current_admin_locale() => $v));
        } else {
            Params::setParam($k, $v);
        }
    }

    $mItem->prepareData(true);
    $successItem = $mItem->add();

    $successPageresult = Page::newInstance()->insert(
        array(
            's_internal_name' => $page['s_internal_name'],
            'b_indelible'     => 0,
            's_meta'          => json_encode('')
        ),
        array(
            osc_current_admin_locale() => array(
                's_title' => $page['s_title'],
                's_text'  => $page['s_text']
            )
        )
    );
}


/**
 * Create config file from scratch
 *
 * @param string $dbname      Database name
 * @param string $username    User of the database
 * @param string $password    Password for user of the database
 * @param string $dbhost      Database host
 * @param string $tableprefix Prefix for table names
 *
 * @return mixed Error messages of the installation
 * @since 1.2
 *
 */
function create_config_file($dbname, $username, $password, $dbhost, $tableprefix)
{
    $password    = addslashes($password);
    $abs_url     = get_absolute_url();
    $rel_url     = get_relative_url();
    $config_text = <<<CONFIG
<?php
/**
 * The base MySQL settings of Osclass
 */

/** MySQL database name for Osclass */
define('DB_NAME', '$dbname');

/** MySQL database username */
define('DB_USER', '$username');

/** MySQL database password */
define('DB_PASSWORD', '$password');

/** MySQL hostname */
define('DB_HOST', '$dbhost');

/** Database Table prefix */
define('DB_TABLE_PREFIX', '$tableprefix');

define('REL_WEB_URL', '$rel_url');

define('WEB_PATH', '$abs_url');

CONFIG;

    file_put_contents(ABS_PATH . 'config.php', $config_text);

    return;
}


/**
 * Create config from config-sample.php file
 *
 * @param $dbname
 * @param $username
 * @param $password
 * @param $dbhost
 * @param $tableprefix
 *
 * @since 1.2
 */
function copy_config_file($dbname, $username, $password, $dbhost, $tableprefix)
{
    $password      = addslashes($password);
    $abs_url       = get_absolute_url();
    $rel_url       = get_relative_url();
    $config_sample = file(ABS_PATH . 'config-sample.php');

    foreach ($config_sample as $line_num => $line) {
        switch (substr($line, 0, 16)) {
            case "define('DB_NAME'":
                $config_sample[$line_num] = str_replace("database_name", $dbname, $line);
                break;
            case "define('DB_USER'":
                $config_sample[$line_num] = str_replace("'username'", "'$username'", $line);
                break;
            case "define('DB_PASSW":
                $config_sample[$line_num] = str_replace("'password'", "'$password'", $line);
                break;
            case "define('DB_HOST'":
                $config_sample[$line_num] = str_replace("localhost", $dbhost, $line);
                break;
            case "define('DB_TABLE":
                $config_sample[$line_num] = str_replace('oc_', $tableprefix, $line);
                break;
            case "define('REL_WEB_":
                $config_sample[$line_num] = str_replace('rel_here', $rel_url, $line);
                break;
            case "define('WEB_PATH":
                $config_sample[$line_num] = str_replace('http://localhost', $abs_url, $line);
                break;
        }
    }

    $handle = fopen(ABS_PATH . 'config.php', 'w');
    foreach ($config_sample as $line) {
        fwrite($handle, $line);
    }
    fclose($handle);
    chmod(ABS_PATH . 'config.php', 0666);
}


/**
 * @return bool
 */
function is_osclass_installed()
{
    if (!file_exists(ABS_PATH . 'config.php')) {
        return false;
    }

    require_once ABS_PATH . 'config.php';

    $conn = new DBConnectionClass(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $c_db = $conn->getOsclassDb();
    $comm = new DBCommandClass($c_db);
    $rs   = $comm->query(sprintf(
        "SELECT * FROM %st_preference WHERE s_name = 'osclass_installed'",
        DB_TABLE_PREFIX
    ));

    if ($rs == false) {
        return false;
    }

    if ($rs->numRows() != 1) {
        return false;
    }

    return true;
}


/**
 * @param $password
 *
 * @return array
 */
function finish_installation($password)
{
    require_once LIB_PATH . 'osclass/helpers/hPlugins.php';

    $data = array();

    $mAdmin = new Admin();

    $mPreference = Preference::newInstance();
    $mPreference->insert(
        array(
            's_section' => 'osclass'
            ,
            's_name'    => 'osclass_installed'
            ,
            's_value'   => '1'
            ,
            'e_type'    => 'BOOLEAN'
        )
    );

    $admin = $mAdmin->findByPrimaryKey(1);

    $data['s_email']    = $admin['s_email'];
    $data['admin_user'] = $admin['s_username'];
    $data['password']   = $password;

    return $data;
}


/**
 * Menus
 */
function display_database_config()
{
    ?>
    <form class="p-3" action="install.php" method="post">
        <input type="hidden" name="step" value="3"/>
        <h2 class="display-6 mb-3"><?php _e('Database information'); ?></h2>
        <div class="form-table">
            <div class="row mb-3">
                <label for="dbhost" class="col-md-3 col-sm-6 col-form-label text"><strong><?php _e('Host'); ?></strong></label>
                <div class="col-md-3 col-sm-6">
                    <input class="form-control" type="text" id="dbhost" name="dbhost" value="localhost" size="25"/>
                </div>
                <div class="small"><?php _e('Server name or IP where the database engine resides'); ?></div>
            </div>
            <div class="row mb-3">
                <label for="dbname" class="col-md-3 col-sm-6 col-form-label"><strong><?php _e('Database name');
                ?></strong></label>
                <div class="col-md-3 col-sm-6">
                    <input class="form-control" type="text" id="dbname" name="dbname" value="osclass" size="25"/>
                </div>
                <div class="small"><?php _e('The name of the database you want to run Osclass in');
                ?></div>
            </div>
            <div class="row mb-3">
                <label for="username" class="col-md-3 col-sm-6 col-form-label"><strong><?php _e('User Name');
                ?></strong></label>
                <div class="col-md-3 col-sm-6">
                    <input class="form-control" type="text" id="username" name="username" size="25"/>
                </div>
                <div class="small"><?php _e('Your MySQL username'); ?></div>
            </div>
            <div class="row mb-3">
                <label for="password" class="col-md-3 col-sm-6 col-form-label"><strong><?php _e('Password');
                ?></strong></label>
                <div class="col-md-3 col-sm-6">
                    <input class="form-control" type="password" id="password" name="password"
                           value="" size="25" autocomplete="off"/>
                </div>
                <div class="small"><?php _e('Your MySQL password'); ?></div>
            </div>
            <div class="row mb-3">
                <label for="tableprefix" class="col-md-3 col-sm-6 col-form-label"><strong><?php _e('Table prefix');
                ?></strong></label>
                <div class="col-md-3 col-sm-6">
                    <input class="form-control" type="text" id="tableprefix" name="tableprefix"
                           value="oc_" size="25"/>
                </div>
                <div class="small"><?php _e('If you want to run multiple Osclass installations in a single database, change this'); ?></div>
            </div>
            <div class="accordion mb-3" id="accordianAdvance">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingAdvance">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseAdvance" aria-expanded="false"
                                aria-controls="accordianAdvance">
                            <?php _e('Advanced'); ?>
                        </button>
                    </h2>
                    <div id="collapseAdvance" class="accordion-collapse collapse hide" aria-labelledby="headingAdvance"
                         data-bs-parent="#accordianAdvance">
                        <div class="accordion-body">
                            <div class="row mb-3">
                                <div class="col-md-8 col-sm-12">
                                    <input type="checkbox" id="createdb" name="createdb" onclick="db_admin();"
                                           value="1"/>
                                    <label for="createdb"><strong><?php _e('Create DB'); ?></strong></label>
                                    <div class="small"><?php _e('Check here if the database is not created and you want to create it now'); ?></div>
                                </div>
                            </div>
                            <div id="admin_username_row" class="row mb-3">
                                <label class="col-md-3 col-sm-6 col-form-label"
                                       for="admin_username"><strong><?php _e('DB admin username'); ?></strong></label>
                                <div class="col-md-4 col-sm-6">
                                    <input class="form-control" type="text" id="admin_username" name="admin_username"
                                           size="25"
                                           disabled="disabled"/>
                                </div>
                            </div>
                            <div id="admin_password_row" class="row mb-3">
                                <label class="col-md-3 col-sm-6 col-form-label"
                                       for="admin_password"><strong><?php _e('DB admin password'); ?></strong></label>
                                <div class="col-md-4 col-sm-6">
                                    <input class="form-control" type="password" id="admin_password"
                                           name="admin_password" value=""
                                           size="25" disabled="disabled" autocomplete="off"/>
                                    <span id="password_copied"><?php _e('Password copied from above'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    var username =
                    $('#createdb').on('click', function () {
                        if ($("#createdb").is(':checked')) {
                            if ($("#admin_username").val() == '') {
                                $("#admin_username").val($("#username").val());
                            }
                            if ($("#admin_password").val() == '') {
                                $("#admin_password").val( $("#password").val());
                                $("#password_copied").show();
                            }
                        } else {
                            $("#password_copied").hide();
                        }
                    });
                    $("#password_copied").hide();
                });
            </script>
        </div>
        <input type="submit" class="btn btn-primary" name="submit" value="Next"/>
        <div class="clear"></div>
    </form>
    <?php
}


function display_target()
{
    $internet_error = false;
    require_once LIB_PATH . 'osclass/helpers/hUtils.php';
    $country_list = osc_file_get_contents(osc_get_locations_json_url());
    $country_list = json_decode($country_list, false);
    $country_list = $country_list->locations;

    $country_ip = '';
    if (preg_match(
        '|([a-z]{2})-([A-Z]{2})|',
        Params::getServerParam('HTTP_ACCEPT_LANGUAGE'),
        $match
    )
    ) {
        $country_ip = $match[2];
    }

    if (!isset($country_list[0]->s_country_name)) {
        $internet_error = true;
    }
    ?>
    <form class="p-3" id="target_form" name="target_form" action="#" method="post" onsubmit="return false;">
        <h2 class="display-6"><?php _e('Information needed'); ?></h2>
        <div class="form-table">
            <h4 class="title"><?php _e('Admin user'); ?></h4>
            <div class="admin-user mb-3">
                <div class="row mb-3">
                    <label class="col-md-3 col-sm-6 col-form-label" for="admin_user"><?php _e('Username'); ?></label>
                    <div class="col-md-4 col-sm-6">
                        <input class="form-control" size="25" id="admin_user" name="s_name" type="text" value="admin"/>
                        <span id="admin-user-error" class="error" aria-hidden="true"
                              style="display:none;"><?php _e('Admin user is required'); ?></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 col-sm-6 col-form-label" for="s_passwd"><?php _e('Password'); ?></label>
                    <div class="col-md-4 col-sm-6">
                        <input size="25" class=" form-control password_test" name="s_passwd"
                               id="s_passwd"
                               type="password" value="" autocomplete="off"/>
                    </div>
                    <td></td>
                </div>
            </div>
            <div class="admin-user mb-3">
                <?php _e('A password will be automatically generated for you if you leave this blank.'); ?>
                <i class="bi bi-question-circle-fill vtip"
                   title="<?php echo osc_esc_html(__('You can modify username and password if you like, just change the input value.')); ?>">
                </i>
            </div>
            <h4 class="title"><?php _e('Contact information'); ?></h4>
            <div class="contact-info">
                <div class="row mb-3">
                    <label class="col-md-3 col-sm-6 col-form-label" for="webtitle"><?php _e('Web title'); ?></label>
                    <div class="col-md-4 col-sm-6"><input class="form-control" type="text" id="webtitle" name="webtitle" size="25"/></div>
                    <td></td>
                </div>
                <div class="row mb-3">
                    <label class="col-md-3 col-sm-6 col-form-label" for="email"><?php _e('Contact e-mail'); ?></label>
                    <div class="col-md-4 col-sm-6">
                        <input class="form-control" type="text" id="email" name="email" size="25"/>
                        <span id="email-error" class="error"
                              style="display:none;"><?php _e('Put your e-mail here'); ?></span>
                    </div>
                    <span id="email-error" class="error"
                              style="display:none;"><?php _e('Put your e-mail here'); ?></span>
                </div>
            </div>
            <h4 class="title"><?php _e('Location'); ?></h4>
            <p class="space-left-25 left no-bottom"><?php _e('Choose a country where your target users are located'); ?>
                .</p>
            <div id="location">
                <?php if (!$internet_error) { ?>
                    <input type="hidden" id="skip-location-input" name="skip-location-input"
                           value="0"/>
                    <div class="col-md-3 col-sm-6" id="country-box">
                        <select class="form-select" name="location-json" id="location-json">
                            <option value="skip"><?php _e("Skip location"); ?></option>
                            <!-- <option value="all"><?php _e("International"); ?></option> -->
                            <?php foreach ($country_list as $c) { ?>
                                <option value="<?php echo $c->s_file_name; ?>" <?php if (strpos($c->s_file_name, $country_ip) === 0) {
                                    echo 'selected="selected"';
                                               } ?>><?php echo $c->s_country_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } else { ?>
                    <div id="location-error">
                        <div class="alert alert-danger">
                            <?php _e('No internet connection. You can continue the installation and insert countries later.'); ?>
                        </div>
                        <input type="hidden" id="skip-location-input" name="skip-location-input"
                               value="1"/>
                    </div>
                <?php }; ?>
            </div>
        </div>
        <div class="mt-3">
            <a href="#" class="btn btn-primary" onclick="validate_form();">Next</a>
        </div>
    </form>
    <div id="lightbox" style="display:none;">
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100"
                 aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
    </div>
    <?php
}


/**
 * @param $error
 * @param $step
 */
function display_database_error($error, $step)
{
    ?>
    <h2 class="target display-6"><?php _e('Error'); ?></h2>
    <div class="alert alert-danger shadow">
        <?php echo $error['error'] ?>
    </div>
    <a href="<?php echo get_absolute_url(); ?>oc-includes/osclass/install.php?step=<?php echo $step; ?>"
       class="btn btn-warning"><?php _e('Go back'); ?></a>
    <div class="clear bottom"></div>
    <?php
}


/**
 * @param $bool
 *
 */
function ping_search_engines($bool)
{
    $mPreference = Preference::newInstance();
    if ($bool == 1) {
        $mPreference->insert(
            array(
                's_section' => 'osclass'
                ,
                's_name'    => 'ping_search_engines'
                ,
                's_value'   => '1'
                ,
                'e_type'    => 'BOOLEAN'
            )
        );
        // GOOGLE
        Utils::doRequest('http://www.google.com/webmasters/sitemaps/ping?sitemap='
            . urlencode(osc_search_url(array('sFeed' => 'rss'))), array());
        // BING
        Utils::doRequest('http://www.bing.com/webmaster/ping.aspx?siteMap='
            . urlencode(osc_search_url(array('sFeed' => 'rss'))), array());
        // YAHOO!
        Utils::doRequest('http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap='
            . urlencode(osc_search_url(array('sFeed' => 'rss'))), array());
    } else {
        $mPreference->insert(
            array(
                's_section' => 'osclass'
                ,
                's_name'    => 'ping_search_engines'
                ,
                's_value'   => '0'
                ,
                'e_type'    => 'BOOLEAN'
            )
        );
    }
}


/**
 * @param $password
 */
function display_finish($password)
{
    $data = finish_installation($password);
    ?>
    <?php if (Params::getParam('error_location') == 1) { ?>
    <script type="text/javascript">
        setTimeout(function () {
            $('.error-location').fadeOut('slow');
        }, 2500);
    </script>
    <div class="alert alert-warning shadow-sm mb-3">
        <?php _e('The selected location could not been installed'); ?>
    </div>
    <?php } ?>
    <h2 class="display-6 text-success"><?php _e('Congratulations!'); ?></h2>
    <div class="alert alert-success shadow-sm mb3"><?php _e("Osclass has been installed. Were you expecting more steps? Sorry to disappoint you!");
    ?></div>
    <div class="alert alert-info shadow-sm mb-3"><?php echo sprintf(
            __('An e-mail with the password for oc-admin has been sent to: %s'),
            $data['s_email']
                                                 ); ?></div>
    <div class="finish">
        <div class="row mb-3">
            <span class="col-md-3 col-sm-6 h6"><?php _e('Username'); ?>: </span>
            <span class="col-md-4 col-sm-6"><?php echo $data['admin_user']; ?></span>
        </div>
        <div class="row mb-3">
            <span class="col-md-3 col-sm-6 h6"><?php _e('Password'); ?>: </span>
            <span class="col-md-4 col-sm-6"><?php echo osc_esc_html($data['password']); ?></span>
        </div>
        <div class="row mb-3">
            <a target="_blank" href="<?php echo get_absolute_url() ?>oc-admin/index.php"
               class="btn btn-primary"><?php _e('Finish and go to the administration panel'); ?></a>
        </div>
    </div>
    <?php
}


/**
 * @return array
 */
function basic_info()
{
    $admin = Params::getParam('s_name');
    if (!$admin) {
        $admin = 'admin';
    }

    $password = Params::getParam('s_passwd', false, false);
    if (!$password) {
        $password = osc_genRandomPassword();
    }
    Params::setParam('password', $password);
    Admin::newInstance()->insert(
        array(
            's_name'     => 'Administrator',
            's_username' => $admin,
            's_password' => osc_hash_password($password),
            's_email'    => Params::getParam('email')
        )
    );

    $mPreference = Preference::newInstance();
    $mPreference->insert(
        array(
            's_section' => 'osclass',
            's_name'    => 'pageTitle',
            's_value'   => Params::getParam('webtitle'),
            'e_type'    => 'STRING'
        )
    );

    $mPreference->insert(
        array(
            's_section' => 'osclass',
            's_name'    => 'contactEmail',
            's_value'   => Params::getParam('email'),
            'e_type'    => 'STRING'
        )
    );

    $body = sprintf(__('Hi %s,'), Params::getParam('webtitle')) . '<br/>';
    $body .= sprintf(__('Your Osclass installation at %s is up and running.'
                        . ' ' . 'You can access the administration panel with these details:'), WEB_PATH);
    $body .= '<br/>';
    $body .= '<ul>';
    $body .= '<li>' . sprintf(__('username: %s'), $admin) . '</li>';
    $body .= '<li>' . sprintf(__('password: %s'), $password) . '</li>';
    $body .= '</ul>';
    $body .= sprintf(
        __('Remember that for any doubts you might have you can consult our <a href="%1$s">documentation</a>'),
        'https://osclass.gitbook.io/osclass-docs/'
    );
    $body .= __('Cheers,') . '<br/>';
    $body .= __('The <a href="https://github.com/mindstellar/osclass">Osclass</a> team');

    $sitename = strtolower(Params::getServerParam('SERVER_NAME'));
    if (0 === strpos($sitename, 'www.')) {
        $sitename = substr($sitename, 4);
    }

    $mail           = new PHPMailer(true);
    $mail->CharSet  = 'utf-8';
    $mail->Host     = 'localhost';
    $mail->From     = 'osclass@' . $sitename;
    $mail->FromName = 'Osclass';
    $mail->Subject  = 'Osclass successfully installed!';
    $mail->addAddress(Params::getParam('email'), 'Osclass administrator');
    $mail->Body    = $body;
    $mail->AltBody = $body;

    try {
        $mail->send();

        return array('email_status' => '', 's_password' => $password);
    } catch (\PHPMailer\PHPMailer\Exception $exception) {
        return array(
            'email_status' => Params::getParam('email') . '<br>' . $exception->errorMessage(),
            's_password'   => $password
        );
    }
}


/**
 * @return bool
 */
function install_locations()
{
    $location = Params::getParam('locationsql');
    if ($location) {
        $sql = osc_file_get_contents(osc_get_locations_sql_url($location));
        if ($sql) {
            $conn = DBConnectionClass::newInstance();
            $c_db = $conn->getOsclassDb();
            $comm = new DBCommandClass($c_db);
            $comm->query('SET FOREIGN_KEY_CHECKS = 0');
            $comm->importSQL($sql);
            $comm->query('SET FOREIGN_KEY_CHECKS = 1');

            return true;
        }
    }

    return false;
}
