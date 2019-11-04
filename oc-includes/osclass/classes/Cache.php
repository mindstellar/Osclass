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

    define('CACHE_PATH',UPLOADS_PATH . 'cache/');

    use Stash\Invalidation;

class Cache {

    /**
     * @var int
     */
    public $cache_hits = 0;

    /**
     * @var int
     */
    public $cache_misses = 0;

    /**
     * Implementation of the caching backend
     *
     * @var Pool
     */
    private $pool;
    

    /**
     * In-memory data cache which is kept in sync with the data in the caching back-end
     *
     * @var array
     */
    private $local = [];

    /**
     * @var bool
     */
    private $useInMemoryCache = true;
    private static $instance = null;

    public static function newInstance() {
        if (self::$instance == null) {
            self::$instance = new self ();
        }
        return self::$instance;
    }
 // single Instance
    private function __construct() {

        if (defined('OSC_CACHE')) {
            $cache = OSC_CACHE;
            $driverClassName = 'Stash\\Driver\\' . $cache;

            if (in_array(DriverInterface::class, class_implements($driverClassName), true) || call_user_func([$driverClassName, 'isAvailable'])
            ) {
                $adapter = new $driverClassName();
                global $_cache_config;
                if (isset($_cache_config) && is_array($_cache_config)) {
                    $adapter->setOptions($_cache_config);
                }
            }
        } else {
            $adapter = new Stash\Driver\FileSystem(array('path' => UPLOADS_PATH . 'cache/'));
        }

      
        $this->pool = new Stash\Pool($adapter);
        
    }

  
 

    /**
     * Set a cache item if it's not set already.
     *
     * @param string $key
     * @param mixed $data
     * @param int $expire
     *
     * @return bool
     *
     * 
     */
    public function add( $key, $data,  $expire = 0) {
       // $key = $this->makeKey($key);
        if ($this->pool->hasItem($key)) {
            return false;
        }
        return $this->set($key, $data, $expire);
    }

    /**
     * Set/update a cache item.
     *
     * @param string $key
     * @param mixed $data
     * @param int $expire
     *
     * @return bool
     *
     * 
     */
    public function set( $key, $data, $expire = 0) {
       //$key = $this->makeKey($key);
        try {
            $item = $this->pool->getItem($key);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
        $item->set($data);
        if ($expire) {
            $item->expiresAfter($expire);
        }
        $item->setInvalidationMethod(Invalidation::OLD);
        $this->pool->save($item);
        if ($this->useInMemoryCache) {
            $this->local[$key] = $data;
        }
        return true;
    }

    /**
     * Increase a numeric cache value by the specified amount.
     *
     * @param string $key
     * @param int $offset
     *
     * @return bool
     */
    public function incr( $key,  $offset = 1) {
       // $key = $this->makeKey($key);
        $data = $this->get($key);
        if (!$data || !is_numeric($data)) {
            return false;
        }
        return $this->set($key, $data + $offset);
    }

    /**
     * Retrieve a cache item.
     *
     * @param string $key
     *
     * @return bool|mixed
     *
     * // phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function get( $key) {
       // $key = $this->makeKey($key);
        if ($this->useInMemoryCache && isset($this->local[$key])) {
            return $this->local[$key];
        }
        try {
            $item = $this->pool->getItem($key);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
        // Check to see if the data was a miss.
        if ($item->isMiss()) {
            $this->cache_misses++;
            return false;
        }
        $result = $item->get();
        if ($this->useInMemoryCache) {
            $this->local[$key] = $result;
        }
        $this->cache_hits++;
        return $result;
    }

    /**
     * Decrease a numeric cache item by the specified amount.
     *
     * @param string $key
     * @param int $offset
     *
     * @return bool
     */
    public function decr( $key,  $offset = 1) {
        $key = $this->makeKey($key);
        $data = $this->get($key);
        if (!$data || !is_numeric($data)) {
            return false;
        }
        return $this->set($key, $data - $offset);
    }

    /**
     * Delete a cache item.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete( $key) {
        //$key = $this->makeKey($key);
        if ($this->useInMemoryCache) {
            unset($this->local[$key]);
        }
        return $this->pool->deleteItem($key);
    }

    /**
     * Clear the whole cache pool
     */
    public function clear() {
        $this->local = [];
        $this->pool->clear();
    }

    /**
     * Replace a cache item if it exists.
     *
     * @param string $key
     * @param mixed $data
     * @param int $expire
     *
     * @return bool
     *
     * 
     */
    public function replace( $key, $data,  $expire = 0) {
     //   $key = $this->makeKey($key);
        // Check to see if the data was a miss.
        if (!$this->pool->hasItem($key)) {
            return false;
        }
        return $this->set($key, $data, $expire);
    }

    public function __destruct() {
        $this->pool->commit();
        $this->local = [];
    }

}
