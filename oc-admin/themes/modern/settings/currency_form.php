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

//customize Head
function customHead()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            oscValidateForm(document.querySelector('form[name=currency_form]'), {
                rules: {
                    pk_c_code: { required: true, minlength: 3, maxlength: 3 },
                    s_name: { required: true, minlength: 1 }
                },
                messages: {
                    pk_c_code: {
                        required: '<?php echo osc_esc_js(__('Currency code: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Currency code: this field is required')); ?>.',
                        maxlength: '<?php echo osc_esc_js(__('Currency code: this field is required')); ?>.'
                    },
                    s_name: {
                        required: '<?php echo osc_esc_js(__('Name: this field is required')); ?>.',
                        minlength: '<?php echo osc_esc_js(__('Name: this field is required')); ?>.'
                    }
                },
                errorContainer: '#error_list',
                onInvalid: function () {
                    var h1 = document.querySelector('h1');
                    if (h1) { h1.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                }
            });
        });
    </script>
    <?php
}

osc_add_hook('admin_header', 'customHead', 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'actions' => array(
        array(
            'icon'  => 'bi-plus-circle-fill',
            'url'   => osc_admin_base_url(true) . '?page=settings&action=currencies&type=add',
            'title' => __('Add'),
        ),
    ),
));

$typeForm = __get('typeForm');
/**
 * @param string $return
 *
 * @return mixed
 */
function customText($return = 'title')
{
    $typeForm = __get('typeForm');
    $text     = array();
    switch ($typeForm) {
        case ('add_post'):
            $text['title']  = __('Add currency');
            $text['button'] = __('Add currency');
            break;
        case ('edit_post'):
            $text['title']  = __('Edit currency');
            $text['button'] = __('Update currency');
            break;
    }

    return $text[$return];
}

/**
 * @param $string
 *
 * @return string
 */
function customPageTitle($string)
{
    return sprintf('%s &raquo; %s', customText('title'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

$aCurrency = View::newInstance()->_get('aCurrency');

osc_current_admin_theme_path('parts/header.php'); ?>
    <div id="add-currency-settings">
        <?php osc_admin_page_head(customText('title')); ?>
        <ul id="error_list"></ul>
        <form name="currency_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="settings"/>
            <input type="hidden" name="action" value="currencies"/>
            <input type="hidden" name="type" value="<?php echo $typeForm; ?>"/>
            <?php if ($typeForm === 'edit_post') { ?>
                <input type="hidden" name="pk_c_code" value="<?php echo osc_esc_html($aCurrency['pk_c_code']); ?>"/>
            <?php } ?>
            <fieldset>
                <div class="form-horizontal">
                    <?php
                    osc_admin_text(array(
                        'name'      => 'pk_c_code',
                        'label'     => __('Currency Code'),
                        'value'     => $aCurrency['pk_c_code'],
                        'width'     => 'num',
                        'disabled'  => $typeForm === 'edit_post',
                        'attrs'     => array('maxlength' => 3),
                        'help_html' => sprintf(
                            __('Must be a three-character code according to the <a href="%s" target="_blank" rel="noopener">ISO 4217</a>'),
                            'https://en.wikipedia.org/wiki/ISO_4217'
                        ),
                    ));
                    osc_admin_text(array(
                        'name'  => 's_description',
                        'label' => __('Currency symbol'),
                        'value' => $aCurrency['s_description'],
                        'width' => 'num',
                    ));
                    osc_admin_text(array(
                        'name'  => 's_name',
                        'label' => __('Name'),
                        'value' => $aCurrency['s_name'],
                    )); ?>
                    <div class="form-actions">
                        <?php if ($typeForm === 'edit_post') {
                            osc_admin_action_button(array(
                                'label'   => __('Cancel'),
                                'variant' => 'red',
                                'url'     => osc_admin_base_url(true) . '?page=settings&action=currencies',
                            ));
                        }
                        osc_admin_action_button(array(
                            'label'   => customText('button'),
                            'type'    => 'submit',
                            'variant' => 'primary',
                        )); ?>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <!-- /settings form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>