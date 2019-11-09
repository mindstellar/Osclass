<?php
/**
 * Object_Cache class
 */
interface iObject_Cache {

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
    public function stats();
    public function _get_cache(); // return string
    public static function is_supported();


    public function __destruct();
}