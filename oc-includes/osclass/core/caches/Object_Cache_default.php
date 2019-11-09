<?php
/**
 * Object_Cache_default class
 */
class Object_Cache_default implements iObject_Cache{

    /**
     * Holds the cached objects
     *
     * @var array
     * @access private
     * @since 3.4
     */
    public $cache = array ();

    /**
     * The amount of times the cache data was already stored in the cache.
     *
     * @since 3.4
     * @access private
     * @var int
     */
    public $cache_hits = 0;

    /**
     * Amount of times the cache did not have the request in cache
     *
     * @var int
     * @access public
     * @since 3.4
     */
    public $cache_misses = 0;

    /**
     * The site prefix to prepend to keys.
     *
     * @var int
     * @access private
     * @since 3.4
     */
    public $site_prefix;
    public $multisite;

    /**
     * Adds data to the cache if it doesn't already exist.
     * @since 3.4
     *
     * @param int|string $key What to call the contents in the cache
     * @param mixed $data The contents to store in the cache
     * @param int $expire When to expire the cache contents
     * @return bool False if cache key and group already exist, true on success
     */
    public function add($key, $data, $expire = 0)
    {
        $id = $key;
        if ( $this->multisite ) {
            $id = $this->site_prefix . $key;
        }

        if ( $this->_exists( $id ) ) {
            return false;
        }

        return $this->set($key, $data, $expire);
    }

    /**
     * Remove the contents of the cache key
     * @since 3.4
     *
     * @param int|string $key What the contents in the cache are called
     * @return bool False if the contents weren't deleted and true on success
     */
    public function delete($key)
    {

        if ( $this->multisite ) {
            $key = $this->site_prefix . $key;
        }

        if ( ! $this->_exists( $key ) ) {
            return false;
        }

        unset( $this->cache[$key] );
        return true;
    }

    /**
     * Clears the object cache of all data
     * @since 3.4
     *
     * @return bool Always returns true
     */
    public function flush()
    {
        $this->cache = array ();

        return true;
    }

    /**
     * Retrieves the cache contents, if it exists
     * @since 3.4
     *
     * @param int|string $key What the contents in the cache are called
     * @param bool $found if can be retrieved from cache
     * @return bool|mixed False on failure to retrieve contents or the cache
     *      contents on success
     */
    public function get($key, &$found = null)
    {

        if ( $this->multisite ) {
            $key = $this->site_prefix . $key;
        }

        if ( $this->_exists( $key ) ) {
            $found = true;
            ++ $this->cache_hits;
            if ( is_object( $this->cache[ $key ] ) ) {
                return clone $this->cache[ $key ];
            } else {
                return $this->cache[ $key ];
            }
        }
        $found = false;
        ++ $this->cache_misses;
        return false;
    }

    /**
     * Sets the data contents into the cache
     * @since 3.4
     *
     * @param int|string $key What to call the contents in the cache
     * @param mixed $data The contents to store in the cache
     * @param int $expire Not Used
     * @return bool Always returns true
     */
    public function set($key, $data, $expire = 0)
    {
        if ( $this->multisite ) {
            $key = $this->site_prefix . $key;
        }

        if ( is_object( $data ) ) {
            $data = clone $data;
        }

        $this->cache[$key] = $data;
        return true;
    }

    /**
     * Echoes the stats of the caching.
     * Gives the cache hits, and cache misses.
     *
     * @since 3.4
     */
    public function stats()
    {
        echo "<div style='position:absolute; width:200px;top:0px;'><div style='float:right;margin-right:30px;margin-top:15px;border: 1px red solid;
border-radius: 17px;
padding: 1em;'><h2>Default(dummy) stats</h2>";
        echo '<p>';
        echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
        echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
        echo '</p>';
        echo '</div></div>';
    }

    /**
     * Utility function to determine whether a key exists in the cache.
     * @since  3.4
     *
     * @access protected
     *
     * @param $key
     *
     * @return bool
     */
    protected function _exists($key)
    {
        return isset( $this->cache[ $key ] );
    }

    /**
     * Return hash of a given key
     *
     * @param $key
     *
     * @return string
     */
    protected function _getKey($key)
    {
        return md5( $key );
    }
    /**
     * Sets up object properties
     *
     * @since 2.4
     */
    public function __construct()
    {

        $this->multisite = false;
//        if(SiteInfo::newInstance()->siteInfo!=array()) {
//            $info       = SiteInfo::newInstance()->siteInfo;
//            $site_id    = osc_sanitizeString($info);
//            $this->multisite = true;
//        }
        $site_id = '';
        $this->site_prefix =  $this->multisite ? $site_id . ':' : '';
    }

    /**
     * is_supported()
     *
     * Check to see if APC is available on this system, bail if it isn't.
     */
    public static function is_supported()
    {
        return true;
    }

    /**
     *
     */
    public function __destruct()
    {
        return true;
    }

    /**
     * @return string
     */
    public function _get_cache()
    {
        return 'default';
    }
}