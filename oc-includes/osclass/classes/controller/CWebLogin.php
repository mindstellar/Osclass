<?php

/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Class CWebLogin
 */
class CWebLogin extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        if (!osc_users_enabled()) {
            osc_add_flash_error_message(_m('Users not enabled'));
            $this->redirectTo(osc_base_url());
        }
        osc_run_hook('init_login');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('login_post'):     //post execution for the login
                if (!osc_users_enabled()) {
                    osc_add_flash_error_message(_m('Users are not enabled'));
                    $this->redirectTo(osc_base_url());
                }
                osc_csrf_check();
                osc_run_hook('before_validating_login');

                // e-mail or/and password is/are empty or incorrect
                $wrongCredentials = false;
                $email            = trim(Params::getParam('email'));
                $password         = Params::getParam('password', false, false);
                if ($email == '') {
                    osc_add_flash_error_message(_m('Please provide an email address'));
                    $wrongCredentials = true;
                }
                if ($password == '') {
                    osc_add_flash_error_message(_m('Empty passwords are not allowed. Please provide a password'));
                    $wrongCredentials = true;
                }
                if ($wrongCredentials) {
                    $this->redirectTo(osc_user_login_url());
                }

                if (osc_validate_email($email)) {
                    $user = User::newInstance()->findByEmail($email);
                }
                if (empty($user)) {
                    $user = User::newInstance()->findByUsername($email);
                }
                if (empty($user)) {
                    osc_add_flash_error_message(_m("The user doesn't exist"));
                    $this->redirectTo(osc_user_login_url());
                }
                if (osc_verify_password(
                    $password,
                    (isset($user['s_password']) ? $user['s_password'] : '')
                )
                ) {
                    if (@$user['s_password'] != '') {
                        if (preg_match('|\$2y\$([0-9]{2})\$|', $user['s_password'], $cost)) {
                            if ($cost[1] != BCRYPT_COST) {
                                User::newInstance()->update(
                                    array('s_password' => osc_hash_password($password)),
                                    array('pk_i_id' => $user['pk_i_id'])
                                );
                            }
                        } else {
                            User::newInstance()->update(
                                array('s_password' => osc_hash_password($password)),
                                array('pk_i_id' => $user['pk_i_id'])
                            );
                        }
                    }
                } else {
                    osc_add_flash_error_message(_m('The password is incorrect'));
                    $this->redirectTo(osc_user_login_url()); // @TODO if valid user, send email parameter back to the login form
                }
                // e-mail or/and IP is/are banned
                $banned =
                    osc_is_banned($email); // int 0: not banned or unknown, 1: email is banned, 2: IP is banned, 3: both email & IP are banned
                if ($banned & 1) {
                    osc_add_flash_error_message(_m('Your current email is not allowed'));
                }
                if ($banned & 2) {
                    osc_add_flash_error_message(_m('Your current IP is not allowed'));
                }
                if ($banned !== 0) {
                    $this->redirectTo(osc_user_login_url());
                }

                osc_run_hook('before_login');

                $url_redirect = osc_get_http_referer();
                if (osc_rewrite_enabled() && $url_redirect != '') {
                    // if comes from oc-admin/
                    if (strpos($url_redirect, 'oc-admin') !== false) {
                        $url_redirect = osc_user_dashboard_url();
                    } else {
                        $request_uri =
                            urldecode(preg_replace('@^' . osc_base_url() . '@', '', $url_redirect));
                        $tmp_ar      = explode('?', $request_uri);
                        $request_uri = $tmp_ar[0];
                        $rules       = Rewrite::newInstance()->listRules();
                        foreach ($rules as $match => $uri) {
                            if (preg_match('#' . $match . '#', $request_uri, $m)) {
                                $request_uri = preg_replace('#' . $match . '#', $uri, $request_uri);
                                if (preg_match(
                                    '|([&?]{1})page=([^&]*)|',
                                    '&' . $request_uri . '&',
                                    $match
                                )
                                ) {
                                    $page_redirect = $match[2];
                                    if ($page_redirect == '' || $page_redirect === 'login') {
                                        $url_redirect = osc_user_dashboard_url();
                                    }
                                }
                                break;
                            }
                        }
                    }
                }

                $uActions = new UserActions(false);
                $logged   = $uActions->bootstrap_login($user['pk_i_id']);

                if ($logged == 0) {
                    osc_add_flash_error_message(_m("The user doesn't exist"));
                } elseif ($logged == 1) {
                    if ((time() - strtotime($user['dt_access_date'])) > 1200) { // EACH 20 MINUTES
                        osc_add_flash_error_message(sprintf(
                            _m('The user has not been validated yet. Would you like to re-send your <a href="%s">activation?</a>'),
                            osc_user_resend_activation_link($user['pk_i_id'], $user['s_email'])
                        ));
                    } else {
                        osc_add_flash_error_message(_m('The user has not been validated yet'));
                    }
                } elseif ($logged == 2) {
                    osc_add_flash_error_message(_m('The user has been suspended'));
                } elseif ($logged == 3) {
                    if (Params::getParam('remember') == 1) {
                        //this include contains de osc_genRandomPassword function
                        require_once osc_lib_path() . 'osclass/helpers/hSecurity.php';
                        $secret = osc_genRandomPassword();

                        User::newInstance()->update(
                            array('s_secret' => $secret),
                            array('pk_i_id' => $user['pk_i_id'])
                        );

                        Cookie::newInstance()->set_expires(osc_time_cookie());
                        Cookie::newInstance()->push('oc_userId', $user['pk_i_id']);
                        Cookie::newInstance()->push('oc_userSecret', $secret);
                        Cookie::newInstance()->set();
                    }

                    if ($url_redirect == '') {
                        $url_redirect = osc_user_dashboard_url();
                    }

                    osc_run_hook('after_login', $user, $url_redirect);

                    $this->redirectTo(osc_apply_filter(
                        'correct_login_url_redirect',
                        $url_redirect
                    ));
                } else {
                    osc_add_flash_error_message(_m('This should never happen'));
                }

                if (!$user['b_enabled']) {
                    $this->redirectTo(osc_user_login_url());
                }

                $this->redirectTo(osc_user_login_url());
                break;
            case ('resend'):
                $id    = Params::getParam('id');
                $email = Params::getParam('email');
                $user  = User::newInstance()->findByPrimaryKey($id);
                if ($id == '' || $email == '' || !isset($user) || $user['b_active'] == 1
                    || $email != $user['s_email']
                ) {
                    osc_add_flash_error_message(_m('Incorrect link'));
                    $this->redirectTo(osc_user_login_url());
                }
                if ((time() - strtotime($user['dt_access_date'])) > 1200) { // EACH 20 MINUTES
                    if (osc_notify_new_user()) {
                        osc_run_hook('hook_email_admin_new_user', $user);
                    }
                    if (osc_user_validation_enabled()) {
                        osc_run_hook('hook_email_user_validation', $user, $user);
                    }
                    User::newInstance()->update(
                        array('dt_access_date' => date('Y-m-d H:i:s')),
                        array('pk_i_id' => $user['pk_i_id'])
                    );
                    osc_add_flash_ok_message(_m('Validation email re-sent'));
                } else {
                    osc_add_flash_warning_message(_m('We have just sent you an email to validate your account, you will have to wait a few minutes to resend it again'));
                }
                $this->redirectTo(osc_user_login_url());
                break;
            case ('recover'):        //form to recover the password (in this case we have the form in /gui/)
                $this->doView('user-recover.php');
                break;
            case ('recover_post'):   //post execution to recover the password
                osc_csrf_check();

                osc_run_hook('before_user_recover');

                // e-mail is incorrect
                if (!osc_validate_email(Params::getParam('s_email'))) {
                    osc_add_flash_error_message(_m('Invalid email address'));
                    $this->redirectTo(osc_recover_user_password_url());
                }

                $userActions = new UserActions(false);
                $success     = $userActions->recover_password();

                switch ($success) {
                    case (0): // recover ok
                        osc_add_flash_ok_message(_m('We have sent you an email with the instructions to reset your password'));
                        $this->redirectTo(osc_base_url());
                        break;
                    case (1): // e-mail does not exist
                        osc_add_flash_error_message(_m('We were not able to identify you given the information provided'));
                        $this->redirectTo(osc_recover_user_password_url());
                        break;
                    case (2): // recaptcha wrong
                        osc_add_flash_error_message(_m('The recaptcha code is wrong'));
                        $this->redirectTo(osc_recover_user_password_url());
                        break;
                }
                break;
            case ('forgot'):         //form to recover the password (in this case we have the form in /gui/)
                $user = User::newInstance()
                    ->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
                if ($user) {
                    $this->doView('user-forgot_password.php');
                } else {
                    osc_add_flash_error_message(_m('Sorry, the link is not valid'));
                    $this->redirectTo(osc_base_url());
                }
                break;
            case ('forgot_post'):
                osc_csrf_check();
                if ((Params::getParam('new_password', false, false) == '')
                    || (Params::getParam('new_password2', false, false) == '')
                ) {
                    osc_add_flash_warning_message(_m('Password cannot be blank'));
                    $this->redirectTo(osc_forgot_user_password_confirm_url(
                        Params::getParam('userId'),
                        Params::getParam('code')
                    ));
                }

                $user = User::newInstance()
                    ->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
                if ($user['b_enabled'] == 1) {
                    if (Params::getParam('new_password', false, false)
                        == Params::getParam('new_password2', false, false)
                    ) {
                        User::newInstance()->update(
                            array(
                                's_pass_code' => osc_genRandomPassword(50)
                                ,
                                's_pass_date' => date('Y-m-d H:i:s', 0)
                                ,
                                's_pass_ip'   => Params::getServerParam('REMOTE_ADDR')
                                ,
                                's_password'  => osc_hash_password(Params::getParam(
                                    'new_password',
                                    false,
                                    false
                                ))
                            ),
                            array('pk_i_id' => $user['pk_i_id'])
                        );
                        osc_add_flash_ok_message(_m('The password has been changed'));
                        $this->redirectTo(osc_user_login_url());
                    } else {
                        osc_add_flash_error_message(_m("Error, the password don't match"));
                        $this->redirectTo(osc_forgot_user_password_confirm_url(
                            Params::getParam('userId'),
                            Params::getParam('code')
                        ));
                    }
                } else {
                    osc_add_flash_error_message(_m('Sorry, the link is not valid'));
                }
                $this->redirectTo(osc_base_url());
                break;
            default:                //login
                Session::newInstance()->_setReferer(osc_get_http_referer());
                if (osc_logged_user_id()) {
                    $this->redirectTo(osc_user_dashboard_url());
                }
                $this->doView('user-login.php');
        }
    }

    //hopefully generic...

    /**
     * @param $file
     *
     * @return void
     */
    public function doView($file)
    {
        osc_run_hook('before_html');
        osc_current_web_theme_path($file);
        osc_run_hook('after_html');
    }
}

/* file end: ./CWebLogin.php */
