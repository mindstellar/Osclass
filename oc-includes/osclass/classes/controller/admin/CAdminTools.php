<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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
 * Class CAdminTools
 */
class CAdminTools extends AdminSecBaseModel
{
    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_admin_tools');
    }

    //Business Layer...
    public function doModel()
    {
        parent::doModel();

        switch ($this->action) {
            case ('import'):         // calling import view
                $this->doView('tools/import.php');
                break;
            case ('import_post'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=import');
                }
                // calling
                osc_csrf_check();
                $sql = Params::getFiles('sql');
                if (isset($sql['size']) && $sql['size'] != 0) {
                    $content_file = file_get_contents($sql['tmp_name']);

                    $conn = DBConnectionClass::newInstance();
                    $c_db = $conn->getOsclassDb();
                    $comm = new DBCommandClass($c_db);
                    if ($comm->importSQL($content_file)) {
                        osc_calculate_location_slug(osc_subdomain_type());
                        osc_add_flash_ok_message(_m('Import complete'), 'admin');
                    } else {
                        osc_add_flash_error_message(_m('There was a problem importing data to the database'), 'admin');
                    }
                } else {
                    osc_add_flash_warning_message(_m('No file was uploaded'), 'admin');
                }
                @unlink($sql['tmp_name']);
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=import');
                break;
            case ('category'):
                $this->doView('tools/category.php');
                break;
            case ('category_post'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=category');
                }
                osc_update_cat_stats();
                osc_add_flash_ok_message(_m('Recount category stats has been successful'), 'admin');
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=category');
                break;
            case ('locations'):
                $this->doView('tools/locations.php');
                break;
            case ('locations_post'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=locations');
                }

                osc_update_location_stats(true);

                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=locations');
                break;
            case ('upgrade'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true));
                }
                $this->doView('tools/upgrade.php');
                break;
            case 'version':
                $this->doView('tools/version.php');
                break;
            case ('backup'):
            case ('backup_post'):
                $this->doView('tools/backup.php');
                break;
            case ('backup-sql'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                }
                osc_csrf_check();
                //databasse dump...
                if (Params::getParam('bck_dir') != '') {
                    $path = trim(Params::getParam('bck_dir'));
                    if (substr($path, -1, 1) !== '/') {
                        $path .= '/';
                    }
                } else {
                    $path = osc_base_path();
                }
                $filename = 'Osclass_mysqlbackup.' . date('YmdHis') . '.sql';

                switch (osc_dbdump($path, $filename)) {
                    case (-1):
                        $msg = _m('Path is empty');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-2):
                        $msg = sprintf(_m('Could not connect with the database'));
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-3):
                        $msg = _m('There are no tables to back up');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-4):
                        $msg = _m('The folder is not writable');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    default:
                        $msg = _m('Backup completed successfully');
                        osc_add_flash_ok_message($msg, 'admin');
                        break;
                }
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                break;
            case ('backup-sql_file'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                }
                //databasse dump...

                $filename = 'Osclass_mysqlbackup.' . date('YmdHis') . '.sql';
                $path     = sys_get_temp_dir() . '/';

                switch (osc_dbdump($path, $filename)) {
                    case (-1):
                        $msg = _m('Path is empty');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-2):
                        $msg = sprintf(_m('Could not connect with the database. Error: %s'), DBConnectionClass::newInstance()
                                                                                                              ->getOsclassDb()->connect_error);
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-3):
                        $msg = sprintf(_m('Could not select the database. Error: %s'), DBConnectionClass::newInstance()
                                                                                                        ->getOsclassDb()->error);
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-4):
                        $msg = _m('There are no tables to back up');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    case (-5):
                        $msg = _m('The folder is not writable');
                        osc_add_flash_error_message($msg, 'admin');
                        break;
                    default:
                        $msg = _m('Backup completed successfully');
                        osc_add_flash_ok_message($msg, 'admin');
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename=' . basename($filename));
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($path . $filename));
                        flush();
                        readfile($path . $filename);
                        exit;
                        break;
                }
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                break;
            case ('backup-zip_file'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                }
                $filename = 'Osclass_backup.' . date('YmdHis') . '.zip';
                $path     = sys_get_temp_dir() . '/';

                if (osc_zip_folder(osc_base_path(), $path . $filename)) {
                    $msg = _m('Archived successfully!');
                    osc_add_flash_ok_message($msg, 'admin');
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=' . basename($filename));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($path . $filename));
                    flush();
                    readfile($path . $filename);
                    exit;
                }

                $msg = _m('Error, the zip file was not created in the specified directory');
                osc_add_flash_error_message($msg, 'admin');
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                break;
            case ('backup-zip'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                }
                //zip of the code just to back it up
                osc_csrf_check();
                if (Params::getParam('bck_dir')) {
                    $archive_name = trim(Params::getParam('bck_dir'));
                    if (substr(trim($archive_name), -1, 1) !== '/') {
                        $archive_name .= '/';
                    }
                    $archive_name .= '/Osclass_backup.' . date('YmdHis') . '.zip';
                } else {
                    $archive_name = osc_base_path() . 'Osclass_backup.' . date('YmdHis') . '.zip';
                }
                $archive_folder = osc_base_path();

                if (osc_zip_folder($archive_folder, $archive_name)) {
                    $msg = _m('Archived successfully!');
                    osc_add_flash_ok_message($msg, 'admin');
                } else {
                    $msg = _m('Error, the zip file was not created in the specified directory');
                    osc_add_flash_error_message($msg, 'admin');
                }
                $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=backup');
                break;
            case ('maintenance'):
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->doView('tools/maintenance.php');
                    break;
                }
                $mode = Params::getParam('mode');
                if ($mode === 'on') {
                    osc_csrf_check();
                    $maintenance_file = osc_base_path() . '.maintenance';
                    $fileHandler      = @fopen($maintenance_file, 'wb');
                    if ($fileHandler) {
                        osc_add_flash_ok_message(_m('Maintenance mode is ON'), 'admin');
                    } else {
                        osc_add_flash_error_message(
                            _m('There was an error creating the .maintenance file, please create it manually at the root folder'),
                            'admin'
                        );
                    }
                    fclose($fileHandler);
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=maintenance');
                } elseif ($mode === 'off') {
                    osc_csrf_check();
                    $deleted = @unlink(osc_base_path() . '.maintenance');
                    if ($deleted) {
                        osc_add_flash_ok_message(_m('Maintenance mode is OFF'), 'admin');
                    } else {
                        osc_add_flash_error_message(
                            _m('There was an error removing the .maintenance file, please remove it manually from the root folder'),
                            'admin'
                        );
                    }
                    $this->redirectTo(osc_admin_base_url(true) . '?page=tools&action=maintenance');
                }
                $this->doView('tools/maintenance.php');
                break;
            case 'system_info':
            default:
                $this->doView('tools/system-info.php');
                break;
        }
    }

    //hopefully generic...
}

/* file end: ./oc-admin/CAdminTools.php */
