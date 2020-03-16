<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * Copyright 2014 Osclass
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

function addHelp()
{
    echo '<p>'
        . __("Add, edit or delete the language in which your Osclass is displayed, "
            ."both the part that's viewable by users and the admin panel.")
        . '</p>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Settings'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
        <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&amp;action=add" class="btn btn-green ico ico-32 ico-add-white float-right"><?php _e('Add language'); ?></a>
    </h1>
    <?php
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string)
{
    return sprintf(__('Languages &raquo; %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead()
{
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // check_all bulkactions
            $("#check_all").change(function () {
                var isChecked = $(this).prop("checked");
                $('.col-bulkactions input').each(function () {
                    if (isChecked == 1) {
                        this.checked = true;
                    } else {
                        this.checked = false;
                    }
                });
            });

            // dialog add official lang
            $("#b_add_official").click(function () {
                $("#dialog-add-official").dialog({
                    width: 400,
                    modal: true,
                    title: '<?php echo osc_esc_js(__('Add official languages.')); ?>',
                });
            });

            // dialog delete
            $("#dialog-language-delete").dialog({
                autoOpen: false,
                modal: true,
                title: '<?php echo osc_esc_js(__('Delete language')); ?>'
            });

            // dialog bulk actions
            $("#dialog-bulk-actions").dialog({
                autoOpen: false,
                modal: true
            });
            $("#bulk-actions-submit").click(function () {
                $("#datatablesForm").submit();
            });
            $("#bulk-actions-cancel").click(function () {
                $("#datatablesForm").attr('data-dialog-open', 'false');
                $('#dialog-bulk-actions').dialog('close');
            });
            // dialog bulk actions function
            $("#datatablesForm").submit(function () {
                if ($("#bulk_actions option:selected").val() == "") {
                    return false;
                }

                if ($("#datatablesForm").attr('data-dialog-open') == "true") {
                    return true;
                }

                $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
                $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
                $("#datatablesForm").attr('data-dialog-open', 'true');
                $("#dialog-bulk-actions").dialog('open');
                return false;
            });
        });

        // dialog delete function
        function delete_dialog(item_id) {
            $("#dialog-language-delete input[name='id[]']").attr('value', item_id);
            $("#dialog-language-delete").dialog('open');
            return false;
        }
    </script>
    <?php
}
osc_add_hook('admin_header', 'customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData          = __get('aLanguages');

osc_current_admin_theme_path('parts/header.php');
?>
<h2 class="render-title">
    <?php _e('Manage Languages'); ?>
    <a id="b_add_official" href="javascript:void(0)" class="btn btn-mini"><?php _e('Add new (official)'); ?></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&amp;action=add" class="btn btn-mini"><?php _e('Add new (.zip)'); ?></a>
</h2>
<div class="relative">
    <div id="language-toolbar" class="table-toolbar">
        <div class="float-right"></div>
    </div>
    <form id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
        <input type="hidden" name="page" value="languages" />
        <div id="bulk-actions">
            <label>
                <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'), 'select-box-extra'); ?>
                <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html(__('Apply')); ?>" />
            </label>
        </div>
        <div class="table-contains-actions">
            <table class="table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="col-bulkactions"><input id="check_all" type="checkbox" /></th>
                        <th><?php _e('Name'); ?></th>
                        <th><?php _e('Short name'); ?></th>
                        <th><?php _e('Description'); ?></th>
                        <th><?php _e('Enabled (website)'); ?></th>
                        <th><?php _e('Enabled (oc-admin)'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($aData['aaData']) > 0) { ?>
                        <?php foreach ($aData['aaData'] as $array) { ?>
                            <tr>
                                <?php foreach ($array as $key => $value) { ?>
                                    <td <?php if ($key == 0) { ?> class="col-bulkactions" <?php } ?>>
                                        <?php echo $value; ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <p><?php _e('No data available in table'); ?></p>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div id="table-row-actions"></div>
        </div>
    </form>
</div>

<?php osc_show_pagination_admin($aData); ?>

<form id="dialog-language-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
    <input type="hidden" name="page" value="languages"/>
    <input type="hidden" name="action" value="delete"/>
    <input type="hidden" name="id[]" value=""/>
    <div class="form-horizontal">
        <div class="form-row">
            <?php _e('Are you sure you want to delete this language?'); ?>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-language-delete').dialog('close');"><?php _e('Cancel'); ?></a>
                <input id="language-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>"  class="btn btn-red"/>
            </div>
        </div>
    </div>
</form>

<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
    <div class="form-horizontal">
        <div class="form-row"></div>
        <div class="form-actions">
            <div class="wrapper">
                <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
                <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-red"><?php echo osc_esc_html(__('Delete')); ?></a>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>

<form id="dialog-add-official" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
    <input type="hidden" name="page" value="languages"/>
    <input type="hidden" name="action" value="import_official"/>
    <div class="form-horizontal">
        <div class="form-row">
            <?php _e("Import a language from our database. " . "Already imported languages aren't shown."); ?>
        </div>
        <div class="form-row">
            <table>
                <tr>
                    <td><?php _e('Import a language'); ?>:</td>
                    <td>
                        <?php $languages = View::newInstance()->_get('aOfficialLanguages'); ?>
                        <?php if (count($languages)) { ?>
                            <select name="language" required>
                                <option value=""><?php _e('Select an option'); ?>
                                <?php foreach ($languages as $code => $name) { ?>
                                    <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <p><?php _e('No official languages available.'); ?></p>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="form-actions">
            <div class="wrapper">
                <a class="btn" href="javascript:void(0);" onclick="$('#dialog-add-official').dialog('close');"><?php _e('Cancel'); ?></a>
                <button type="submit" class="btn btn-submit"><?php echo osc_esc_html(__('Import')); ?></button>
            </div>
        </div>
    </div>
</form>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
