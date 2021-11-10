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
 * Model database for LocationsTmp table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      2.4
 */
class LocationsTmp extends DAO
{
    /**
     * It references to self object: LocationsTmp.
     * It is used as a singleton
     *
     * @access private
     * @since  2.4
     * @var CountryStats
     */
    private static $instance;

    /**
     * Set data related to t_locations_tmp table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_locations_tmp');
        $this->setFields(array('id_location', 'e_type'));
    }

    /**
     * It creates a new LocationsTmp object class if it has been created
     * before, it return the previous object
     *
     * @access public
     * @return LocationsTmp
     * @since  2.4
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param $max
     *
     * @return array
     */
    public function getLocations($max)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->limit($max);
        $rs = $this->dao->get();

        if ($rs === false) {
            return array();
        }

        return $rs->result();
    }

    /**
     * Populate Cities from City Table
     * @return bool
     */
    public function populateCities()
    : bool
    {
        // INSERT IGNORE ...SELECT pk_i_id column from t_city table to t_locations_tmp table
        $cityTableName = City::newInstance()->getTableName();
        $rs = $this->dao->query(sprintf("INSERT IGNORE INTO %s (id_location, e_type) SELECT pk_i_id, 'CITY' FROM %s", $this->getTableName(), $cityTableName));

        return !($rs === false);
    }

    /**
     * populate Regions from Region Table
     * @return bool
     */
    public function populateRegions()
    : bool
    {
        // INSERT IGNORE ...SELECT pk_i_id column from t_region table to t_locations_tmp table
        $regionTableName = Region::newInstance()->getTableName();
        $rs = $this->dao->query(sprintf("INSERT IGNORE INTO %s (id_location, e_type) SELECT pk_i_id, 'REGION' FROM %s", $this->getTableName(), $regionTableName));

        return !($rs === false);
    }

    /**
     * populate Countries from Country Table
     * @return bool
     */
    public function populateCountries()
    : bool
    {
        // INSERT IGNORE ...SELECT pk_c_code column from t_country table to t_locations_tmp table
        $countryTableName = Country::newInstance()->getTableName();
        $rs = $this->dao->query(sprintf("INSERT IGNORE INTO %s (id_location, e_type) SELECT pk_c_code, 'COUNTRY' FROM %s", $this->getTableName(), $countryTableName));

        return !($rs === false);
    }


    /**
     * @param array $where
     *
     * @return bool|int
     */
    public function delete($where)
    {
        return $this->dao->delete($this->getTableName(), $where);
    }

    /**
     * @param $ids
     * @param $type
     *
     * @return bool|\DBRecordsetClass
     */
    public function batchInsert($ids, $type)
    {
        if (!empty($ids)) {
            return $this->dao->query(sprintf(
                "INSERT INTO %s (id_location, e_type) VALUES (%s, '%s')",
                $this->getTableName(),
                implode(",'" . $type . "'),(", $ids),
                $type
            ));
        }

        return false;
    }

    /**
     * Batch Delete Locations
     * @param $ids
     * @param $type
     * @return bool|\DBRecordsetClass
     */
    public function batchDelete($ids, $type)
    {
        if (!empty($ids)) {
            return $this->dao->query(sprintf(
                "DELETE FROM %s WHERE id_location IN (%s) AND e_type = '%s'",
                $this->getTableName(),
                implode(",", $ids),
                $type
            ));
        }

        return false;
    }
}
