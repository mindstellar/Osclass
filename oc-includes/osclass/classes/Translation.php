<?php use Gettext\Translator;

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
 * Class Translation
 */
class Translation
{
    private static $instance;
    private $translator;

    /**
     * Translation constructor.
     *
     * @param bool $install
     */
    public function __construct($install = false)
    {
        $this->translator = new Translator();
        if (!$install) {
            // get user/admin locale
            if (defined(OC_ADMIN) && OC_ADMIN) {
                $locale = osc_current_admin_locale();
            } else {
                $locale = osc_current_user_locale();
            }

            // load core
            $core_file = osc_apply_filter('mo_core_path', osc_translations_path() . $locale . '/core.mo', $locale);
            $this->_load($core_file, 'core');

            // load messages
            $domain        = osc_apply_filter('theme', osc_theme());
            $messages_file = osc_apply_filter(
                'mo_theme_messages_path',
                osc_themes_path() . $domain . '/languages/' . $locale . '/messages.mo',
                $locale,
                $domain
            );

            if (!file_exists($messages_file)) {
                $messages_file =
                    osc_apply_filter(
                        'mo_core_messages_path',
                        osc_translations_path() . $locale . '/messages.mo',
                        $locale
                    );
            }
            $this->_load($messages_file, 'messages');

            // load theme
            $theme_file =
                osc_apply_filter(
                    'mo_theme_path',
                    osc_themes_path() . $domain . '/languages/' . $locale . '/theme.mo',
                    $locale,
                    $domain
                );
            if (!file_exists($theme_file)) {
                if (!file_exists(osc_themes_path() . $domain)) {
                    $domain = osc_theme();
                }
                $theme_file = osc_translations_path() . $locale . '/theme.mo';
            }
            $this->_load($theme_file, $domain);

            // load plugins
            $aPlugins = Plugins::listEnabled();
            foreach ($aPlugins as $plugin) {
                $domain      = preg_replace('|/.*|', '', $plugin);
                $plugin_file = osc_apply_filter(
                    'mo_plugin_path',
                    osc_plugins_path() . $domain . '/languages/' . $locale . '/messages.mo',
                    $locale,
                    $domain
                );
                if (file_exists($plugin_file)) {
                    $this->_load($plugin_file, $domain);
                }
            }
        } else {
            $core_file = osc_translations_path() . osc_current_admin_locale() . '/core.mo';
            $this->_load($core_file, 'core');
        }
    }

    /**
     * @param $file
     * @param $domain
     *
     * @return bool|\Translation
     */
    public function _load($file, $domain)
    {
        if (!file_exists($file)) {
            return false;
        }
        //Create a Translations instance using a po file
        $translations = Gettext\Translations::fromMoFile($file);

        $translations->addFromMoFile($file);
        $translations->setDomain($domain);

        $this->translator->loadTranslations($translations);

        return $this;
    }

    /**
     * @param bool $install
     *
     * @return \Translation
     */
    public static function newInstance($install = false)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($install);
        }

        return self::$instance;
    }

    /**
     * @return \Translation
     */
    public static function init()
    {
        self::$instance = new self();

        return self::$instance;
    }

    /**
     * @return \Gettext\Translator
     */
    public function _get()
    {
        return $this->translator;
    }
}
