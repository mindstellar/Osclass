<?php
/* 
 * @author: Navjot Tomer
 * 
 * OSClass – software for creating and publishing online classified advertising platforms
 *
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

use mindstellar\utility\SystemInfo;

if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}

function addHelp()
{
    echo '<p>' . __('Show important system information.') . '</p>';
}


osc_add_hook('help_box', 'addHelp');
function customPageHeader()
{
    ?>
    <h1>
        <?php _e('System Info'); ?>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"
           href="#help-box"></a>
    </h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string)
{
    return sprintf(__('System info &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');
osc_current_admin_theme_path('parts/header.php');
?>
    <div id="system-info">
        <?php
        $SystemInfo = new SystemInfo();
        $infoType   = Params::getParam('info-type'); ?>
        <ul class="nav nav-tabs mb-2">
            <li class="nav-item<?php if (!in_array($infoType, ['php-info', 'database-info'], true)) {
                echo ' show';
            } else {
                echo '';
            } ?>">
                <a class="nav-link"
                   href="<?php echo osc_admin_base_url(true) . '?' . http_build_query(
                           ['page' => 'tools', 'action' => 'system-info']
                       ); ?>"><?php _e('General Info'); ?></a>
            </li>
            <li class="nav-item<?php if ($infoType === 'php-info') {
                echo ' show';
            } else {
                echo '';
            } ?>">
                <a class="nav-link"
                   href="<?php echo osc_admin_base_url(true) . '?' . http_build_query(
                           ['page' => 'tools', 'action' => 'system-info', 'info-type' => 'php-info']
                       ); ?>"><?php _e('PHP Info'); ?></a>
            </li>
            <?php
            /**
             * @todo full database info page
             *
            <li class="nav-item<?php if ($infoType === 'database-info') {
                echo ' show';
            } else {
                echo '';
            } ?>">
                <a class="nav-link"
                   href="<?php echo osc_admin_base_url(true) . '?' . http_build_query(
                           ['page' => 'tools', 'action' => 'system-info', 'info-type' => 'database-info']
                       ); ?>"><?php _e('Database Info'); ?></a>
            </li>*/
            ?>
        </ul>
        <?php switch ($infoType) {
            case('php-info'):
                print($SystemInfo->getPHPInfoAllToStr());
                break;
            case('database-info'):
                break;
            default:
                ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php $systemInfoArray = (new SystemInfo())->getSystemInfoArr();
                    if (!empty($systemInfoArray)) {
                        foreach ($systemInfoArray as $info_type => $info_array) {
                            echo '<div class="col">';
                            echo '<div class="card">';
                            echo '<div class="card-header"><h6 class="card-title">' . ucfirst($info_type) . '</h6></div>';
                            echo '<div class="card-body">';
                            foreach ($info_array as $info_name => $info_value) {
                                echo '<div class="row">';
                                echo '<div class="col-sm-4 info-label">' . str_replace('_', ' ', $info_name) . '</div>' . PHP_EOL;
                                echo '<div class="col info-value">';
                                if ($info_value === false) {
                                    echo '<span class="text-danger">' . __('disabled') . '</span>';
                                } elseif ($info_value === true) {
                                    echo '<span class="text-success">' . __('enabled') . '</span>';
                                } elseif ($info_value === null) {
                                    echo '<span class="text-secondary">' . __('NA') . '</span>';
                                } else {
                                    echo $info_value;
                                }
                                echo '</div>' . PHP_EOL;
                                echo '</div>' . PHP_EOL;
                            }
                            echo '</div>' . PHP_EOL;
                            echo '</div>' . PHP_EOL;
                            echo '</div>' . PHP_EOL;
                        }
                    }
                    unset($systemInfoArray);
                    ?>
                </div>
            <?php
        }
        unset($infoType, $SystemInfo);
        ?>
    </div>
<?php
osc_current_admin_theme_path('parts/footer.php');