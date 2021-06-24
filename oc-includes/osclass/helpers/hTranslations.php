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
 * Helper Translation
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

/**
 * Translate strings
 *
 * @param string $key
 * @param string $domain
 *
 * @return string
 * @since unknown
 *
 */
function __($key, $domain = 'core')
{
    $gt     = Translation::newInstance()->_get();
    $string = $gt->dgettext($domain, $key);

    return osc_apply_filter('gettext', $string);
}


/**
 * Translate strings and echo them
 *
 * @param string $key
 * @param string $domain
 *
 * @since unknown
 *
 */
function _e($key, $domain = 'core')
{
    echo __($key, $domain);
}


/**
 * Translate string (flash messages)
 *
 * @param string $key
 *
 * @return string
 * @since unknown
 *
 */
function _m($key)
{
    return __($key, 'messages');
}


/**
 * Retrieve the singular or plural translation of the string.
 *
 * @param string $single_key
 * @param string $plural_key
 * @param int    $count
 * @param string $domain
 *
 * @return string
 * @since 2.2
 *
 */
function _n($single_key, $plural_key, $count, $domain = 'core')
{
    $gt     = Translation::newInstance()->_get();
    $string = $gt->dngettext($domain, $single_key, $plural_key, $count);

    return osc_apply_filter('ngettext', $string);
}


/**
 * Retrieve the singular or plural translation of the string.
 *
 * @param string $single_key
 * @param string $plural_key
 * @param int    $count
 *
 * @return string
 * @since 2.2
 *
 */
function _mn($single_key, $plural_key, $count)
{
    return _n($single_key, $plural_key, $count, 'messages');
}

/* file end: ./oc-includes/osclass/helpers/hTranslations.php */
