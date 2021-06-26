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
 * Model database for Admin table
 *
 * @package    Osclass
 * @subpackage Model
 * @since      unknown
 */
class Admin extends DAO
{
    /**
     * It references to self object: Admin.
     * It is used as a singleton
     *
     * @access private
     * @since  unknown
     * @var Admin
     */
    private static $instance;

    /**
     * array for save currencies
     *
     * @var array
     */
    private $cachedAdmin;

    /**
     * Set data from t_admin table
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_admin');
        $this->setPrimaryKey('pk_i_id');

        $return = $this->dao->query('SHOW COLUMNS FROM ' . $this->getTableName() . ' where Field = "b_moderator" ');
        if ($return instanceof DBRecordsetClass) {
            if ($return->numRows() > 0) {
                $this->setFields(array(
                    'pk_i_id',
                    's_name',
                    's_username',
                    's_password',
                    's_email',
                    's_secret',
                    'b_moderator'
                ));
            } else {
                $this->setFields(array('pk_i_id', 's_name', 's_username', 's_password', 's_email', 's_secret'));
            }
        } else {
            throw new mysqli_sql_exception($this->dao->errorDesc);
        }
    }

    /**
     * @return \Admin
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param string $id
     * @param null   $locale
     *
     * @return mixed|string
     */
    public function findByPrimaryKey($id, $locale = null)
    {
        if ($id == '') {
            return '';
        }
        if (isset($this->cachedAdmin[$id])) {
            return $this->cachedAdmin[$id];
        }
        $this->cachedAdmin[$id] = parent::findByPrimaryKey($id);

        return $this->cachedAdmin[$id];
    }

    /**
     * Searches for admin information, given an email address.
     * If email not exist return false.
     *
     * @access public
     *
     * @param string $email
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function findByEmail($email)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_email', $email);
        $result = $this->dao->get();

        if ($result->numRows == 0) {
            return false;
        }

        return $result->row();
    }

    /**
     * Searches for admin information, given a username and password
     * If credential don't match return false.
     *
     * @access public
     *
     * @param string $userName
     * @param string $password
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function findByCredentials($userName, $password)
    {
        $user = $this->findByUsername($userName);
        if ($user !== false && isset($user['s_password']) && osc_verify_password($password, $user['s_password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Searches for admin information, given a username.
     * If admin not exist return false.
     *
     * @access public
     *
     * @param string $username
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function findByUsername($username)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_username', $username);
        $result = $this->dao->get();

        if ($result->numRows == 0) {
            return false;
        }

        return $result->row();
    }

    /**
     * Searches for admin information, given a admin id and secret.
     * If credential don't match return false.
     *
     * @access public
     *
     * @param integer $id
     * @param string  $secret
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function findByIdSecret($id, $secret)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $conditions = array(
            'pk_i_id'  => $id,
            's_secret' => $secret
        );
        $this->dao->where($conditions);
        $result = $this->dao->get();

        if ($result->numRows == 0) {
            return false;
        }

        return $result->row();
    }

    /**
     * Searches for admin information, given a admin id and password.
     * If credential don't match return false.
     *
     * @access public
     *
     * @param integer $id
     * @param string  $password
     *
     * @return array|bool
     * @since  unknown
     *
     */
    public function findByIdPassword($id, $password)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $conditions = array(
            'pk_i_id'    => $id,
            's_password' => $password
        );
        $this->dao->where($conditions);
        $result = $this->dao->get();

        if ($result->numRows == 0) {
            return false;
        }

        return $result->row();
    }

    /**
     * Perform a batch delete (for more than one admin ID)
     *
     * @access public
     *
     * @param array $id
     *
     * @return boolean
     * @since  2.3.4
     */
    public function deleteBatch($id)
    {
        $this->dao->from($this->getTableName());
        $this->dao->whereIn('pk_i_id', $id);

        return $this->dao->delete();
    }
}

/* file end: ./oc-includes/osclass/model/Admin.php */
