<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 08/05/20
 * Time: 6:26 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\utility;

use Category;
use CategoryStats;
use City;
use CityStats;
use Country;
use CountryStats;
use DateTimeZone;
use Item;
use LocationsTmp;
use Params;
use Preference;
use Region;
use RegionStats;
use RuntimeException;
use Session;
use Translation;

/**
 * Class Utils
 * Utility class contains some useful static methods
 * Most functions derived from old Utils file, some of them may have been tweaked
 * @package mindstellar\osclass\classes\utility
 */
class Utils
{
    /**
     * VERY BASIC
     * Perform a POST request, so we could launch fake-cron calls and other core-system calls without annoying the user
     *
     * @param $target_url   string
     * @param $query_data array http_build_query compatible query_data
     *                    https://www.php.net/manual/en/function.http-build-query.php
     *
     * @return bool false on error or number of bytes sent.
     */
    public static function doRequest($target_url, $query_data)
    {
        if (ini_get('allow_url_fopen') === false) {
            throw new RuntimeException(__('Is allow_url_fopen enabled?'));
        }
        // parse the given URL
        $parsed_url = parse_url($target_url);

        if (!isset($parsed_url['host'], $parsed_url['path']) || $parsed_url === false) {
            return false;
        }
        // extract host, path, port:
        $host = $parsed_url['host'];
        $path = $parsed_url['path'];
        $port = 80;
        if (isset($parsed_url['port'])) {
            $port = $parsed_url['port'];
        }

        if (isset($parsed_url['scheme']) && $parsed_url['scheme'] === 'https') {
            $host = 'ssl://' . $host;
            $port = 443;
        }
        $fp = fsockopen($host, $port);

        if ($fp === false) {
            return false;
        }
        $data              = http_build_query($query_data);
        $out               = 'POST ' . $path . ' HTTP/1.1' . PHP_EOL;
        $out               .= 'Host: ' . $parsed_url['host'] . PHP_EOL;
        $out               .= 'Referer: Osclass ' . OSCLASS_VERSION . PHP_EOL;
        $out               .= 'Content-type: application/x-www-form-urlencoded' . PHP_EOL;
        $out               .= 'Content-Length: ' . strlen($data) . PHP_EOL;
        $out               .= 'Connection: close' . PHP_EOL . PHP_EOL;
        $out               .= $data;
        $number_bytes_sent = fwrite($fp, $out);
        fclose($fp);

        return $number_bytes_sent; // or false on fwrite() error
    }

    /**
     * Check if we loaded some specific module of apache
     *
     * @param string $mod
     *
     * @return bool
     */
    public static function apacheModLoaded($mod)
    {
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (in_array($mod, $modules)) {
                return true;
            }
        } elseif (function_exists('phpinfo')) {
            ob_start();
            phpinfo(INFO_MODULES);
            $content = ob_get_clean();
            if (stripos($content, $mod) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Change current osclass version to given param number
     *
     * @param mixed version
     */
    public static function changeOsclassVersionTo($version = null)
    {
        if ($version) {
            Preference::newInstance()->set('version', $version);
            Preference::newInstance()->toArray();
        }
    }

    /**
     * Un-quotes a quoted string
     * @param string|array $data
     * @return string|array a string or array of string with backslashes stripped off.
     * (\' becomes ' and so on.)
     * Double backslashes (\\) are made into a single
     * backslash (\).
     */
    public static function stripSlashesExtended($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => &$v) {
                $v = self::stripSlashesExtended($v);
            }
        } else {
            $data = stripslashes($data);
        }

        return $data;
    }
    /**
     * replace double slash with single slash
     *
     * @param $path
     *
     * @return string
     */
    public static function replaceDoubleSlash($path)
    {
        return str_replace('//', '/', $path);
    }
    /**
     * Prepare Price for osclass
     * @param $price
     *
     * @return string
     */
    public static function preparePrice($price)
    {
        return number_format(
            $price / 1000000,
            osc_locale_num_dec(),
            osc_locale_dec_point(),
            osc_locale_thousands_sep()
        );
    }
    /**
     * Compare version
     * Returns
     *      0  if both are equal,
     *      1  if A > B, and
     *      -1 if A < B.
     *
     * @param string $a
     * @param string $b
     * @param string $operator test for a particular relationship.
     *                         The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne
     *                         respectively.
     *
     * @return int|bool
     *@link https://www.php.net/manual/en/function.version-compare.php
     */
    public static function versionCompare($a, $b, $operator = null)
    {
        return version_compare($a, $b, $operator);
    }

    /**
     * Return Category Stats in array
     * @param $aux
     * @param $categoryTotal
     *
     * @return int
     */
    public static function recursiveCategoryStats(&$aux, &$categoryTotal)
    {
        $count_items = Item::newInstance()->numItems($aux);
        if (is_array($aux['categories'])) {
            foreach ($aux['categories'] as &$cat) {
                $count_items += self::recursiveCategoryStats($cat, $categoryTotal);
            }
            unset($cat);
        }
        $categoryTotal[$aux['pk_i_id']] = $count_items;

        return $count_items;
    }
    /**
     * Update category stats
     *
     * @return void
     */
    public static function updateAllCategoriesStats()
    {
        $categoryTotal = array();
        $aCategories   = Category::newInstance()->toTreeAll();

        foreach ($aCategories as &$category) {
            if ($category['fk_i_parent_id'] === null) {
                self::recursiveCategoryStats($category, $categoryTotal);
            }
        }
        unset($category);

        $sql     = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
        $aValues = array();
        foreach ($categoryTotal as $k => $v) {
            $aValues[] = "($k, $v)";
        }
        $sql .= implode(',', $aValues);

        CategoryStats::newInstance()->dao->query($sql);
    }
    /**
     * Recount items for a given a category id
     *
     * @param int $id
     *
     */
    public static function updateCategoryStatsById($id)
    {
        // get sub categorias
        $aCategories   = Category::newInstance()->findSubcategories($id);
        $categoryTotal = 0;
        $category      = Category::newInstance()->findByPrimaryKey($id);

        if (count($aCategories) > 0) {
            // sumar items de la categoría
            foreach ($aCategories as $subcategory) {
                $total         = Item::newInstance()->numItems($subcategory);
                $categoryTotal += $total;
            }
            $categoryTotal += Item::newInstance()->numItems($category);
        } else {
            $total         = Item::newInstance()->numItems($category);
            $categoryTotal += $total;
        }

        $sql = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
        $sql .= ' (' . $id . ', ' . $categoryTotal . ')';

        CategoryStats::newInstance()->dao->query($sql);

        if ($category['fk_i_parent_id'] != 0) {
            self::updateCategoryStatsById($category['fk_i_parent_id']);
        }
    }

    /**
     * Return indexed array fo php supported timezone
     * @return array
     */
    public static function timezoneList()
    {
        return DateTimeZone::listIdentifiers();
    }
    /**
     * Update locations stats.
     *
     * @param bool $force
     * @param int  $limit
     *
     * @return int
     */
    public static function updateLocationStats($force = false, $limit = 1000)
    {
        $loctmp   = LocationsTmp::newInstance();
        $workToDo = $loctmp->count();

        if ($workToDo > 0) {
            // there is work to do
            if ($limit === 'auto') {
                $total_cities = City::newInstance()->count();
                $limit        = max(1000, ceil($total_cities / 22));
            }
            $aLocations = $loctmp->getLocations($limit);
            foreach ($aLocations as $location) {
                $id   = $location['id_location'];
                $type = $location['e_type'];
                $data = 0;
                // update locations stats
                switch ($type) {
                    case 'COUNTRY':
                        $numItems = CountryStats::newInstance()->calculateNumItems($id);
                        $data     = CountryStats::newInstance()->setNumItems($id, $numItems);
                        unset($numItems);
                        break;
                    case 'REGION':
                        $numItems = RegionStats::newInstance()->calculateNumItems($id);
                        $data     = RegionStats::newInstance()->setNumItems($id, $numItems);
                        unset($numItems);
                        break;
                    case 'CITY':
                        $numItems = CityStats::newInstance()->calculateNumItems($id);
                        $data     = CityStats::newInstance()->setNumItems($id, $numItems);
                        unset($numItems);
                        break;
                    default:
                        break;
                }
                if ($data >= 0) {
                    $loctmp->delete(array(
                        'e_type'      => $location['e_type'],
                        'id_location' => $location['id_location']
                    ));
                }
            }
        } elseif ($force) {
            // we need to populate location tmp table
            $aCountry = Country::newInstance()->listAll();

            foreach ($aCountry as $country) {
                $aRegionsCountry = Region::newInstance()->findByCountry($country['pk_c_code']);
                $loctmp->insert(array('id_location' => $country['pk_c_code'], 'e_type' => 'COUNTRY'));
                foreach ($aRegionsCountry as $region) {
                    $aCitiesRegion = City::newInstance()->findByRegion($region['pk_i_id']);
                    $loctmp->insert(array('id_location' => $region['pk_i_id'], 'e_type' => 'REGION'));
                    $batchCities = array();
                    foreach ($aCitiesRegion as $city) {
                        $batchCities[] = $city['pk_i_id'];
                    }
                    unset($aCitiesRegion);
                    $loctmp->batchInsert($batchCities, 'CITY');
                    unset($batchCities);
                }
                unset($aRegionsCountry);
            }
            unset($aCountry);
            Preference::newInstance()->set('location_todo', LocationsTmp::newInstance()->count());
        }

        return LocationsTmp::newInstance()->count();
    }

    /**
     * Translate current categories to new locale
     *
     * @param $locale
     *
     */
    public static function translateCategories($locale)
    {
        $old_locale = Session::newInstance()->_get('adminLocale');
        Session::newInstance()->_set('adminLocale', $locale);
        Translation::newInstance()->_load(osc_translations_path() . $locale . '/core.mo', 'cat_' . $locale);
        $catManager     = Category::newInstance();
        $old_categories = $catManager->_findNameIDByLocale($old_locale);
        $tmp_categories = $catManager->_findNameIDByLocale($locale);
        foreach ($tmp_categories as $category) {
            $new_categories[$category['pk_i_id']] = $category['s_name'];
        }
        unset($tmp_categories);
        foreach ($old_categories as $category) {
            if (!isset($new_categories[$category['pk_i_id']])) {
                $fieldsDescription['s_name']           = __($category['s_name'], 'cat_' . $locale);
                $fieldsDescription['s_description']    = '';
                $fieldsDescription['fk_i_category_id'] = $category['pk_i_id'];
                $fieldsDescription['fk_c_locale_code'] = $locale;
                $slug                                  = osc_sanitizeString(
                    osc_apply_filter('slug', $fieldsDescription['s_name'])
                );
                $slug_tmp                              = $slug;
                $slug_unique                           = 1;
                while (true) {
                    if (!$catManager->findBySlug($slug)) {
                        break;
                    }

                    $slug = $slug_tmp . '_' . $slug_unique;
                    $slug_unique++;
                }
                $fieldsDescription['s_slug'] = $slug;
                $catManager->insertDescription($fieldsDescription);
            }
        }
        Session::newInstance()->_set('adminLocale', $old_locale);
    }

    /**
     * Get Current Client IP Address
     *
     * @return string
     */
    public static function getClientIp()
    {
        if (($http_client_ip = (Params::getServerParam('HTTP_CLIENT_IP') !== ''))
            && filter_var($http_client_ip, FILTER_VALIDATE_IP)
        ) {
            return $http_client_ip;
        }

        if ($http_x_forward_for = (Params::getServerParam('HTTP_X_FORWARDED_FOR') !== '')) {
            $ip_array = explode(',', $http_x_forward_for);
            $ip = trim($ip_array[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        //Most Reliable
        return Params::getServerParam('REMOTE_ADDR');
    }
    /**
     * Prune null or empty array element
     * @param $input
     */
    public static function pruneArray(&$input)
    {
        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                self::pruneArray($value);
                if (empty($input[$key])) {
                    unset($input[$key]);
                }
            } elseif ($value === '' || $value === false || $value === null) {
                unset($input[$key]);
            }
        }
    }

    /**
     * Redirect to give url
     *
     * @param      $url
     * @param null $http_response_code
     */
    public static function redirectTo($url, $http_response_code = null)
    {
        if (ob_get_length() > 0) {
            ob_end_flush();
        }
        if ($http_response_code !== null) {
            header('Location: ' . $url, true, $http_response_code);
        } else {
            header('Location: ' . $url);
        }
        exit;
    }

    /**
     * Calculate location slug
     * @param string $type
     *
     * @return bool|int|mixed
     */
    public static function calculateLocationSlug($type)
    {
        $field = 'pk_i_id';
        switch ($type) {
            case 'country':
                $manager = Country::newInstance();
                $field   = 'pk_c_code';
                break;
            case 'region':
                $manager = Region::newInstance();
                break;
            case 'city':
                $manager = City::newInstance();
                break;
            default:
                return false;
                break;
        }
        $locations         = $manager->listByEmptySlug();
        $locations_changed = 0;
        foreach ($locations as $location) {
            $slug_tmp    = $slug = osc_sanitizeString($location['s_name']);
            $slug_unique = 1;
            while (true) {
                $location_slug = $manager->findBySlug($slug);
                if (!isset($location_slug[$field])) {
                    break;
                }

                $slug = $slug_tmp . '-' . $slug_unique;
                $slug_unique++;
            }
            $locations_changed += $manager->update(array('s_slug' => $slug), array($field => $location[$field]));
        }

        return $locations_changed;
    }

    /**
     * Check if protocol is ssl
     * @return bool
     */
    public static function isSsl()
    {
        return ((isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || (isset($_SERVER['HTTPS'])
                && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)));
    }
    /**
     * Used to encode a field for Amazon Auth
     * (taken from the Amazon S3 PHP example library)
     *
     * @param $str
     *
     * @return string
     */
    public static function hex2b64($str)
    {
        $raw = '';
        for ($i = 0, $iMax = strlen($str); $i < $iMax; $i += 2) {
            $raw .= chr(hexdec(substr($str, $i, 2)));
        }

        return base64_encode($raw);
    }
    /**
     * Calculate HMAC-SHA1 according to RFC2104
     * See http://www.faqs.org/rfcs/rfc2104.html
     *
     * @param $key
     * @param $data
     *
     * @return string
     */
    public static function hmacsha1($key, $data)
    {
        $blocksize = 64;
        $hashfunc  = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key  = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
            'H*',
            $hashfunc(
                ($key ^ $opad) . pack(
                    'H*',
                    $hashfunc(
                        ($key ^ $ipad) . $data
                    )
                )
            )
        );

        return bin2hex($hmac);
    }
}
