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
 * Class CAdminSettings
 */
class CAdminSettings extends AdminSecBaseModel
{

    public function __construct()
    {
        $this->setParams();
        $this->init();
        osc_run_hook('init_admin_settings');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('advanced'):
            case ('advanced_post'):
            case ('advanced_cache_flush'):
                require_once('settings/CAdminSettingsAdvanced.php');
                $do = new CAdminSettingsAdvanced();
                break;
            case ('comments'):
            case ('comments_post'):
                require_once('settings/CAdminSettingsComments.php');
                $do = new CAdminSettingsComments();
                break;
            case ('locations'):
                require_once('settings/CAdminSettingsLocations.php');
                $do = new CAdminSettingsLocations();
                break;
            case ('permalinks'):
            case ('permalinks_post'):
                require_once('settings/CAdminSettingsPermalinks.php');
                $do = new CAdminSettingsPermalinks();
                break;
            case ('spamNbots'):
            case ('akismet_post'):
            case ('recaptcha_post'):
                require_once('settings/CAdminSettingsSpamnBots.php');
                $do = new CAdminSettingsSpamnBots();
                break;
            case ('currencies'):
                require_once('settings/CAdminSettingsCurrencies.php');
                $do = new CAdminSettingsCurrencies();
                break;
            case ('mailserver'):
            case ('mailserver_post'):
                require_once('settings/CAdminSettingsMailserver.php');
                $do = new CAdminSettingsMailserver();
                break;
            case ('media'):
            case ('media_post'):
            case ('images_post'):
                require_once('settings/CAdminSettingsMedia.php');
                $do = new CAdminSettingsMedia();
                break;
            case ('latestsearches'):
            case ('latestsearches_post'):
                require_once('settings/CAdminSettingsLatestSearches.php');
                $do = new CAdminSettingsLatestSearches();
                break;
            case ('update'):
            case ('check_updates'):
            default:
                require_once('settings/CAdminSettingsMain.php');
                $do = new CAdminSettingsMain();
                break;
        }

        $do->doModel();
    }
}

/* file end: ./oc-admin/CAdminSettings.php */
