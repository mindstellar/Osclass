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
 * Log DAO
 */
class Log extends DAO
{
    /**
     *
     * @var \Log
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_log');
        $array_fields = array(
            'dt_date',
            's_section',
            's_action',
            'fk_i_id',
            's_data',
            's_ip',
            's_who',
            'fk_i_who_id'
        );
        $this->setFields($array_fields);
    }

    /**
     * @return \Log
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Insert a log row.
     *
     * @access public
     *
     * @param string  $section
     * @param string  $action
     * @param integer $id
     * @param string  $data
     * @param string  $who
     * @param         $whoId
     *
     * @return boolean
     * @since  unknown
     *
     */
    public function insertLog($section, $action, $id, $data, $who, $whoId)
    {
        if (!Params::getServerParam('REMOTE_ADDR')) {
            // CRON.
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        $array_set = array(
            'dt_date'     => date('Y-m-d H:i:s'),
            's_section'   => $section,
            's_action'    => $action,
            'fk_i_id'     => $id,
            's_data'      => $data,
            's_ip'        => Params::getServerParam('REMOTE_ADDR'),
            's_who'       => $who,
            'fk_i_who_id' => $whoId
        );

        return $this->dao->insert($this->getTableName(), $array_set);
    }
}

/* file end: ./oc-includes/osclass/model/Log.php */
