<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

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
 * @param $alert
 * @param $email
 * @param $secret
 */
function fn_email_alert_validation($alert, $email, $secret)
{
    $user['s_name'] = '';


    // send alert validation email
    $prefLocale       = osc_language();
    $page             = Page::newInstance()->findByInternalName('email_alert_validation');
    $page_description = $page['locale'];

    $_title = osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'email_alert_validation_title',
            $page_description[$prefLocale]['s_title'],
            $alert,
            $email,
            $secret
        )
    );
    $_body  = osc_apply_filter(
        'email_description',
        osc_apply_filter(
            'email_alert_validation_description',
            $page_description[$prefLocale]['s_text'],
            $alert,
            $email,
            $secret
        )
    );

    $validation_link = osc_user_activate_alert_url($alert['pk_i_id'], $secret, $email);
    $validation_link = '<a href ="' . $validation_link . '" target="_blank" >' . $validation_link . '</a>';

    $words   = array();
    $words[] = array(
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{VALIDATION_LINK}'
    );
    $words[] = array(
        $user['s_name'],
        $email,
        $validation_link
    );

    $title = osc_apply_filter(
        'email_alert_validation_title_after',
        osc_mailBeauty($_title, $words),
        $alert,
        $email,
        $secret
    );
    $body  = osc_apply_filter(
        'email_alert_validation_description_after',
        osc_mailBeauty($_body, $words),
        $alert,
        $email,
        $secret
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $email,
        'to_name'  => $user['s_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_alert_validation', 'fn_email_alert_validation');

/**
 * @param $user
 * @param $ads
 * @param $s_search
 * @param $items
 * @param $totalItems
 */
function fn_alert_email_hourly($user, $ads, $s_search, $items, $totalItems)
{
    $prefLocale       = osc_language();
    $page             = Page::newInstance()->findByInternalName('alert_email_hourly');
    $page_description = $page['locale'];

    $_title = osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'alert_email_hourly_title',
            $page_description[$prefLocale]['s_title'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );
    $_body  = osc_apply_filter(
        'email_description',
        osc_apply_filter(
            'alert_email_hourly_description',
            $page_description[$prefLocale]['s_text'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );

    if ($user['fk_i_user_id'] != 0) {
        $user = User::newInstance()->findByPrimaryKey($user['fk_i_user_id']);
    } else {
        $user['s_name'] = $user['s_email'];
    }

    $unsub_link = osc_user_unsubscribe_alert_url(
        $s_search['pk_i_id'],
        $user['s_email'],
        $s_search['s_secret']
    );
    $unsub_link = '<a href="' . $unsub_link . '">' . __('unsubscribe alert') . '</a>';

    $words   = array();
    $words[] = array(
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ADS}',
        '{UNSUB_LINK}'
    );
    $words[] = array(
        $user['s_name'],
        $user['s_email'],
        $ads,
        $unsub_link
    );

    $title = osc_apply_filter(
        'alert_email_hourly_title_after',
        osc_mailBeauty($_title, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );
    $body  = osc_apply_filter(
        'alert_email_hourly_description_after',
        osc_mailBeauty($_body, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $user['s_email'],
        'to_name'  => $user['s_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_alert_email_hourly', 'fn_alert_email_hourly');

/**
 * @param $user
 * @param $ads
 * @param $s_search
 * @param $items
 * @param $totalItems
 */
function fn_alert_email_daily($user, $ads, $s_search, $items, $totalItems)
{
    $prefLocale       = osc_language();
    $page             = Page::newInstance()->findByInternalName('alert_email_daily');
    $page_description = $page['locale'];

    $_title = osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'alert_email_daily_title',
            $page_description[$prefLocale]['s_title'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );
    $_body  = osc_apply_filter(
        'email_description',
        osc_apply_filter(
            'alert_email_daily_description',
            $page_description[$prefLocale]['s_text'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );

    if (isset($user['fk_i_user_id']) && $user['fk_i_user_id'] != 0) {
        $user = User::newInstance()->findByPrimaryKey($user['fk_i_user_id']);
    } else {
        $user['s_name'] = $user['s_email'];
    }

    $unsub_link = osc_user_unsubscribe_alert_url(
        $s_search['pk_i_id'],
        $user['s_email'],
        $s_search['s_secret']
    );
    $unsub_link = '<a href="' . $unsub_link . '">' . __('unsubscribe alert') . '</a>';

    $words   = array();
    $words[] = array(
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ADS}',
        '{UNSUB_LINK}'
    );
    $words[] = array(
        $user['s_name'],
        $user['s_email'],
        $ads,
        $unsub_link
    );

    $title = osc_apply_filter(
        'alert_email_daily_title_after',
        osc_mailBeauty($_title, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );
    $body  = osc_apply_filter(
        'alert_email_daily_description_after',
        osc_mailBeauty($_body, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $user['s_email'],
        'to_name'  => $user['s_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_alert_email_daily', 'fn_alert_email_daily');

/**
 * @param $user
 * @param $ads
 * @param $s_search
 * @param $items
 * @param $totalItems
 */
function fn_alert_email_weekly($user, $ads, $s_search, $items, $totalItems)
{
    $prefLocale       = osc_language();
    $page             = Page::newInstance()->findByInternalName('alert_email_weekly');
    $page_description = $page['locale'];

    $_title = osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'alert_email_weekly_title',
            $page_description[$prefLocale]['s_title'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );
    $_body  = osc_apply_filter(
        'email_description',
        osc_apply_filter(
            'alert_email_weekly_description',
            $page_description[$prefLocale]['s_text'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems
        )
    );

    if ($user['fk_i_user_id'] != 0) {
        $user = User::newInstance()->findByPrimaryKey($user['fk_i_user_id']);
    } else {
        $user['s_name'] = $user['s_email'];
    }

    $unsub_link = osc_user_unsubscribe_alert_url(
        $s_search['pk_i_id'],
        $user['s_email'],
        $s_search['s_secret']
    );
    $unsub_link = '<a href="' . $unsub_link . '">' . __('unsubscribe alert') . '</a>';

    $words   = array();
    $words[] = array(
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ADS}',
        '{UNSUB_LINK}'
    );
    $words[] = array(
        $user['s_name'],
        $user['s_email'],
        $ads,
        $unsub_link
    );

    $title = osc_apply_filter(
        'alert_email_weekly_title_after',
        osc_mailBeauty($_title, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );
    $body  = osc_apply_filter(
        'alert_email_weekly_description_after',
        osc_mailBeauty($_body, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $user['s_email'],
        'to_name'  => $user['s_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_alert_email_weekly', 'fn_alert_email_weekly');

/**
 * @param $user
 * @param $ads
 * @param $s_search
 * @param $items
 * @param $totalItems
 */
function fn_alert_email_instant($user, $ads, $s_search, $items, $totalItems)
{
    $prefLocale       = osc_language();
    $page             = Page::newInstance()->findByInternalName('alert_email_instant');
    $page_description = $page['locale'];

    $_title = osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'alert_email_instant_title',
            $page_description[$prefLocale]['s_title'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems,
            $items,
            $totalItems
        )
    );
    $_body  = osc_apply_filter(
        'email_description',
        osc_apply_filter(
            'alert_email_instant_description',
            $page_description[$prefLocale]['s_text'],
            $user,
            $ads,
            $s_search,
            $items,
            $totalItems,
            $items,
            $totalItems
        )
    );

    if ($user['fk_i_user_id'] != 0) {
        $user = User::newInstance()->findByPrimaryKey($user['fk_i_user_id']);
    } else {
        $user['s_name'] = $user['s_email'];
    }

    $unsub_link = osc_user_unsubscribe_alert_url(
        $s_search['pk_i_id'],
        $user['s_email'],
        $s_search['s_secret']
    );
    $unsub_link = '<a href="' . $unsub_link . '">' . __('unsubscribe alert') . '</a>';

    $words   = array();
    $words[] = array(
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ADS}',
        '{UNSUB_LINK}'
    );
    $words[] = array(
        $user['s_name'],
        $user['s_email'],
        $ads,
        $unsub_link
    );

    $title = osc_apply_filter(
        'alert_email_instant_title_after',
        osc_mailBeauty($_title, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );
    $body  = osc_apply_filter(
        'alert_email_instant_description_after',
        osc_mailBeauty($_body, $words),
        $user,
        $ads,
        $s_search,
        $items,
        $totalItems
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $user['s_email'],
        'to_name'  => $user['s_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_alert_email_instant', 'fn_alert_email_instant');

/**
 * @param $aComment
 *
 */
function fn_email_comment_validated($aComment)
{
    $mPages = new Page();
    $locale = osc_current_user_locale();
    $aPage  = $mPages->findByInternalName('email_comment_validated');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $words   = array();
        $words[] = array(
            '{COMMENT_AUTHOR}',
            '{COMMENT_EMAIL}',
            '{COMMENT_TITLE}',
            '{COMMENT_BODY}',
            '{ITEM_URL}',
            '{ITEM_LINK}',
            '{ITEM_TITLE}'
        );
        $words[] = array(
            $aComment['s_author_name'],
            $aComment['s_author_email'],
            $aComment['s_title'],
            $aComment['s_body'],
            osc_item_url(),
            '<a href="' . osc_item_url() . '">' . osc_item_url() . '</a>',
            osc_item_title()
        );

        $title = osc_apply_filter(
            'email_comment_validated_title_after',
            osc_mailBeauty(osc_apply_filter(
                'email_title',
                osc_apply_filter('email_comment_validated_title', $content['s_title'], $aComment)
            ), $words),
            $aComment
        );
        $body  = osc_apply_filter(
            'email_comment_validated_description_after',
            osc_mailBeauty(osc_apply_filter(
                'email_description',
                osc_apply_filter('email_comment_validated_description', $content['s_text'], $aComment)
            ), $words),
            $aComment
        );

        $emailParams = array(
            'from'     => _osc_from_email_aux(),
            'to'       => $aComment['s_author_email'],
            'to_name'  => $aComment['s_author_name'],
            'subject'  => $title,
            'body'     => $body,
            'alt_body' => $body
        );
        osc_sendMail($emailParams);
    }
}


osc_add_hook('hook_email_comment_validated', 'fn_email_comment_validated');

/**
 * @param $item
 */
function fn_email_new_item_non_register_user($item)
{
    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_new_item_non_register_user');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url   = osc_item_url();
    $item_url   = '<a href="' . $item_url . '" >' . $item_url . '</a>';
    $edit_url   = osc_item_edit_url($item['s_secret'], $item['pk_i_id']);
    $delete_url = osc_item_delete_url($item['s_secret'], $item['pk_i_id']);

    $words   = array();
    $words[] = array(
        '{ITEM_ID}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ITEM_TITLE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{EDIT_LINK}',
        '{EDIT_URL}',
        '{DELETE_LINK}',
        '{DELETE_URL}'
    );
    $words[] = array(
        $item['pk_i_id'],
        $item['s_contact_name'],
        $item['s_contact_email'],
        $item['s_title'],
        osc_item_url(),
        $item_url,
        '<a href="' . $edit_url . '">' . $edit_url . '</a>',
        $edit_url,
        '<a href="' . $delete_url . '">' . $delete_url . '</a>',
        $delete_url
    );

    $title = osc_apply_filter(
        'email_new_item_non_register_user_title_after',
        osc_mailBeauty(osc_apply_filter(
            'email_title',
            osc_apply_filter('email_new_item_non_register_user_title', $content['s_title'], $item)
        ), $words),
        $item
    );
    $body  = osc_apply_filter(
        'email_new_item_non_register_user_description_after',
        osc_mailBeauty(osc_apply_filter(
            'email_description',
            osc_apply_filter('email_new_item_non_register_user_description', $content['s_text'], $item)
        ), $words),
        $item
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $item['s_contact_email'],
        'to_name'  => $item['s_contact_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_new_item_non_register_user', 'fn_email_new_item_non_register_user');

/**
 * @param $user
 * @param $password_url
 */
function fn_email_user_forgot_password($user, $password_url)
{
    $aPage  = Page::newInstance()->findByInternalName('email_user_forgot_password');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $words   = array();
        $words[] = array(
            '{USER_NAME}',
            '{USER_EMAIL}',
            '{PASSWORD_LINK}',
            '{PASSWORD_URL}',
            '{DATE_TIME}'
        );
        $words[] = array(
            $user['s_name'],
            $user['s_email'],
            '<a href="' . $password_url . '">' . $password_url . '</a>',
            $password_url,
            date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:00')
        );

        $title = osc_apply_filter(
            'email_user_forgot_pass_word_title_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_title',
                    osc_apply_filter('email_user_forgot_pass_word_title', $content['s_title'], $user, $password_url)
                ),
                $words
            ),
            $user,
            $password_url
        );
        $body  = osc_apply_filter(
            'email_user_forgot_password_description_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_description',
                    osc_apply_filter(
                        'email_user_forgot_password_description',
                        $content['s_text'],
                        $user,
                        $password_url
                    )
                ),
                $words
            ),
            $user,
            $password_url
        );

        $emailParams = array(
            'from'     => _osc_from_email_aux(),
            'to'       => $user['s_email'],
            'to_name'  => $user['s_name'],
            'subject'  => $title,
            'body'     => $body,
            'alt_body' => $body
        );

        osc_sendMail($emailParams);
    }
}


osc_add_hook('hook_email_user_forgot_password', 'fn_email_user_forgot_password');

/**
 * @param $user
 */
function fn_email_user_registration($user)
{
    $pageManager = new Page();
    $locale      = osc_current_user_locale();
    $aPage       = $pageManager->findByInternalName('email_user_registration');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $words   = array();
        $words[] = array(
            '{USER_NAME}',
            '{USER_EMAIL}'
        );
        $words[] = array(
            $user['s_name'],
            $user['s_email']
        );

        $title = osc_apply_filter(
            'email_user_registration_title_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_title',
                    osc_apply_filter('email_user_registration_title', $content['s_title'], $user)
                ),
                $words
            ),
            $user
        );
        $body  = osc_apply_filter(
            'email_user_registration_description_after',
            osc_mailBeauty(osc_apply_filter(
                'email_description',
                osc_apply_filter('email_user_registration_description', $content['s_text'], $user)
            ), $words),
            $user
        );

        $emailParams = array(
            'from'     => _osc_from_email_aux(),
            'to'       => $user['s_email'],
            'to_name'  => $user['s_name'],
            'subject'  => $title,
            'body'     => $body,
            'alt_body' => $body
        );

        osc_sendMail($emailParams);
    }
}


osc_add_hook('hook_email_user_registration', 'fn_email_user_registration');

/**
 * @param $new_email
 * @param $validation_url
 */
function fn_email_new_email($new_email, $validation_url)
{
    $locale = osc_current_user_locale();
    $aPage  = Page::newInstance()->findByInternalName('email_new_email');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $words   = array();
        $words[] = array(
            '{USER_NAME}',
            '{USER_EMAIL}',
            '{VALIDATION_LINK}',
            '{VALIDATION_URL}'
        );
        $words[] = array(
            Session::newInstance()->_get('userName'),
            Params::getParam('new_email'),
            '<a href="' . $validation_url . '" >' . $validation_url . '</a>',
            $validation_url
        );

        $title = osc_apply_filter(
            'email_new_email_title_after',
            osc_mailBeauty(osc_apply_filter(
                'email_title',
                osc_apply_filter('email_new_email_title', $content['s_title'], $new_email, $validation_url)
            ), $words),
            $new_email,
            $validation_url
        );
        $body  = osc_apply_filter(
            'email_new_email_description_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_description',
                    osc_apply_filter('email_new_email_description', $content['s_text'], $new_email, $validation_url)
                ),
                $words
            ),
            $new_email,
            $validation_url
        );

        $emailParams = array(
            'from'     => _osc_from_email_aux(),
            'to'       => $new_email,
            'to_name'  => Session::newInstance()->_get('userName'),
            'subject'  => $title,
            'body'     => $body,
            'alt_body' => $body
        );
        osc_sendMail($emailParams);
        osc_add_flash_ok_message(_m("We've sent you an e-mail. Follow its instructions to validate the changes"));
    } else {
        osc_add_flash_error_message(
            _m('We tried to sent you an e-mail, but it failed. Please, contact an administrator')
        );
    }
}


osc_add_hook('hook_email_new_email', 'fn_email_new_email');

/**
 * @param $user
 * @param $input
 */
function fn_email_user_validation($user, $input)
{
    $mPages = new Page();
    $locale = osc_current_user_locale();
    $aPage  = $mPages->findByInternalName('email_user_validation');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $validation_url = osc_user_activate_url($user['pk_i_id'], $input['s_secret']);
        $words          = array();
        $words[]        = array(
            '{USER_NAME}',
            '{USER_EMAIL}',
            '{VALIDATION_LINK}',
            '{VALIDATION_URL}'
        );
        $words[]        = array(
            $user['s_name'],
            $user['s_email'],
            '<a href="' . $validation_url . '" >' . $validation_url . '</a>',
            $validation_url
        );

        $title = osc_apply_filter(
            'email_user_validation_title_after',
            osc_mailBeauty(osc_apply_filter(
                'email_title',
                osc_apply_filter('email_user_validation_title', $content['s_title'], $user, $input)
            ), $words),
            $user,
            $input
        );
        $body  = osc_apply_filter(
            'email_user_validation_description_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_description',
                    osc_apply_filter('email_user_validation_description', $content['s_text'], $user, $input)
                ),
                $words
            ),
            $user,
            $input
        );

        $emailParams = array(
            'from'     => _osc_from_email_aux(),
            'to'       => $user['s_email'],
            'to_name'  => $user['s_name'],
            'subject'  => $title,
            'body'     => $body,
            'alt_body' => $body
        );
        osc_sendMail($emailParams);
    }
}


osc_add_hook('hook_email_user_validation', 'fn_email_user_validation');

/**
 * @param $aItem
 *
 */
function fn_email_send_friend($aItem)
{
    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_send_friend');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url = osc_item_url();
    $item_url = '<a href="' . $item_url . '" >' . $item_url . '</a>';

    $words   = array();
    $words[] = array(
        '{FRIEND_NAME}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{FRIEND_EMAIL}',
        '{ITEM_TITLE}',
        '{COMMENT}',
        '{ITEM_URL}',
        '{ITEM_LINK}'
    );
    $words[] = array(
        $aItem['friendName'],
        $aItem['yourName'],
        $aItem['yourEmail'],
        $aItem['friendEmail'],
        $aItem['s_title'],
        $aItem['message'],
        osc_item_url(),
        $item_url
    );

    $title = osc_apply_filter('email_send_friend_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_send_friend_title', $content['s_title'], $aItem)
    ), $words), $aItem);
    $body  = osc_apply_filter(
        'email_send_friend_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_send_friend_description', $content['s_text'], $aItem)
            ),
            $words
        ),
        $aItem
    );

    $emailParams = array(
        'from'      => _osc_from_email_aux(),
        'from_name' => osc_page_title(),
        'to'        => $aItem['friendEmail'],
        'to_name'   => $aItem['friendName'],
        'reply_to'  => $aItem['yourEmail'],
        'subject'   => $title,
        'body'      => $body
    );

    if (osc_notify_contact_friends()) {
        $admins      = Admin::newInstance()->listAll();
        $adminEmails = array();
        foreach ($admins as $admin) {
            if (!empty($admin['s_email'])) {
                $adminEmails[] = $admin['s_email'];
            }
        }
        $emailParams['add_bcc'] = $adminEmails;
    }

    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_send_friend', 'fn_email_send_friend');

/**
 * @param $aItem
 *
 */
function fn_email_item_inquiry($aItem)
{
    $id          = $aItem['id'];
    $yourEmail   = $aItem['yourEmail'];
    $yourName    = $aItem['yourName'];
    $phoneNumber = $aItem['phoneNumber'];
    $message     = nl2br(strip_tags($aItem['message']));

    $path = null;
    $item = Item::newInstance()->findByPrimaryKey($id);
    View::newInstance()->_exportVariableToView('item', $item);

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_item_inquiry');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url  = osc_item_url();
    $item_link = '<a href="' . $item_url . '" >' . $item_url . '</a>';

    $words   = array();
    $words[] = array(
        '{CONTACT_NAME}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{USER_PHONE}',
        '{ITEM_TITLE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{COMMENT}'
    );

    $words[] = array(
        $item['s_contact_name'],
        $yourName,
        $yourEmail,
        $phoneNumber,
        $item['s_title'],
        $item_url,
        $item_link,
        $message
    );

    $title = osc_apply_filter('email_item_inquiry_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_item_inquiry_title', $content['s_title'], $aItem)
    ), $words), $aItem);
    $body  = osc_apply_filter(
        'email_item_inquiry_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_item_inquiry_description', $content['s_text'], $aItem)
            ),
            $words
        ),
        $aItem
    );

    $emailParams = array(
        'from'      => _osc_from_email_aux(),
        'from_name' => osc_page_title(),
        'to'        => $item['s_contact_email'],
        'to_name'   => $item['s_contact_name'],
        'reply_to'  => $yourEmail,
        'subject'   => $title,
        'body'      => $body,
        'alt_body'  => $body
    );

    if (osc_notify_contact_item()) {
        $admins      = Admin::newInstance()->listAll();
        $adminEmails = array();
        foreach ($admins as $admin) {
            if (!empty($admin['s_email'])) {
                $adminEmails[] = $admin['s_email'];
            }
        }
        $emailParams['add_bcc'] = $adminEmails;
    }

    if (osc_item_attachment()) {
        $attachment   = Params::getFiles('attachment');
        $resourceName = $attachment['name'];
        $tmpName      = $attachment['tmp_name'];
        $path         = osc_uploads_path() . time() . '_' . $resourceName;

        if (!is_writable(osc_uploads_path())) {
            osc_add_flash_error_message(_m('There has been some errors sending the message'));
        }

        if (!move_uploaded_file($tmpName, $path)) {
            unset($path);
        }
    }

    if (isset($path)) {
        $emailParams['attachment'] = $path;
    }

    osc_sendMail($emailParams);

    @unlink($path);
}


osc_add_hook('hook_email_item_inquiry', 'fn_email_item_inquiry');

/**
 * @param $aItem
 *
 */
function fn_email_new_comment_admin($aItem)
{
    $authorName  = trim(strip_tags($aItem['authorName']));
    $authorEmail = trim(strip_tags($aItem['authorEmail']));
    $body        = trim($aItem['body']);
    // only \n -> <br/>
    $body   = nl2br(strip_tags($body));
    $title  = $aItem['title'];
    $itemId = $aItem['id'];

    $item = Item::newInstance()->findByPrimaryKey($itemId);
    View::newInstance()->_exportVariableToView('item', $item);
    $itemURL = osc_item_url();
    $itemURL = '<a href="' . $itemURL . '" >' . $itemURL . '</a>';

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_new_comment_admin');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $words   = array();
    $words[] = array(
        '{COMMENT_AUTHOR}',
        '{COMMENT_EMAIL}',
        '{COMMENT_TITLE}',
        '{COMMENT_TEXT}',
        '{ITEM_TITLE}',
        '{ITEM_ID}',
        '{ITEM_URL}',
        '{ITEM_LINK}'
    );
    $words[] = array(
        $authorName,
        $authorEmail,
        $title,
        $body,
        $item['s_title'],
        $itemId,
        osc_item_url(),
        $itemURL
    );

    $title_email = osc_apply_filter(
        'email_new_comment_admin_title_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_title',
                osc_apply_filter('email_new_comment_admin_title', $content['s_title'], $aItem)
            ),
            $words
        ),
        $aItem
    );
    $body_email  = osc_apply_filter(
        'email_new_comment_admin_description_after',
        osc_mailBeauty(osc_apply_filter(
            'email_description',
            osc_apply_filter('email_new_comment_admin_description', $content['s_text'], $aItem)
        ), $words),
        $aItem
    );

    $admins = Admin::newInstance()->listAll();
    foreach ($admins as $admin) {
        if (!empty($admin['s_email'])) {
            $emailParams = array(
                'from'     => _osc_from_email_aux(),
                'to'       => $admin['s_email'],
                'to_name'  => __('Admin'),
                'subject'  => $title_email,
                'body'     => $body_email,
                'alt_body' => $body_email
            );
            osc_sendMail($emailParams);
        }
    }
}


osc_add_hook('hook_email_new_comment_admin', 'fn_email_new_comment_admin');

/**
 * @param $item
 *
 */
function fn_email_item_validation($item)
{
    View::newInstance()->_exportVariableToView('item', $item);
    $contactEmail = $item['s_contact_email'];
    $contactName  = $item['s_contact_name'];
    $mPages       = new Page();
    $locale       = osc_current_user_locale();
    $aPage        = $mPages->findByInternalName('email_item_validation');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url  = osc_item_url();
    $item_link = '<a href="' . $item_url . '" >' . $item_url . '</a>';

    $all = '';

    if (isset($item['locale'])) {
        foreach ($item['locale'] as $locale => $data) {
            $locale_name = OSCLocale::newInstance()->findByCode($locale);
            $all         .= '<br/>';
            if (isset($locale_name[0]) && isset($locale_name[0]['s_name'])) {
                $all .= __('Language') . ': ' . $locale_name[0]['s_name'] . '<br/>';
            } else {
                $all .= __('Language') . ': ' . $locale . '<br/>';
            }
            $all .= __('Title') . ': ' . $data['s_title'] . '<br/>';
            $all .= __('Description') . ': ' . $data['s_description'] . '<br/>';
            $all .= '<br/>';
        }
    } else {
        $all .= __('Title') . ': ' . $item['s_title'] . '<br/>';
        $all .= __('Description') . ': ' . $item['s_description'] . '<br/>';
    }

    // Format activation URL
    $validation_url = osc_item_activate_url($item['s_secret'], $item['pk_i_id']);

    $words   = array();
    $words[] = array(
        '{ITEM_DESCRIPTION_ALL_LANGUAGES}',
        '{ITEM_DESCRIPTION}',
        '{ITEM_COUNTRY}',
        '{ITEM_PRICE}',
        '{ITEM_REGION}',
        '{ITEM_CITY}',
        '{ITEM_ID}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ITEM_TITLE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{VALIDATION_LINK}',
        '{VALIDATION_URL}'
    );
    $words[] = array(
        $all,
        $item['s_description'],
        $item['s_country'],
        osc_format_price($item['i_price']),
        $item['s_region'],
        $item['s_city'],
        $item['pk_i_id'],
        $item['s_contact_name'],
        $item['s_contact_email'],
        $item['s_title'],
        $item_url,
        $item_link,
        '<a href="' . $validation_url . '" >' . $validation_url . '</a>',
        $validation_url
    );

    $title = osc_apply_filter('email_item_validation_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_item_validation_title', $content['s_title'], $item)
    ), $words), $item);
    $body  = osc_apply_filter(
        'email_item_validation_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_item_validation_description', $content['s_text'], $item)
            ),
            $words
        ),
        $item
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $contactEmail,
        'to_name'  => $contactName,
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );
    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_item_validation', 'fn_email_item_validation');

/**
 * @param $item
 *
 */
function fn_email_admin_new_item($item)
{
    View::newInstance()->_exportVariableToView('item', $item);
    $title  = osc_item_title();
    $mPages = new Page();
    $locale = osc_current_user_locale();
    $aPage  = $mPages->findByInternalName('email_admin_new_item');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url  = osc_item_url();
    $item_link = '<a href="' . $item_url . '" >' . $item_url . '</a>';

    $all = '';

    if (isset($item['locale'])) {
        foreach ($item['locale'] as $locale => $data) {
            $locale_name = OSCLocale::newInstance()->findByCode($locale);
            $all         .= '<br/>';
            if (isset($locale_name[0]) && isset($locale_name[0]['s_name'])) {
                $all .= __('Language') . ': ' . $locale_name[0]['s_name'] . '<br/>';
            } else {
                $all .= __('Language') . ': ' . $locale . '<br/>';
            }
            $all .= __('Title') . ': ' . $data['s_title'] . '<br/>';
            $all .= __('Description') . ': ' . $data['s_description'] . '<br/>';
            $all .= '<br/>';
        }
    } else {
        $all .= __('Title') . ': ' . $item['s_title'] . '<br/>';
        $all .= __('Description') . ': ' . $item['s_description'] . '<br/>';
    }

    // Format activation URL
    $validation_url = osc_item_activate_url($item['s_secret'], $item['pk_i_id']);

    // Format admin edit URL
    $admin_edit_url = osc_item_admin_edit_url($item['pk_i_id']);

    $words   = array();
    $words[] = array(
        '{EDIT_LINK}',
        '{EDIT_URL}',
        '{ITEM_DESCRIPTION_ALL_LANGUAGES}',
        '{ITEM_DESCRIPTION}',
        '{ITEM_COUNTRY}',
        '{ITEM_PRICE}',
        '{ITEM_REGION}',
        '{ITEM_CITY}',
        '{ITEM_ID}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ITEM_TITLE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{VALIDATION_LINK}',
        '{VALIDATION_URL}'
    );
    $words[] = array(
        '<a href="' . $admin_edit_url . '" >' . $admin_edit_url . '</a>',
        $admin_edit_url,
        $all,
        $item['s_description'],
        $item['s_country'],
        osc_format_price($item['i_price']),
        $item['s_region'],
        $item['s_city'],
        $item['pk_i_id'],
        $item['s_contact_name'],
        $item['s_contact_email'],
        $item['s_title'],
        $item_url,
        $item_link,
        '<a href="' . $validation_url . '" >' . $validation_url . '</a>',
        $validation_url
    );

    $title = osc_apply_filter('email_admin_new_item_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_admin_new_item_title', $content['s_title'], $item)
    ), $words), $item);
    $body  = osc_apply_filter(
        'email_admin_new_item_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_admin_new_item_description', $content['s_text'], $item)
            ),
            $words
        ),
        $item
    );

    $admins = Admin::newInstance()->listAll();
    foreach ($admins as $admin) {
        if (!empty($admin['s_email'])) {
            $emailParams = array(
                'from'     => _osc_from_email_aux(),
                'to'       => $admin['s_email'],
                'to_name'  => __('Admin'),
                'subject'  => $title,
                'body'     => $body,
                'alt_body' => $body
            );
            osc_sendMail($emailParams);
        }
    }
}


osc_add_hook('hook_email_admin_new_item', 'fn_email_admin_new_item');

/**
 * @param $item
 *
 */
function fn_email_item_validation_non_register_user($item)
{
    View::newInstance()->_exportVariableToView('item', $item);

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_item_validation_non_register_user');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url   = osc_item_url();
    $item_link  = '<a href="' . $item_url . '" >' . $item_url . '</a>';
    $edit_url   = osc_item_edit_url($item['s_secret'], $item['pk_i_id']);
    $delete_url = osc_item_delete_url($item['s_secret'], $item['pk_i_id']);

    $all = '';

    if (isset($item['locale'])) {
        foreach ($item['locale'] as $locale => $data) {
            $locale_name = OSCLocale::newInstance()->findByCode($locale);
            $all         .= '<br/>';
            if (isset($locale_name[0]) && isset($locale_name[0]['s_name'])) {
                $all .= __('Language') . ': ' . $locale_name[0]['s_name'] . '<br/>';
            } else {
                $all .= __('Language') . ': ' . $locale . '<br/>';
            }
            $all .= __('Title') . ': ' . $data['s_title'] . '<br/>';
            $all .= __('Description') . ': ' . $data['s_description'] . '<br/>';
            $all .= '<br/>';
        }
    } else {
        $all .= __('Title') . ': ' . $item['s_title'] . '<br/>';
        $all .= __('Description') . ': ' . $item['s_description'] . '<br/>';
    }

    // Format activation URL
    $validation_url = osc_item_activate_url($item['s_secret'], $item['pk_i_id']);

    $words   = array();
    $words[] = array(
        '{ITEM_DESCRIPTION_ALL_LANGUAGES}',
        '{ITEM_DESCRIPTION}',
        '{ITEM_COUNTRY}',
        '{ITEM_PRICE}',
        '{ITEM_REGION}',
        '{ITEM_CITY}',
        '{ITEM_ID}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{ITEM_TITLE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{VALIDATION_LINK}',
        '{VALIDATION_URL}',
        '{EDIT_LINK}',
        '{EDIT_URL}',
        '{DELETE_LINK}',
        '{DELETE_URL}'
    );
    $words[] = array(
        $all,
        $item['s_description'],
        $item['s_country'],
        osc_format_price($item['i_price']),
        $item['s_region'],
        $item['s_city'],
        $item['pk_i_id'],
        $item['s_contact_name'],
        $item['s_contact_email'],
        $item['s_title'],
        $item_url,
        $item_link,
        '<a href="' . $validation_url . '" >' . $validation_url . '</a>',
        $validation_url,
        '<a href="' . $edit_url . '">' . $edit_url . '</a>',
        $edit_url,
        '<a href="' . $delete_url . '">' . $delete_url . '</a>',
        $delete_url
    );

    $title = osc_apply_filter(
        'email_item_validation_non_register_user_title_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_title',
                osc_apply_filter('email_item_validation_non_register_user_title', $content['s_title'], $item)
            ),
            $words
        ),
        $item
    );
    $body  = osc_apply_filter(
        'email_item_validation_non_register_user_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_item_validation_non_register_user_description', $content['s_text'], $item)
            ),
            $words
        ),
        $item
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $item['s_contact_email'],
        'to_name'  => $item['s_contact_name'],
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_item_validation_non_register_user', 'fn_email_item_validation_non_register_user');

/**
 * @param $user
 */
function fn_email_admin_new_user($user)
{
    $pageManager = new Page();
    $locale      = osc_current_user_locale();
    $aPage       = $pageManager->findByInternalName('email_admin_new_user');

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    if (null !== $content) {
        $words   = array();
        $words[] = array(
            '{USER_NAME}',
            '{USER_EMAIL}'
        );
        $words[] = array(
            $user['s_name'],
            $user['s_email']
        );

        $title = osc_apply_filter(
            'email_admin_user_registration_title_after',
            osc_mailBeauty(osc_apply_filter(
                'email_title',
                osc_apply_filter('email_admin_user_registration_title', $content['s_title'], $user)
            ), $words),
            $user
        );
        $body  = osc_apply_filter(
            'email_admin_user_regsitration_description_after',
            osc_mailBeauty(
                osc_apply_filter(
                    'email_description',
                    osc_apply_filter('email_admin_user_regsitration_description', $content['s_text'], $user)
                ),
                $words
            ),
            $user
        );

        $admins = Admin::newInstance()->listAll();
        foreach ($admins as $admin) {
            if (!empty($admin['s_email'])) {
                $emailParams = array(
                    'from'     => _osc_from_email_aux(),
                    'to'       => $admin['s_email'],
                    'to_name'  => osc_page_title(),
                    'subject'  => $title,
                    'body'     => $body,
                    'alt_body' => $body,
                );
                osc_sendMail($emailParams);
            }
        }
    }
}


osc_add_hook('hook_email_admin_new_user', 'fn_email_admin_new_user');

/**
 * @param $id
 * @param $yourEmail
 * @param $yourName
 * @param $phoneNumber
 * @param $message
 */
function fn_email_contact_user($id, $yourEmail, $yourName, $phoneNumber, $message)
{
    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_contact_user');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $words   = array();
    $words[] = array(
        '{CONTACT_NAME}',
        '{USER_NAME}',
        '{USER_EMAIL}',
        '{USER_PHONE}',
        '{COMMENT}'
    );
    $words[] = array(
        osc_user_name(),
        $yourName,
        $yourEmail,
        $phoneNumber,
        $message
    );

    $title = osc_apply_filter('email_item_inquiry_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter(
            'email_item_inquiry_title',
            $content['s_title'],
            $id,
            $yourEmail,
            $yourName,
            $phoneNumber,
            $message
        )
    ), $words), $id, $yourEmail, $yourName, $phoneNumber, $message);
    $body  = osc_apply_filter(
        'email_item_inquiry_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter(
                    'email_item_inquiry_description',
                    $content['s_text'],
                    $id,
                    $yourEmail,
                    $yourName,
                    $phoneNumber,
                    $message
                )
            ),
            $words
        ),
        $id,
        $yourEmail,
        $yourName,
        $phoneNumber,
        $message
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => osc_user_email(),
        'to_name'  => osc_user_name(),
        'reply_to' => $yourEmail,
        'subject'  => $title,
        'body'     => $body,
        'alt_body' => $body
    );

    if (osc_notify_contact_item()) {
        $admins      = Admin::newInstance()->listAll();
        $adminEmails = array();
        foreach ($admins as $admin) {
            if (!empty($admin['s_email'])) {
                $adminEmails[] = $admin['s_email'];
            }
        }
        $emailParams['add_bcc'] = $adminEmails;
    }

    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_contact_user', 'fn_email_contact_user');

/**
 * @param $aItem
 *
 */
function fn_email_new_comment_user($aItem)
{
    $authorName  = trim(strip_tags($aItem['authorName']));
    $authorEmail = trim(strip_tags($aItem['authorEmail']));
    $body        = trim(strip_tags($aItem['body']));
    $body        = nl2br($body);
    $title       = $aItem['title'];
    $itemId      = $aItem['id'];

    $item = Item::newInstance()->findByPrimaryKey($itemId);
    View::newInstance()->_exportVariableToView('item', $item);
    $itemURL = osc_item_url();
    $itemURL = '<a href="' . $itemURL . '" >' . $itemURL . '</a>';

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_new_comment_user');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $words   = array();
    $words[] = array(
        '{COMMENT_AUTHOR}',
        '{COMMENT_EMAIL}',
        '{COMMENT_TITLE}',
        '{COMMENT_TEXT}',
        '{ITEM_TITLE}',
        '{ITEM_ID}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{SELLER_NAME}',
        '{SELLER_EMAIL}'
    );
    $words[] = array(
        $authorName,
        $authorEmail,
        $title,
        $body,
        $item['s_title'],
        $itemId,
        osc_item_url(),
        $itemURL,
        $item['s_contact_name'],
        $item['s_contact_email']
    );

    $title_email = osc_apply_filter(
        'email_new_comment_user_title_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_title',
                osc_apply_filter('email_new_comment_user_title', $content['s_title'], $aItem)
            ),
            $words
        ),
        $aItem
    );
    $body_email  = osc_apply_filter(
        'email_new_comment_user_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_new_comment_user_description', $content['s_text'], $aItem)
            ),
            $words
        ),
        $aItem
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $item['s_contact_email'],
        'to_name'  => $item['s_contact_name'],
        'subject'  => $title_email,
        'body'     => $body_email,
        'alt_body' => $body_email
    );
    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_new_comment_user', 'fn_email_new_comment_user');

/**
 * @param $data
 */
function fn_email_new_admin($data)
{

    $name     = trim(strip_tags($data['s_name']));
    $username = trim(strip_tags($data['s_username']));

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_new_admin');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $words   = array();
    $words[] = array(
        '{ADMIN_NAME}',
        '{USERNAME}',
        '{PASSWORD}',
        '{WEB_ADMIN_LINK}'
    );
    $words[] = array(
        $name,
        $username,
        $data['s_password'],
        '<a href="' . osc_admin_base_url() . '">' . osc_page_title() . '</a>',
    );

    $title_email = osc_apply_filter('email_new_admin_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_new_admin_title', $content['s_title'], $data)
    ), $words), $data);
    $body_email  = osc_apply_filter(
        'email_new_admin_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_new_admin_description', $content['s_text'], $data)
            ),
            $words
        ),
        $data
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $data['s_email'],
        'to_name'  => $data['s_name'],
        'subject'  => $title_email,
        'body'     => $body_email,
        'alt_body' => $body_email
    );
    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_new_admin', 'fn_email_new_admin');


/**
 * @param $aItem
 *
 */
function fn_email_warn_expiration($aItem)
{

    $itemId = $aItem['pk_i_id'];

    View::newInstance()->_exportVariableToView('item', $aItem);
    $itemURL = osc_item_url();
    $itemURL = '<a href="' . $itemURL . '" >' . $itemURL . '</a>';

    $mPages = new Page();
    $aPage  = $mPages->findByInternalName('email_warn_expiration');
    $locale = osc_current_user_locale();

    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $words       = array();
    $words[]     = array(
        '{USER_NAME}',
        '{ITEM_TITLE}',
        '{ITEM_ID}',
        '{ITEM_EXPIRATION_DATE}',
        '{ITEM_URL}',
        '{ITEM_LINK}',
        '{SELLER_NAME}',
        '{SELLER_EMAIL}',
        '{CONTACT_NAME}',
        '{CONTACT_EMAIL}'
    );
    $words[]     = array(
        $aItem['s_contact_name'],
        $aItem['s_title'],
        $itemId,
        $aItem['dt_expiration'],
        osc_item_url(),
        $itemURL,
        $aItem['s_contact_name'],
        $aItem['s_contact_email'],
        $aItem['s_contact_name'],
        $aItem['s_contact_email']
    );
    $title_email = osc_apply_filter(
        'email_warn_expiration_title_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_title',
                osc_apply_filter('email_warn_expiration_title', $content['s_title'], $aItem)
            ),
            $words
        ),
        $aItem
    );
    $body_email  = osc_apply_filter(
        'email_warn_expiration_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_warn_expiration_description', $content['s_text'], $aItem)
            ),
            $words
        ),
        $aItem
    );

    $emailParams = array(
        'from'     => _osc_from_email_aux(),
        'to'       => $aItem['s_contact_email'],
        'to_name'  => $aItem['s_contact_name'],
        'subject'  => $title_email,
        'body'     => $body_email,
        'alt_body' => $body_email
    );
    osc_sendMail($emailParams);
}


osc_add_hook('hook_email_warn_expiration', 'fn_email_warn_expiration');

/**
 * @param $result
 */
function fn_email_auto_upgrade($result)
{

    $body = __('<p>Dear {WEB_TITLE} admin,</p>');
    if ($result['error'] == 0 || $result['error'] == 6) {
        $title = __('{WEB_TITLE} - Your site has upgraded to Osclass {VERSION}');
        $body  .= __('<p>Your site at {WEB_LINK} has been updated automatically to Osclass {VERSION}</p>');
        if ($result['error'] == 6) {
            $body .= __('<p>There were some minor errors removing temporary files. '
                .'Please manually remove the "oc-content/downloads/oc-temp" folder</p>');
        }
    } else {
        $title = __('{WEB_TITLE} - We failed trying to upgrade your site to Osclass {VERSION}');
        $body  .= '<p>We failed trying to upgrade your site to Osclass {VERSION}. Heres is the error message: {MESSAGE}</p>';
    }
    $body .= '<p>If you experience any issues or need support, we will be happy to help you at the Osclass support forums</p>';
    $body .= '<p><a href="https://osclass.discourse.group/">https://osclass.discourse.group/</a></p>';
    $body .= '<p>The Osclass team</p>';

    $words   = array();
    $words[] = array(
        '{MESSAGE}',
        '{VERSION}'
    );
    $words[] = array(
        $result['message'],
        $result['version']
    );

    $title = osc_apply_filter('email_after_auto_upgrade_title_after', osc_mailBeauty(osc_apply_filter(
        'email_title',
        osc_apply_filter('email_after_auto_upgrade_title', $title, $result)
    ), $words), $result);
    $body  = osc_apply_filter(
        'email_after_auto_upgrade_description_after',
        osc_mailBeauty(
            osc_apply_filter(
                'email_description',
                osc_apply_filter('email_after_auto_upgrade_description', $body, $result)
            ),
            $words
        ),
        $result
    );

    $admins = Admin::newInstance()->listAll();
    foreach ($admins as $admin) {
        if (!empty($admin['s_email']) && ($admin['b_moderator'] == 0)) {
            $emailParams = array(
                'from'     => _osc_from_email_aux(),
                'to'       => $admin['s_email'],
                'to_name'  => osc_page_title(),
                'subject'  => $title,
                'body'     => $body,
                'alt_body' => $body,
            );
            osc_sendMail($emailParams);
        }
    }
}


osc_add_hook('after_auto_upgrade', 'fn_email_auto_upgrade', 10);

/**
 * @return string
 */
function _osc_from_email_aux()
{
    $tmp = osc_mailserver_mail_from();

    return !empty($tmp) ? $tmp : osc_contact_email();
}


/* file end: ./oc-includes/osclass/emails.php */
