<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 30/06/20
 * Time: 9:21 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes\utility;

/**
 * Class Sanitize
 * Provide common sanitization methods
 *
 * @package mindstellar\osclass\classes\utility
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
     */
    public function filterString($value, $options = [])
    {
        return $this->filter($value, 'string', $options);
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
    private function filter($value, $type = 'string', $options = [])
    {
        return $this->filterVar($value, $this->getFilter($type), $options);
    }

    /**
     * @param $value
     * @param $type
     * @param $options
     *
     * @return false|int|float|string will return false on failure
     */
    private function filterVar($value, $type, $options = [])
    {
        return filter_var($value, $type, $options);
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
            case 'string':
                $filter = FILTER_SANITIZE_STRING;
                break;

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

            default:
                $filter = FILTER_SANITIZE_STRING;
        }

        return $filter;
    }

    /**
     * Sanitised Int
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|int
     */
    public function filterInt($value, $options = [])
    {
        return $this->filter($value, 'int', $options);
    }

    /**
     * Sanitised float
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|float
     */
    public function filterFloat($value, $options = [])
    {
        return $this->filter($value, 'float', $options);
    }

    /**
     * Sanitised encoded
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function filterEncoded($value, $options = [])
    {
        return $this->filter($value, 'encoded', $options);
    }

    /**
     * Sanitised Email
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function filterEmail($value, $options = [])
    {
        return $this->filter($value, 'email', $options);
    }

    /**
     * Sanitised Quotes
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function filterQuotes($value, $options = [])
    {
        return $this->filter($value, 'quotes', $options);
    }

    /**
     * Sanitize a website URL.
     *
     * @param string $value value to sanitize
     *
     * @return string sanitized
     */
    public function url($value)
    {
        return $this->filterURL($value);
    }

    /**
     * Sanitised URL
     *
     * @param       $value
     * @param array $options
     *
     * @return bool|string
     */
    public function filterURL($value, $options = [])
    {
        return $this->filter($value, 'url', $options);
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
        return preg_replace('/(_+)/', '_', preg_replace('/(\W*)/', '', str_replace(' ', '_', trim($value))));
    }


    /**
     * Format phone number. Supports 10-digit with extensions,
     * and defaults to international if cannot match US number.
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

        // Remove strings that aren't letter and number.
        $value = preg_replace('/[^a-z0-9]/', '', strtolower($value));

        // Remove 1 from front of number.
        if (preg_match('/^(\d{11})/', $value) && $value[0] === 1) {
            $value = substr($value, 1);
        }

        // Check for phone ext.
        if (!preg_match('/^\d$/', $value)) {
            $value =
                preg_replace('/^(\d{10})([a-z]+)(\d+)/', '$1ext$3', $value); // Replace 'x|ext|extension' with 'ext'.
            list($value, $ext) = explode('ext', $value); // Split number & ext.
        }

        // Add dashes: ___-___-____
        if (strlen($value) === 7) {
            $value = preg_replace('/(\d{3})(\d{4})/', '$1-$2', $value);
        } elseif (strlen($value) === 10) {
            $value = preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $value);
        }

        if (isset($ext) && $ext) {
            return $value . ' x' . $ext;
        }

        return $value;
    }
}
