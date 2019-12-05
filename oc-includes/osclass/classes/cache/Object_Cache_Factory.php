<?php

/**
 * Class Object_Cache_Factory
 */
class Object_Cache_Factory
{

    private static $instance;

    /**
     * @return null|\Object_Cache_default
     */
    public static function newInstance()
    {
        if (self::$instance === null) {
            self::$instance = self::getCache();
        }

        return self::$instance;
    }

    /**
     * @return null|\Object_Cache_default
     */
    public static function getCache()
    {
        if (self::$instance === null) {
            $cache = 'default';
            if (defined('OSC_CACHE')) {
                $cache = OSC_CACHE;
            }

            $cache_class = 'Object_Cache_' . $cache;

            if (class_exists($cache_class, true)) {
                // all correct ?
                if (call_user_func(array($cache_class, 'is_supported'))) {
                    self::$instance = new $cache_class();
                } else {
                    self::$instance = new Object_Cache_default();
                    error_log('Cache ' . $cache . ' NOT SUPPORTED - loaded Object_Cache_default cache');
                }

                return self::$instance;
            }

            throw new RuntimeException('Unknown cache');
        }

        return self::$instance;
    }
}
