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
 * Class Widget
 */
class Widget extends DAO
{
    /**
     *
     * @var \Widget
     */
    private static $instance;

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_widget');
        $this->setPrimaryKey('pk_i_id');
        $this->setFields(array('pk_i_id', 's_description', 's_location', 'e_kind', 's_content'));
    }

    /**
     * @return \Widget
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     *
     * @access public
     *
     * @param string $location
     *
     * @return array
     * @since  unknown
     */
    public function findByLocation($location)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_location', $location);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     *
     * @access public
     *
     * @param string $description
     *
     * @return array
     * @since  3.3.3+
     */
    public function findByDescription($description)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_description', $description);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }
}

/* file end: ./oc-includes/osclass/model/Widget.php */
