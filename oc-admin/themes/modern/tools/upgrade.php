<?php
if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}

$perms = osc_save_permissions();
$ok    = osc_change_permissions();

//customize Head
function customHead()
{
    ?>
    <script>
        $(document).ready(function () {
            $("#steps_div").hide();
        });
        <?php

        $perms = osc_save_permissions();
        $ok = osc_change_permissions();
        foreach ($perms as $k => $v) {
            chmod($k, $v);
        }
        if ($ok) {
            ?>
        $(function () {
            var steps_div = document.getElementById('steps_div');
            steps_div.style.display = '';
            var steps = document.getElementById('steps');
            var version = <?php echo osc_version(); ?>;
            var fileToUnzip = '';
            steps.innerHTML += '<?php echo osc_esc_js(sprintf(__('Checking for updates (Current version %s)'),
                osc_version())); ?> ';

            $.getJSON("https://example.org/latest_version_v1.php?callback=?", function (data) {
                if (data.version <= version) {
                    steps.innerHTML += '<?php echo osc_esc_js(__('Congratulations! Your Osclass installation is up to date!')); ?>';
                } else {
                    steps.innerHTML += '<?php echo osc_esc_js(__('New version to update:')); ?> ' + oscEscapeHTML(data.version);
                    +"<br />";
                    <?php if (Params::getParam('confirm') == 'true') {?>
                    steps.innerHTML += '<img id="loading_image" src="<?php echo osc_current_admin_theme_url('images/loading.gif'); ?>" /><?php echo osc_esc_js(__('Upgrading your Osclass installation (this could take a while):')); ?>';

                    var tempAr = data.url.split('/');
                    fileToUnzip = tempAr.pop();
                    $.getJSON('<?php echo osc_admin_base_url(true); ?>?page=ajax&action=upgrade&<?php echo osc_csrf_token_url(); ?>', function (data) {
                        if (data.error == 0 || data.error == 6) {
                            window.location = "<?php echo osc_admin_base_url(true); ?>?page=tools&action=version";
                        }
                        var loading_image = document.getElementById('loading_image');
                        loading_image.style.display = "none";
                        steps.innerHTML += $("<div>").text(data.message).html();
                        +"<br />";
                    });
                    <?php } else { ?>
                    steps.innerHTML += '<input type="button" value="<?php echo osc_esc_html(__('Upgrade')); ?>" onclick="window.location.href=\'<?php echo osc_admin_base_url(true); ?>?page=tools&action=upgrade&confirm=true\';" />';
                    <?php } ?>
                }
            });
        });
            <?php
        } ?>
    </script>
    <?php
}


//TODO Not using it right now
//osc_add_hook('admin_header', 'customHead', 10);

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
        . __("Check to see if you're using the latest version of Osclass. If you're not, the system will let you know so you can update and use the newest features.")
        . '</p>';
}


osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');
function customPageHeader()
{
    ?>
    <h1><?php _e('Tools'); ?>
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
                                    _e('Your Osclass installation can\'t be auto-upgraded, we are working on this feature. Please, back up your database, the folder oc-content and follow our Step-by-Step');
                                    echo '<a href="https://osclass.gitbook.io/osclass-docs//"> '
                                        . __('Osclass Documentation') . '</a>.';
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