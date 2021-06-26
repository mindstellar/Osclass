<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
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
                    echo nl2br(file_get_contents(ABS_PATH . 'CHANGELOG.md'));
                    ?>
                </ul>
            </div>
        </div>
    </div>
<?php
osc_current_admin_theme_path('parts/footer.php'); ?>