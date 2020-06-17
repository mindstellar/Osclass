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

define('OC_ADMIN', true);

require_once dirname(__DIR__) . '/oc-load.php';

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
osc_enqueue_style('jquery-ui', osc_assets_url('css/jquery-ui/jquery-ui.min.css'));
osc_enqueue_style('admin-css', osc_current_admin_theme_styles_url('main.css'));
osc_enqueue_style('fontawesome5', osc_assets_url('fonts/fontawesome5/css/all.min.css'));

switch (Params::getParam('page')) {
    case ('items'):
        $do = new CAdminItems();
        $do->doModel();
        break;
    case ('comments'):
        $do = new CAdminItemComments();
        $do->doModel();
        break;
    case ('media'):
        $do = new CAdminMedia();
        $do->doModel();
        break;
    case ('login'):
        $do = new CAdminLogin();
        $do->doModel();
        break;
    case ('categories'):
        $do = new CAdminCategories();
        $do->doModel();
        break;
    case ('emails'):
        $do = new CAdminEmails();
        $do->doModel();
        break;
    case ('pages'):
        $do = new CAdminPages();
        $do->doModel();
        break;
    case ('settings'):
        $do = new CAdminSettings();
        $do->doModel();
        break;
    case ('plugins'):
        $do = new CAdminPlugins();
        $do->doModel();
        break;
    case ('languages'):
        $do = new CAdminLanguages();
        $do->doModel();
        break;
    case ('admins'):
        $do = new CAdminAdmins();
        $do->doModel();
        break;
    case ('users'):
        $do = new CAdminUsers();
        $do->doModel();
        break;
    case ('ajax'):
        $do = new CAdminAjax();
        $do->doModel();
        break;
    case ('appearance'):
        $do = new CAdminAppearance();
        $do->doModel();
        break;
    case ('tools'):
        $do = new CAdminTools();
        $do->doModel();
        break;
    case ('stats'):
        $do = new CAdminStats();
        $do->doModel();
        break;
    case ('cfields'):
        $do = new CAdminCFields();
        $do->doModel();
        break;
    case ('upgrade'):
        $do = new CAdminUpgrade();
        $do->doModel();
        break;
    default:            //login of oc-admin
        $do = new CAdminMain();
        $do->doModel();
}

/* file end: ./oc-admin/index.php */
