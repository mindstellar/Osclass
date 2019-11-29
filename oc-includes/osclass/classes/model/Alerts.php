<?php if ( ! defined( 'ABS_PATH' ) ) {
    exit( 'ABS_PATH is not loaded. Direct access is not allowed.' );
}

/*
 * Copyright 2014 Osclass
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
     * Alerts DAO
     */
class Alerts extends DAO
{
    /**
     *
     * @var \Alerts
     */
    private static $instance;

    /**
     * @return \Alerts
     */
    public static function newInstance()
    {
        if ( !self::$instance instanceof self ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_alerts');
        $this->setPrimaryKey('pk_i_id');
        $array_fields = array(
            'pk_i_id'
            ,'s_email'
            ,'fk_i_user_id'
            ,'s_search'
            ,'s_secret'
            ,'b_active'
            ,'e_type'
            ,'dt_date'
            ,'dt_unsub_date'
        );
        $this->setFields($array_fields);
    }

    /**
     * Searches for user alerts, given an user id.
     * If user id not exist return empty array.
     *
     * @access public
     * @since  unknown
     *
     * @param string $userId
     * @param bool   $unsub
     *
     * @return array
     */
    public function findByUser($userId, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_user_id', $userId);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for user alerts, given an user id.
     * If user id not exist return empty array.
     *
     * @access public
     * @since  unknown
     *
     * @param string $email
     * @param bool   $unsub
     *
     * @return array
     */
    public function findByEmail($email, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_email', $email);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given a type.
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $type
     * @param bool   $active
     * @param bool   $unsub
     * @return array
*/
    public function findByType($type, $active = false, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('e_type', $type);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        if ( $active ) {
            $this->dao->where('b_active', 1);
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given a type group by s_search.
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $type
     * @param bool   $active
     * @param bool   $unsub
     * @return array
*/
    public function findByTypeGroup($type, $active = false, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('e_type', $type);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        if ($active) {
            $this->dao->where('b_active', 1);
        }
        $this->dao->groupBy('s_search');
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given an user and a s_search.
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $search
     * @param string $user
     * @param bool   $unsub
     * @return array
     *
     * WARNIGN doble where!
*/
    public function findBySearchAndUser($search, $user, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('fk_i_user_id', $user);
        $this->dao->where('s_search', $search);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given a type group and a s_search.
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $search
     * @param string $type
     * @param bool   $unsub
     * @return array
     *
     * WARNIGN doble where!
*/
    public function findBySearchAndType($search, $type, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('e_type', $type);
        $this->dao->where('s_search', $search);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    // a.s_email, a.fk_i_user_id @TODO

    /**
     * Searches for users, given a type group and a s_search.
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $search
     * @param string $type
     * @param bool   $active
     * @param bool   $unsub
     * @return array
*/
    public function findUsersBySearchAndType($search, $type, $active = false, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('e_type', $type);
        $this->dao->where('s_search', $search);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        if ($active) {
            $this->dao->where('b_active', 1);
        }
            
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given a type group and an user id
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param int    $userId
     * @param string $type
     * @param bool   $unsub
     * @return array
*/
    public function findByUserByType($userId, $type, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $conditions = array('e_type'        => $type,
                            'fk_i_user_id'  => $userId);
        $this->dao->where($conditions);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Searches for alerts, given a type group and an email
     * If type don't match return empty array.
     *
     * @access public
     * @since  unknown
     * @param string $email
     * @param string $type
     * @param bool   $unsub
     * @return array
*/
    public function findByEmailByType($email, $type, $unsub = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $conditions = array('e_type'   => $type,
                            's_email'  => $email);
        if (!$unsub) {
            $this->dao->where('dt_unsub_date IS NULL');
        }
        $this->dao->where($conditions);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }
        return $result->result();
    }

    /**
     * Create a new alert
     *
     * @access public
     * @since unknown
     * @param int $userid
     * @param string $email
     * @param string $alert
     * @param string $secret
     * @param string $type
     * @return bool on success
     */
    public function createAlert($userid, $email, $alert, $secret, $type = 'DAILY')
    {
        $results = 0;
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('s_search', $alert);
            
        $this->dao->where('dt_unsub_date IS NULL');
            
        if ($userid == 0 || $userid == null) {
            $this->dao->where('fk_i_user_id', 0);
            $this->dao->where('s_email', $email);
        } else {
            $this->dao->where('fk_i_user_id', $userid);
        }
        $results = $this->dao->get();

        if ($results->numRows() == 0) {
            $this->dao->insert($this->getTableName(), array(
                'fk_i_user_id' => $userid
                ,'s_email' => $email
                ,'s_search' => $alert
                ,'e_type' => $type
                ,'s_secret' => $secret
                ,'dt_date' => date('Y-m-d H:i:s')));
            return $this->dao->insertedId();
        }
        return false;
    }

    /**
     * Activate an alert
     *
     * @access public
     * @since unknown
     * @param string $id
     * @return mixed false on fail, int of num. of affected rows
     */
    public function activate($id)
    {
        return $this->dao->update($this->getTableName(), array('b_active' => 1), array('pk_i_id' => $id));
    }

    /**
     * Dectivate an alert
     *
     * @access public
     * @since 3.1
     * @param string $id
     * @return mixed false on fail, int of num. of affected rows
     */
    public function deactivate($id)
    {
        return $this->dao->update($this->getTableName(), array('b_active' => 0), array('pk_i_id' => $id));
    }

    /**
     * Unsub from an alert
     *
     * @access public
     * @since 3.1
     * @param string $id
     * @return mixed false on fail, int of num. of affected rows
     */
    public function unsub($id)
    {
        return $this->dao->update( $this->getTableName(), array('dt_unsub_date' => date( 'Y-m-d H:i:s' )), array( 'pk_i_id' => $id ) );
    }


    /**
     * Search alerts
     *
     * @access public
     * @since  3.1
     * @param int    $start
     * @param int    $end
     * @param string $order_column
     * @param string $order_direction
     * @param string $name
     * @return array
*/
    public function search($start = 0, $end = 10, $order_column = 'dt_date', $order_direction = 'DESC', $name = '')
    {
        // SET data, so we always return a valid object
        $alerts = array();
        $alerts['rows']             = 0;
        $alerts['total_results']    = 0;
        $alerts['alerts']           = array();

        $this->dao->select('SQL_CALC_FOUND_ROWS *');
        $this->dao->from($this->getTableName());
        $this->dao->orderBy($order_column, $order_direction);
        $this->dao->limit($start, $end);
        if ( $name != '' ) {
            $this->dao->like('s_email', $name);
        }
        $rs = $this->dao->get();

        if ( !$rs ) {
            return $alerts;
        }

        $alerts['alerts'] = $rs->result();

        $rsRows = $this->dao->query('SELECT FOUND_ROWS() as total');
        $data   = $rsRows->row();
        if ( $data['total'] ) {
            $alerts['total_results'] = $data['total'];
        }

        $rsTotal = $this->dao->query('SELECT COUNT(*) as total FROM '.$this->getTableName());
        $data   = $rsTotal->row();
        if ( $data['total'] ) {
            $alerts['rows'] = $data['total'];
        }

        return $alerts;

    }




}

    /* file end: ./oc-includes/osclass/model/Alerts.php */

