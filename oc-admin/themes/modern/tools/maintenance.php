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

$maintenance = file_exists(osc_base_path() . '.maintenance');

/**
 * @return string
 */
function render_offset()
{
    return 'row-offset';
}


function addHelp()
{
    echo '<p>'
         . __('Show a "Site in maintenance mode" message to your users while you\'re updating your site or modifying its configuration.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Tools'); ?>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"
           href="#help-box"></a>
    </h1>
    <?php
}


/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Maintenance &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="backup-setting">
    <!-- settings form -->
    <div id="backup-settings">
        <h2 class="render-title"><?php _e('Maintenance'); ?></h2>
        <form>
            <fieldset>
                <div class="form-horizontal">
                    <div class="form-row">
                        <?php _e("While in maintenance mode, users can't access your website. Useful if you need to "
                                 . "make changes on your website. Use the following button to toggle maintenance mode ON/OFF."); ?>
                        <div class="<?php echo $maintenance ? 'callout-danger' : 'callout-success'; ?>">
                            <?php printf(__('Maintenance mode is: <strong>%s</strong>'),
                                ($maintenance ? __('ON') : __('OFF'))); ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <input type="button"
                               value="<?php echo($maintenance ? osc_esc_html(__('Disable maintenance mode'))
                                   : osc_esc_html(__('Enable maintenance mode'))); ?>"
                               onclick="window.location.href='<?php echo osc_admin_base_url(true);
                                ?>?page=tools&amp;action=maintenance&amp;mode=<?php
                               echo ($maintenance ? 'off' : 'on') . '&amp;' . osc_csrf_token_url();
?>';" class="btn btn-submit"/>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
