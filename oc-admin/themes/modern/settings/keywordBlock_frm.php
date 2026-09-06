<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

$keyword = __get('keyword');

/**
 * @return array
 */
function customFrmText()
{
    $keyword = __get('keyword');
    $return  = array();

    if (isset($keyword['pk_i_id'])) {
        $return['edit']       = true;
        $return['title']      = __('Edit keyword');
        $return['action_frm'] = 'keyword_block_edit_post';
        $return['btn_text']   = __('Update keyword');
    } else {
        $return['edit']       = false;
        $return['title']      = __('Add new keyword');
        $return['action_frm'] = 'keyword_block_add_post';
        $return['btn_text']   = __('Add new keyword');
    }

    return $return;
}

osc_admin_page(array(
    'section' => __('Settings'),
));

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    $aux = customFrmText();

    return sprintf('%s &raquo; %s', $aux['title'], $string);
}

osc_add_filter('admin_title', 'customPageTitle');

$aux = customFrmText();
?>
<?php osc_current_admin_theme_path('parts/header.php'); ?>
<?php osc_admin_page_head($aux['title']); ?>
<div class="settings-user">
    <ul id="error_list"></ul>
    <?php
    osc_admin_form_open(array(
        'name'   => 'keyword_block_form',
        'action' => $aux['action_frm'],
    ));
    KeywordBlockForm::primary_input_hidden($keyword); ?>
                <?php osc_admin_form_row_open(__('Keyword')); ?>
                        <?php KeywordBlockForm::keyword_text($keyword); ?>
                        <div class="help-box">
                            <?php printf(
                                __('At least %d characters. Matched as a whole word unless "Substring" is checked below.'),
                                ItemSpamFilter::MIN_KEYWORD_LENGTH
                            ); ?>
                        </div>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Where to match')); ?>
                        <?php KeywordBlockForm::scope_select($keyword); ?>
                <?php osc_admin_form_row_close(); ?>
                <?php osc_admin_form_row_open(__('Substring')); ?>
                        <div class="form-label-checkbox">
                            <?php KeywordBlockForm::substring_checkbox($keyword); ?>
                            <label for="b_substring"><?php _e('Match anywhere inside a word, not just whole words'); ?></label>
                        </div>
                        <div class="help-box text-danger">
                            <?php _e('Broader and riskier — leave unchecked unless you specifically need to catch a fragment inside longer words.'); ?>
                        </div>
                <?php osc_admin_form_row_close(); ?>
                <div class="clear"></div>
                <?php osc_admin_form_close(array(
                    array('label' => $aux['btn_text'], 'type' => 'submit', 'variant' => 'primary'),
                    array(
                        'label'   => __('Cancel'),
                        'variant' => 'dim',
                        'url'     => osc_admin_base_url(true) . '?page=settings&action=keyword_block',
                    ),
                )); ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
