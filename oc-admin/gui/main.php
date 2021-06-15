<?php
/*
 *  Copyright 2020 Mindstellar Osclass
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
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
if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}
?>
<html dir="ltr" lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="robots" content="noindex, nofollow, noarchive"/>
    <meta name="googlebot" content="noindex, nofollow, noarchive"/>
    <title><?php echo osc_page_title(); ?> &raquo; <?php _e('Log in'); ?></title>
    <link type="text/css" media="screen" rel="stylesheet"
          href="<?php echo osc_assets_url('bootstrap/bootstrap.min.css'); ?>"/>
    <link type="text/css" media="screen" rel="stylesheet" href="style/backoffice_login.css"/>
    <link type="text/css" media="screen" rel="stylesheet"
          href="<?php echo osc_assets_url('bootstrap-icons/bootstrap-icons.css'); ?>"/>
    <?php osc_run_hook('admin_login_header'); ?>
</head>
<body class="container">
<div class="row">
    <div class="col-md-12 text-center">
        <div class="form-signin">
            <h1 class="mb-3">
                <a href="<?php echo View::newInstance()->_get('login_admin_url'); ?>"
                   title="<?php echo View::newInstance()->_get('login_admin_title'); ?>">
                    <img class="img-fluid" src="<?php echo View::newInstance()->_get('login_admin_image'); ?>"
                         title="<?php echo
                         View::newInstance()->_get('login_admin_title'); ?>"
                         alt="<?php echo View::newInstance()->_get('login_admin_title'); ?>"/>
                </a>
            </h1>
            <div class="mb-3">
                <?php osc_show_flash_message('admin', 'alert'); ?>
            </div>
            <?php require_once osc_admin_base_path() . View::newInstance()->_get('login_admin_form'); ?>
        </div>
        <script type="text/javascript" src="<?php echo osc_assets_url('jquery/jquery.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo osc_assets_url('bootstrap/bootstrap.min.js'); ?>"></script>
        <?php osc_run_hook('admin_login_footer'); ?>
    </div>
</div>
</body>
</html>
