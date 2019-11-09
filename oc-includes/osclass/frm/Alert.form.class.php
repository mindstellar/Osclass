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

    /**
     * Class AlertForm
     */
class AlertForm extends Form {

    /**
     * @return bool
     */
    public static function user_id_hidden()
    {
        parent::generic_input_hidden('alert_userId', osc_logged_user_id() );
        return true;
    }

    /**
     * @return bool
     */
    public static function email_hidden()
    {
        parent::generic_input_hidden('alert_email', osc_logged_user_email() );
        return true;
    }

    /**
     * @return string
     */
    public static function default_email_text()
    {
        return __('Enter your e-mail');
    }

    /**
     * @return bool
     */
    public static function email_text()
    {
        $value = '';
        if ( osc_logged_user_email() == '' ) {
            $value = self::default_email_text();
        }
        parent::generic_input_text('alert_email', $value );
        return true;
    }

    /**
     * @return bool
     */
    public static function page_hidden()
    {
        parent::generic_input_hidden('page', 'search');
        return true;
    }

    /**
     * @return bool
     */
    public static function alert_hidden()
    {
        parent::generic_input_hidden('alert', osc_search_alert() );
        return true;
    }
}

