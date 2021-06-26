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
 * LatestSearches DAO
 */
class LatestSearches extends DAO
{
    /**
     *
     * @var \LatestSearches
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_latest_searches');
        $array_fields = array(
            'd_date',
            's_search'
        );
        $this->setFields($array_fields);
    }

    /**
     * @return \LatestSearches
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get last searches, given a limit.
     *
     * @access public
     *
     * @param int $limit
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function getSearches($limit = 20)
    {
        $this->dao->select('d_date, s_search, COUNT(s_search) as i_total');
        $this->dao->from($this->getTableName());
        $this->dao->groupBy('s_search');
        $this->dao->orderBy('d_date', 'DESC');
        $this->dao->limit($limit);
        $result = $this->dao->get();

        if ($result == false) {
            return false;
        }

        return $result->result();
    }

    /**
     * Get last searches, given since time.
     *
     * @access public
     *
     * @param int $time
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function getSearchesByDate($time = null, $limit = 20)
    {
        if ($time == null) {
            $time = time() - (7 * 24 * 3600);
        }

        $this->dao->select('d_date, s_search, COUNT(s_search) as i_total');
        $this->dao->from($this->getTableName());
        $this->dao->where('d_date', date('Y-m-d H:i:s', $time));
        $this->dao->groupBy('s_search');
        $this->dao->orderBy('d_date', 'DESC');
        $this->dao->limit($limit);
        $result = $this->dao->get();

        if ($result == false) {
            return false;
        }

        return $result->result();
    }

    /**
     * Purge n last searches.
     *
     * @access public
     *
     * @param int $number
     *
     * @return bool
     * @since  unknown
     */
    public function purgeNumber($number = null)
    {
        if ($number == null) {
            return false;
        }

        $this->dao->select('d_date');
        $this->dao->from($this->getTableName());
        $this->dao->groupBy('s_search');
        $this->dao->orderBy('d_date', 'DESC');
        $this->dao->limit($number, 1);
        $result = $this->dao->get();
        $last   = $result->row();

        if ($result == false) {
            return false;
        }

        if ($result->numRows() == 0) {
            return false;
        }

        return $this->purgeDate($last['d_date']);
    }

    /**
     * Purge all searches by date.
     *
     * @access public
     *
     * @param string $date
     *
     * @return bool
     * @since  unknown
     */
    public function purgeDate($date = null)
    {
        if ($date == null) {
            return false;
        }

        $this->dao->from($this->getTableName());
        $this->dao->where('d_date <= ' . $this->dao->escape($date));

        return $this->dao->delete();
    }
}

/* file end: ./oc-includes/osclass/model/LatestSearches.php */
