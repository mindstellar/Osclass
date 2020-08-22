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
