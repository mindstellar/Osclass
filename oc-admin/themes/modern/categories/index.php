<?php
if (!defined('OC_ADMIN')) {
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

osc_enqueue_script('jquery-nested');
//osc_enqueue_script('tabber');

$categories = __get('categories');

function addHelp()
{
    echo '<p>'
         . __('Add, edit or delete the categories or subcategories in which users can post listings. '
              . 'Reorder sections by dragging and dropping, or nest a subcategory in an expanded category. '
              . '<strong>Be careful</strong>: If you delete a category, all listings associated will also be deleted!')
         . '</p>';
}


osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Categories'); ?>
        <a href="#" class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"></a>
        <a href="<?php echo osc_admin_base_url(true); ?>?page=categories&amp;action=add_post_default&<?php echo osc_csrf_token_url(); ?>"
           class="text-success ms-1 float-end" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php _e('Add'); ?>"><i class="bi
           bi-plus-circle-fill"></i></a>
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
    return sprintf(__('Categories &raquo; %s'), $string);
}


osc_add_filter('admin_title', 'customPageTitle');

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        document.write('<style type="text/css">.tabber{ display:none; }</style>');
    </script>
    <style>
        .placeholder {
            background-color: #cfcfcf;
        }

        .footest .category_div {
            opacity: 0.8;
        }

        .list-categories li {
            opacity: 1 !important;
        }

        .category_div {
            background: #ffffff;
        }

        .alert-custom {
            background-color: #FDF5D9;
            border-bottom: 1px solid #EEDC94;
            color: #404040;
        }

        .cat-hover,
        .cat-hover .category_row {
            background-color: #fffccc !important;
            background: #fffccc !important;
        }
    </style>
    <script type="text/javascript">
        $(function () {
            $('.category_div').on('mouseenter', function () {
                $(this).addClass('cat-hover');
            }).on('mouseleave', function () {
                $(this).removeClass('cat-hover');
            });
            var list_original = '';

            $('.sortable').nestedSortable({
                disableNesting: 'no-nest',
                forcePlaceholderSize: true,
                handle: '.handle',
                helper: 'clone',
                listType: 'ul',
                items: 'li',
                maxLevels: 4,
                opacity: 0.6,
                placeholder: 'placeholder',
                revert: 250,
                tabSize: 25,
                tolerance: 'pointer',
                toleranceElement: '> div',
                create: function (event, ui) {
                },
                start: function (event, ui) {
                    list_original = $('.sortable').nestedSortable('serialize');
                    $(ui.helper).addClass('footest');
                    $(ui.helper).prepend('<div style="opacity: 1 !important; padding:5px;" class="alert-custom"><?php echo osc_esc_js(__('Note: You must expand the category in order to make it a subcategory.')); ?></div>');
                },
                stop: function (event, ui) {

                    var plist;
                    $(".jsMessage").fadeIn("fast");
                    $(".jsMessage p").attr('class', '');
                    $(".jsMessage p").html('<img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif'); ?>"> <?php echo osc_esc_js(__('This action could take a while.')); ?>');

                    var list = '';
                    list = $('.sortable').nestedSortable('serialize');
                    var array_list = $('.sortable').nestedSortable('toArray');
                    var l = array_list.length;
                    for (let k = 0; k < l; k++) {
                        if (array_list[k].item_id === $(ui.item).find('div').attr('category_id')) {
                            if (array_list[k].parent_id === 'root') {
                                $(ui.item).closest('.toggle').show();
                            }
                            break;
                        }
                    }
                    if (!$(ui.item).parent().hasClass('sortable')) {
                        $(ui.item).parent().addClass('subcategory list-unstyled');
                    }
                    if (list_original !== list) {
                        plist = array_list.reduce(function (total, current, index) {
                            total[index] = {
                                'c': current.id,
                                'p': current.parent_id
                            };
                            return total;
                        }, {});
                        //console.log(plist);
                        $.ajax({
                            type: 'POST',
                            url: "<?php echo osc_admin_base_url(true) . '?page=ajax&action=categories_order&' . osc_csrf_token_url(); ?>",
                            data: {
                                'list': JSON.stringify(plist)
                            },
                            context: document.body,
                            success: function (res) {
                                var ret = eval("(" + res + ")");
                                var message = "";
                                if (ret.error) {
                                    $(".jsMessage p").attr('class', 'error');
                                    message += ret.error;
                                }
                                if (ret.ok) {
                                    $(".jsMessage p").attr('class', 'ok');
                                    message += ret.ok;
                                }

                                $(".jsMessage").show();
                                $(".jsMessage p").html(message);
                            },
                            error: function () {
                                $(".jsMessage").fadeIn("fast");
                                $(".jsMessage p").attr('class', '');
                                $(".jsMessage p").html('<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
                            }
                        });

                        list_original = list;
                    }
                }
            });

            $(".toggle").bind("click", function (e) {
                var list = $(this).parents('li').first().find('ul');
                var lili = $(this).closest('li').find('ul').find('li').find('ul');
                var li = $(this).closest('li').first();
                if ($(this).hasClass('bi-chevron-right')) {
                    $(li).removeClass('no-nest');
                    $(list).show();
                    $(lili).hide();
                    $(this).removeClass('bi-chevron-right').addClass('bi-chevron-down');
                } else {
                    $(li).addClass('no-nest');
                    $(list).hide();
                    $(this).removeClass('bi-chevron-down').addClass('bi-chevron-right');
                }
            });
        });

        list_original = $('.sortable').nestedSortable('serialize');

        function show_iframe(class_name, id) {
            if ($('.content_list_' + id + ' .iframe-category').length == 0) {
                $('.iframe-category').remove();
                var name = 'frame_' + id;
                var id_ = 'frame_' + id;
                var url = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=category_edit_iframe&id=' + id;
                $.ajax({
                    url: url,
                    context: document.body,
                    success: function (res) {
                        $('div.' + class_name).html(res);
                        $('div.' + class_name).fadeIn("fast");
                    }
                });
            } else {
                $('.iframe-category').remove();
            }
            return false;
        }

        function enable_cat(id) {
            let url;
            let enabled;
            let jMessage = $(".jsMessage");
            let jMessageP = $(".jsMessage p");
            let category = 'div[category_id=' + id + ']';

            jMessage.fadeIn("fast");
            jMessageP.attr('class', '');
            jMessageP.html('<img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif'); ?>"> <?php echo
            osc_esc_js(__('This action could take a while.')); ?>');

            if ($(category).hasClass('disabled')) {
                enabled = 1;
            } else {
                enabled = 0;
            }

            url = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=enable_category&<?php echo osc_csrf_token_url(); ?>&id=' + id + '&enabled=' + enabled;
            $.ajax({
                url: url,
                context: document.body,
                success: function (res) {
                    const ret = eval("(" + res + ")");
                    let message = "";

                    if (ret.error) {
                        message += ret.error;
                        jMessageP.attr('class', 'error');
                    }
                    if (ret.ok) {
                        if (enabled === 0) {
                            $(category).addClass('disabled')
                                .removeClass('enabled')
                                .prop('title', '<?php _e('Enable'); ?>');
                            $(category + ' a i.bi-slash-circle-fill')
                                .removeClass('bi-slash-circle-fill')
                                .addClass('bi-play-circle-fill');

                            for (var i = 0; i < ret.affectedIds.length; i++) {
                                id = ret.affectedIds[i].id;
                                $('div[category_id=' + id + ']').addClass('disabled')
                                    .removeClass('enabled')
                                    .prop('title', '<?php _e('Enable'); ?>');
                                $('div[category_id=' + id + '] a i.bi-slash-circle-fill')
                                    .removeClass('bi-slash-circle-fill')
                                    .addClass('bi-play-circle-fill');
                            }
                        } else {
                            $(category)
                                .removeClass('disabled')
                                .addClass('enabled')
                                .prop('title', '<?php _e('Disable'); ?>');
                            $(category + 'a i.bi-play-circle-fill')
                                .addClass('bi-slash-circle-fill')
                                .removeClass('bi bi-play-circle-fill');

                            for (var i = 0; i < ret.affectedIds.length; i++) {
                                id = ret.affectedIds[i].id;
                                $('div[category_id=' + id + ']')
                                    .removeClass('disabled')
                                    .addClass('enabled')
                                    .prop('title', '<?php _e('Disable'); ?>');
                                $('div[category_id=' + id + '] a i.bi-play-circle-fill')
                                    .addClass('bi-slash-circle-fill')
                                    .removeClass('bi bi-play-circle-fill');
                            }
                        }

                        message += ret.ok;
                        jMessageP.attr('class', 'ok');
                    }

                    jMessage.show();
                    jMessageP.html(message);
                },
                error: function () {
                    jMessage.show();
                    jMessageP.attr('class', '');
                    jMessageP.html("<?php echo osc_esc_js(__('Ajax error, try again.')); ?>");
                }
            });
        }
    </script>
    <?php
}


osc_add_hook('admin_header', 'customHead', 10);

/**
 * @param $category
 */
function drawCategory($category)
{
    if (count($category['categories']) > 0) {
        $has_subcategories = true;
    } else {
        $has_subcategories = false;
    } ?>
    <li id="list_<?php echo $category['pk_i_id']; ?>"
        class="category_li <?php echo($category['b_enabled'] == 1 ? 'enabled' : 'disabled'); ?> ">
        <div class="<?php echo($category['b_enabled'] == 1 ? 'enabled' : 'disabled'); ?>" category_id="<?php echo $category['pk_i_id']; ?>">
            <div class="d-flex flex-row-horizontal bg-light rounded-1 shadow-sm">
                <div class="px-2 border-end handle"><i class="align-middle bi bi-arrows-move" role="button"></i></div>
                <div class="px-2 border-end">
                    <?php
                    if ($has_subcategories) {
                        echo '<span class="align-middle toggle bi bi-chevron-right"></span>';
                    } else {
                        echo '<span class="align-middle toggle bi bi-chevron-down"></span>';
                    } ?>
                </div>
                <div class="px-2 name-cat" id="<?php echo 'quick_edit_' . $category['pk_i_id']; ?>">
                    <?php echo '<span class="align-middle name">' . $category['s_name'] . '</span>'; ?>
                </div>
                <div class="px-2 ms-auto btn-group">
                    <a class="btn btn-sm" onclick="show_iframe('content_list_<?php echo $category['pk_i_id']; ?>','<?php echo
                    $category['pk_i_id']; ?>');" title="<?php _e('Edit'); ?>"><i class="bi bi-pencil-fill"></i></a>
                    <a class="btn btn-sm enable"
                       onclick="enable_cat('<?php echo $category['pk_i_id']; ?>')" <?php if ($category['b_enabled'] == 1) {
                            echo 'title="' . __('Disable') . '"><i class="bi bi-slash-circle-fill"></i></a>';
                                            } else {
                                                echo 'title="' . __('Enable') . '"><i class="bi bi-play-circle-fill"></i></a>';
                                            } ?> <a class="btn btn-sm" onclick="delete_category(<?php echo $category['pk_i_id']; ?>)"
                            title="<?php _e('Delete'); ?>">
                        <i class="text-danger bi bi-x-circle-fill"></i>
                    </a>
                </div>
            </div>
            <div class="edit content_list_<?php echo $category['pk_i_id']; ?>"></div>
        </div>
        <?php if ($has_subcategories) { ?>
            <ul class="list-unstyled subcategory subcategories-<?php echo $category['pk_i_id']; ?> " style="display: none;">
                <?php foreach ($category['categories'] as $subcategory) {
                    drawCategory($subcategory);
                } ?>
            </ul>
        <?php } ?>
    </li>
    <?php
} //End drawCategory
?>
<?php osc_current_admin_theme_path('parts/header.php'); ?>

    <!-- right container -->
    <div class="right">
        <!-- categories form -->
        <div class="categories">
            <div class="callout-info callout-block">
                <div class="info"><?php _e('Drag&drop the categories to reorder them the way you like. Click on edit link to edit the 
            category'); ?></div>
            </div>
            <div class="list-categories">
                <ul class="sortable list-unstyled">
                    <?php foreach ($categories as $category) {
                        if (count($category['categories']) > 0) {
                            $has_subcategories = true;
                        } else {
                            $has_subcategories = false;
                        }
                        drawCategory($category);
                    } ?>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
        <!-- /categories form -->
    </div>
    <!-- right container -->
    <form id="deleteModal" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="modal fade static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo __('Delete category'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php _e('<strong>WARNING</strong>: This will also delete the listings under that category.'
                             . ' This action cannot be undone. Are you sure you want to continue?');
?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?php _e('Cancel'); ?></button>
                    <button id="deleteSubmit" data-bs-dismiss="modal" class="btn btn-sm btn-red" type="submit">
                        <?php echo __('Delete'); ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <script>
        function delete_category(id) {
            var deleteModal = document.getElementById('deleteModal');
            deleteModal.setAttribute('data-category-id', id);
            (new bootstrap.Modal(document.getElementById('deleteModal'))).toggle();
            return false;
        }

        document.getElementById("deleteSubmit").onclick = function () {
            let categoryId = document.getElementById(
                "deleteModal"
            ).dataset.categoryId;
            let url = "<?php
                echo osc_admin_base_url(true); ?>?page=ajax&action=delete_category&<?php echo osc_csrf_token_url();
?>&id=" + categoryId;
            fetch(url, {
                credentials: "same-origin"
            }).then(function (response) {
                    if (!response.ok) {
                        setJsMessage("error", response.statusText);
                    }
                    return response.json()
                })
                .then(function (jsonObj) {
                    if (jsonObj.error) {
                        setJsMessage("error", jsonObj.error);
                    }
                    if (jsonObj.ok) {
                        setJsMessage("ok", jsonObj.ok);
                        document.getElementById('list_' + categoryId).remove()
                    }
                }).catch(function (error) {
                setJsMessage("error", "<?php echo osc_esc_js(__("Ajax error, try again.")); ?>:" + error);
            });
        };

        function setJsMessage(alertClass, alertMessage) {
            var pTag = document.getElementById("jsMessage").querySelector("p");
            pTag.setAttribute("class", alertClass);
            pTag.textContent = alertMessage;
            document.getElementById("jsMessage").classList.remove('hide')
        }
    </script>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>