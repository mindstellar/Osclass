<?php
/*
 *  Copyright 2020 Osclass
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

$step = Params::getParam('step');
if (!is_numeric($step)) {
    $step = '1';
}

$existingLangs = osc_listLocales();
$jsonLangs = osc_file_get_contents(osc_get_languages_json_url());
$jsonLangs = json_decode($jsonLangs, true);
$installLang = Params::getParam('install_locale');

if ($step == 1 && $installLang != '') {
    if (!array_key_exists($installLang, $existingLangs) && array_key_exists($installLang, $jsonLangs)) {
        $langFolder = osc_translations_path().$installLang;
        mkdir($langFolder, 0755, true);

        $files = osc_get_language_files_urls($installLang);
        foreach ($files as $file => $url) {
            $content = osc_file_get_contents($url);
            file_put_contents($langFolder.'/'.$file, $content);
        }
    }

    $existingLangs = osc_listLocales();
    if (array_key_exists($installLang, $existingLangs)) {
        Session::newInstance()->_set('userLocale', $installLang);
        Session::newInstance()->_set('adminLocale', $installLang);
    }
} elseif ($step == 1) {
    $defaultLang = 'en_US';
    $langFolder = osc_translations_path().$defaultLang;

    if (!is_dir($langFolder) && count($jsonLangs)) {
        mkdir($langFolder, 0755, true);

        $files = osc_get_language_files_urls($defaultLang);
        foreach ($files as $file => $url) {
            $content = osc_file_get_contents($url);
            file_put_contents($langFolder.'/'.$file, $content);
        }
    }

    Session::newInstance()->_set('userLocale', $defaultLang);
    Session::newInstance()->_set('adminLocale', $defaultLang);
}

$translation = Translation::newInstance(true);

if (is_osclass_installed()) {
    $message =
        __("Looks like you've already installed Osclass. To reinstall please clear your old database tables first.");
    osc_die('Osclass &raquo; Error', $message);
}

switch ($step) {
    case 1:
        $requirements = get_requirements();
        $error        = check_requirements($requirements);
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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php _e('Osclass Installation'); ?></title>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/assets/js/jquery/jquery.min.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/assets/js/jquery-ui/jquery-ui.min.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/vtip.js"
            type="text/javascript"></script>
    <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.js"
            type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.css"/>
    <link rel="stylesheet" type="text/css" media="all"
          href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/css/vtip.css"/>
</head>
<body>
<div id="wrapper">
    <div id="container">
        <div id="header" class="installation">
            <h1 id="logo">
                <img src="<?php echo get_absolute_url(); ?>oc-includes/images/osclass-logo.png" alt="Osclass"
                     title="Osclass"/>
            </h1>
            <?php if (in_array($step, array(2, 3))) { ?>
                <ul id="nav">
                    <li class="<?php if ($step == 2) {
                        ?>actual<?php
                               } elseif ($step < 2) {
                                    ?>next<?php
                               } else {
                                    ?>past<?php
                               } ?>">
                        1 - Database
                    </li>
                    <li class="<?php if ($step == 3) {
                        ?>actual<?php
                               } elseif ($step < 3) {
                                    ?>next<?php
                               } else {
                                    ?>past<?php
                               } ?>">
                        2 - Target
                    </li>
                </ul>
                <div class="clear"></div>
            <?php } ?>
        </div>
        <div id="content">
            <?php if ($step == 1) { ?>
                <h2 class="target"><?php _e('Welcome'); ?></h2>

                <?php if ($error) { ?>
                    <div class="form-table">
                        <h3 style="font-weight: 400;font-size: 1.2rem;margin: 0;margin-bottom: .5em;">
                            <?php _e('Oops! You need a compatible Hosting'); ?>
                        </h3>
                        <span style="color: red;font-weight: 300;font-size: 1.09em;">
                            <?php _e('Your hosting seems to be not compatible, check your settings.'); ?>
                        </span>
                    </div>
                    <br>
                <?php } ?>

                <form action="install.php" method="post">
                    <input type="hidden" name="step" value="2"/>
                    <div class="form-table">
                        <?php if (count($jsonLangs) > 1) { ?>
                            <div>
                                <label for="install_locale"><?php _e('Choose language'); ?></label>
                                <select id="install_locale" name="install_locale"
                                        onchange="window.location.href='?install_locale='+document.getElementById(this.id).value">
                                    <?php foreach ($jsonLangs as $k => $locale) { ?>
                                        <option value="<?php echo osc_esc_html($k); ?>" <?php if ($k
                                            == Session::newInstance()->_get('userLocale')
                                        ) {
                                            echo 'selected="selected"';
                                                       } ?>><?php echo $locale; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                        <?php if ($error) { ?>
                            <p><?php _e('Check the next requirements:'); ?></p>
                            <div class="requirements_help">
                                <p><b><?php _e('Requirements help:'); ?></b></p>
                                <ul>
                                    <?php foreach ($requirements as $k => $v) { ?>
                                        <?php if (!$v['fn'] && $v['solution'] != '') { ?>
                                            <li><?php echo $v['solution']; ?></li>
                                        <?php } ?>
                                    <?php } ?>
                                    <li>
                                        <a href="https://osclass.discourse.group"
                                           hreflang="en"><?php _e('Need more help?'); ?></a></li>
                                </ul>
                            </div>
                        <?php } else { ?>
                            <p><?php _e('All right! All the requirements have met:'); ?></p>
                        <?php } ?>
                        <ul>
                            <?php foreach ($requirements as $k => $v) { ?>
                                <li><?php echo $v['requirement']; ?> <img
                                            src="<?php echo get_absolute_url(); ?>oc-includes/images/<?php echo $v['fn']
                                                ? 'tick.png' : 'cross.png'; ?>"
                                            alt="" title=""/></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php if ($error) { ?>
                        <p class="margin20">
                            <input type="button" class="button" onclick="document.location = 'install.php?step=1'"
                                   value="<?php echo osc_esc_html(__('Try again')); ?>"/>
                        </p>
                    <?php } else { ?>
                        <p class="margin20">
                            <input type="submit" class="button"
                                   value="<?php echo osc_esc_html(__('Run the install')); ?>"/>
                        </p>
                    <?php } ?>
                </form>
            <?php } elseif ($step == 2) {
                display_database_config();
            } elseif ($step == 3) {
                if (!isset($error['error'])) {
                    display_target();
                } else {
                    display_database_error($error, $step - 1);
                }
            } elseif ($step == 4) {
                // ping engines

                if (isset($_COOKIE['osclass_ping_engines'])) {
                    try {
                        ping_search_engines($_COOKIE['osclass_ping_engines']);
                    } catch (Exception $e) {
                        LogOsclassInstaller::newInstance()->error($e->getMessage(), $e->getFile().' at line: '
                            .$e->getLine());
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
            }
            ?>
        </div>
        <div id="footer">
            <ul>
                <li>
                    <a href="https://docs.mindstellar.com/osclass-docs/" target="_blank"
                       hreflang="en"><?php _e('Documentation'); ?></a>
                </li>
                <li>
                    <a href="https://github.com/mindstellar/Osclass/" target="_blank"
                       hreflang="en"><?php _e('Feedback'); ?></a>
                </li>
                <li>
                    <a href="https://osclass.discourse.group/" target="_blank"
                       hreflang="en"><?php _e('Forums'); ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
