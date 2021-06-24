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
 * Model database for CityArea table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class CityArea extends DAO
{
    /**
     * It references to self object: CityArea.
     * It is used as a singleton
     *
     * @access private
     * @since  unknown
     * @var CityArea
     */
    private static $instance;

    /**
     * Set data related to t_city_area table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_city_area');
        $this->setPrimaryKey('pk_i_id');
        $this->setFields(array('pk_i_id', 'fk_i_city_id', 's_name'));
    }

    /**
     * It creates a new CityArea object class ir if it has been created
     * before, it return the previous object
     *
     * @access public
     * @return CityArea
     * @since  unknown
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get the cityArea by its name and city
     *
     * @access public
     *
     * @param     $cityAreaName
     * @param int $cityId
     *
     * @return array
     * @since  unknown
     *
     */
    public function findByName($cityAreaName, $cityId = null)
    {
        $this->dao->select($this->getFields());
        $this->dao->from($this->getTableName());
        $this->dao->where('s_name', $cityAreaName);
        $this->dao->limit(1);
        if ($cityId != null) {
            $this->dao->where('fk_i_city_id', $cityId);
        }

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->row();
    }

    /**
     * Return city areas of a given city ID
     *
     * @access public
     *
     * @param $cityId
     *
     * @return array
     * @since  2.4
     */
    public function findByCity($cityId)
    {
        $this->dao->select($this->getFields());
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_city_id', $cityId);

        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     *  Delete a city area
     *
     * @access public
     *
     * @param $pk
     *
     * @return int number of failed deletions or 0 in case of none
     * @since  3.1
     *
     */
    public function deleteByPrimaryKey($pk)
    {
        Item::newInstance()->deleteByCityArea($pk);
        User::newInstance()->update(
            array('fk_i_city_area_id' => null, 's_city_area' => ''),
            array('fk_i_city_area_id' => $pk)
        );
        if (!$this->delete(array('pk_i_id' => $pk))) {
            return 1;
        }

        return 0;
    }
}

/* file end: ./oc-includes/osclass/model/CityArea.php */
