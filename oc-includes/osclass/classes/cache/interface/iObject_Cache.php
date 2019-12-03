<?php

/**
 * Interface iObject_Cache
 */
interface iObject_Cache
{
    public static function is_supported();

    /**
     * @param     $key
     * @param     $data
     * @param int $expire
     *
     * @return mixed
     */
    public function add($key, $data, $expire = 0);

    /**
     * @param     $key
     * @param     $data
     * @param int $expire
     *
     * @return mixed
     */
    public function set($key, $data, $expire = 0);

    /**
     * @param      $key
     * @param null $found
     *
     * @return mixed
     */
    public function get($key, &$found = null);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function delete($key);

    public function flush();

    public function stats(); // return string

    public function _get_cache();

    public function __destruct();
}
