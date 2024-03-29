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
 * Helper Locales
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

/**
 * Gets locale generic field
 *
 * @param $field
 * @param $locale
 *
 * @return string
 */
function osc_locale_field($field, $locale = '')
{
    return osc_field(osc_locale(), $field, $locale);
}


/**
 * Gets locale object
 *
 * @return array
 */
function osc_locale()
{
    $locale = null;
    if (View::newInstance()->_exists('locales')) {
        $locale = View::newInstance()->_current('locales');
    } elseif (View::newInstance()->_exists('locale')) {
        $locale = View::newInstance()->_get('locale');
    }

    return $locale;
}


/**
 * Gets list of locales
 *
 * @return array
 */
function osc_get_locales()
{
    if (!View::newInstance()->_exists('locales')) {
        $locale = OSCLocale::newInstance()->listAllEnabled();
        View::newInstance()->_exportVariableToView('locales', $locale);
    } else {
        $locale = View::newInstance()->_get('locales');
    }

    return $locale;
}


/**
 * Private function to count locales
 *
 * @return boolean
 */
function osc_priv_count_locales()
{
    return View::newInstance()->_count('locales');
}


/**
 * Reset iterator of locales
 *
 * @return void
 */
function osc_goto_first_locale()
{
    View::newInstance()->_reset('locales');
}


/**
 * Gets number of enabled locales for website
 *
 * @return int
 */
function osc_count_web_enabled_locales()
{
    if (!View::newInstance()->_exists('locales')) {
        View::newInstance()->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());
    }

    return osc_priv_count_locales();
}


/**
 * Iterator for enabled locales for website
 *
 * @return bool
 */
function osc_has_web_enabled_locales()
{
    if (!View::newInstance()->_exists('locales')) {
        View::newInstance()->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());
    }

    return View::newInstance()->_next('locales');
}


/**
 * Gets current locale's code
 *
 * @return string
 */
function osc_locale_code()
{
    return osc_locale_field('pk_c_code');
}


/**
 * Gets current locale's name
 *
 * @return string
 */
function osc_locale_name()
{
    return osc_locale_field('s_name');
}


/**
 * Gets current locale's currency format
 *
 * @return string
 */
function osc_locale_currency_format()
{
    $aLocales = osc_get_locales();
    $cLocale  = $aLocales[0];

    foreach ($aLocales as $locale) {
        if ($locale['pk_c_code'] == osc_current_user_locale()) {
            $cLocale = $locale;
            break;
        }
    }

    return $cLocale['s_currency_format'];
}


/**
 * Gets current locale's decimal point
 *
 * @return string
 */
function osc_locale_dec_point()
{
    $aLocales = osc_get_locales();
    $cLocale  = $aLocales[0];

    foreach ($aLocales as $locale) {
        if ($locale['pk_c_code'] == osc_current_user_locale()) {
            $cLocale = $locale;
            break;
        }
    }

    return $cLocale['s_dec_point'];
}


/**
 * Gets current locale's thousands separator
 *
 * @return string
 */
function osc_locale_thousands_sep()
{
    $aLocales = osc_get_locales();
    $cLocale  = $aLocales[0];

    foreach ($aLocales as $locale) {
        if ($locale['pk_c_code'] === osc_current_user_locale()) {
            $cLocale = $locale;
            break;
        }
    }

    return $cLocale['s_thousands_sep'];
}

/**
 * Gets current locale's test direction
 *
 * @return string
 */
function osc_locale_text_direction()
{
    $aLocales = osc_get_locales();
    $cLocale  = $aLocales[0];
    
    foreach ($aLocales as $locale) {
        if ($locale['pk_c_code'] === osc_current_user_locale()) {
            $cLocale = $locale;
            break;
        }
    }

    return $cLocale['s_direction'];
}


/**
 * Gets current locale's number of decimals
 *
 * @return string
 */
function osc_locale_num_dec()
{
    $aLocales = osc_get_locales();
    $cLocale  = $aLocales[0];

    foreach ($aLocales as $locale) {
        if ($locale['pk_c_code'] == osc_current_user_locale()) {
            $cLocale = $locale;
            break;
        }
    }

    return $cLocale['i_num_dec'];
}
/**
 * Gets list of enabled admin locales
 *
 * @return array
 * @since 4.0.0
 */
function osc_get_admin_locales()
{
    if (!View::newInstance()->_exists('adminLocales')) {
        $locale = OSCLocale::newInstance()->listAllEnabled(true);
        View::newInstance()->_exportVariableToView('adminLocales', $locale);
    } else {
        $locale = View::newInstance()->_get('adminLocales');
    }

    return $locale;
}

/**
 * Gets list of enabled locales
 *
 * @param bool $indexed_by_pk
 *
 * @return array
 */
function osc_all_enabled_locales_for_admin($indexed_by_pk = false)
{
    return OSCLocale::newInstance()->listAllEnabled(true, $indexed_by_pk);
}


/**
 * Gets current locale object
 *
 * @return array
 */
function osc_get_current_user_locale()
{
    $locale = OSCLocale::newInstance()->findByPrimaryKey(osc_current_user_locale());
    View::newInstance()->_exportVariableToView('locale', $locale);

    return $locale;
}


/**
 * Get the actual locale of the user.
 *
 * You get the right locale code. If an user is using the website in another language different of the default one, or
 * the user uses the default one, you'll get it.
 *
 * @return string Locale Code
 */
function osc_current_user_locale()
{
    if (Session::newInstance()->_get('userLocale') != '') {
        return Session::newInstance()->_get('userLocale');
    }

    return osc_language();
}


/**
 * Get the actual locale of the admin.
 *
 * You get the right locale code. If an admin is using the website in another language different of the default one, or
 * the admin uses the default one, you'll get it.
 *
 * @return string OSCLocale Code
 */
function osc_current_admin_locale()
{
    if (Session::newInstance()->_get('adminLocale') != '') {
        return Session::newInstance()->_get('adminLocale');
    }

    return osc_admin_language();
}
