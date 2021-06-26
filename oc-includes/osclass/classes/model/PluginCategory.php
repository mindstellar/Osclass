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
 *
 */
class PluginCategory extends DAO
{
    /**
     *
     * @var
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_plugin_category');
        /* $this->setPrimaryKey('pk_i_id'); */
        $this->setFields(array('s_plugin_name', 'fk_i_category_id'));
    }

    /**
     *
     * @return \PluginCategory
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Return all information given a category id
     *
     * @access public
     *
     * @param $categoryId
     *
     * @return array
     * @since  unknown
     *
     */
    public function findByCategoryId($categoryId)
    {
        $this->dao->select($this->getFields());
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_category_id', $categoryId);

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     * Return list of categories asociated with a plugin
     *
     * @access public
     *
     * @param string $plugin
     *
     * @return array
     * @since  unknown
     */
    public function listSelected($plugin)
    {
        $this->dao->select($this->getFields());
        $this->dao->from($this->getTableName());
        $this->dao->where('s_plugin_name', $plugin);

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        $list = array();
        foreach ($result->result() as $sel) {
            $list[] = $sel['fk_i_category_id'];
        }

        return $list;
    }

    /**
     * Check if a category is asociated with a plugin
     *
     * @access public
     *
     * @param string $pluginName
     * @param int    $categoryId
     *
     * @return bool
     * @since  unknown
     */
    public function isThisCategory($pluginName, $categoryId)
    {
        $this->dao->select('COUNT(*) AS numrows');
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_category_id', $categoryId);
        $this->dao->where('s_plugin_name', $pluginName);

        $result = $this->dao->get();

        if ($result == false) {
            return false;
        }

        if ($result->numRows() == 0) {
            return false;
        }

        $row = $result->row();

        return !($row['numrows'] == 0);
    }
}

/* file end: ./oc-includes/osclass/model/PluginCategory.php */
