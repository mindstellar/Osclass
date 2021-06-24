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
 * Helper Error
 *
 * @package    Osclass
 * @subpackage Helpers
 * @author     Osclass
 */

/**
 * Kill Osclass with an error message
 *
 * @param string $message Error message
 * @param string $title   Error title
 *
 * @since 1.2
 *
 */
function osc_die($title, $message)
{
    ?>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" type="text/css" media="all"
              href="<?php echo osc_get_absolute_url(); ?>oc-includes/assets/bootstrap/bootstrap.min.css"/>
        <link rel="stylesheet" type="text/css" media="all"
              href="<?php echo osc_get_absolute_url(); ?>oc-includes/assets/bootstrap-icons/bootstrap-icons.css"/>
        <script src="<?php echo osc_get_absolute_url(); ?>oc-includes/assets/bootstrap/bootstrap.min.js"
                type="text/javascript"></script>
    </head>
    <body>
    <div id="wrapper" class="container-md">
        <div class="row">
            <div class="offset-md-1 col-md-10 col-sm-12 align-self-center p-5" id="container">
                <div class="card rounded-3" tabindex="-1">
                    <div class="card-body bg-light" id="content">
                        <h1 class="display-6"><i class="small bi bi-info-circle"></i> <?php echo $title; ?></h1>
                        <p class="alert alert-danger shadow"><?php echo $message; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </html>
    <?php die();
}


/**
 * @param      $param
 * @param bool $htmlencode
 * @param bool $quotes_encode
 *
 * @return string
 */
function getErrorParam($param, $htmlencode = false, $quotes_encode = true)
{
    if ($param == '') {
        return '';
    }
    if (!isset($_SERVER[$param])) {
        return '';
    }
    $value = $_SERVER[$param];
    if ($htmlencode) {
        if ($quotes_encode) {
            return htmlspecialchars(stripslashes($value), ENT_QUOTES);
        }

        return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
    }
    return $value;
}


/**
 * @param $array
 *
 * @return string
 */
function strip_slashes_extended_e($array)
{
    if (is_array($array)) {
        foreach ($array as $k => &$v) {
            $v = strip_slashes_extended_e($v);
        }
    } else {
        $array = stripslashes($array);
    }

    return $array;
}


/**
 * @return string
 */
function osc_get_absolute_url()
{
    $protocol = (getErrorParam('HTTPS') === 'on' || getErrorParam('HTTPS') == 1
        || getErrorParam('HTTP_X_FORWARDED_PROTO') === 'https') ? 'https' : 'http';

    return $protocol . '://' . getErrorParam('HTTP_HOST')
        . preg_replace(
            '/((oc-admin)|(oc-includes)|(oc-content)|([a-z]+\.php)|(\?.*)).*/i',
            '',
            getErrorParam('REQUEST_URI', false, false)
        );
}
