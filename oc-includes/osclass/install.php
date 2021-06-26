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

error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE);

define('ABS_PATH', dirname(dirname(__DIR__)) . '/');
define('LIB_PATH', ABS_PATH . 'oc-includes/');
define('CONTENT_PATH', ABS_PATH . 'oc-content/');
define('TRANSLATIONS_PATH', CONTENT_PATH . 'languages/');
define('OSC_INSTALLING', 1);

require_once LIB_PATH . 'vendor/autoload.php';

if (extension_loaded('mysqli')) {
    require_once LIB_PATH . 'osclass/helpers/hPreference.php';
}
require_once LIB_PATH . 'osclass/helpers/hCache.php';
require_once LIB_PATH . 'osclass/helpers/hDatabaseInfo.php';
require_once LIB_PATH . 'osclass/helpers/hDefines.php';
require_once LIB_PATH . 'osclass/helpers/hErrors.php';
require_once LIB_PATH . 'osclass/helpers/hLocale.php';
require_once LIB_PATH . 'osclass/helpers/hSearch.php';
require_once LIB_PATH . 'osclass/helpers/hPlugins.php';
require_once LIB_PATH . 'osclass/helpers/hUtils.php';
require_once LIB_PATH . 'osclass/helpers/hTranslations.php';
require_once LIB_PATH . 'osclass/helpers/hSanitize.php';
require_once LIB_PATH . 'osclass/default-constants.php';
require_once LIB_PATH . 'osclass/install-functions.php';
require_once LIB_PATH . 'osclass/utils.php';
require_once LIB_PATH . 'osclass/locales.php';

Params::init();
Session::newInstance()->session_start();

$step = (int)Params::getParam('step');
if ($step < 1) {
    $step = 1;
}

$locales = osc_listLocales();
$jsonLocales = osc_file_get_contents(osc_get_languages_json_url());
$jsonLocales = json_decode($jsonLocales, true);
$install_locale = Params::getParam('install_locale');

if (Params::getParam('install_locale') && !(strlen($install_locale) > 5)) {
    Session::newInstance()->_set('userLocale', Params::getParam('install_locale'));
    Session::newInstance()->_set('adminLocale', Params::getParam('install_locale'));
}

if (Session::newInstance()->_get('adminLocale')
    && array_key_exists(Session::newInstance()->_get('adminLocale'), $locales)
) {
    $current_locale = Session::newInstance()->_get('adminLocale');
} elseif (isset($locales['en_US'])) {
    $current_locale = 'en_US';
} elseif (key($locales)) {
    $current_locale = key($locales);
} else {
    $current_locale = 'en_US';
}
Session::newInstance()->_set('userLocale', $current_locale);
Session::newInstance()->_set('adminLocale', $current_locale);

Translation::newInstance(true);

if (is_osclass_installed()) {
    $message =
        __("Looks like you've already installed Osclass. To reinstall please clear your old database tables first.");
    osc_die('Osclass &raquo; Error', $message);
}

switch ($step) {
    case 1:
        $requirements = get_requirements();
        $error        = check_requirements($requirements);
        if ($error === false && $install_locale && !array_key_exists($install_locale, $locales)
            && array_key_exists($install_locale, $jsonLocales)
        ) {
            $langFolder = osc_translations_path() . $install_locale;
            mkdir($langFolder, 0755, true);

            $files = osc_get_language_files_urls($install_locale);
            foreach ($files as $file => $url) {
                $content = osc_file_get_contents($url);
                file_put_contents($langFolder . '/' . $file, $content);
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            die;
        }
        break;
    case 2:
        if (Params::getParam('save_stats') == '1' || isset($_COOKIE['osclass_save_stats'])) {
            setcookie('osclass_save_stats', 1, time() + (24 * 60 * 60));
        } else {
            setcookie('osclass_save_stats', 0, time() + (24 * 60 * 60));
        }

        if (isset($_COOKIE['osclass_ping_engines'])) {
            setcookie('osclass_ping_engines', 1, time() + (24 * 60 * 60));
        }

        break;
    case 3:
        if (Params::getParam('dbname') != '') {
            $error = oc_install();
        }
        break;
    case 4:
        if (Params::getParam('result') != '') {
            $error = Params::getParam('result');
        }
        $password = Params::getParam('password', false, false);
        break;
    case 5:
        $password = Params::getParam('password', false, false);
        break;
    default:
        break;
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Osclass Installation'); ?></title>
    <!--<link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.css"/>
          -->
    <link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/assets/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/assets/bootstrap-icons/bootstrap-icons.css"/>
    <link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/css/vtip.css"/>

    <script src="<?php echo get_absolute_url(); ?>oc-includes/assets/jquery/jquery.min.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/assets/jquery-ui/jquery-ui.min.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/assets/bootstrap/bootstrap.min.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/vtip.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.js"
            type="text/javascript"></script>
</head>
<body>
<div id="wrapper" class="container-md">
    <div class="row">
        <div class="offset-md-1 col-md-10 col-sm-12 align-self-center p-5" id="container">
            <div class="card rounded-3" tabindex="-1">
                <div id="header" class="card-header text-dark bg-light installation">
                    <div class="text-center">
                        <img width="350" src="<?php echo get_absolute_url(); ?>oc-includes/images/osclass-logo.png"
                             alt="Osclass"
                             title="Osclass"/>
                    </div>
                    <?php if (in_array($step, array(2, 3))) { ?>
                        <?php if ($step === 2) {
                            $databaseStep = 'text-info';
                            $targetStep   = 'text-muted';
                        } elseif ($step === 3) {
                            $databaseStep = 'text-muted';
                            $targetStep   = 'text-info';
                        } ?>
                        <ul class="nav nav-pills nav-fill justify-content-center">
                            <li class="nav-item border-bottom">
                                <div class="nav-link <?php echo $databaseStep; ?>"><strong>1 - Database</strong></div>
                            </li>
                            <li class="nav-item border-bottom">
                                <div class="nav-link <?php echo $targetStep; ?>"><strong>2 - Target</strong></div>
                            </li>
                        </ul>
                    <?php } ?>
                </div>
                <div class="card-body bg-light" id="content">
                    <?php if ($step === 1) { ?>
                        <h2 class="card-title text-center display-6"><?php _e('Welcome'); ?></h2>
                        <?php if (isset($error) && $error) { ?>
                            <div class="alert alert-danger shadow-sm" role="alert">
                                <h4><?php _e('Oops! You need a compatible Hosting'); ?></h4>
                                <?php _e('Your hosting seems to be not compatible, check your settings.'); ?>
                            </div>
                            <br>
                        <?php } ?>

                        <form class="p-3" action="install.php" method="post">
                            <input type="hidden" name="step" value="2"/>
                            <div class="form-table">
                                <?php if (count($jsonLocales) > 1) { ?>
                                    <div>
                                        <div class="row mb-3">
                                            <label for="install_locale" class="col-md-3 col-sm-6
                                                col-form-label"><strong><?php _e('Choose language'); ?></strong></label>
                                            <div class="col-md-3 col-sm-6">
                                                <select class="form-control"
                                                        aria-label="<?php _e('Choose language'); ?>" id="install_locale"
                                                        name="install_locale"
                                                        onchange="window.location.href='?install_locale='+document.getElementById(this.id).value">
                                                    <?php foreach ($jsonLocales as $k => $locale) { ?>
                                                        <option value="<?php echo osc_esc_html($k); ?>" <?php if ($k
                                                            === Session::newInstance()->_get('userLocale')
                                                        ) {
                                                            echo 'selected="selected"';
                                                                       } ?>><?php echo $locale; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if ($error) { ?>
                                    <p><?php _e('Check the next requirements:'); ?></p>
                                    <div class="requirements_help alert alert-warning shadow-sm">
                                        <span class="small"><b><?php _e('Requirements help:'); ?></b></span>
                                        <ul>
                                            <?php foreach ($requirements as $k => $v) { ?>
                                                <?php if (!$v['fn'] && $v['solution'] != '') { ?>
                                                    <li class="small"><?php echo $v['solution']; ?></li>
                                                <?php } ?>
                                            <?php } ?>
                                            <li class="small">
                                                <a href="https://osclass.discourse.group"
                                                   hreflang="en"><?php _e('Need more help?'); ?></a></li>
                                        </ul>
                                    </div>
                                <?php } else { ?>
                                    <div class="alert alert-success shadow-sm"><?php _e('All right! All the requirements have met:'); ?></div>
                                <?php } ?>
                                <ul>
                                    <?php foreach ($requirements as $k => $v) { ?>
                                        <li><?php echo $v['requirement']; ?> <i
                                                    class="bi <?php echo $v['fn'] ? 'text-success bi-check'
                                                        : 'text-danger bi-x-circle-fill'; ?>"></i></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php if ($error) { ?>
                                <p class="margin20">
                                    <input type="button" class="btn btn-primary"
                                           onclick="document.location = 'install.php?step=1'"
                                           value="<?php echo osc_esc_html(__('Try again')); ?>"/>
                                </p>
                            <?php } else { ?>
                                <p class="margin20">
                                    <input type="submit" class="btn btn-primary"
                                           value="<?php echo osc_esc_html(__('Run the install')); ?>"/>
                                </p>
                            <?php } ?>
                        </form>
                    <?php } elseif ($step == 2) {
                        display_database_config();
                    } elseif ($step === 3) {
                        if (!isset($error['error'])) {
                            display_target();
                        } else {
                            display_database_error($error, $step - 1);
                        }
                    } elseif ($step === 4) {
                        // ping engines

                        if (isset($_COOKIE['osclass_ping_engines'])) {
                            try {
                                ping_search_engines($_COOKIE['osclass_ping_engines']);
                            } catch (Exception $e) {
                                LogOsclassInstaller::newInstance()
                                    ->error($e->getMessage(), $e->getFile() . ' at line: '
                                        . $e->getLine());
                            }
                        }

                        setcookie('osclass_save_stats', '', time() - 3600);
                        setcookie('osclass_ping_engines', '', time() - 3600);

                        // copy robots.txt
                        $source      = LIB_PATH . 'osclass/installer/robots.txt';
                        $destination = ABS_PATH . 'robots.txt';
                        if (function_exists('copy')) {
                            @copy($source, $destination);
                        } else {
                            $contentx   = @file_get_contents($source);
                            $openedfile = fopen($destination, 'wb');
                            fwrite($openedfile, $contentx);
                            fclose($openedfile);
                            $status = true;
                            if ($contentx === false) {
                                $status = false;
                            }
                        }
                        display_finish($password);

                        // Install bender theme for first time.
                        if (!is_dir(CONTENT_PATH.'themes/bender')) {
                            $fileSystem = new \mindstellar\utility\FileSystem();
                            $bender_filename       = 'bender.zip';
                            $download_path   = CONTENT_PATH . 'downloads/';
                            if ($downloaded = $fileSystem->downloadFile(
                                'https://github.com/mindstellar/theme-bender/releases/download/v3.2.3/bender_3.2.3.zip',
                                $download_path . 'bender.zip'
                            )
                            ) {
                                $zip = new \mindstellar\utility\Zip();
                                $resultCode =$zip->unzipFile($downloaded, CONTENT_PATH . 'themes/');
                                $fileSystem->remove($downloaded);
                            }
                        }
                    }
                    ?>
                </div>
                <div class="card-footer" id="footer">
                    <ul class="list-inline">
                        <li class="list-inline-item"><a href="https://docs.mindstellar.com/osclass-docs/"
                                                        target="_blank"
                                                        hreflang="en"><?php _e('Documentation'); ?></a></li>
                        <li class="list-inline-item"><a href="https://github.com/mindstellar/Osclass/" target="_blank"
                                                        hreflang="en"><?php _e('Feedback'); ?></a></li>
                        <li class="list-inline-item"><a href="https://osclass.discourse.group/" target="_blank"
                                                        hreflang="en"><?php _e('Forums'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>