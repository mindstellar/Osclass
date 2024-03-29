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

$category    = __get('category');
$has_subcats = __get('has_subcategories');
$locales     = OSCLocale::newInstance()->listAllEnabled();
?>
<div class="iframe-category">
    <h3><?php _e('Edit category'); ?></h3>
    <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="ajax"/>
        <input type="hidden" name="action" value="edit_category_post"/>
        <?php CategoryForm::primary_input_hidden($category); ?>
        <fieldset>
            <div class="grid-system">
                <div class="grid-row grid-first-row grid-30 mb-0">
                    <div class="row-wrapper form-row">
                        <label><?php _e('Expiration dates'); ?></label>
                        <div class="input micro">
                            <?php CategoryForm::expiration_days_input_text($category); ?>
                            <p class="help-inline"><?php _e("If the value is zero, it means this category doesn't have an expiration"); ?></p>
                            <label><?php CategoryForm::price_enabled_for_category($category); ?>
                                <span><?php _e('Enable / Disable the price field'); ?></span></label>
                            <?php if ($has_subcats) { ?>
                                <br/>
                                <br/>
                                <label><?php CategoryForm::apply_changes_to_subcategories($category); ?>
                                    <span><?php _e('Apply the expiration date and price field changes to children categories'); ?></span></label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="grid-row grid-70 mb-0">
                    <div class="row-wrapper form-row">
                        <?php CategoryForm::multilanguage_name_description($locales, $category); ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-vertical">
                <div class="form-actions">
                    <input type="submit" class="btn btn-submit btn-sm"
                           value="<?php echo osc_esc_html(__('Save changes')); ?>"/>
                    <input type="button" class="btn btn-red btn-sm" onclick="$('.iframe-category').remove();"
                           value="<?php echo osc_esc_html(__('Cancel')); ?>"/>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.iframe-category form').submit(function () {
            $(".jsMessage").hide();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (data) {
                    var ret = eval("(" + data + ")");
                    var message = "";
                    if (ret.error == 0 || ret.error == 4) {
                        $('.iframe-category').fadeOut('fast', function () {
                            $('.iframe-category').remove();
                        });
                        $(".jsMessage p").attr('class', 'ok');
                        message += ret.msg;
                        $('.iframe-category').parent().parent().find('.name').html(ret.text);
                    } else {
                        $(".jsMessage p").attr('class', 'error');
                        message += ret.msg;
                    }

                    $(".jsMessage").fadeIn("fast");
                    $(".jsMessage p").html(message);
                    $('div.content_list_<?php echo osc_category_id(); ?>').html('');
                },
                error: function () {
                    $(".jsMessage").fadeIn("fast");
                    $(".jsMessage p").attr('class', '');
                    $(".jsMessage p").html('<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
                }
            })
            return false;
        });
        oscTab();
    });
</script>
