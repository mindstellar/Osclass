<?php

/**
 * Object_Cache_apcu class
 *
 * @author Navjot Tomer
 */
class Object_Cache_apcu implements iObject_Cache
{

    /**
     * Holds the cached objects
     *
     * @var array
     * @access public
     * @since  3.7
     */
    public $cache = array();

    /**
     * The amount of times the cache data was already stored in the cache.
     *
     * @since  3.7
     * @access public
     * @var int
     */
    public $cache_hits = 0;

    /**
     * Amount of times the cache did not have the request in cache
     *
     * @var int
     * @access public
     * @since  3.7
     */
    public $cache_misses = 0;

    /**
     * The blog prefix to prepend to keys in non-global groups.
     *
     * @var int
     * @access public
     * @since  3.7
     */
    public $site_prefix;
    public $multisite;
    public $default_expiration = 60;

    /**
     * Sets up object properties; PHP 5 style constructor
     *
     * @since 3.7
     */
    public function __construct()
    {

        $this->multisite   = false;
        $site_id           = '';
        $this->site_prefix = $this->multisite ? $site_id . ':' : '';
    }

    /**
     * Adds data to the cache if it doesn't already exist.
     *
     * @param int|string $key    What to call the contents in the cache
     * @param mixed      $data   The contents to store in the cache
     * @param int        $expire When to expire the cache contents
     *
     * @return bool False if cache key and group already exist, true on success
     * @since 3.7
     *
     */
    public function add($key, $data, $expire = 0)
    {
        $id = $key;
        if ($this->multisite) {
            $id = $this->site_prefix . $key;
        }

        if (is_object($data)) {
            $data = clone $data;
        }

        $store_data = $data;

        if (is_array($data)) {
            $store_data = new ArrayObject($data);
        }

        $expire = ($expire == 0) ? $this->default_expiration : $expire;
        $result = apcu_add($id, $store_data, $expire);
        if (false !== $result) {
            $this->cache[$key] = $data;
        }

        return $result;
    }

    /**
     * Remove the contents of the cache key in the group
     *
     * @param int|string $key What the contents in the cache are called
     *
     * @return bool False if the contents weren't deleted and true on success
     * @since 3.7
     *
     */
    public function delete($key)
    {

        if ($this->multisite) {
            $key = $this->site_prefix . $key;
        }

        $result = apcu_delete($key);
        if (false !== $result) {
            unset($this->cache[$key]);
        }

        return $result;
    }

    /**
     * Clears the object cache of all data
     *
     * @return bool Always returns true
     * @since 3.7
     *
     */
    public function flush()
    {
        $this->cache = array();
        if (extension_loaded('apcu')) {
            return apcu_clear_cache();
        }

        return true;
    }

    /**
     * Retrieves the cache contents, if it exists
     *
     * @param int|string $key   What the contents in the cache are called
     * @param bool       $found if can be retrieved from cache
     *
     * @return bool|mixed False on failure to retrieve contents or the cache
     *        contents on success
     * @since 3.7
     *
     */
    public function get($key, &$found = null)
    {

        if ($this->multisite) {
            $key = $this->site_prefix . $key;
        }

        if (isset($this->cache[$key])) {
            if (is_object($this->cache[$key])) {
                $value = clone $this->cache[$key];
            } else {
                $value = $this->cache[$key];
            }
            ++$this->cache_hits;
            $return = $value;
        } else {
            $value = apcu_fetch($key, $found);

            if (is_object($value) && 'ArrayObject' === get_class($value)) {
                $value = $value->getArrayCopy();
            }
            if (null === $value) {
                $value = false;
            }
            $this->cache[$key] = is_object($value) ? clone $value : $value;
            if ($found) {
                ++$this->cache_hits;
                $return = $this->cache[$key];
            } else {
                ++$this->cache_misses;
                $return = false;
            }
        }

        return $return;
    }

    /**
     * Sets the data contents into the cache
     *
     * @param int|string $key    What to call the contents in the cache
     * @param mixed      $data   The contents to store in the cache
     * @param int        $expire Not Used
     *
     * @return bool Always returns true on success, false on failure
     * @since 3.7
     *
     */
    public function set($key, $data, $expire = 0)
    {
        if ($this->multisite) {
            $key = $this->site_prefix . $key;
        }

        if (is_object($data)) {
            $data = clone $data;
        }

        $store_data = $data;

        if (is_array($data)) {
            $store_data = new ArrayObject($data);
        }

        $this->cache[$key] = $data;

        $expire = ($expire == 0) ? $this->default_expiration : $expire;

        return apcu_store($key, $store_data, $expire);
    }

    /**
     * Echoes the stats of the caching.
     * Gives the cache hits, and cache misses.
     *
     * @since 3.7
     */
    public function stats()
    {
        echo "<div style='position:absolute; width:200px;top:0px;'><div style='float:right;margin-right:30px;margin-top:15px;border: 1px red solid;border-radius: 17px;padding: 1em;'><h2>APC stats</h2>";
        echo '<p>';
        echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
        echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
        echo '</p>';
        echo '<ul>';
        echo '</ul></div></div>';
    }

    /**
     * is_supported()
     *
     * Check to see if APCu is available on this system, bail if it isn't.
     */
    public static function is_supported()
    {
        if (!extension_loaded('apcu') or ini_get('apc.enabled') != '1') {
            error_log('The APCu PHP extension must be loaded to use APCu Cache.');

            return false;
        }

        return true;
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * @return string
     */
    public function _get_cache()
    {
        return 'apcu';
    }

    /**
     * Utility function to determine whether a key exists in the cache.
     *
     * @param $key
     *
     * @return bool
     * @since  3.7
     *
     * @access protected
     *
     */
    protected function _exists($key)
    {
        return isset($this->cache[$key]);
    }
}
