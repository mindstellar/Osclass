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
class UserEmailTmp extends DAO
{
    /**
     *
     * @var \UserEmailTmp
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_user_email_tmp');
        $this->setPrimaryKey('fk_i_user_id');
        $this->setFields(array('fk_i_user_id', 's_new_email', 'dt_date'));
    }

    /**
     * @return \UserEmailTmp
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
     * @param $userEmailTmp
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function insertOrUpdate($userEmailTmp)
    {

        $status = $this->dao->insert($this->getTableName(), array(
            'fk_i_user_id' => $userEmailTmp['fk_i_user_id'],
            's_new_email'  => $userEmailTmp['s_new_email'],
            'dt_date'      => date('Y-m-d H:i:s')
        ));
        if (!$status) {
            return $this->dao->update(
                $this->getTableName(),
                array('s_new_email' => $userEmailTmp['s_new_email'], 'dt_date' => date('Y-m-d H:i:s')),
                array('fk_i_user_id' => $userEmailTmp['fk_i_user_id'])
            );
        }

        return false;
    }
}

/* file end: ./oc-includes/osclass/model/UserEmailTmp.php */
