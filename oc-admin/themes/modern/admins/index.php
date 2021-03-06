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
         . __('Add users who can manage your page. You can add admins or moderators: '
              . 'admins have access to the whole admin panel while moderators can only modify listings and see stats.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Admins &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

function customPageHeader()
{
    ?>
    <h1><?php _e('Admins'); ?>
        <a href="#" class="btn ico ico-32 ico-help float-right"></a>
        <a href="<?php echo osc_admin_base_url(true); ?>?page=admins&amp;action=add"
           class="btn btn-green ico ico-32 ico-add-white float-right"><?php _e('Add admin'); ?></a>
    </h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');
//customize Head
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

            // dialog delete
            $("#dialog-admin-delete").dialog({
                autoOpen: false,
                modal: true,
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
            // /dialog bulk actions
        });

        // dialog delete function
        function delete_dialog(item_id) {
            $("#dialog-admin-delete input[name='id[]']").attr('value', item_id);
            $("#dialog-admin-delete").dialog('open');
            return false;
        }
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData          = __get('aAdmins');

osc_current_admin_theme_path('parts/header.php'); ?>
    <h2 class="render-title"><?php _e('Manage admins'); ?> <a
                href="<?php echo osc_admin_base_url(true); ?>?page=admins&amp;action=add"
                class="btn btn-mini"><?php _e('Add new'); ?></a></h2>
    <div class="relative">
        <div id="admins-toolbar" class="table-toolbar">
            <div class="float-right">
            </div>
        </div>
        <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="admins"/>
            <div id="bulk-actions">
                <label>
                    <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'),
                                                 'select-box-extra'); ?>
                    <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html(__('Apply')); ?>"/>
                </label>
            </div>
            <div class="table-contains-actions">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="col-bulkactions"><input id="check_all" type="checkbox"/></th>
                        <th><?php _e('Username'); ?></th>
                        <th><?php _e('Name'); ?></th>
                        <th><?php _e('E-mail'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($aData['aaData']) > 0) { ?>
                        <?php foreach ($aData['aaData'] as $array) { ?>
                            <tr>
                                <?php foreach ($array as $key => $value) { ?>
                                    <?php if ($key == 0) { ?>
                                        <td class="col-bulkactions">
                                    <?php } else { ?>
                                        <td>
                                    <?php } ?>
                                    <?php echo $value; ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                <p><?php _e('No data available in table'); ?></p>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div id="table-row-actions"></div><!-- used for table actions -->
            </div>
        </form>
    </div>
<?php
function showingResults()
{
    $aData = __get('aAdmins');
    echo '<ul class="showing-results"><li><span>'
         . osc_pagination_showing((Params::getParam('iPage') - 1)
                                  * $aData['iDisplayLength'] + 1,
                                  ((Params::getParam('iPage') - 1) * $aData['iDisplayLength'])
                                  + count($aData['aaData']),
                                  $aData['iTotalDisplayRecords']) . '</span></li></ul>';
}


osc_add_hook('before_show_pagination_admin', 'showingResults');
osc_show_pagination_admin($aData);
?>
    <form id="dialog-admin-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>"
          class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete admin')); ?>">
        <input type="hidden" name="page" value="admins"/>
        <input type="hidden" name="action" value="delete"/>
        <input type="hidden" name="id[]" value=""/>
        <div class="form-horizontal">
            <div class="form-row">
                <?php _e('Are you sure you want to delete this admin?'); ?>
            </div>
            <div class="form-actions">
                <div class="wrapper">
                    <a class="btn" href="javascript:void(0);"
                       onclick="$('#dialog-admin-delete').dialog('close');"><?php _e('Cancel'); ?></a>
                    <input id="admin-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>"
                           class="btn btn-red"/>
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
                    <a id="bulk-actions-submit" href="javascript:void(0);"
                       class="btn btn-red"><?php echo osc_esc_html(__('Delete')); ?></a>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>