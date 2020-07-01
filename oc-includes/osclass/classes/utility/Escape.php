<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 01/07/20
 * Time: 2:40 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\utility;

/**
 * Class Escape
 *
 * @package mindstellar\osclass\classes\utility
 */
class Escape
{
    /**
     * Escape html
     *
     * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
     *
     * @access  public
     *
     * @param string
     *
     * @return  string
     * @version 2.4
     */
    public static function html($str)
    {
        if (!isset($str) || $str === '') {
            return '';
        }

        $temp = '__TEMP_AMPERSANDS__';

        // Replace entities to temporary markers so that
        // htmlspecialchars won't mess them up
        $str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
        $str = preg_replace("/&(\w+);/", "$temp\\1;", $str);

        $str = htmlspecialchars($str);

        // In case htmlspecialchars misses these.
        $str = str_replace(array("'", '"'), array('&#39;', '&quot;'), $str);

        // Decode the temp markers back to entities
        $str = preg_replace("/$temp(\d+);/", "&#\\1;", $str);
        $str = preg_replace("/$temp(\w+);/", "&\\1;", $str);

        return $str;
    }


    /**
     * Escape single quotes, double quotes, <, >, & and line endings
     *
     * @access  public
     *
     * @param string $str
     *
     * @return string
     * @version 2.4
     */
    public static function js($str)
    {
        static $sNewLines = '<br><br/><br />';
        static $aNewLines = array('<br>', '<br/>', '<br />');
        $str = strip_tags($str, $sNewLines);
        $str = str_replace("\r", '', $str);
        $str = addslashes($str);
        $str = str_replace(array("\n", $aNewLines), '\n', $str);

        return $str;
    }
}
