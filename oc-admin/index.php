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

define('ABS_PATH', str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/'));
define('OC_ADMIN', true);

require_once ABS_PATH . 'oc-load.php';

if (file_exists(ABS_PATH . '.maintenance')) {
    define('__OSC_MAINTENANCE__', true);
}

// register admin scripts
osc_register_script('admin-osc', osc_current_admin_theme_js_url('osc.js'), 'jquery');
osc_register_script('admin-ui-osc', osc_current_admin_theme_js_url('ui-osc.js'), 'jquery');
osc_register_script('admin-location', osc_current_admin_theme_js_url('location.js'), 'jquery');

// enqueue scripts
osc_enqueue_script('jquery');
osc_enqueue_script('jquery-ui');
osc_enqueue_script('admin-osc');
osc_enqueue_script('admin-ui-osc');

osc_add_hook('admin_footer', array('FieldForm', 'i18n_datePicker'));

// enqueue css styles
osc_enqueue_style('jquery-ui', osc_assets_url('css/jquery-ui/jquery-ui.css'));
osc_enqueue_style('admin-css', osc_current_admin_theme_styles_url('main.css'));

switch (Params::getParam('page')) {
    case ('items'):
        require_once('controller/CAdminItems.php');
        $do = new CAdminItems();
        $do->doModel();
        break;
    case ('comments'):
        require_once('controller/CAdminItemComments.php');
        $do = new CAdminItemComments();
        $do->doModel();
        break;
    case ('media'):
        require_once('controller/CAdminMedia.php');
        $do = new CAdminMedia();
        $do->doModel();
        break;
    case ('login'):
        require_once('controller/CAdminLogin.php');
        $do = new CAdminLogin();
        $do->doModel();
        break;
    case ('categories'):
        require_once('controller/CAdminCategories.php');
        $do = new CAdminCategories();
        $do->doModel();
        break;
    case ('emails'):
        require_once('controller/CAdminEmails.php');
        $do = new CAdminEmails();
        $do->doModel();
        break;
    case ('pages'):
        require_once('controller/CAdminPages.php');
        $do = new CAdminPages();
        $do->doModel();
        break;
    case ('settings'):
        require_once('controller/CAdminSettings.php');
        $do = new CAdminSettings();
        $do->doModel();
        break;
    case ('plugins'):
        require_once('controller/CAdminPlugins.php');
        $do = new CAdminPlugins();
        $do->doModel();
        break;
    case ('languages'):
        require_once('controller/CAdminLanguages.php');
        $do = new CAdminLanguages();
        $do->doModel();
        break;
    case ('admins'):
        require_once('controller/CAdminAdmins.php');
        $do = new CAdminAdmins();
        $do->doModel();
        break;
    case ('users'):
        require_once('controller/CAdminUsers.php');
        $do = new CAdminUsers();
        $do->doModel();
        break;
    case ('ajax'):
        require_once('controller/ajax/CAdminAjax.php');
        $do = new CAdminAjax();
        $do->doModel();
        break;
    case ('appearance'):
        require_once('controller/CAdminAppearance.php');
        $do = new CAdminAppearance();
        $do->doModel();
        break;
    case ('tools'):
        require_once('controller/CAdminTools.php');
        $do = new CAdminTools();
        $do->doModel();
        break;
    case ('stats'):
        require_once('controller/CAdminStats.php');
        $do = new CAdminStats();
        $do->doModel();
        break;
    case ('cfields'):
        require_once('controller/CAdminCFields.php');
        $do = new CAdminCFields();
        $do->doModel();
        break;
    case ('upgrade'):
        require_once('controller/CAdminUpgrade.php');
        $do = new CAdminUpgrade();
        $do->doModel();
        break;
    default:            //login of oc-admin
        require_once('controller/CAdminMain.php');
        $do = new CAdminMain();
        $do->doModel();
}

/* file end: ./oc-admin/index.php */
