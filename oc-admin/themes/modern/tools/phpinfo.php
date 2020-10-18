<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
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

function addHelp() {
    echo '<p>' . __('Show PHP info.') . '</p>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader() {
    ?>
    <h1>
        <?php _e('Tools'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    </h1>
    <?php
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string) {
    return sprintf(__('PHP info &raquo; %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

// Fix for phpinfo CSS messing with page.
ob_start();
phpinfo();
$phpinfo = ob_get_contents();
ob_end_clean();
$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);

osc_current_admin_theme_path('parts/header.php'); ?>
    <style>
        #phpinfo {}
        #phpinfo pre {margin: 0; font-family: monospace;}
        #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
        #phpinfo a:hover {text-decoration: underline;}
        #phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
        #phpinfo .center {text-align: center;}
        #phpinfo .center table {margin: 1em auto; text-align: left;}
        #phpinfo .center th {text-align: center !important;}
        #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
        #phpinfo h1 {font-size: 150%;}
        #phpinfo h2 {font-size: 125%;}
        #phpinfo .p {text-align: left;}
        #phpinfo .e {background-color: #ccf; width: 300px; font-weight: bold;}
        #phpinfo .h {background-color: #99c; font-weight: bold;}
        #phpinfo .v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
        #phpinfo .v i {color: #999;}
        #phpinfo img {float: right; border: 0;}
        #phpinfo hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}
    </style>
    <div id="backup-setting">
        <div id="backup-settings">
            <h2 class="render-title"><?php _e('PHP info'); ?></h2>
            <div id="phpinfo"><?php echo $phpinfo; ?></div>
        </div>
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
