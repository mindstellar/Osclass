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
 * Helper Database Info
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

use mindstellar\utility\Deprecate;

/**
 * Gets database name
 *
 * @return string
 * @deprecated 4.0.0
 */
function osc_db_name()
{
    Deprecate::deprecatedFunction(__FUNCTION__, '4.0.0', 'DB_NAME constant');

    return DB_NAME;
}


/**
 * Gets database host
 *
 * @return string
 * @deprecated 4.0.0
 */
function osc_db_host()
{
    Deprecate::deprecatedFunction(__FUNCTION__, '4.0.0', 'DB_HOST constant');

    return DB_HOST;
}


/**
 * Gets database user
 *
 * @return string
 * @deprecated 4.0.0
 */
function osc_db_user()
{
    Deprecate::deprecatedFunction(__FUNCTION__, '4.0.0', 'DB_USER constant');

    return DB_USER;
}


/**
 * Gets database password
 *
 * @return string
 * @deprecated 4.0.0
 */
function osc_db_password()
{
    Deprecate::deprecatedFunction(__FUNCTION__, '4.0.0', 'DB_PASSWORD constant');

    return DB_PASSWORD;
}
