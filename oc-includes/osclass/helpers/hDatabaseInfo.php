<?php

/*
 *  Copyright 2020 Osclass
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
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
 * Helper Database Info
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

use mindstellar\osclass\classes\utility\Deprecate;

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
