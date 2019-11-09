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
     *
     */
class SiteInfo extends DAO
{
    /**
     *
     * @var
     */
    private static $instance;
    /**
     *
     * @var
     */
    private $daoMetadata;
    /**
     *
     * @var
     */
    private $siteInfo;

    /**
     *
     * @return \SiteInfo
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
        $this->setTableName('tbl_sites');
        $this->setPrimaryKey('s_site');
        $this->setFields( array('s_site', 'dt_date', 's_site_mapping', 'fk_i_user_id', 's_db_name', 's_db_host', 's_db_user', 's_db_password', 's_upload_path') );

        $conn = new DBConnectionClass(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn->connectToMetadataDb();
        $m_db = $conn->getMetadataDb();
        $this->daoMetadata = new DBCommandClass($m_db);

        $this->toArray();
    }

    /**
     *
     */
    public function toArray()
    {
        $domain = '//' . $_SERVER['HTTP_HOST'] . '/';
        $this->siteInfo = $this->findByPrimaryKey($domain);
    }

    /**
     *
     * @access public
     * @since  unknown
     *
     * @param $key
     *
     * @return string
     */
    public function get($key)
    {
        if (!isset($this->siteInfo[$key])) {
            return '';
        }

        return $this->siteInfo[$key];
    }

    /**
     *
     * @access public
     * @since  unknown
     * @param $value
     * @return array
*/
    public function findByPrimaryKey($value)
    {
        $this->daoMetadata->select($this->getFields());
        $this->daoMetadata->from($this->getTableName());
        $this->daoMetadata->like('s_site', $value);
        $this->daoMetadata->orLike('s_site_mapping', $value);
        $result = $this->daoMetadata->get();

        if ( $result == false ) {
            return array();
        }

        return $result->row();
    }

    /**
     *
     * @access public
     * @since  unknown
     * @param $table
     * @return string
*/
    public function setTableName($table)
    {
        return $this->tableName = $table;
    }
}

    /* file end: ./oc-includes/osclass/model/SiteInfo.php */
