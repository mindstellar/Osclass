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
 * AdminToolbar class
 *
 * @since      3.0
 * @package    Osclass
 * @subpackage classes
 * @author     Osclass
 */
class AdminToolbar
{
    private static $instance;
    private $nodes = array();

    public function __construct()
    {
    }

    /**
     * @return \AdminToolbar
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function init()
    {
    }

    /**
     * Add toolbar menus and add menus running hook add_admin_toolbar_menus
     */
    public function add_menus()
    {
        // User related, aligned right.
        //osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_menu', 0);
        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_comments', 0);
        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_spam', 0);

        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_core', 0);

        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_themes', 0);
        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_plugins', 0);
        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_update_languages', 0);

        osc_add_hook('add_admin_toolbar_menus', 'osc_admin_toolbar_logout', 0);

        osc_run_hook('add_admin_toolbar_menus');
    }

    /**
     * Add a node to the menu.
     *
     * @param $array
     *
     * @todo implement parent nodes
     *
     */
    public function add_menu($array)
    {
        if (isset($array['id'])) {
            $this->nodes[$array['id']] = (object)$array;
        }
    }

    /**
     * Add a submenu to the menu.
     *
     * @param $array $args - The arguments for each subitem.
     *               - id         - string    - The ID of the mainitem.
     *               - parentid   - string    - The ID of the parent item.
     *               - title      - string    - The title of the node.
     *               - href       - string    - The link for the item. Optional.
     *               - meta       - array     - Meta data including the following keys: html, class, onclick, target,
     *               title, tabindex.
     *               - target     - string    - _blank
     */
    public function add_submenu($array)
    {
        if (isset($array['parentid'], $array['id'])) {
            $this->nodes[$array['parentid']]->submenu[$array['id']] = (object)$array;
        }
    }

    /**
     * Remove entry with id $id
     *
     * @param string $id
     */
    public function remove_menu($id)
    {
        unset($this->nodes[$id]);
    }

    /**
     * Remove entry with id $id
     *
     * @param string $parentid
     * @param string $id
     */
    public function remove_submenu($parentid, $id)
    {
        if (isset($this->nodes[$parentid], $this->nodes[$parentid]->submenu[$id])) {
            unset($this->nodes[$parentid]->submenu[$id]);
        }
    }

    /**
     * Render admin toolbar
     *
     * <div>
     *   <a></a>
     * </div>
     */
    public function render()
    {
        echo '<nav id="header" class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">';
        echo '<div class="container-fluid">';
        echo '<a id="osc_toolbar_home" class="navbar-brand"  target="_blank" href="' . osc_base_url()
             . '"><i class="bi bi-house-fill"></i> '
             . osc_page_title() . '</a>';
        echo '<ul class="navbar-nav me-right mb-2 mb-md-0">';
        if (count($this->nodes) > 0) {

            foreach ($this->nodes as $value) {
                $meta = '';
                if (isset($value->meta)) {
                    foreach ($value->meta as $k => $v) {
                        if ($k === 'class') {
                            $v = "nav-link " . $v;
                        }
                        $meta .= $k . '="' . $v . '" ';
                    }
                }


                //echo '<a class="navbar-brand" href="'.osc_admin_base_url().'">'.osc_page_title().'</a>';
                echo '<li class="nav-item" id="osc_toolbar_' . $value->id . '" ><a ' . $meta . ' href="' . $value->href . '" '
                     . ((isset($value->target)) ? 'target="' . $value->target . '"' : '') . '>' . $value->title . '</a>';

                if (isset($value->submenu) && is_array($value->submenu)) {
                    echo '<div class="osc_admin_submenu" id="osc_toolbar_sub_' . $value->id . '"><ul>';
                    foreach ($value->submenu as $subvalue) {
                        if (isset($subvalue->subid)) {
                            $submeta = '';
                            if (isset($subvalue->meta)) {
                                foreach ($subvalue->meta as $sk => $sv) {
                                    $submeta .= $sk . '="' . $sv . '" ';
                                }
                            }
                            echo '<li><a ' . $submeta . ' href="' . $subvalue->href . '" ' . ((isset($subvalue->target))
                                    ? 'target="' . $subvalue->target . '"' : '') . '>' . $subvalue->title . '</a><li>';
                        }
                    }

                }
                echo '</li>';
            }
            osc_run_hook('render_admintoolbar');
        }
        echo '</ul></div></nav>';
    }
}
