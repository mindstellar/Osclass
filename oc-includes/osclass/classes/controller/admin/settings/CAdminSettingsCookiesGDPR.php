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
 * Class CAdminSettingsCookiesGDPR
 */
class CAdminSettingsCookiesGDPR extends AdminSecBaseModel
{

    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_admin_settings_cookiesgdpr');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('cookiesgdpr'):
                // calling the settings view
                $this->_exportVariableToView('pages', Page::newInstance()->listAll(false));
                $this->doView('settings/cookiesgdpr.php');
                break;
            case ('cookiesgdpr_post'):
                // updating settings
                osc_csrf_check();

                $iUpdated = 0;
                $iUpdated += osc_set_preference('cookie_consent_enabled', (bool) Params::getParam('cookie_consent_enabled'));
                $iUpdated += osc_set_preference('cookie_consent_nonmandatory', (bool) Params::getParam('cookie_consent_nonmandatory'));
                $iUpdated += osc_set_preference('cookie_consent_url', trim(strip_tags(Params::getParam('cookie_consent_url'))));
                $iUpdated += osc_set_preference('gdpr_delete_enabled', (bool) Params::getParam('gdpr_delete_enabled'));
                $iUpdated += osc_set_preference('gdpr_download_enabled', (bool) Params::getParam('gdpr_download_enabled'));
                $iUpdated += osc_set_preference('gdpr_checkboxes_enabled', (bool) Params::getParam('gdpr_checkboxes_enabled'));
                $iUpdated += osc_set_preference('gdpr_terms_page', (int) Params::getParam('gdpr_terms_page'));
                $iUpdated += osc_set_preference('gdpr_privacy_page', (int) Params::getParam('gdpr_privacy_page'));
                
                if ($iUpdated > 0) {
                    osc_add_flash_ok_message(_m('Cookie & GDPR settings have been updated'), 'admin');
                }
                $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=cookiesgdpr');
                break;
        }
    }
}

// EOF: ./oc-admin/controller/settings/CAdminSettingsCookiesGDPR.php
