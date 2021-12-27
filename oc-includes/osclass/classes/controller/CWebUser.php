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
 * Class CWebUser
 */
class CWebUser extends WebSecBaseModel
{
    public function __construct()
    {
        parent::__construct();
        if (!osc_users_enabled()) {
            osc_add_flash_error_message(_m('Users not enabled'));
            $this->redirectTo(osc_base_url());
        }
        osc_run_hook('init_user');
    }

    //Business Layer...
    public function doModel()
    {
        switch ($this->action) {
            case ('dashboard'):      //dashboard...
                $max_items =
                    (Params::getParam('max_items') != '') ? Params::getParam('max_items') : 5;
                $aItems    =
                    Item::newInstance()->findByUserIDEnabled(osc_logged_user_id(), 0, $max_items);
                //calling the view...
                $this->_exportVariableToView('items', $aItems);
                $this->_exportVariableToView('max_items', $max_items);
                $this->doView('user-dashboard.php');
                break;
            case ('profile'):        //profile...
                $aUser      = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
                $aCountries = Country::newInstance()->listAll();
                $aRegions   = array();
                if ($aUser['fk_c_country_code'] != '') {
                    $aRegions = Region::newInstance()->findByCountry($aUser['fk_c_country_code']);
                } elseif (count($aCountries) > 0) {
                    $aRegions = Region::newInstance()->findByCountry($aCountries[0]['pk_c_code']);
                }
                $aCities = array();
                if ($aUser['fk_i_region_id'] != '') {
                    $aCities = City::newInstance()->findByRegion($aUser['fk_i_region_id']);
                } elseif (count($aRegions) > 0) {
                    $aCities = City::newInstance()->findByRegion($aRegions[0]['pk_i_id']);
                }

                // user profile info description | user-profile.php @ frontend
                $aLocale = $aUser['locale'];
                foreach ($aLocale as $locale => $aInfo) {
                    $aUser['locale'][$locale]['s_info'] =
                        osc_apply_filter(
                            'user_profile_info',
                            $aInfo['s_info'],
                            $aUser['pk_i_id'],
                            $aInfo['fk_c_locale_code']
                        );
                }

                //calling the view...
                $this->_exportVariableToView('user', $aUser);
                $this->_exportVariableToView('countries', $aCountries);
                $this->_exportVariableToView('regions', $aRegions);
                $this->_exportVariableToView('cities', $aCities);
                $this->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());

                $this->doView('user-profile.php');
                break;
            case ('profile_post'):   //profile post...
                osc_csrf_check();
                $userId = Session::newInstance()->_get('userId');

                $userActions = new UserActions(false);
                $success     = $userActions->edit($userId);
                if ($success == 1 || $success == 2) {
                    osc_add_flash_ok_message(_m('Your profile has been updated successfully'));
                } else {
                    osc_add_flash_error_message($success);
                }
                $this->redirectTo(osc_user_profile_url());
                break;
            case ('alerts'):         //alerts
                $aAlerts =
                    Alerts::newInstance()->findByUser(Session::newInstance()->_get('userId'));
                $user    =
                    User::newInstance()->findByPrimaryKey(Session::newInstance()->_get('userId'));
                foreach ($aAlerts as $k => $a) {
                    $array_conditions = (array)json_decode($a['s_search'], true);

                    $search = new Search();
                    $search->setJsonAlert($array_conditions);
                    $search->notFromUser(Session::newInstance()->_get('userId'));
                    $search->limit(0, 3);

                    $aAlerts[$k]['items'] = $search->doSearch();
                }

                $this->_exportVariableToView('alerts', $aAlerts);
                View::newInstance()->_reset('alerts');
                $this->_exportVariableToView('user', $user);
                $this->doView('user-alerts.php');
                break;
            case ('change_email'):           //change email
                $this->doView('user-change_email.php');
                break;
            case ('change_email_post'):      //change email post
                osc_csrf_check();
                if (osc_validate_email(Params::getParam('new_email'))) {
                    $user = User::newInstance()->findByEmail(Params::getParam('new_email'));
                    if (isset($user['pk_i_id'])) {
                        osc_add_flash_error_message(_m('The specified e-mail is already in use'));
                        $this->redirectTo(osc_change_user_email_url());
                    } else {
                        $userEmailTmp                 = array();
                        $userEmailTmp['fk_i_user_id'] = Session::newInstance()->_get('userId');
                        $userEmailTmp['s_new_email']  = Params::getParam('new_email');

                        UserEmailTmp::newInstance()->insertOrUpdate($userEmailTmp);

                        $code = osc_genRandomPassword(30);
                        $date = date('Y-m-d H:i:s');

                        $userManager = new User();
                        $userManager->update(
                            array(
                                's_pass_code' => $code,
                                's_pass_date' => $date,
                                's_pass_ip'   => Params::getServerParam('REMOTE_ADDR')
                            ),
                            array('pk_i_id' => Session::newInstance()->_get('userId'))
                        );

                        $validation_url = osc_change_user_email_confirm_url(Session::newInstance()
                            ->_get('userId'), $code);
                        osc_run_hook(
                            'hook_email_new_email',
                            Params::getParam('new_email'),
                            $validation_url
                        );
                        $this->redirectTo(osc_user_profile_url());
                    }
                } else {
                    osc_add_flash_error_message(_m('The specified e-mail is not valid'));
                    $this->redirectTo(osc_change_user_email_url());
                }
                break;
            case ('change_username'):        //change username
                $this->doView('user-change_username.php');
                break;
            case ('change_username_post'):   //change username
                osc_csrf_check();
                $username = osc_sanitize_username(Params::getParam('s_username'));
                osc_run_hook(
                    'before_username_change',
                    Session::newInstance()->_get('userId'),
                    $username
                );
                if ($username != '') {
                    $user = User::newInstance()->findByUsername($username);
                    if (isset($user['s_username'])) {
                        osc_add_flash_error_message(_m('The specified username is already in use'));
                    } elseif (osc_is_username_blacklisted($username)) {
                        osc_add_flash_error_message(_m('The specified username is not valid, it contains some invalid words'));
                    } else {
                        User::newInstance()->update(
                            array('s_username' => $username),
                            array('pk_i_id' => Session::newInstance()->_get('userId'))
                        );
                        osc_add_flash_ok_message(_m('The username was updated'));
                        osc_run_hook(
                            'after_username_change',
                            Session::newInstance()->_get('userId'),
                            Params::getParam('s_username')
                        );
                        $this->redirectTo(osc_user_profile_url());
                    }
                } else {
                    osc_add_flash_error_message(_m('The specified username could not be empty'));
                }
                $this->redirectTo(osc_change_user_username_url());
                break;
            case ('change_password'):        //change password
                $this->doView('user-change_password.php');
                break;
            case 'change_password_post':    //change password post
                osc_csrf_check();
                $user =
                    User::newInstance()->findByPrimaryKey(Session::newInstance()->_get('userId'));

                if ((Params::getParam('password', false, false) == '')
                    || (Params::getParam('new_password', false, false) == '')
                    || (Params::getParam('new_password2', false, false) == '')
                ) {
                    osc_add_flash_warning_message(_m('Password cannot be blank'));
                    $this->redirectTo(osc_change_user_password_url());
                }

                if (!osc_verify_password(
                    Params::getParam('password', false, false),
                    $user['s_password']
                )
                ) {
                    osc_add_flash_error_message(_m("Current password doesn't match"));
                    $this->redirectTo(osc_change_user_password_url());
                }

                if (!Params::getParam('new_password', false, false)) {
                    osc_add_flash_error_message(_m("Passwords can't be empty"));
                    $this->redirectTo(osc_change_user_password_url());
                }

                if (Params::getParam('new_password', false, false)
                    != Params::getParam('new_password2', false, false)
                ) {
                    osc_add_flash_error_message(_m("Passwords don't match"));
                    $this->redirectTo(osc_change_user_password_url());
                }

                User::newInstance()->update(
                    array(
                        's_password' => osc_hash_password(Params::getParam(
                            'new_password',
                            false,
                            false
                        ))
                    ),
                    array('pk_i_id' => Session::newInstance()->_get('userId'))
                );

                osc_add_flash_ok_message(_m('Password has been changed'));
                $this->redirectTo(osc_user_profile_url());
                break;
            case 'items':                   // view items user
                $itemsPerPage =
                    (Params::getParam('itemsPerPage') != '') ? Params::getParam('itemsPerPage')
                        : 10;
                $page         = (Params::getParam('iPage') > 0) ? Params::getParam('iPage') - 1 : 0;
                $itemType     = Params::getParam('itemType');
                $total_items  =
                    Item::newInstance()->countItemTypesByUserID(osc_logged_user_id(), $itemType);
                $total_pages  = ceil($total_items / $itemsPerPage);
                $items        = Item::newInstance()
                    ->findItemTypesByUserID(
                        osc_logged_user_id(),
                        $page * $itemsPerPage,
                        $itemsPerPage,
                        $itemType
                    );

                $this->_exportVariableToView('items', $items);
                $this->_exportVariableToView('search_total_pages', $total_pages);
                $this->_exportVariableToView('search_total_items', $total_items);
                $this->_exportVariableToView('items_per_page', $itemsPerPage);
                $this->_exportVariableToView('items_type', $itemType);
                $this->_exportVariableToView('search_page', $page);

                $this->doView('user-items.php');
                break;
            case 'activate_alert':
                $email  = Params::getParam('email');
                $secret = Params::getParam('secret');

                $result = 0;
                if ($email != '' && $secret != '') {
                    $result = Alerts::newInstance()->activate($email);
                }

                if ($result == 1) {
                    osc_add_flash_ok_message(_m('Alert activated'));
                } else {
                    osc_add_flash_error_message(_m('Oops! There was a problem trying to activate your alert. Please contact an administrator'));
                }

                $this->redirectTo(osc_base_url());
                break;
            case 'unsub_alert':
                $email  = Params::getParam('email');
                $secret = Params::getParam('secret');
                $id     = Params::getParam('id');

                $alert  = Alerts::newInstance()->findByPrimaryKey($id);
                $result = 0;
                if (!empty($alert) && $email == $alert['s_email']
                    && $secret == $alert['s_secret']
                ) {
                    $result = Alerts::newInstance()->unsub($id);
                }

                if ($result == 1) {
                    osc_add_flash_ok_message(_m('Unsubscribed correctly'));
                } else {
                    osc_add_flash_error_message(_m('Oops! There was a problem trying to unsubscribe you. Please contact an administrator'));
                }

                $this->redirectTo(osc_user_alerts_url());
                break;
            case 'delete':
                $id     = Params::getParam('id');
                $secret = Params::getParam('secret');
                if (osc_is_web_user_logged_in()) {
                    $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
                    osc_run_hook('before_user_delete', $user);
                    View::newInstance()->_exportVariableToView('user', $user);
                    if (!empty($user) && osc_logged_user_id() == $id
                        && $secret == $user['s_secret']
                    ) {
                        try {
                            User::newInstance()->deleteUser(osc_logged_user_id());
                        } catch (Exception $e) {
                            trigger_error($e->getMessage(), E_USER_WARNING);
                        }

                        Session::newInstance()->_drop('userId');
                        Session::newInstance()->_drop('userName');
                        Session::newInstance()->_drop('userEmail');
                        Session::newInstance()->_drop('userPhone');

                        Cookie::newInstance()->pop('oc_userId');
                        Cookie::newInstance()->pop('oc_userSecret');
                        Cookie::newInstance()->set();

                        osc_add_flash_ok_message(_m('Your account have been deleted'));
                        $this->redirectTo(osc_base_url());
                    } else {
                        osc_add_flash_error_message(_m('Oops! you can not do that'));
                        $this->redirectTo(osc_user_dashboard_url());
                    }
                } else {
                    osc_add_flash_error_message(_m('Oops! you can not do that'));
                    $this->redirectTo(osc_base_url());
                }
                break;
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
        Session::newInstance()->_clearVariables();
        osc_run_hook('after_html');
    }
}

/* file end: ./CWebUser.php */
