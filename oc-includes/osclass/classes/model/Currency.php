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
 * Model database for Currency table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class Currency extends DAO
{
    /**
     * It references to self object: Currency.
     * It is used as a singleton
     *
     * @access private
     * @since  unknown
     * @var Currency
     */
    private static $instance;
    private static $_currencies;

    /**
     * Set data related to t_currency table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_currency');
        $this->setPrimaryKey('pk_c_code');
        $this->setFields(array('pk_c_code', 's_name', 's_description', 'b_enabled'));
    }

    /**
     * It creates a new Currency object class ir if it has been created
     * before, it return the previous object
     *
     * @access public
     * @return Currency
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
     * @param string $value
     *
     * @return bool|mixed
     */
    public function findByPrimaryKey($value)
    {
        if (isset(self::$_currencies[$value])) {
            return self::$_currencies[$value];
        }

        $this->dao->select($this->fields);
        $this->dao->from($this->getTableName());
        $this->dao->where($this->getPrimaryKey(), $value);
        $result = $this->dao->get();

        if ($result === false) {
            return false;
        }

        if ($result->numRows() !== 1) {
            return false;
        }

        self::$_currencies[$value] = $result->row();

        return self::$_currencies[$value];
    }
}

/* file end: ./oc-includes/osclass/model/Currency.php */
