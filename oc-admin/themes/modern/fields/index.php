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

osc_enqueue_script('jquery-treeview');

$fields     = __get('fields');
$categories = __get('categories');
$selected   = __get('default_selected');

function addHelp()
{
    echo '<p>'
         . __('Create new fields for users to fill out when they publish a listing. '
              . 'You can require extra  information such as the number of bedrooms in real estate listings or '
              . 'fuel type in car listings, for example.')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Listing'); ?>
        <a href="#" class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"></a>
        <a href="#" class="text-success ms-1 float-end"
           id="add-button" title="<?php _e('Add custom field'); ?>"><i class="bi bi-plus-circle-fill"></i></a>
    </h1>
    <?php
}


osc_add_hook('admin_page_header', 'customPageHeader');
//customize Head
function customHead()
{
    $csrf_token = osc_csrf_token_url(); ?>
    <script type="text/javascript">

        function show_iframe(class_name, id) {
            if ($('.content_list_' + id + ' .custom-field-frame').length == 0) {
                $('.custom-field-frame').remove();
                var name = 'frame_' + id;
                var id_ = 'frame_' + id;
                var url = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=field_categories_iframe&<?php echo $csrf_token;
                ?>&id=' + id;
                $.ajax({
                    url: url,
                    context: document.body,
                    success: function (res) {
                        $('div.' + class_name).append().html(res);
                        $('div.' + class_name).fadeIn("fast");
                    }
                });
            } else {
                $('.custom-field-frame').remove();
            }
            return false;
        }

        // check all the categories
        function checkAll(id, check) {
            aa = $('#' + id + ' input[type=checkbox]').each(function () {
                $(this).prop('checked', check);
            });
        }

        function checkCat(id, check) {
            aa = $('#cat' + id + ' input[type=checkbox]').each(function () {
                $(this).prop('checked', check);
            });
        }

        $(document).ready(function () {



            $("#add-button, .add-button").bind('click', function () {
                $.ajax({
                    url: '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=add_field&<?php echo $csrf_token; ?>',
                    context: document.body,
                    success: function (res) {
                        var ret = eval("(" + res + ")");
                        if (ret.error == 0) {
                            const customFieldMarkup = `
                            <li id="list_${ret.field_id}" class="field_li even">
                            <div class="cfield-div" field_id="${ret.field_id}">
                                <div class="name-edit-cfield" id="${ret.field_id}">
                                    ${ret.field_name}
                                </div>
                                <div class="ms-auto btn-group">
                                    <a class="px-2 text-dark" href="javascript:void(0);"
                                       onclick="show_iframe('content_list_${ret.field_id}','${ret.field_id}');"
                                       title="<?php echo osc_esc_js(__('Edit')); ?>"><i class="bi bi-pencil-fill"></i></a>
                                    <a class="px-2 text-dark" href="javascript:void(0);"
                                       onclick="delete_field('${ret.field_id}');" title="<?php echo osc_esc_js(__('Delete'));?>">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="edit content_list_${ret.field_id}"></div>
                        </li>`;
                            $("#fields-empty").remove();
                            $("#ul_fields").append(customFieldMarkup);
                            show_iframe('content_list_' + ret.field_id, ret.field_id);
                        } else {
                            var message = "";
                            message += '<?php echo osc_esc_js(__('Custom field could not be added')); ?>'
                            $(".jsMessage").fadeIn('fast');
                            $(".jsMessage p").html(message);
                        }
                    }
                });
                return false;
            });

            $("#new_cat_tree").treeview({
                animated: "fast",
                collapsed: true
            });

            $("select[name='field_type_new']").bind('change', function () {
                if ($(this).attr('value') == 'DROPDOWN' || $(this).attr('value') == 'RADIO') {
                    $('#div_field_options').show();
                } else {
                    $('#div_field_options').hide();
                }
            });

            var field_type_new_value = $("select[name='field_type_new']").attr('value');
            if (field_type_new_value == 'TEXT'
                || field_type_new_value == 'TEXTAREA'
                || field_type_new_value == 'CHECKBOX'
                || field_type_new_value == 'URL') {
                $('#div_field_options').hide();
            }
        });
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf(__('Custom fields &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>
    <div class="header_title">
        <h2 class="render-title">
            <?php _e('Custom fields'); ?>
        </h2>
    </div>
    <!-- custom fields -->
    <div class="custom-fields">
        <!-- list fields -->
        <div class="list-fields">
            <ul id="ul_fields">
                <?php $even = true;
                if (count($fields) == 0) { ?>
                    <span id="fields-empty"><?php _e("You don't have any custom fields yet"); ?></span>
                <?php } else {
                    foreach ($fields as $field) { ?>
                        <li id="list_<?php echo $field['pk_i_id']; ?>"
                            class="field_li <?php echo($even ? 'even' : 'odd'); ?>">
                            <div class="cfield-div" field_id="<?php echo $field['pk_i_id']; ?>">
                                <div class="name-edit-cfield" id="<?php echo 'quick_edit_' . $field['pk_i_id']; ?>">
                                    <?php echo $field['s_name']; ?>
                                </div>
                                <div class="ms-auto btn-group">
                                    <a class="px-2 text-dark" href="javascript:void(0);"
                                       onclick="show_iframe('content_list_<?php echo $field['pk_i_id']; ?>','<?php echo
                                        $field['pk_i_id']; ?>');" title="<?php _e('Edit'); ?>"><i class="bi bi-pencil-fill"></i></a>
                                    <a class="px-2 text-dark" href="javascript:void(0);"
                                       onclick="delete_field('<?php echo $field['pk_i_id']; ?>');" title="<?php _e('Delete');
                                        ?>">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="edit content_list_<?php echo $field['pk_i_id']; ?>"></div>
                        </li>
                        <?php $even = !$even;
                    }
                } ?>
            </ul>
        </div>
        <!-- /list fields -->
    </div>
    <!-- /custom fields -->
    <div class="clear"></div>
    <div id="dialog-delete-field" title="<?php echo osc_esc_html(__('Delete custom field')); ?>"
         class="has-form-actions hide" data-field-id="">
        <div class="form-horizontal">
            <div class="form-row">
                <?php _e('Are you sure you want to delete this custom field?'); ?>
            </div>
            <div class="form-actions">
                <div class="wrapper">
                    <a class="btn btn-dim" href="javascript:void(0);"
                       onclick="$('#dialog-delete-field').dialog('close');"><?php _e('Cancel'); ?></a>
                    <a id="field-delete-submit" href="javascript:void(0);"
                       class="btn btn-red"><?php echo osc_esc_html(__('Delete')); ?></a>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="deleteModal" method="get"
         action="<?php echo osc_admin_base_url(true); ?>"
         class="modal fade static"
         data-field-id="">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo __('Delete custom field'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php _e('Are you sure you want to delete this custom field?'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel'); ?></button>
                    <button id="deleteSubmit" data-bs-dismiss="modal" class="btn btn-sm btn-red" type="submit">
                        <?php echo __('Delete'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("deleteSubmit").onclick = function() {
            let fieldId = document.getElementById(
                "deleteModal"
            ).dataset.fieldId;
            let url = "<?php
                echo osc_admin_base_url(true); ?>?page=ajax&action=delete_field&<?php echo osc_csrf_token_url();
?>&id=" + fieldId;
            fetch(url, {
                credentials: "same-origin"
            }).then(function(response) {
                    if (!response.ok) {
                        setJsMessage("error", response.statusText);
                    }
                    return response.json()
                })
                .then(function(jsonObj) {
                    if (jsonObj.error) {
                        setJsMessage("error", jsonObj.error);
                    }
                    if (jsonObj.ok) {
                        setJsMessage("ok", jsonObj.ok);
                        document.getElementById('list_' + fieldId).remove()
                    }
                }).catch(function(error) {
                setJsMessage("error", "<?php echo osc_esc_js(__("Ajax error, try again.")); ?>:" + error);
            });
        };

        function delete_field(id) {
            var deleteModal = document.getElementById("deleteModal");
            deleteModal.setAttribute("data-field-id", id);
            (new bootstrap.Modal(document.getElementById("deleteModal"))).toggle();
            return false;
        }
    </script>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>