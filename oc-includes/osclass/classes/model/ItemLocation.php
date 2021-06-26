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
 * Model database for ItemLocation table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class ItemLocation extends DAO
{
    /**
     * It references to self object: ItemLocation.
     * It is used as a singleton
     *
     * @access private
     * @since  unknown
     * @var ItemResource
     */
    private static $instance;

    /**
     * Set data related to t_item_location table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_item_location');
        $this->setPrimaryKey('fk_i_item_id');
        $array_fields = array(
            'fk_i_item_id',
            'fk_c_country_code',
            's_country',
            's_address',
            's_zip',
            'fk_i_region_id',
            's_region',
            'fk_i_city_id',
            's_city',
            'fk_i_city_area_id',
            's_city_area',
            'd_coord_lat',
            'd_coord_long'
        );
        $this->setFields($array_fields);
    }

    /**
     * It creates a new ItemLocation object class ir if it has been created
     * before, it return the previous object
     *
     * @access public
     * @return ItemLocation
     * @since  unknown
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}

/* file end: ./oc-includes/osclass/model/ItemLocation.php */
