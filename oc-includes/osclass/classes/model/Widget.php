<?php

/*
 *  Copyright 2020 Osclass 
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
