<?php

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
 * OSCLocale DAO
 */
class OSCLocale extends DAO
{
    /**
     *
     * @var \OSCLocale
     */
    private static $instance;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTableName('t_locale');
        $this->setPrimaryKey('pk_c_code');
        $array_fields = array(
            'pk_c_code',
            's_name',
            's_short_name',
            's_description',
            's_version',
            's_author_name',
            's_author_url',
            's_currency_format',
            's_dec_point',
            's_thousands_sep',
            'i_num_dec',
            's_date_format',
            's_stop_words',
            'b_enabled',
            'b_enabled_bo'
        );
        $this->setFields($array_fields);
    }

    /**
     * @return \OSCLocale
     */
    public static function newInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Return all locales enabled.
     *
     * @access public
     *
     * @param bool $isBo
     * @param bool $indexedByPk
     *
     * @return array
     * @since  unknown
     *
     */
    public function listAllCodes()
    {
        $this->dao->select('pk_c_code');
        $this->dao->from($this->getTableName());
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        $aResults = $result->result();
        $aCodes   = array();

        foreach ($aResults as $result) {
            $aCodes[] = $result['pk_c_code'];
        }

        return $aCodes;
    }

    /**
     * Return all locales enabled.
     *
     * @access public
     *
     * @param bool $isBo
     * @param bool $indexedByPk
     *
     * @return array
     * @since  unknown
     *
     */
    public function listAllEnabled($isBo = false, $indexedByPk = false)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        if ($isBo) {
            $this->dao->where('b_enabled_bo', 1);
        } else {
            $this->dao->where('b_enabled', 1);
        }
        $this->dao->orderBy('s_name', 'ASC');
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        $aResults = $result->result();

        if ($indexedByPk) {
            $aTmp = array();
            for ($i = 0, $iMax = count($aResults); $i < $iMax; $i++) {
                $aTmp[(string)$aResults[$i][$this->getPrimaryKey()]] = $aResults[$i];
            }
            $aResults = $aTmp;
        }

        return $aResults;
    }

    /**
     * Return all locales by code
     *
     * @access public
     *
     * @param string $code
     *
     * @return array
     * @since  2.3
     */
    public function findByCode($code)
    {
        $this->dao->select();
        $this->dao->from($this->getTableName());
        $this->dao->where('pk_c_code', $code);
        $result = $this->dao->get();

        if ($result == false) {
            return array();
        }

        return $result->result();
    }

    /**
     * Delete all related to locale code.
     *
     * @access public
     *
     * @param string $locale
     *
     * @return bool
     * @since  unknown
     */
    public function deleteLocale($locale)
    {
        osc_run_hook('delete_locale', $locale);

        $array_where = array('fk_c_locale_code' => $locale);
        $this->dao->delete(DB_TABLE_PREFIX . 't_category_description', $array_where);
        $this->dao->delete(DB_TABLE_PREFIX . 't_item_description', $array_where);
        $this->dao->delete(DB_TABLE_PREFIX . 't_keywords', $array_where);
        $this->dao->delete(DB_TABLE_PREFIX . 't_user_description', $array_where);
        $this->dao->delete(DB_TABLE_PREFIX . 't_pages_description', $array_where);

        return $this->dao->delete($this->getTableName(), array('pk_c_code' => $locale));
    }
}

/* file end: ./oc-includes/osclass/model/OSCLocale.php */
