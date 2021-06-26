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
    echo '<p>'
         . __('Manually add Osclass themes in .zip format. If you prefer, '
              . 'you can manually upload the decompressed theme to <em>oc-content/themes</em>.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Appearance'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
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
    return sprintf(__('Add theme &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); ?>
<!-- themes list -->
<div class="appearance">
    <h2 class="render-title"><?php _e('Add new theme'); ?></h2>
    <div id="upload-themes" class="ui-osc-tabs-panel">
        <div class="form-horizontal">
            <?php if (is_writable(osc_themes_path())) { ?>
                <form class="separate-top" action="<?php echo osc_admin_base_url(true); ?>" method="post"
                      enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_post"/>
                    <input type="hidden" name="page" value="appearance"/>
                    <div class="form-row">
                        <div class="form-label"><?php _e('Theme package (.zip)'); ?></div>
                        <div class="form-controls">
                            <div class="form-label-checkbox"><input type="file" name="package" id="package"/></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <input type="submit" value="<?php echo osc_esc_html(__('Upload')); ?>" class="btn btn-submit"/>
                    </div>
                </form>
            <?php } else { ?>
                <div class="flashmessage flashmessage-error">
                    <a class="btn ico btn-mini ico-close" href="#">×</a>
                    <p><?php _e("Can't install a new theme"); ?></p>
                </div>
                <p class="text">
                    <?php _e("The theme folder is not writable on your server so you can't upload themes from "
                             . "the administration panel. Please make the theme folder writable and try again."); ?>
                </p>
                <p class="text">
                    <?php _e('To make the directory writable under UNIX execute this command from the shell:'); ?>
                </p>
                <pre>chmod 0755 <?php echo osc_themes_path(); ?></pre>
            <?php } ?>
        </div>
    </div>
</div>
<!-- /themes list -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
