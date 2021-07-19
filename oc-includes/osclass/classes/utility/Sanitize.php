<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 30/06/20
 * Time: 9:21 PM
 * License is provided in root directory.
 */

namespace mindstellar\utility;

/**
 * Class Sanitize
 * Provide common sanitization methods using PHP filter_var() method where possible
 *
 * @package mindstellar\utility
 */
class Sanitize
{
    /**
     * Sanitised String
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     * @deprecated use Sanitize::string() instead will be removed in the next 6.x release
     */
    public function filterString($value, ...$options)
    {
        return $this->string($value, ...$options);
    }

    /**
     * Sanitised String
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function string($value, ...$options)
    {
        return $this->filter($value, 'string', ...$options);
    }

    /**
     * sanitise using filter_var
     * common method to get sanitized value
     * Validate before using these values, this will only sanitize the requested param
     *
     * @param string $value  name of param
     * @param string $type   What type is the variable (string, email, int, float, encoded, url, email)
     * @param array  $option Options for filter_var
     *
     * @return false|int|float|string will return false on failure
     */
    private function filter($value, $type = 'string', ...$options)
    {
        return $this->filterVar($value, $this->getFilter($type), ...$options);
    }

    /**
     * @param $value
     * @param $type
     * @param $options
     *
     * @return false|int|float|string will return false on failure
     */
    private function filterVar($value, $type, ...$options)
    {
        return filter_var($value, $type, ...$options);
    }

    /**
     * Private function to get filter type
     *
     * @param string $type What type is the variable (string, email, int, float, encoded, url, email)
     *
     * @return int
     */
    private function getFilter($type)
    {
        switch (strtolower($type)) {

            case 'int':
                $filter = FILTER_SANITIZE_NUMBER_INT;
                break;

            case 'float':
                $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                break;

            case 'encoded':
                $filter = FILTER_SANITIZE_ENCODED;
                break;

            case 'url':
                $filter = FILTER_SANITIZE_URL;
                break;

            case 'email':
                $filter = FILTER_SANITIZE_EMAIL;
                break;

            case 'quotes':
                $filter = FILTER_SANITIZE_MAGIC_QUOTES;
                break;

            case 'html':
                $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
                break;

            default:
                $filter = FILTER_SANITIZE_STRING;
                break;
        }

        return $filter;
    }

    /**
     * Sanitise String HTML Safe
     *
     * @param string $value
     * @param array  $options filter_var() options
     */
    public function html($value)
    {
        return $this->filter($value, 'html', FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    /**
     * Sanitised Int
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|int
     * @deprecated use Sanitize::int() instead will be removed in the next 6.x release
     */
    public function filterInt($value, ...$options)
    {
        return $this->int($value, ...$options);
    }

    /**
     * Sanitised Int
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|int
     */
    public function int($value, ...$options)
    {
        return $this->filter($value, 'int', ...$options);
    }

    /**
     * Sanitised float
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|float
     * @deprecated use Sanitize::float() instead will be removed in the next major 6.x release
     */
    public function filterFloat($value, ...$options)
    {
        return $this->float($value, ...$options);
    }

    /**
     * Sanitised float
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|float
     */
    public function float($value, ...$options)
    {
        return $this->filter($value, 'float', ...$options);
    }

    /**
     * Sanitised encoded
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     * @deprecated use Sanitize::encoded() instead will be removed in the next major 6.x release
     */
    public function filterEncoded($value, ...$options)
    {
        return $this->encoded($value, ...$options);
    }

    /**
     * Sanitised encoded
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function encoded($value, ...$options)
    {
        return $this->filter($value, 'encoded', ...$options);
    }

    /**
     * Sanitised Email
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     * @deprecated use Sanitize::email() instead will be removed in the next major 6.x release
     */
    public function filterEmail($value, ...$options)
    {
        return $this->email($value, ...$options);
    }

    /**...$options
     * Sanitised Email
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     *
     */
    public function email($value, ...$options)
    {
        return $this->filter($value, 'email', ...$options);
    }

    /**
     * Sanitised Quotes
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     * @deprecated use Sanitize::quotes() instead will be removed in the next major 6.x release
     */
    public function filterQuotes($value, ...$options)
    {
        return $this->quotes($value, ...$options);
    }

    /**
     * Sanitise Quotes
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function quotes($value, ...$options)
    {
        return $this->filter($value, 'quotes', ...$options);
    }

    /**
     * Sanitize a URL.
     *
     * @param string $value value to sanitize
     * @param        $options
     *
     * @return string sanitized
     */
    public function url($value, ...$options)
    {
        return $this->filter($value, 'url', ...$options);
    }


    /**
     * Sanitised URL
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     * @deprecated use Sanitize::url() instead will be removed in the next major 6.x release
     */
    public function filterURL($value, ...$options)
    {
        return $this->url($value, ...$options);
    }

    /**
     * Sanitize capitalization for a string.
     * Capitalize first letter of each name.
     * If all-caps, remove all-caps.
     *
     * @param string $value value to sanitize
     *
     * @return string sanitized
     */
    public function name($value)
    {
        return ucwords($this->allcaps(trim($value)));
    }


    /**
     * Sanitize string that's all-caps
     *
     * @param string $value value to sanitize
     *
     * @return string sanitized
     */
    public function allcaps($value)
    {
        if (preg_match('/^([A-Z][^A-Z]*)+$/', $value) && !preg_match('/[a-z]+/', $value)) {
            $value = ucfirst(strtolower($value));
        }

        return $value;
    }


    /**
     * Sanitize a username
     *
     * @param string $value
     *
     * @return string sanitized
     */
    public function username($value)
    {
        // Sanitize username, trim leading/trailing spaces and replace space with underscore.
        $value = preg_replace('/[^a-zA-Z0-9_\.]/', '', $value);
        $value = trim($value);
        $value = preg_replace('/[\s]+/', '_', $value);
        $value = strtolower($value);

        return $value;
    }


    /**
     * Format phone number. Remove non-numeric characters.
     *
     * @param string $value value to sanitize
     *
     * @return string sanitized
     */
    public function phone($value)
    {
        if (empty($value)) {
            return '';
        }
        // Remove strings that aren't number. leave leading + in number.
        $value = preg_replace('/[^0-9\+]/', '', $value);
        // Add leading zero if it's less than 11 digits and doesn't has leading +
        if (strlen($value) < 11 && strpos($value, '+') === false) {
            $value = '0' . $value;
        }

        return $value;
    }
}
