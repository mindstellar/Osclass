<?php
/**
 * Object_Cache class
 */
interface iObject_Cache {

    public function add( $key, $data, $expire = 0);
    public function set($key, $data, $expire = 0);
    public function get( $key, &$found = null ) ;
    public function delete($key);
    public function flush();
    public function stats();
    public function _get_cache(); // return string 
    public static function is_supported();


    public function __destruct();
}