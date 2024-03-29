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
         . __('Add, edit or delete ban rules. Keep in mind that ban rules prevent users to register, publish or comment on listings.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Users'); ?>
        <a href="<?php echo osc_admin_base_url(true) . '?page=users&action=settings'; ?>"
           class="ms-1 text-dark float-end" title="<?php _e('Settings'); ?>"><i class="bi bi-gear-fill"></i></a>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse" href="#help-box"></a>
        <a href="<?php echo osc_admin_base_url(true) . '?page=users&action=create_ban_rule'; ?>"
           class="text-success ms-1 float-end" title="<?php _e('Add new'); ?>">
            <i class="bi bi-plus-circle-fill"></i>
        </a>
    </h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Manage ban rules &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

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
            $("#dialog-ban-delete").dialog({
                autoOpen: false,
                modal: true
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
            $("#dialog-ban-delete input[name='id[]']").attr('value', item_id);
            $("#dialog-ban-delete").dialog('open');
            return false;
        }
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

$aData     = __get('aData');
$aRawRows  = __get('aRawRows');
$sort      = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$rows    = $aData['aRows'];


?>
<?php osc_current_admin_theme_path('parts/header.php'); ?>
    <h2 class="render-title"><?php _e('Manage ban rules'); ?></h2>
    <div class="relative">
        <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="users"/>

            <div id="bulk-actions">
                <div class="input-group input-group-sm">
                    <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'),
                                                 'select-box-extra'); ?>
                    <input type="submit" id="bulk_apply" class="btn btn-primary" value="<?php echo osc_esc_html(__('Apply')); ?>"/>
                </div>
            </div>
            <div class="table-contains-actions shadow-sm">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr class="table-secondary">
                        <?php foreach ($columns as $k => $v) {
                            if ($direction === 'desc') {
                                echo '<th class="col-' . $k . ' ' . ($sort === $k ? ('sorting_desc') : '') . '">' . $v . '</th>';
                            } else {
                                echo '<th class="col-' . $k . ' ' . ($sort === $k ? ('sorting_asc') : '') . '">' . $v . '</th>';
                            }
                        } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($rows) > 0) { ?>
                        <?php foreach ($rows as $key => $row) { ?>
                            <tr>
                                <?php foreach ($row as $k => $v) { ?>
                                    <td class="col-<?php echo $k; ?>" data-col-name="<?php echo ucfirst($k); ?>"><?php echo $v; ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <p><?php _e('No data available in table'); ?></p>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div id="table-row-actions"></div> <!-- used for table actions -->
            </div>
        </form>
    </div>
<?php
function showingResults()
{
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>'
         . osc_pagination_showing((Params::getParam('iPage') - 1)
                                  * $aData['iDisplayLength'] + 1,
                                  ((Params::getParam('iPage') - 1) * $aData['iDisplayLength'])
                                  + count($aData['aRows']),
                                  $aData['iTotalDisplayRecords'], $aData['iTotalRecords'])
         . '</span></li></ul>';
}


osc_add_hook('before_show_pagination_admin', 'showingResults');
osc_show_pagination_admin($aData);
?>
    <form id="dialog-ban-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>"
          class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete rule')); ?>">
        <input type="hidden" name="page" value="users"/>
        <input type="hidden" name="action" value="delete_ban_rule"/>
        <input type="hidden" name="id[]" value=""/>
        <div class="form-horizontal">
            <div class="form-row">
                <?php _e('Are you sure you want to delete this ban rule?'); ?>
            </div>
            <div class="form-actions">
                <div class="wrapper">
                    <a class="btn btn-dim" href="javascript:void(0);"
                       onclick="$('#dialog-ban-delete').dialog('close');"><?php _e('Cancel'); ?></a>
                    <input id="ban-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>"
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
                    <a id="bulk-actions-cancel" class="btn btn-dim" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
                    <a id="bulk-actions-submit" href="javascript:void(0);"
                       class="btn btn-red"><?php echo osc_esc_html(__('Delete')); ?></a>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>