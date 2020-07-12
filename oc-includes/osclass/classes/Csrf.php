<?php
/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 11/07/20
 * Time: 5:32 PM
 * License is provided in root directory.
 */

namespace mindstellar\osclass\classes;

use mindstellar\osclass\classes\utility\Utils;
use Params;
use Plugins;
use Preference;
use Session;

/**
 * Class Csrf
 *
 * @package mindstellar\osclass\classes
 */
class Csrf
{
    /**
     * @var string
     */
    private $csrfTokenName;
    /**
     * @var string
     */
    private $csrfTokenValue;

    /**
     * @var string
     */
    public $csrfName;
    /**
     * @var \Session
     */
    private $session;

    /**
     * Csrf constructor.
     */
    public function __construct()
    {
        $this->session  = Session::newInstance();
        $this->csrfName = Preference::newInstance()->get('csrf_name');
        $this->setToken();
    }

    /**
     * Ger token from previous session if found or generate a new pair
     */
    private function setToken()
    {
        $token_name = $this->session->_get('token_name');
        if ($token_name !== '' && $this->session->_get($token_name) !== '') {
            $this->csrfTokenName  = $token_name;
            $this->csrfTokenValue = $this->session->_get($token_name);
        } else {
            $unique_token_name = $this->csrfName . '_' . mt_rand(0, mt_getrandmax());

            $this->csrfTokenName  = $unique_token_name;
            $this->csrfTokenValue = hash('sha256', mt_rand(0, mt_getrandmax()));
            $this->session->_set('token_name', $this->csrfTokenName);
            $this->session->_set($unique_token_name, $this->csrfTokenValue);
        }
    }

    /**
     * Initalize csrf guard
     */
    public static function init()
    {
        ob_start();
        $injectCsrf = static function () {
            $data = ob_get_clean();
            $data = (new self)->replaceForms($data);
            echo $data;
        };
        $functions  = Plugins::applyFilter('shutdown_functions', [$injectCsrf]);
        foreach ($functions as $f) {
            register_shutdown_function($f);
        }
    }

    /**
     * Replace form with csrf inputs added
     * @param $form_data_html
     *
     * @return string
     */
    public function replaceForms($form_data_html)
    {
        preg_match_all('/<form(.*?)>/is', $form_data_html, $matches, PREG_SET_ORDER);
        if (is_array($matches)) {
            foreach ($matches as $m) {
                if (strpos($m[1], 'nocsrf') !== false) {
                    continue;
                }
                $form_data_html = str_replace($m[0], "<form{$m[1]}>" . $this->tokenForm(), $form_data_html);
            }
        }

        return $form_data_html;
    }

    /**
     * Create a hidden CSRF token input field to be placed in a form
     *
     * @return string
     *
     */
    private function tokenForm()
    {
        return "<input type='hidden' name='CSRFName' value='" . $this->csrfTokenName . "' />
        <input type='hidden' name='CSRFToken' value='" . $this->csrfTokenValue . "' />";
    }

    /**
     * Create a CSRF token to be placed in a url
     *
     * @return string
     *
     */
    public function tokenUrl()
    {
        return 'CSRFName=' . $this->csrfTokenName . '&CSRFToken=' . $this->csrfTokenValue;
    }

    /**
     * Check if CSRF token is valid, die in other case
     */
    public function check()
    {
        $error         = false;
        $str_error     = '';
        $csrfTokenName = Params::getParam('CSRFName');
        $csrfToken     = Params::getParam('CSRFToken');
        if (!$csrfTokenName || !$csrfToken) {
            $str_error = _m('Probable invalid request.');
            $error     = true;
        } elseif (!$this->validateToken($csrfTokenName, $csrfToken)) {
            $str_error = _m('Invalid CSRF token.');
            $error     = true;
        }

        // check ajax request
        if (defined('IS_AJAX') && $error && IS_AJAX === true) {
            echo json_encode(array(
                'error' => 1,
                'msg'   => $str_error
            ));
            exit;
        }

        if ($error === true) {
            $this->setMessage($str_error);

            $this->errorRedirect();
        }
    }

    /**
     * Check if Token is valid
     *
     * @param $csrfTokenName
     * @param $csrfTokenValue
     *
     * @return bool
     */
    private function validateToken($csrfTokenName, $csrfTokenValue)
    {
        $name  = $this->session->_get('token_name');
        $token = $this->session->_get($csrfTokenName);

        return $name === $csrfTokenName && $token === $csrfTokenValue;
    }

    /**
     * Flash error message
     *
     * @param $str_error
     */
    private function setMessage($str_error)
    {
        if (OC_ADMIN) {
            $this->session->_setMessage('admin', $str_error, 'error');
        } else {
            $this->session->_setMessage('pubMessages', $str_error, 'error');
        }
    }

    private function errorRedirect()
    {
        $url = Utils::getHttpReferer();
        // drop session referer
        $this->session->_dropReferer();
        if ($url) {
            Utils::redirectTo($url);
        }

        if (OC_ADMIN) {
            Utils::redirectTo(osc_admin_base_url(true));
        } else {
            Utils::redirectTo(osc_base_url(true));
        }
    }

    /**
     * @return string
     */
    public function getCsrfTokenName()
    {
        return $this->csrfTokenName;
    }

    /**
     * @return string
     */
    public function getCsrfTokenValue()
    {
        return $this->csrfTokenValue;
    }
}
