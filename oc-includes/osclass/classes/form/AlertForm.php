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

/**
 * Class AlertForm
 */
class AlertForm extends Form
{

    /**
     * @return bool
     */
    public static function user_id_hidden()
    {
        parent::generic_input_hidden('alert_userId', osc_logged_user_id());

        return true;
    }

    /**
     * @return bool
     */
    public static function email_hidden()
    {
        parent::generic_input_hidden('alert_email', osc_logged_user_email());

        return true;
    }

    /**
     * @return bool
     */
    public static function email_text()
    {
        $value = '';
        if (osc_logged_user_email() == '') {
            $value = self::default_email_text();
        }
        parent::generic_input_text('alert_email', $value);

        return true;
    }

    /**
     * @return string
     */
    public static function default_email_text()
    {
        return __('Enter your e-mail');
    }

    /**
     * @return bool
     */
    public static function page_hidden()
    {
        parent::generic_input_hidden('page', 'search');

        return true;
    }

    /**
     * @return bool
     */
    public static function alert_hidden()
    {
        parent::generic_input_hidden('alert', osc_search_alert());

        return true;
    }
}
