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

function addHelp()
{
    echo '<p>' . __('Show PHP info.') . '</p>';
}


osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1>
        <?php _e('Tools'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    </h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string)
{
    return sprintf(__('PHP info &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

// Fix for phpinfo CSS messing with page.
ob_start();
phpinfo();
$phpinfo = ob_get_clean();
$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);

osc_current_admin_theme_path('parts/header.php'); ?>
<style>
    #phpinfo {
    }

    #phpinfo pre {
        margin: 0;
        font-family: monospace;
    }

    #phpinfo a:link {
        color: #009;
        text-decoration: none;
        background-color: #fff;
    }

    #phpinfo a:hover {
        text-decoration: underline;
    }

    #phpinfo table {
        border-collapse: collapse;
        border: 0;
        width: 934px;
        box-shadow: 1px 2px 3px #ccc;
    }

    #phpinfo .center {
        text-align: center;
    }

    #phpinfo .center table {
        margin: 1em auto;
        text-align: left;
    }

    #phpinfo .center th {
        text-align: center !important;
    }

    #phpinfo td, th {
        border: 1px solid #666;
        font-size: 75%;
        vertical-align: baseline;
        padding: 4px 5px;
    }

    #phpinfo h1 {
        font-size: 150%;
    }

    #phpinfo h2 {
        font-size: 125%;
    }

    #phpinfo .p {
        text-align: left;
    }

    #phpinfo .e {
        background-color: #ccf;
        width: 300px;
        font-weight: bold;
    }

    #phpinfo .h {
        background-color: #99c;
        font-weight: bold;
    }

    #phpinfo .v {
        background-color: #ddd;
        max-width: 300px;
        overflow-x: auto;
        word-wrap: break-word;
    }

    #phpinfo .v i {
        color: #999;
    }

    #phpinfo img {
        float: right;
        border: 0;
    }

    #phpinfo hr {
        width: 934px;
        background-color: #ccc;
        border: 0;
        height: 1px;
    }
</style>
<div id="backup-setting">
    <div id="backup-settings">
        <h2 class="render-title"><?php _e('PHP info'); ?></h2>
        <div id="phpinfo"><?php echo $phpinfo; ?></div>
    </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
