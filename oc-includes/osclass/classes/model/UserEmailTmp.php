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
