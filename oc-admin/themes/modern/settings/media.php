<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2014 Osclass (original work, licensed under the Apache License 2.0)
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. The original
 * Osclass code it derives from was licensed under the Apache License 2.0.
 * See LICENSE (GPL-3.0) and LICENSE-APACHE (Apache-2.0).
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

$maxPHPsize    = View::newInstance()->_get('max_size_upload');
$imagickLoaded = extension_loaded('imagick');
$aGD           = @gd_info();
$freeType      = array_key_exists('FreeType Support', $aGD);

//customize Head
$media_js = static function () {
    ?>
    <script type="text/javascript">
        // Code for form validation. Dimension fields must match NxN. Wrapped in
        // DOMContentLoaded so it works when ui-osc.js (oscValidateForm) is deferred.
        document.addEventListener('DOMContentLoaded', function () {
        oscValidateForm(document.querySelector('form[name=media_form]'), {
            rules: {
                dimThumbnail: { required: true, pattern: /^[0-9]+x[0-9]+$/i },
                dimPreview: { required: true, pattern: /^[0-9]+x[0-9]+$/i },
                dimNormal: { required: true, pattern: /^[0-9]+x[0-9]+$/i },
                maxSizeKb: { required: true, digits: true }
            },
            messages: {
                dimThumbnail: {
                    required: '<?php echo osc_esc_js(__('Thumbnail size: this field is required')); ?>',
                    pattern: '<?php echo osc_esc_js(__('Thumbnail size: is not in the correct format')); ?>'
                },
                dimPreview: {
                    required: '<?php echo osc_esc_js(__('Preview size: this field is required')); ?>',
                    pattern: '<?php echo osc_esc_js(__('Preview size: is not in the correct format')); ?>'
                },
                dimNormal: {
                    required: '<?php echo osc_esc_js(__('Normal size: this field is required')); ?>',
                    pattern: '<?php echo osc_esc_js(__('Normal size: is not in the correct format')); ?>'
                },
                maxSizeKb: {
                    required: '<?php echo osc_esc_js(__('Maximum size: this field is required')); ?>',
                    digits: '<?php echo osc_esc_js(__('Maximum size: this field must only contain numeric characters')); ?>'
                }
            },
            errorContainer: '#error_list',
            onInvalid: function () {
                var h1 = document.querySelector('h1');
                if (h1) { h1.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            }
        });

        document.querySelector('#watermark_none').addEventListener('change', function () {
            if (this.checked) {
                document.querySelector('#watermark_text_box').style.display = "none";
                document.querySelector('#watermark_image_box').style.display = "none";
            }
        });

        function watermarkModal() {
            document.getElementById('dialog-watermark-warning').showModal();
            return false;
        }

        document.querySelector('#watermark_text').addEventListener('change', function () {
            if (this.checked) {
                document.querySelector('#watermark_text_box').style.display = "block";
                document.querySelector('#watermark_image_box').style.display = "none";
                if (!document.querySelector('input[name="keep_original_image"]').checked) {
                    watermarkModal();
                }
            }
        });

        document.querySelector('#watermark_image').addEventListener('change', function () {
            if (this.checked) {
                document.querySelector('#watermark_text_box').style.display = "none";
                document.querySelector('#watermark_image_box').style.display = "block";
                if (!document.querySelector('input[name="keep_original_image"]').checked) {
                    watermarkModal();
                }
            }
        });

        document.querySelector('input[name="keep_original_image"]').addEventListener("change", function () {
            if (!this.checked) {
                if (!document.querySelector('#watermark_none').checked) {
                    watermarkModal();
                }
            }
        });
        });
    </script>
    <?php
};

osc_add_hook('admin_footer', $media_js, 10);

osc_admin_page(array(
    'section' => __('Media'),
    'title'   => __('Media Settings'),
    'help'    => __('Manage the options for the images users can upload along with their listings. You can limit their size, '
                    . 'the number of images per ad, include a watermark, etc.'),
));

$watermarkPlaces = array(
    'centre' => __('Centre'),
    'tl'     => __('Top Left'),
    'tr'     => __('Top Right'),
    'bl'     => __('Bottom Left'),
    'br'     => __('Bottom Right'),
);

osc_current_admin_theme_path('parts/header.php'); ?>
    <div id="general-settings">
        <?php osc_admin_page_head(__('Media Settings')); ?>
        <ul id="error_list"></ul>
        <?php osc_admin_form_open(array(
            'name'   => 'media_form',
            'page'   => 'settings',
            'action' => 'media_post',
            'upload' => true,
        )); ?>
                    <?php osc_admin_page_head(__('Image sizes')); ?>
                    <p class="form-intro"><?php _e('The sizes listed below determine the maximum dimensions in pixels to use when uploading a image.'
                                . ' Format: <b>Width</b> x <b>Height</b>.'); ?>
                    </p>
                    <?php
                    osc_admin_text(array(
                        'name'  => 'dimThumbnail',
                        'label' => __('Thumbnail size'),
                        'value' => osc_thumbnail_dimensions(),
                        'width' => 'num',
                    ));
                    osc_admin_text(array(
                        'name'  => 'dimPreview',
                        'label' => __('Preview size'),
                        'value' => osc_preview_dimensions(),
                        'width' => 'num',
                    ));
                    osc_admin_text(array(
                        'name'  => 'dimNormal',
                        'label' => __('Normal size'),
                        'value' => osc_normal_dimensions(),
                        'width' => 'num',
                    ));
                    osc_admin_field(array(
                        'type'      => 'checkbox',
                        'row_label' => __('Original size'),
                        'id'        => 'keep_original_image',
                        'name'      => 'keep_original_image',
                        'label'     => __('Keep original image, unaltered after uploading.'),
                        'checked'   => osc_keep_original_image(),
                        'help'      => __('Image may occupy more space than usual.'),
                    ));

                    osc_admin_page_head(__('Restrictions'));

                    osc_admin_field(array(
                        'type'      => 'checkbox',
                        'row_label' => __('Force JPEG'),
                        'id'        => 'force_jpeg',
                        'name'      => 'force_jpeg',
                        'label'     => __('Force JPEG extension.'),
                        'checked'   => osc_force_jpeg(),
                        'help'      => __('Uploaded images will be saved in JPG/JPEG format, '
                                          . 'it saves space but images will not have transparent background.'),
                    ));

                    $jpegQuality = (int)osc_get_preference('jpeg_quality');
                    if ($jpegQuality < 1 || $jpegQuality > 100) {
                        $jpegQuality = 82;
                    }
                    osc_admin_number(array(
                        'name'  => 'jpeg_quality',
                        'label' => __('JPEG quality'),
                        'value' => $jpegQuality,
                        'min'   => 1,
                        'max'   => 100,
                        'help'  => __('Compression quality for saved JPEGs, from 1 (smallest file) to '
                                      . '100 (best quality). 82 is a good balance.'),
                    ));
                    osc_admin_field(array(
                        'type'      => 'checkbox',
                        'row_label' => __('Force aspect'),
                        'id'        => 'force_aspect_image',
                        'name'      => 'force_aspect_image',
                        'label'     => __('Force image aspect.'),
                        'checked'   => osc_force_aspect_image(),
                        'help'      => __('No white background will be added to keep the size.'),
                    ));
                    osc_admin_number(array(
                        'name'      => 'maxSizeKb',
                        'label'     => __('Maximum size'),
                        'value'     => osc_max_size_kb(),
                        'min'       => 1,
                        'suffix'    => __('KB'),
                        'help_html' => '<span class="callout-warning">'
                            . osc_esc_html(sprintf(__('Maximum size PHP configuration allows: %d KB'), $maxPHPsize))
                            . '</span>',
                    ));
                    osc_admin_field(array(
                        'type'      => 'checkbox',
                        'row_label' => __('ImageMagick'),
                        'id'        => 'use_imagick',
                        'name'      => 'use_imagick',
                        'label'     => __('Use ImageMagick instead of GD library'),
                        'checked'   => $imagickLoaded && osc_use_imagick(),
                        'disabled'  => !$imagickLoaded,
                        'help_html' => ($imagickLoaded
                            ? ''
                            : '<span class="callout-danger">' . osc_esc_html(__('ImageMagick library is not loaded')) . '</span> ')
                            . osc_esc_html(__("It's faster and consumes less resources than GD library.")),
                    )); ?>
                    <?php osc_admin_page_head(__('Watermark')); ?>
                    <?php
                    $watermarkType = osc_is_watermark_image() ? 'image' : (osc_is_watermark_text() ? 'text' : 'none');
                    osc_admin_radio_group(array(
                        'name'     => 'watermark_type',
                        'label'    => __('Watermark type'),
                        'selected' => $watermarkType,
                        'options'  => array(
                            'none'  => array('label' => __('None'), 'id' => 'watermark_none'),
                            'text'  => array(
                                'label'       => __('Text'),
                                'id'          => 'watermark_text',
                                'disabled'    => !$freeType,
                                'custom_html' => $freeType ? '' : '<span class="callout-danger">' . sprintf(
                                    __('Freetype library is required. How to <a target="_blank" rel="noopener" href="%s">install/configure</a>'),
                                    'https://www.php.net/manual/en/image.installation.php'
                                ) . '</span>',
                            ),
                            'image' => array('label' => __('Image'), 'id' => 'watermark_image'),
                        ),
                    )); ?>
                    <div id="watermark_text_box" class="table-backoffice-form" <?php echo(osc_is_watermark_text() ? ''
                        : 'style="display:none;"'); ?>>
                        <?php osc_admin_page_head(__('Watermark Text Settings')); ?>
                        <?php osc_admin_text(array(
                            'name'  => 'watermark_text',
                            'label' => __('Watermark Text'),
                            'value' => osc_watermark_text(),
                        )); ?>
                        <?php
                        if (Preference::newInstance()->get('watermark_text_options')) {
                            $watermark_options = json_decode(
                                Preference::newInstance()->get('watermark_text_options'),
                                true
                            );
                            if (isset($watermark_options['watermark_width']) && $watermark_options['watermark_width']) {
                                $watermark_width = (int)$watermark_options['watermark_width'];
                            } else {
                                $watermark_width = 200;
                            }
                            if (isset($watermark_options['watermark_height']) && $watermark_options['watermark_height']) {
                                $watermark_height = (int)$watermark_options['watermark_height'];
                            } else {
                                $watermark_height = 30;
                            }
                            if (isset($watermark_options['text_offset_x']) && $watermark_options['text_offset_x']) {
                                $text_offset_x = (int)$watermark_options['text_offset_x'];
                            } else {
                                $text_offset_x = 0;
                            }
                            if (isset($watermark_options['text_offset_y']) && $watermark_options['text_offset_y']) {
                                $text_offset_y = (int)$watermark_options['text_offset_y'];
                            } else {
                                $text_offset_y = $watermark_height;
                            }
                            if (isset($watermark_options['text_angle']) && $watermark_options['text_angle']) {
                                $text_angle = (int)$watermark_options['text_angle'];
                            } else {
                                $text_angle = 0;
                            }
                            if (isset($watermark_options['background_color']) && $watermark_options['background_color']) {
                                $background_color = $watermark_options['background_color'];
                            } else {
                                $background_color = '#000000';
                            }

                            ?>
                            <?php
                            osc_admin_number(array(
                                'name'   => 'watermark_width',
                                'label'  => __('Watermark Width'),
                                'value'  => $watermark_width,
                                'step'   => 1,
                                'suffix' => __('px'),
                            ));
                            osc_admin_number(array(
                                'name'   => 'watermark_height',
                                'label'  => __('Watermark Height'),
                                'value'  => $watermark_height,
                                'step'   => 1,
                                'suffix' => __('px'),
                            ));
                            osc_admin_number(array(
                                'name'   => 'text_offset_x',
                                'label'  => __('Text offset_x'),
                                'value'  => $text_offset_x,
                                'step'   => 1,
                                'suffix' => __('px'),
                            ));
                            osc_admin_number(array(
                                'name'   => 'text_offset_y',
                                'label'  => __('Text offset_y'),
                                'value'  => $text_offset_y,
                                'step'   => 1,
                                'suffix' => __('px'),
                            ));
                            osc_admin_field(array(
                                'type'  => 'color',
                                'id'    => 'colorpickerField1',
                                'name'  => 'watermark_text_color',
                                'label' => __('Text Color'),
                                'value' => osc_watermark_text_color(),
                            ));
                            osc_admin_field(array(
                                'type'  => 'color',
                                'id'    => 'colorpickerField2',
                                'name'  => 'background_color',
                                'label' => __('Background Color'),
                                'value' => $background_color,
                                'help'  => __('Background Hexadecimal color value'),
                            )); ?>
                        <?php } ?>
                        <?php if (osc_is_watermark_text() && osc_watermark_text_color()) { ?>
                            <?php osc_admin_form_row_open(__('Preview Watermark')); ?>
                                    <div class="help-box">
                                        <?php if (!file_exists(Preference::newInstance()->get('watermark_text_options'))) {
                                            ImageProcessing::createWatermarkImageFromText(
                                                osc_watermark_text(),
                                                osc_watermark_text_color()
                                            );
                                        }
                            ?>
                                        <img src="<?php
                            echo osc_base_url()
                                 . str_replace(osc_base_path(), '', osc_uploads_path())
                                 . Preference::newInstance()->get('watermark_text_image_name') ?>"/>
                                    </div>
                            <?php osc_admin_form_row_close(); ?>
                        <?php } ?>
                        <?php osc_admin_select(array(
                            'id'       => 'watermark_text_place',
                            'name'     => 'watermark_text_place',
                            'label'    => __('Position'),
                            'selected' => osc_watermark_place(),
                            'options'  => $watermarkPlaces,
                        )); ?>
                    </div>
                    <div id="watermark_image_box" <?php echo(osc_is_watermark_image() ? '' : 'style="display:none;"'); ?>>
                        <?php osc_admin_page_head(__('Watermark Image Settings')); ?>
                        <?php osc_admin_field(array(
                            'type'      => 'file',
                            'id'        => 'watermark_image_file',
                            'name'      => 'watermark_image',
                            'label'     => __('Image'),
                            'attrs'     => array('accept' => 'image/png'),
                            'help_html' => (osc_is_watermark_image()
                                ? '<img width="100" alt="" src="' . osc_esc_html(
                                    osc_base_url() . str_replace(osc_base_path(), '', osc_uploads_path()) . 'watermark.png'
                                ) . '"><br>'
                                : '')
                                . osc_esc_html(__('It has to be a .PNG image')) . '<br>'
                                . osc_esc_html(__("Shopclass doesn't check the watermark image size")),
                        )); ?>
                        <?php osc_admin_select(array(
                            'id'       => 'watermark_image_place',
                            'name'     => 'watermark_image_place',
                            'label'    => __('Position'),
                            'selected' => osc_watermark_place(),
                            'options'  => $watermarkPlaces,
                        )); ?>
                    </div>
                    <?php osc_admin_page_head(__('Regenerate images')); ?>
                    <?php osc_admin_form_row_open(''); ?>
                            <p>
                                <?php _e('You can regenerate different image dimensions. If you have changed the dimension of thumbnails, '
                                                                . 'preview or normal images, you might want to regenerate your images.'); ?>
                            </p>
                            <a class="btn btn-dim"
                               href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post' . '&'
                                                                       . osc_csrf_token_url(); ?>"><?php _e('Regenerate'); ?></a>
                    <?php osc_admin_form_row_close(); ?>
                    <div class="clear"></div>
                    <?php osc_admin_form_close(array()); ?>
    </div>
    <dialog id="dialog-watermark-warning" class="osc-dialog">
        <div class="osc-dialog-body">
            <p class="osc-dialog-title"><?php echo osc_esc_html(__('Recommendation')); ?></p>
            <p class="osc-dialog-text"><?php _e("We highly recommend you have the 'Keep original image' option active when you use watermarks."); ?></p>
        </div>
        <div class="osc-dialog-actions">
            <button type="button" class="btn btn-dim btn-sm" data-osc-dialog-close><?php _e('Cancel'); ?></button>
        </div>
    </dialog>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>