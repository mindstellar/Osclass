<?php if (! defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}
/**
 * @todo Need removal of legacy code.
 */

    set_time_limit(0);

    error_log(' ------- START upgrade-funcs ------- ');

if (!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(dirname(__DIR__)) . '/');
}

    require_once ABS_PATH . 'oc-load.php';
    require_once LIB_PATH . 'osclass/helpers/hErrors.php';

if (!defined('AUTO_UPGRADE')) {
    if (file_exists(osc_lib_path() . 'osclass/installer/struct.sql')) {
        $sql  = file_get_contents(osc_lib_path() . 'osclass/installer/struct.sql');

        $conn = DBConnectionClass::newInstance();
        $c_db = $conn->getOsclassDb();
        $comm = new DBCommandClass($c_db);

        $error_queries = $comm->updateDB(str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $sql));
    }

    if ((Params::getParam('skipdb') == '') && !$error_queries[0]) {
        $skip_db_link = osc_admin_base_url(true) . '?page=upgrade&action=upgrade-funcs&skipdb=true';
        $title    = __('Osclass &raquo; Has some errors');
        $message  = __("We've encountered some problems while updating the database structure. The following queries failed:");
        $message .= '<br/><br/>' . implode('<br>', $error_queries[2]);
        $message .= '<br/><br/>' . sprintf(__("These errors could be false-positive errors. If you're sure that is the case, you can <a href=\"%s\">continue with the upgrade</a>, or <a href=\"https://osclass.discourse.group\">ask in our forums</a>."), $skip_db_link);
        osc_die($title, $message);
    }
}

    $aMessages = array();
    //osc_set_preference('last_version_check', time());

    $conn = DBConnectionClass::newInstance();
    $c_db = $conn->getOsclassDb();
    $comm = new DBCommandClass($c_db);

if (osc_version() < 340) {
    $comm->query(sprintf('ALTER TABLE `%st_widget` ADD INDEX `idx_s_description` (`s_description`);', DB_TABLE_PREFIX));
    osc_set_preference('force_jpeg', '0', 'osclass', 'BOOLEAN');

    @unlink(ABS_PATH . '.maintenance');

    // THESE LINES PROBABLY HIT LOW TIMEOUT SCRIPTS, RUN THE LAST OF THE UPGRADE PROCESS
    //osc_calculate_location_slug('country');
    //osc_calculate_location_slug('region');
    //osc_calculate_location_slug('city');
}

if (osc_version() < 343) {
    // update t_alerts - Save them in plain json instead of base64
    $mAlerts = Alerts::newInstance();
    $aAlerts = $mAlerts->findByType('HOURLY');
    foreach ($aAlerts as $alert) {
        $s_search = base64_decode($alert['s_search']);
        if (stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
            $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
        } else {
            $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
        }
    }
    unset($aAlerts);

    $aAlerts = $mAlerts->findByType('DAILY');
    foreach ($aAlerts as $alert) {
        $s_search = base64_decode($alert['s_search']);
        if (stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
            $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
        } else {
            $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
        }
    }
    unset($aAlerts);

    $aAlerts = $mAlerts->findByType('WEEKLY');
    foreach ($aAlerts as $alert) {
        $s_search = base64_decode($alert['s_search']);
        if (stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
            $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
        } else {
            $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
        }
    }
    unset($aAlerts);
}

if (osc_version() < 370) {
    osc_set_preference('recaptcha_version', '1');
    $comm->query(sprintf('ALTER TABLE  %st_category_description MODIFY s_slug VARCHAR(255) NOT NULL', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE  %st_preference MODIFY s_section VARCHAR(128) NOT NULL', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE  %st_preference MODIFY s_name VARCHAR(128) NOT NULL', DB_TABLE_PREFIX));
}

if (osc_version() < 372) {
    osc_delete_preference('recaptcha_version', 'STRING');
}

if (osc_version() < 374) {
    $admin = Admin::newInstance()->findByEmail('demo@demo.com');
    if (isset($admin['pk_i_id'])) {
        Admin::newInstance()->deleteByPrimaryKey($admin['pk_i_id']);
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABS_PATH), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
    $objects = iterator_to_array($iterator);
    foreach ($objects as $file => $object) {
        try {
            $handle = @fopen($file, 'rb');
            if ($handle!==false) {
                $exist = false;
                $text = array("htmlspecialchars(file_get_contents(\$_POST['path']))",
                    '?option&path=$path' ,
                    'msdsaa' ,"shell_exec('cat /proc/cpuinfo');",
                    'PHPTerm' ,
                    'lzw_decompress'
                );
                while (($buffer = fgets($handle)) !== false) {
                    foreach ($text as $_t) {
                        if (strpos($buffer, $_t) !== false) {
                            $exist = true;
                            break;
                        }
                    }
                }
                fclose($handle);
                if ($exist && strpos($file, __FILE__) === false) {
                    error_log('remove ' . $file);
                    @unlink($file);
                }
            }
        } catch (Exception $e) {
            error_log($e);
        }
    }
}

if (osc_version() < 390) {
    osc_delete_preference('marketAllowExternalSources');
    osc_delete_preference('marketURL');
    osc_delete_preference('marketAPIConnect');
    osc_delete_preference('marketCategories');
    osc_delete_preference('marketDataUpdate');
}

    osc_changeVersionTo(strtr(OSCLASS_VERSION, array( '.' => '' )));

if (!defined('IS_AJAX') || !IS_AJAX) {
    if (empty($aMessages)) {
        osc_add_flash_ok_message(_m('Osclass has been updated successfully. <a href="https://github.com/navjottomer/osclass">Need more help?</a>'), 'admin');
        echo '<script type="text/javascript"> window.location = "'.osc_admin_base_url(true).'?page=tools&action=version"; </script>';
    } else {
        echo '<div class="well ui-rounded-corners separate-top-medium">';
        echo '<p>'.__('Osclass &raquo; Updated correctly').'</p>';
        echo '<p>'.__('Osclass has been updated successfully. <a href="https://github.com/navjottomer/osclass">Need more help?</a>').'</p>';
        foreach ($aMessages as $msg) {
            echo '<p>' . $msg . '</p>';
        }
        echo '</div>';
    }
}
