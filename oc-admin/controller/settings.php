<?php

if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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
                require_once('settings/advanced.php');
                $do = new CAdminSettingsAdvanced();
                break;
            case ('comments'):
            case ('comments_post'):
                require_once('settings/comments.php');
                $do = new CAdminSettingsComments();
                break;
            case ('locations'):
                require_once('settings/locations.php');
                $do = new CAdminSettingsLocations();
                break;
            case ('permalinks'):
            case ('permalinks_post'):
                require_once('settings/permalinks.php');
                $do = new CAdminSettingsPermalinks();
                break;
            case ('spamNbots'):
            case ('akismet_post'):
            case ('recaptcha_post'):
                require_once('settings/spamnbots.php');
                $do = new CAdminSettingsSpamnBots();
                break;
            case ('currencies'):
                require_once('settings/currencies.php');
                $do = new CAdminSettingsCurrencies();
                break;
            case ('mailserver'):
            case ('mailserver_post'):
                require_once('settings/mailserver.php');
                $do = new CAdminSettingsMailserver();
                break;
            case ('media'):
            case ('media_post'):
            case ('images_post'):
                require_once('settings/media.php');
                $do = new CAdminSettingsMedia();
                break;
            case ('latestsearches'):
            case ('latestsearches_post'):
                require_once('settings/latestsearches.php');
                $do = new CAdminSettingsLatestSearches();
                break;
            case ('update'):
            case ('check_updates'):
            default:
                require_once('settings/main.php');
                $do = new CAdminSettingsMain();
                break;
        }

        $do->doModel();
    }
}

/* file end: ./oc-admin/settings.php */
