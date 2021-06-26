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
 * DataTable class
 *
 * @since      3.1
 * @package    Osclass
 * @subpackage classes
 * @author     Osclass
 */
abstract class DataTable
{
    protected $aColumns;
    protected $aRows;
    protected $rawRows;

    protected $limit;
    protected $start;
    protected $iPage;
    protected $total;
    protected $totalFiltered;

    public function __construct()
    {
        $this->aColumns = array();
        $this->aRows    = array();
        $this->rawRows  = array();
    }


    /**
     * FUNCTIONS THAT SHOULD BE REDECLARED IN SUB-CLASSES
     *
     * @param null $results
     */
    public function setResults($results = null)
    {
        if (is_array($results)) {
            $this->start         = 0;
            $this->limit         = count($results);
            $this->total         = count($results);
            $this->totalFiltered = count($results);

            if (count($results) > 0) {
                foreach ($results as $r) {
                    $row = array();
                    if (is_array($r)) {
                        foreach ($r as $k => $v) {
                            $row[$k] = $v;
                        }
                    }
                    $this->addRow($row);
                }
                if (is_array($results[0])) {
                    foreach ($results[0] as $k => $v) {
                        $this->addColumn($k, $k);
                    }
                }
            }
        }
    }




    /**
     * COMMON FUNCTIONS . DO NOT MODIFY THEM
     */

    /**
     * @param $aRow
     */
    protected function addRow($aRow)
    {
        $this->aRows[] = $aRow;
    }

    /**
     * Add a colum
     *
     * @param     $id
     * @param     $text
     * @param int $priority
     */
    public function addColumn($id, $text, $priority = 5)
    {
        $this->removeColumn($id);
        $this->aColumns[$priority][$id] = $text;
    }

    /**
     * @param $id
     */
    public function removeColumn($id)
    {
        for ($priority = 1; $priority <= 10; $priority++) {
            unset($this->aColumns[$priority][$id]);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'aColumns'             => $this->sortedColumns()
            ,
            'aRows'                => $this->sortedRows()
            ,
            'iDisplayLength'       => $this->limit
            ,
            'iTotalDisplayRecords' => $this->total
            ,
            'iTotalRecords'        => $this->totalFiltered
            ,
            'iPage'                => $this->iPage
        );
    }

    /**
     * @return array
     */
    public function sortedColumns()
    {
        $columns_ordered = array();
        for ($priority = 1; $priority <= 10; $priority++) {
            if (isset($this->aColumns[$priority]) && is_array($this->aColumns[$priority])) {
                foreach ($this->aColumns[$priority] as $k => $v) {
                    $columns_ordered[$k] = $v;
                }
            }
        }

        return $columns_ordered;
    }

    /**
     * @return array
     */
    public function sortedRows()
    {
        $rows    = array();
        $aRows   = (array)$this->aRows;
        $columns = (array)$this->sortedColumns();
        if (count($aRows) === 0) {
            return $rows;
        }
        foreach ($aRows as $row) {
            $aux_row = array();
            foreach ($columns as $k => $v) {
                if (isset($row[$k])) {
                    $aux_row[$k] = $row[$k];
                } else {
                    $aux_row[$k] = '';
                }
            }
            $rows[] = $aux_row;
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function rawRows()
    {
        return $this->rawRows;
    }
}
