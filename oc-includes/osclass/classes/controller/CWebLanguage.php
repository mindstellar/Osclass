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

use mindstellar\utility\Validate;

/**
 * Class CWebLanguage
 */
class CWebLanguage extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_language');
    }

    // business layer...
    public function doModel()
    {
        $locale = Params::getParam('locale');
        if ($locale && (new Validate())->localeCode($locale)) {
            Session::newInstance()->_set('userLocale', $locale);
        }

        $redirect_url = '';
        if (Params::getServerParam('HTTP_REFERER', false, false)) {
            $redirect_url = Params::getServerParam('HTTP_REFERER', false, false);
        } else {
            $redirect_url = osc_base_url(true);
        }

        $this->redirectTo($redirect_url);
    }

    // hopefully generic...

    /**
     * @param $file
     *
     * @return void
     */
    public function doView($file)
    {
    }
}

/* file end: ./CWebLanguage.php */
