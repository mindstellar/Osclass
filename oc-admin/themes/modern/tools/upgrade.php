<?php

use mindstellar\upgrade\Osclass;

if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}

//customize Head
function customHead()
{
    ?>
    <script>
        $(document).ready(function () {
            $("#steps_div").hide();
        });
        <?php
        $update_core_json = osc_get_preference('update_core_json');
        $is_upgrade_available = $update_core_json && (new Osclass(json_decode($update_core_json, true)))->isUpgradable();
        ?>
        $(function () {
            let steps;
            let remoteVersion;
            const stepsDiv = document.getElementById('steps_div');
            stepsDiv.style.display = '';
            steps = $('#steps');

            <?php if ($is_upgrade_available && Params::getParam('confirm') !== 'true') { ?>
            remoteVersion = '<?php echo osc_esc_js((new Osclass(json_decode($update_core_json, true)))->getNewVersion()); ?>';
            steps.append('<?php
                echo '<li>' . sprintf(__('Upgrade is available for (Current version %s)'), osc_get_preference('version')) . '<\/li>';
            ?>');
            steps.append('<li><?php echo osc_esc_js(__('New version to update:')); ?> ' + remoteVersion + '<\/li>');
            steps.append(`<input type="button" value="<?php echo osc_esc_html(__('Upgrade')); ?>"
            onclick="window.location.href='<?php echo osc_admin_base_url(true) . '?page=tools&action=upgrade&confirm=true';?> ';" />`);

            <?php } elseif ($is_upgrade_available && Params::getParam('confirm') === 'true') { ?>
            steps.append(`<i id="loading_image" class="fas fa-spinner fa-spin"></i> <?php
            echo osc_esc_js(__('Upgrading your Osclass installation (this could take a while):'));
            ?>`);
            $.getJSON('<?php
                echo osc_admin_base_url(true) . '?page=ajax&action=upgrade&' . osc_csrf_token_url();
            ?>', function (data) {
                if (data.error == 0 || data.error == 6) {
                    window.location = "<?php echo osc_admin_base_url(true); ?>?page=tools&action=version";
                }
                var loading_image = document.getElementById('loading_image');
                loading_image.style.display = "none";
                steps.append(data.message).html() + "<br />");
            });
            <?php } else { ?>
            steps.append('<?php echo osc_esc_js(__('Congratulations! Your Osclass installation is up to date!')); ?>');
            <?php } ?>
        })
    </script>
    <?php
}


//TODO Not using it right now
osc_add_hook('admin_footer', 'customHead', 10);

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
         . __("Check to see if you're using the latest version of Osclass. If you're not, 
        the system will let you know so you can update and use the newest features.")
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
    return sprintf(__('Upgrade &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); ?>
    <div id="backup-setting">
        <!-- settings form -->
        <div id="backup-settings">
            <h2 class="render-title"><?php _e('Upgrade'); ?></h2>
            <form>
                <fieldset>
                    <div class="form-horizontal">
                        <div class="form-row">
                            <div class="tools upgrade">
                                <p class="text">
                                    <?php
                                    printf(
                                        __('Your Osclass installation can be auto-upgraded. 
                                        Please, back up your database and the folder oc-content before attempting to 
                                        upgrade your Osclass installation. 
                                        You can also upgrade Osclass manually, more information in the %s'),
                                        '<a href="https://docs.mindstellar.com/">Documentation</a>'
                                    );
                                    ?>
                                </p>
                                <div id="steps_div">
                                    <div id="steps">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        <!-- /settings form -->
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>