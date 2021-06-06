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
