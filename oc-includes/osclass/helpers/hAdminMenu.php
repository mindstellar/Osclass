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

/**
 * Helper Menu Admin
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

use mindstellar\osclass\classes\helpers\AdminMenuHelper;

/**
 * Draws menu with sections and subsections
 */
function osc_draw_admin_menu()
{
    AdminMenuHelper::osc_draw_admin_menu();
}


/**
 * Add menu entry
 *
 * @param        $menu_title
 * @param        $url
 * @param        $menu_id
 * @param string $capability
 * @param null   $icon_url
 * @param null   $position
 */
function osc_add_admin_menu_page(
    $menu_title,
    $url,
    $menu_id,
    $capability = 'administrator',
    $icon_url = null,
    $position = null
) {
    AdminMenuHelper::osc_add_admin_menu_page($menu_title, $url, $menu_id, $capability, $icon_url, $position);
}


/**
 * Remove the whole menu
 */
function osc_remove_admin_menu()
{
    AdminMenuHelper::osc_remove_admin_menu();
}


/**
 * Remove menu section with id $id_menu
 *
 * @param $menu_id
 */
function osc_remove_admin_menu_page($menu_id)
{
    AdminMenuHelper::osc_remove_admin_menu_page($menu_id);
}


/**
 * Add submenu under menu id $id_menu, with $array information
 *
 * @param        $menu_id
 * @param        $submenu_title
 * @param        $url
 * @param        $submenu_id
 * @param string $capability
 */
function osc_add_admin_submenu_page($menu_id, $submenu_title, $url, $submenu_id, $capability = 'administrator')
{
    AdminMenuHelper::osc_add_admin_submenu_page($menu_id, $submenu_title, $url, $submenu_id, $capability);
}


/**
 * Remove submenu with id $id_submenu under menu id $id_menu
 *
 * @param $menu_id
 * @param $submenu_id
 */
function osc_remove_admin_submenu_page($menu_id, $submenu_id)
{
    AdminMenuHelper::osc_remove_admin_submenu_page($menu_id, $submenu_id);
}


/**
 * Add submenu divider under menu id $id_menu, with $array information
 *
 * @param      $menu_id
 * @param      $submenu_title
 * @param      $submenu_id
 * @param null $capability
 *
 * @since 3.1
 */
function osc_add_admin_submenu_divider($menu_id, $submenu_title, $submenu_id, $capability = null)
{
    AdminMenuHelper::osc_add_admin_submenu_divider($menu_id, $submenu_title, $submenu_id, $capability);
}


/**
 * Remove submenu divider with id $id_submenu under menu id $id_menu
 *
 * @param $menu_id
 * @param $submenu_id
 *
 * @since 3.1
 */
function osc_remove_admin_submenu_divider($menu_id, $submenu_id)
{
    AdminMenuHelper::osc_remove_admin_submenu_divider($menu_id, $submenu_id);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_items($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_items($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_categories($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_categories($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_pages($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_pages($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_appearance($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_appearance($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_plugins($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_plugins($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_settings($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_settings($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_tools($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_tools($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_users($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_users($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * Add submenu into items menu page
 *
 * @param      $submenu_title
 * @param      $url
 * @param      $submenu_id
 * @param null $capability
 * @param null $icon_url
 */
function osc_admin_menu_stats($submenu_title, $url, $submenu_id, $capability = null, $icon_url = null)
{
    AdminMenuHelper::osc_admin_menu_stats($submenu_title, $url, $submenu_id, $capability, $icon_url);
}


/**
 * @return string
 */
function osc_current_menu()
{
    return AdminMenuHelper::osc_current_menu();
}
