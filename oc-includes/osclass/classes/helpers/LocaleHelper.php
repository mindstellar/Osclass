<?php
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

namespace mindstellar\osclass\classes\helpers;

use OSCLocale;
use Session;
use View;

/**
 * Helper Locales
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */
class LocaleHelper
{
    /**
     * Gets locale generic field
     *
     * @param $field
     * @param $locale
     *
     * @return string
     */
    public static function osc_locale_field($field, $locale = '')
    {
        return osc_field(osc_locale(), $field, $locale);
    }


    /**
     * Gets locale object
     *
     * @return array
     */
    public static function osc_locale()
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
    public static function osc_get_locales()
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
    public static function osc_priv_count_locales()
    {
        return View::newInstance()->_count('locales');
    }


    /**
     * Reset iterator of locales
     *
     * @return void
     */
    public static function osc_goto_first_locale()
    {
        View::newInstance()->_reset('locales');
    }


    /**
     * Gets number of enabled locales for website
     *
     * @return int
     */
    public static function osc_count_web_enabled_locales()
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
    public static function osc_has_web_enabled_locales()
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
    public static function osc_locale_code()
    {
        return osc_locale_field('pk_c_code');
    }


    /**
     * Gets current locale's name
     *
     * @return string
     */
    public static function osc_locale_name()
    {
        return osc_locale_field('s_name');
    }


    /**
     * Gets current locale's currency format
     *
     * @return string
     */
    public static function osc_locale_currency_format()
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
    public static function osc_locale_dec_point()
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
    public static function osc_locale_thousands_sep()
    {
        $aLocales = osc_get_locales();
        $cLocale  = $aLocales[0];

        foreach ($aLocales as $locale) {
            if ($locale['pk_c_code'] == osc_current_user_locale()) {
                $cLocale = $locale;
                break;
            }
        }

        return $cLocale['s_thousands_sep'];
    }


    /**
     * Gets current locale's number of decimals
     *
     * @return string
     */
    public static function osc_locale_num_dec()
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
     * Gets list of enabled locales
     *
     * @param bool $indexed_by_pk
     *
     * @return array
     */
    public static function osc_all_enabled_locales_for_admin($indexed_by_pk = false)
    {
        return OSCLocale::newInstance()->listAllEnabled(true, $indexed_by_pk);
    }


    /**
     * Gets current locale object
     *
     * @return array
     */
    public static function osc_get_current_user_locale()
    {
        $locale = OSCLocale::newInstance()->findByPrimaryKey(osc_current_user_locale());
        View::newInstance()->_exportVariableToView('locale', $locale);

        return $locale;
    }


    /**
     * Get the actual locale of the user.
     *
     * You get the right locale code. If an user is using the website in another language different of the default one,
     * or the user uses the default one, you'll get it.
     *
     * @return string Locale Code
     */
    public static function osc_current_user_locale()
    {
        if (Session::newInstance()->_get('userLocale') != '') {
            return Session::newInstance()->_get('userLocale');
        }

        return osc_language();
    }


    /**
     * Get the actual locale of the admin.
     *
     * You get the right locale code. If an admin is using the website in another language different of the default
     * one, or the admin uses the default one, you'll get it.
     *
     * @return string OSCLocale Code
     */
    public static function osc_current_admin_locale()
    {
        if (Session::newInstance()->_get('adminLocale') != '') {
            return Session::newInstance()->_get('adminLocale');
        }

        return osc_admin_language();
    }
}
