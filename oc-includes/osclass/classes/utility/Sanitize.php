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
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function string($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_STRIP_LOW
                                                                                                                                                   | FILTER_FLAG_STRIP_HIGH,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],
                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_STRING, $options);
    }

    /**
     * Sanitised Price
     *
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function price($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_STRIP_LOW
                                                                                                                                                   | FILTER_FLAG_STRIP_HIGH,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],
                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, $options);
    }

    /**
     * Sanitise

    /**
     * Sanitize a string and escape it for use in html
     * @param string $value
     */
    public function html($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
     * @param mixed $value
     */
    public function int($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_ALLOW_OCTAL,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],

                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_NUMBER_INT, $options);
    }

    /**
     * Sanitised Float
     *
     * @param mixed $value
     * @param array $options
     *
     * @return float;
     */
    public function float($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_ALLOW_FRACTION,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],

                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, $options);
    }

    /**
     * Sanitised Email
     *
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function email($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_STRIP_LOW
                                                                                                                                                   | FILTER_FLAG_STRIP_HIGH,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],

                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_EMAIL, $options);
    }

    /**
     * Sanitised URL
     *
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function url($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_STRIP_LOW
                                                                                                                                                   | FILTER_FLAG_STRIP_HIGH,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],

                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_URL, $options);
    }

    /**
     * Sanitised website URL
     *
     * @param mixed $value
     */
    public function websiteUrl($value)
    {
        //remove invalid chars from url
        $value = $this->url($value);
        //remove possible xss attempts
        $value = str_replace(['<', '>', '"', '\'', '%3C', '%3E', '%22', '%27'], '', $value);
        //check if it has http:// or https://
        if (strpos($value, 'http') !== 0) {
            $value = 'http://' . $value;
        }

        return $value;
    }

    /**
     * Sanitised Encoded
     *
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function encoded($value, ...$options)
    {
        $options = array_merge(                                                                                                   [
                                                                                                                                      'flags'   => FILTER_FLAG_STRIP_LOW
                                                                                                                                                   | FILTER_FLAG_STRIP_HIGH,
                                                                                                                                      'options' => [
                                                                                                                                          'min_range' => 0,
                                                                                                                                          'max_range' => 65535,
                                                                                                                                      ],

                                                                                                                                  ],
                                                                                                                                  $options);

        return filter_var($value, FILTER_SANITIZE_ENCODED, $options);
    }

    /**
     * Add Slashes
     *
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function quotes($value)
    {
        return addslashes($value);
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
     * Sanitised encoded
     *
     * @param       $value
     * @param array $options
     *
     * @return string
     * @deprecated use Sanitize::encoded() instead will be removed in the next major 6.x release
     */
    public function filterEncoded($value, ...$options)
    {
        return $this->encoded($value, ...$options);
    }

    /**
     * Sanitised Email
     *
     * @param       $value
     * @param array $options
     *
     * @return string
     * @deprecated use Sanitize::email() instead will be removed in the next major 6.x release
     */
    public function filterEmail($value, ...$options)
    {
        return $this->email($value, ...$options);
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
     * Sanitize string that's all-caps
     *
     * @param string $value value to sanitize
     *
     * @return string sanitized
     */
    public function allcaps($value)
    {
        $sanitizedString = $this->string($value);
        if ($sanitizedString != false) {
            return ucfirst(strtolower($sanitizedString));
        }
        return '';
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
        $sanitizedString = $this->string($value);
        if ($sanitizedString != false) {
            // Sanitize username, trim leading/trailing spaces and replace space with underscore.
            $value = preg_replace('/[^a-zA-Z0-9_\.]/', '', $value);
            $value = trim($value);
            $value = preg_replace('/[\s]+/', '_', $value);
            $value = strtolower($value);

            return $value;
        }
        return '';
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
