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
 * @param     $key
 * @param     $data
 * @param int $expire
 *
 * @return bool
 */
function osc_cache_add($key, $data, $expire = 0)
{
    $key .= osc_current_user_locale();

    return Object_Cache_Factory::newInstance()->add($key, $data, $expire);
}


/**
 * @return mixed
 */
function osc_cache_close()
{
    return Object_Cache_Factory::newInstance()->close();
}


/**
 * @param $key
 *
 * @return bool
 */
function osc_cache_delete($key)
{
    $key .= osc_current_user_locale();

    return Object_Cache_Factory::newInstance()->delete($key);
}


/**
 * @return bool
 */
function osc_cache_flush()
{
    return Object_Cache_Factory::newInstance()->flush();
}


/**
 * Initialize Cache factory instance using singleton
 */
function osc_cache_init()
{
    Object_Cache_Factory::newInstance();
}


/**
 * @param $key
 * @param $found
 *
 * @return bool|mixed
 */
function osc_cache_get($key, &$found)
{
    $key .= osc_current_user_locale();

    return Object_Cache_Factory::newInstance()->get($key, $found);
}


/**
 * @param     $key
 * @param     $data
 * @param int $expire
 *
 * @return bool
 */
function osc_cache_set($key, $data, $expire = 0)
{
    $key .= osc_current_user_locale();

    return Object_Cache_Factory::newInstance()->set($key, $data, $expire);
}
