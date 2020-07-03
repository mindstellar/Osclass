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
	 * Class CWebLanguage
	 */
	class CWebLanguage extends BaseModel
    {
        public function __construct()
        {
            parent::__construct();
            osc_run_hook( 'init_language' );
        }

        // business layer...
        public function doModel()
        {
            $locale = Params::getParam('locale');

            if(osc_validate_locale_code($locale)) {
                Session::newInstance()->_set( 'userLocale', $locale);
            }

            $redirect_url = '';
            if(Params::getServerParam('HTTP_REFERER', false, false) != '') {
                $redirect_url = Params::getServerParam('HTTP_REFERER', false, false);
            } else {
                $redirect_url = osc_base_url(true);
            }

            $this->redirectTo($redirect_url);
        }

        // hopefully generic...

		/**
		 * @param $file
		 *
		 * @return mixed|void
		 */
		public function doView( $file ) {
		}
    }

    /* file end: ./language.php */
