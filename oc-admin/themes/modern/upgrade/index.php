<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
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

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Tools'); ?></h1>
    <?php
}


/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return __('Upgrade');
}


osc_add_filter('admin_title', 'customPageTitle');

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            if (typeof $.uniform != 'undefined') {
                $('textarea, button,select, input:file').uniform();
            }

            <?php if (Params::getParam('confirm') === 'true') {?>
            $('#output').show();
            $('#tohide').hide();

            $.get('<?php echo osc_admin_base_url(true); ?>?page=ajax&action=upgrade-db', function (data) {
                $('#loading_image').removeClass('fa-spinner fa-spin');
                data = JSON.parse(data);
                if(data.status) {
                    $('#loading_image').addClass('fa-check-circle');
                    $('#message').html('Success: ' + data.message);
                } else {
                    $('#loading_image').addClass('fa-exclamation-circle');
                    $('#message').html('Error: ' + data.message.replace(/\n/g, '<br>'));
                }
            });
            <?php } ?>
        });
    </script>
<?php }

osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php'); ?>

<div id="backup-settings">
    <h2 class="render-title"><?php _e('Upgrade'); ?></h2>
    <div>
        <div id="output" style="display:none">
            <i id="loading_image" class="fas fa-spinner fa-spin"></i>
            <span id="message"><?php _e('Upgrading your Osclass installation (this could take a while): ', 'admin'); ?></span>
        </div>
        <div id="tohide">
            <p>
                <?php _e('You have uploaded a new version of Osclass, you need to upgrade Osclass for it to work correctly.'); ?>
            </p>
            <a class="btn"
               href="<?php echo osc_admin_base_url(true); ?>?page=upgrade&confirm=true"><?php _e('Upgrade now'); ?></a>
        </div>
    </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
