<?php

if (!defined('ABS_PATH')) {
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
 * Class CAdminSettingsAdvanced
 */
class CAdminSettingsAdvanced extends AdminSecBaseModel
{

    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_admin_settings_advanced');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('advanced'):
                //calling the advanced settings view
                $this->doView('settings/advanced.php');
                break;
            case ('advanced_post'):
                // updating advanced settings
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
                    $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
                }
                osc_csrf_check();
                $subdomain_type = Params::getParam('e_type');
                if (!in_array($subdomain_type, array('category', 'country', 'region', 'city', 'user'))) {
                    $subdomain_type = '';
                }
                $iUpdated = osc_set_preference('subdomain_type', $subdomain_type);
                $iUpdated += osc_set_preference('subdomain_host', Params::getParam('s_host'));

                if ($iUpdated > 0) {
                    osc_add_flash_ok_message(_m('Advanced settings have been updated'), 'admin');
                }
                osc_calculate_location_slug(osc_subdomain_type());
                $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
                break;
            case ('advanced_cache_flush'):
                osc_cache_flush();
                osc_add_flash_ok_message(_m('Cache flushed correctly'), 'admin');
                $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
                break;
        }
    }
}

// EOF: ./oc-admin/controller/settings/CAdminSettingsMain.php
