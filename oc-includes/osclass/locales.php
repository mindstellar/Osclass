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
 * @return array
 */
function osc_listLocales()
{
    $languages = array();

    $codes = osc_listLanguageCodes();
    foreach ($codes as $code) {
        if (file_exists(osc_translations_path().$code.'/locale.json')) {
            $aInfo = json_decode(file_get_contents(osc_translations_path().$code.'/locale.json'), true);
            $languages[$code] = $aInfo;
            unset($aInfo);
        } else {
            $path   = osc_translations_path() . $code . '/index.php';
            $fxName = "locale_{$code}_info";
            if (file_exists($path)) {
                require_once $path;
                if (function_exists($fxName)) {
                    $languages[$code]                = $fxName();
                    $languages[$code]['locale_code'] = $code;
                }
            }
        }
    }

    return $languages;
}

/**
 * @return bool
 */
function osc_checkLocales()
{
    $locales = osc_listLocales();

    foreach ($locales as $locale) {
        // if it's a demo, we don't import any data
        if (defined('DEMO')) {
            return true;
        }

        $data = OSCLocale::newInstance()->findByPrimaryKey($locale['locale_code']);
        if (!is_array($data)) {
            $result = OSCLocale::newInstance()->insertLocaleInfo($locale);

            if ($result === false) {
                return false;
            }

            // if it's a demo, we don't import any sql
            if (defined('DEMO')) {
                return true;
            }

            // inserting e-mail translations
            if (file_exists(osc_translations_path() . $locale['locale_code'] . '/mail.json' )) {
                $mailJson = file_get_contents(osc_translations_path() . $locale['locale_code'] . '/mail.json' );
                if ($mailJson) {
                    Page::newInstance()->importEmailJsonTemplates($mailJson);
                }
            } else {
                // old templates
                $path = osc_translations_path() . $locale['locale_code'] . '/mail.sql';
                if (file_exists($path)) {
                    $sql    = file_get_contents($path);
                    $conn   = DBConnectionClass::newInstance();
                    $c_db   = $conn->getOsclassDb();
                    $comm   = new DBCommandClass($c_db);
                    $result = $comm->importSQL($sql);
                    if (!$result) {
                        return false;
                    }
                }
            }
        } else {
            OSCLocale::newInstance()->insertLocaleInfo($locale);
        }
    }

    return true;
}


/**
 * @return array
 */
function osc_listLanguageCodes()
{
    $codes = array();

    $dir = opendir(osc_translations_path());
    while ($file = readdir($dir)) {
        if (preg_match('/^[a-z_]+$/i', $file)) {
            $codes[] = $file;
        }
    }
    closedir($dir);

    return $codes;
}
