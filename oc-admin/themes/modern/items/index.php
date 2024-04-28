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

osc_enqueue_script('jquery-validate');

function addHelp()
{
    echo '<p>'
        . __('Manage all the listings on your site: edit, delete or block the latest listings published. You can also filter by several parameters: user, region, city, etc.')
        . '</p>';
}


osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Listings'); ?>
        <a href="<?php echo osc_admin_base_url(true); ?>?page=items&amp;action=settings" class="ms-1 text-dark float-end" title="<?php _e('Settings'); ?>"><i class="bi bi-gear-fill"></i></a>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse" href="#help-box"></a>
        <a href="<?php echo osc_admin_base_url(true) . '?page=items&action=post'; ?>" class="ms-1 text-success float-end" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php _e('Add listing'); ?>"><i class="bi bi-plus-circle-fill"></i></a>
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
    return sprintf(__('Manage listings &raquo; %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

//customize Head
function customHead()
{
    ItemForm::location_javascript_new('admin'); ?>
    <script type="text/javascript">
        // autocomplete users
        $(document).ready(function() {

            $('input[name="user"]').attr("autocomplete", "off");
            $('#user,#fUser').autocomplete({
                source: "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=userajax",
                minLength: 0,
                select: function(event, ui) {
                    if (ui.item.id == '')
                        return false;
                    $('#userId').val(ui.item.id);
                    $('#fUserId').val(ui.item.id);
                },
                search: function() {
                    $('#userId').val('');
                    $('#fUserId').val('');
                }
            });

            $('.ui-autocomplete').css('zIndex', 10000);

            // check_all bulkactions
            $("#check_all").change(function() {
                var isChecked = $(this).prop("checked");
                $('.col-bulkactions input').each(function() {
                    if (isChecked == 1) {
                        this.checked = true;
                    } else {
                        this.checked = false;
                    }
                });
            });
        });
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

$categories  = __get('categories');
$withFilters = __get('withFilters');

$iDisplayLength = __get('iDisplayLength');

$aData     = __get('aData');
$aRawRows  = __get('aRawRows');
$sort      = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$rows    = $aData['aRows'];

osc_current_admin_theme_path('parts/header.php'); ?>
<h2 class="render-title"><?php _e('Manage listings'); ?></h2>
<div class="relative">
    <div id="listing-toolbar">
        <div class="d-flex justify-content-end">
            <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
                <?php foreach (Params::getParamsAsArray('get') as $key => $value) { ?>
                    <?php if ($key !== 'iDisplayLength') { ?>
                        <input type="hidden" name="<?php echo osc_esc_html($key); ?>" value="<?php echo osc_esc_html($value); ?>" />
                    <?php }
                } ?>
                <select name="iDisplayLength" class="form-select form-select-sm" onchange="this.form.submit();">
                    <option value="10"><?php printf(__('%d Listings'), 10); ?></option>
                    <option value="25" <?php if (Params::getParam('iDisplayLength') == 25) {
                                            echo 'selected';
                                       } ?>><?php printf(__('%d Listings'), 25); ?></option>
                    <option value="50" <?php if (Params::getParam('iDisplayLength') == 50) {
                                            echo 'selected';
                                       } ?>><?php printf(__('%d Listings'), 50); ?></option>
                    <option value="100" <?php if (Params::getParam('iDisplayLength') == 100) {
                                            echo 'selected';
                                        } ?>><?php printf(__('%d Listings'), 100); ?></option>
                    <option value="250" <?php if (Params::getParam('iDisplayLength') == 250) {
                                            echo 'selected';
                                        } ?>><?php printf(__('%d Listings'), 250); ?></option>
                    <option value="500" <?php if (Params::getParam('iDisplayLength') == 500) {
                                            echo 'selected';
                                        } ?>><?php printf(__('%d Listings'), 500); ?></option>                    
                </select>
            </form>
            <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters">
                <input type="hidden" name="page" value="items" />
                <input type="hidden" name="iDisplayLength" value="<?php echo $iDisplayLength; ?>" />
                <div class="input-group-sm input-group">
                    <?php if ($withFilters) { ?>
                        <a id="btn-hide-filters" class="btn btn-dim" href="<?php echo osc_admin_base_url(true) . '?page=items'; ?>"><?php _e('Reset filters'); ?></a>
                    <?php } ?>
                    <?php $opt = 'oPattern';
                    if (Params::getParam('shortcut-filter') != '') {
                        $opt = Params::getParam('shortcut-filter');
                    } ?>
                    <?php $classPattern = 'hide';
                    $classUser          = 'hide';
                    $classItemId        = 'hide'; ?>
                    <?php if ($opt === 'oUser') {
                        $classUser = '';
                    } ?>
                    <?php if ($opt === 'oPattern') {
                        $classPattern = '';
                    } ?>
                    <?php if ($opt === 'oItemId') {
                        $classItemId = '';
                    } ?>
                    <select id="filter-select" name="shortcut-filter" class="form-select form-select-sm">
                        <option value="oPattern" <?php if ($opt === 'oPattern') {
                                                        echo 'selected="selected"';
                                                 } ?>><?php _e('Pattern'); ?></option>
                        <option value="oUser" <?php if ($opt === 'oUser') {
                                                    echo 'selected="selected"';
                                              } ?>><?php _e('Email'); ?></option>
                        <option value="oItemId" <?php if ($opt === 'oItemId') {
                                                    echo 'selected="selected"';
                                                } ?>><?php _e('Item ID'); ?></option>
                    </select>
                    <input id="fPattern" type="text" name="sSearch" placeholder="<?php _e('Keywords') ?>" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" class="form-control w-25 <?php echo $classPattern; ?>" />
                    <input id="fUser" name="user" type="text" placeholder="<?php _e('User Email') ?>" class="fUser form-control w-25 <?php echo $classUser; ?>" value="<?php echo osc_esc_html(Params::getParam('user')); ?>" />
                    <input id="fUserId" name="userId" type="hidden" placeholder="<?php _e('User ID') ?>" class="form-control w-25" value="<?php echo osc_esc_html(Params::getParam('userId')); ?>" />
                    <input id="fItemId" type="text" name="itemId" placeholder="<?php _e('Item ID') ?>" value="<?php echo osc_esc_html(Params::getParam('itemId')); ?>" class="form-control w-25 <?php echo $classItemId; ?>" />

                    <a id="btn-display-filters" data-bs-toggle="modal" data-bs-target="#display-filters" href="#" class="btn btn-dim <?php
                    if ($withFilters) {
                        echo 'btn-red';
                    } ?>" title="<?php _e('Show filters'); ?>"><i class="bi bi-filter"></i>
                    </a>
                    <button type="submit" class="btn btn-primary" title="<?php echo osc_esc_html(__('Find')); ?>">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
        <input type="hidden" name="page" value="items" />
        <input type="hidden" name="action" value="bulk_actions" />
        <div id="bulk-actions">
            <div class="input-group input-group-sm">
                <?php osc_print_bulk_actions(
                    'bulk_actions',
                    'bulk_actions',
                    __get('bulk_options'),
                    'select-box-extra'
                ); ?>
                <input type="submit" id="bulk_apply" class="btn btn-primary" value="<?php echo osc_esc_html(__('Apply')); ?>" />
            </div>
        </div>
        <div class="table-contains-actions shadow-sm">
            <table class="table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
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
                            <tr class="<?php echo implode(
                                            ' ',
                                            osc_apply_filter('datatable_listing_class', array(), $aRawRows[$key], $row)
                                        ); ?>">
                                <?php foreach ($row as $k => $v) { ?>
                                    <td class="col-<?php echo $k; ?>" data-col-name=<?php echo ucfirst($k); ?>><?php echo $v; ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="<?php echo count($columns); ?>" class="text-center">
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
    echo '<ul class="showing-results"><li><span>' . osc_pagination_showing((Params::getParam('iPage') - 1)
            * $aData['iDisplayLength'] + 1,
        ((Params::getParam('iPage') - 1) * $aData['iDisplayLength'])
            + count($aData['aRows']),
        $aData['iTotalDisplayRecords'],
        $aData['iTotalRecords']
    )
        . '</span></li></ul>';
}


osc_add_hook('before_show_pagination_admin', 'showingResults');
osc_show_pagination_admin($aData);
?>
<form id="display-filters" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="modal fade nocsrf" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?php _e('Filters') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <input type="hidden" name="page" value="items" />
            <input type="hidden" name="iDisplayLength" value="<?php echo $iDisplayLength; ?>" />
            <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
            <input type="hidden" name="direction" value="<?php echo $direction; ?>" />
            <div class="form-horizontal modal-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="row-wrapper">
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Pattern'); ?>
                                </div>
                                <div class="form-controls">
                                    <input class="form-control" type="text" name="sSearch" id="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Category'); ?>
                                </div>
                                <div class="form-controls">
                                    <?php ManageItemsForm::category_select($categories, null, null, true); ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Country'); ?>
                                </div>
                                <div class="form-controls">
                                    <?php ManageItemsForm::country_text(); ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Region'); ?>
                                </div>
                                <div class="form-controls">
                                    <?php ManageItemsForm::region_text(); ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('City'); ?>
                                </div>
                                <div class="form-controls">
                                    <?php ManageItemsForm::city_text(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row-wrapper">
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Email'); ?>
                                </div>
                                <div class="form-controls">
                                    <input class="form-control" id="user" name="user" type="text" value="<?php echo osc_esc_html(Params::getParam('user')); ?>" />
                                    <input id="userId" name="userId" type="hidden" value="<?php echo osc_esc_html(Params::getParam('userId')); ?>" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Premium'); ?>
                                </div>
                                <div class="form-controls">
                                    <select class="form-select" id="b_premium" name="b_premium">
                                        <option value="" <?php echo ((Params::getParam('b_premium') == '')
                                                                ? 'selected="selected"' : '') ?>><?php _e('Choose an option'); ?></option>
                                        <option value="1" <?php echo ((Params::getParam('b_premium') == '1')
                                                                ? 'selected="selected"' : '') ?>><?php _e('ON'); ?></option>
                                        <option value="0" <?php echo ((Params::getParam('b_premium') == '0')
                                                                ? 'selected="selected"' : '') ?>><?php _e('OFF'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Active'); ?>
                                </div>
                                <div class="form-controls">
                                    <select class="form-select" id="b_active" name="b_active">
                                        <option value="" <?php echo ((Params::getParam('b_active') == '') ? 'selected="selected"'
                                                                : '') ?>><?php _e('Choose an option'); ?></option>
                                        <option value="1" <?php echo ((Params::getParam('b_active') == '1')
                                                                ? 'selected="selected"' : '') ?>><?php _e('ON'); ?></option>
                                        <option value="0" <?php echo ((Params::getParam('b_active') == '0')
                                                                ? 'selected="selected"' : '') ?>><?php _e('OFF'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Block'); ?>
                                </div>
                                <div class="form-controls">
                                    <select class="form-select" id="b_enabled" name="b_enabled">
                                        <option value="" <?php echo ((Params::getParam('b_enabled') == '')
                                                                ? 'selected="selected"' : '') ?>><?php _e('Choose an option'); ?></option>
                                        <option value="0" <?php echo ((Params::getParam('b_enabled') == '0')
                                                                ? 'selected="selected"' : '') ?>><?php _e('ON'); ?></option>
                                        <option value="1" <?php echo ((Params::getParam('b_enabled') == '1')
                                                                ? 'selected="selected"' : '') ?>><?php _e('OFF'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-label">
                                    <?php _e('Spam'); ?>
                                </div>
                                <div class="form-controls">
                                    <select class="form-select" id="b_spam" name="b_spam">
                                        <option value="" <?php echo ((Params::getParam('b_spam') == '') ? 'selected="selected"'
                                                                : '') ?>><?php _e('Choose an option'); ?></option>
                                        <option value="1" <?php echo ((Params::getParam('b_spam') == '1') ? 'selected="selected"'
                                                                : '') ?>><?php _e('ON'); ?></option>
                                        <option value="0" <?php echo ((Params::getParam('b_spam') == '0') ? 'selected="selected"'
                                                                : '') ?>><?php _e('OFF'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php osc_run_hook('filters_manage_item_search'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <input id="show-filters" type="submit" value="<?php echo osc_esc_html(__('Apply filters')); ?>" class="btn btn-success btn-sm" />
                <a class="btn btn-warning btn-sm" href="<?php echo osc_admin_base_url(true) . '?page=items'; ?>"><?php _e('Reset filters'); ?></a>
            </div>
        </div>
    </div>
</form>
<form id="itemDeleteModal" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="modal fade static">
    <input type="hidden" name="page" value="items" />
    <input type="hidden" name="action" value="delete" />
    <input type="hidden" name="id[]" value="" />
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php _e('Delete listing'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php _e('Are you sure you want to delete this listing?'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel'); ?></button>
                <button id="itemDeleteSubmit" class="btn btn-sm btn-red" type="submit">
                    <?php echo __('Delete'); ?>
                </button>
            </div>
        </div>
    </div>
</form>
<div id="bulkActionsModal" class="modal fade static" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel"><?php _e('Bulk actions'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel'); ?></button>
                <button id="bulkActionsSubmit" onclick="bulkActionsSubmit()" class="btn btn-sm btn-red"><?php echo osc_esc_html(__('Delete')); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    var filterSelect = document.getElementById("filter-select")
    filterSelect.onchange = function() {
        let selectedOption = this.options[this.selectedIndex].value
        let inputIdsArr = ['ItemId', 'User', 'Pattern']
        for (let i = 0; i < inputIdsArr.length; i++) {
            if ('o' + inputIdsArr[i] === selectedOption) {
                document.getElementById("f" + inputIdsArr[i]).classList.remove("hide");
            } else {
                document.getElementById("f" + inputIdsArr[i]).classList.add("hide");
            }
        }
    }

    function delete_dialog(item_id) {
        var deleteModal = document.getElementById("itemDeleteModal")
        deleteModal.querySelector("input[name='id[]']").value = item_id;
        (new bootstrap.Modal(document.getElementById("itemDeleteModal"))).toggle()
        return false;
    }
</script>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>