<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
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


$customPageHeader = static function () { ?>
    <h1><?php printf(__('Osclass %s'), OSCLASS_VERSION); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    </h1>
    <?php
};
osc_add_hook('admin_page_header', $customPageHeader);

$customPageTitle = static function ($string) {
    return sprintf(__('Osclass %s &raquo; %s'), OSCLASS_VERSION, $string);
};
osc_add_filter('admin_title', $customPageTitle);

unset($customPageTitle, $customPageHeader);

osc_current_admin_theme_path('parts/header.php');
?>
    <div class="row-wrapper">
        <div class="widget-box">
            <div class="widget-box-title">
                <h3>Osclass <?php echo OSCLASS_VERSION; ?></h3>
            </div>
            <div class="widget-box-content">
                <b class="stats-title">Changelog</b>
                <ul style="list-style-type: disc !important;">
                    <?php
                    echo preg_replace('/.+/', '<li>$0</li>',
                        file_get_contents(ABS_PATH . 'CHANGELOG.txt'));
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php
osc_current_admin_theme_path('parts/footer.php'); ?>