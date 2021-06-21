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

osc_enqueue_script('jquery-validate');

//getting variables for this view
$themes = __get('themes');
$info   = WebThemes::newInstance()->loadThemeInfo(osc_theme());

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // dialog delete
            $("#dialog-delete-theme").dialog({
                autoOpen: false,
                modal: true,
                title: '<?php echo osc_esc_js(__('Delete theme')); ?>'
            });
        });

        // dialog delete function
        function delete_dialog(theme) {
            $("#dialog-delete-theme input[name='webtheme']").attr('value', theme);
            $("#dialog-delete-theme").dialog('open');
            return false;
        }
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

function addHelp()
{
    echo '<p>'
         . __("Change your site's look and feel by activating a theme among those available. "
              . '<strong>Be careful</strong>: if your theme has been customized, '
              . "you'll lose all changes if you change to a new theme.")
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Appearance'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
        <a class="btn btn-green ico ico-32 ico-add-white float-right"
           href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add"><?php _e('Add theme'); ?></a>
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
    return sprintf(__('Appearance &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); ?>
<div id="appearance-page">
    <!-- themes list -->
    <div class="appearance">
        <div id="tabs">
            <div id="available-themes">
                <h2 class="render-title"><?php _e('Current theme'); ?> <a
                            href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add"
                            class="btn btn-mini"><?php _e('Add new'); ?></a></h2>
                <div class="current-theme">
                    <div class="theme">
                        <img
                                src="<?php echo osc_base_url() . '/oc-content/themes/' . osc_theme()
                                                . '/screenshot.png' ?>"
                                title="<?php echo $info['name']; ?>" alt="<?php echo $info['name']; ?>"
                        />
                        <div class="theme-info">
                            <h3><?php echo $info['name']; ?> <?php echo $info['version']; ?> <?php _e('by'); ?>
                                <a
                                        href="<?php echo $info['author_url']; ?>"
                                        target="_blank"><?php echo $info['author_name']; ?>
                                </a>
                            </h3>
                        </div>
                        <div class="theme-description">
                            <?php echo $info['description']; ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <h2 class="render-title"><?php _e('Available themes'); ?></h2>
                <div class="available-theme">
                    <?php
                    $aThemesToUpdate = json_decode(osc_get_preference('themes_to_update'), true);
                    $bThemesToUpdate = (is_array($aThemesToUpdate)) ? true : false;
                    $csrf_token      = osc_csrf_token_url();
                    foreach ($themes as $theme) { ?>
                        <?php
                        if ($theme === osc_theme()) {
                            continue;
                        }
                        $info = WebThemes::newInstance()->loadThemeInfo($theme);
                        ?>
                        <div class="theme">
                            <div class="theme-stage">
                                <img src="<?php echo osc_base_url(); ?>/oc-content/themes/<?php echo $theme; ?>/screenshot.png"
                                     title="<?php echo $info['name']; ?>" alt="<?php echo $info['name']; ?>"/>
                                <div class="theme-actions">
                                    <a href="<?php echo osc_admin_base_url(true);
                                    ?>?page=appearance&amp;action=activate&amp;theme=<?php
                                    echo $theme; ?>&amp;<?php echo $csrf_token;
?>" class="btn btn-mini btn-green"><?php _e('Activate'); ?></a>
                                    <a target="_blank"
                                       href="<?php echo osc_base_url(true); ?>?theme=<?php echo $theme; ?>"
                                       class="btn btn-mini btn-blue"><?php _e('Preview'); ?></a>
                                    <a onclick="return delete_dialog('<?php echo $theme; ?>');"
                                       href="<?php echo osc_admin_base_url(true);
                                        ?>?page=appearance&amp;action=delete&amp;webtheme=<?php
                                       echo $theme; ?>&amp;<?php echo $csrf_token; ?>"
                                       class="btn btn-mini float-right delete"><?php _e('Delete'); ?></a>
                                    <?php
                                    if ($bThemesToUpdate && in_array($theme, $aThemesToUpdate)) { ?>
                                        <a href='#<?php echo htmlentities(@$info['theme_update_uri']); ?>'
                                           class="btn btn-mini btn-orange market-popup"><?php _e('Update'); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="theme-info">
                                <h3><?php echo $info['name']; ?> <?php echo $info['version']; ?> <?php _e('by'); ?>
                                    <a target="_blank" href="<?php echo $info['author_url']; ?>">
                                        <?php echo $info['author_name']; ?>
                                    </a>
                                </h3>
                            </div>
                            <div class="theme-description">
                                <?php echo $info['description']; ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /themes list -->
</div>
<form id="dialog-delete-theme" method="get" action="<?php echo osc_admin_base_url(true); ?>"
      class="has-form-actions hide">
    <input type="hidden" name="page" value="appearance"/>
    <input type="hidden" name="action" value="delete"/>
    <input type="hidden" name="webtheme" value=""/>
    <div class="form-horizontal">
        <div class="form-row">
            <?php _e('This action can not be undone. Are you sure you want to delete the theme?'); ?>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);"
                   onclick="$('#dialog-delete-theme').dialog('close');"><?php _e('Cancel'); ?></a>
                <input id="delete-theme-submit" type="submit" value="<?php echo osc_esc_html(__('Uninstall')); ?>"
                       class="btn btn-red"/>
            </div>
        </div>
    </div>
</form>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
