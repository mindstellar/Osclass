<?php
/*
 * OSClass – software for creating and publishing online classified advertising platforms
 *
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

/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 06-08-2021
 * Time: 16:03
 * License is provided in root directory.
 */

namespace mindstellar\form\admin;

use Category;
use Exception;
use mindstellar\form\base\FormInputs;
use mindstellar\utility\Escape;
use mindstellar\utility\Sanitize;
use Session;

/**
 * Admin Item Form
 */
class Item extends FormInputs
{
    /**
     * @var \mindstellar\form\admin\Item
     */
    private static $instance;
    protected $textClass = 'form-control form-control-sm';
    protected $selectClass = 'form-select form-select-sm';
    protected $passwordClass = 'form-control form-control-sm';
    /**
     * @var \Session
     */
    private $Session;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $currentLocaleCode;

    public function __construct(Escape $escape = null, Sanitize $sanitize = null)
    {
        parent::__construct($escape, $sanitize);
        $this->Session           = Session::newInstance();
        $this->locales           = osc_get_admin_locales();
        $this->currentLocaleCode = osc_current_admin_locale();
    }

    /**
     * @return \mindstellar\form\admin\Item
     */
    public static function instance(): Item
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Generate MultiLanguage Title Description Fields for Item
     *
     * @param null $locales
     * @param null $item
     */
    public function printMultiLangTitleDesc($item = null, $with_tab = true)
    {
        if ($item === null) {
            $item = osc_item();
        }
        if ($with_tab) {
            $this->printMultiLangTab();
        }
        echo '<div class="tab-content mb-3" id="multiLangTabsContent" >';

        foreach ($this->locales as $locale) {
            // Add class active if $current_locale is equal to $locale['pk_c_code']
            $active = '';
            if ($locale['pk_c_code'] === $this->currentLocaleCode) {
                $active = 'show active';
            }
            echo '<div class="tab-pane fade ' . $active . '" id="' . $locale['pk_c_code'] . '" role="tabpanel">';
            $this->printItemTitleInput($locale, $item);
            $this->printItemDescriptionInput($locale, $item);
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Print MultiLang Tab
     */
    public function printMultiLangTab()
    {
        if (count($this->locales) > 1) {
            echo '<div id="language-tab" class="mt-3">';
            echo '<ul class="nav nav-tabs" id="multiLangTabs" role="tablist">';
            foreach ($this->locales as $locale) {
                $active = '';
                if ($locale['pk_c_code'] === $this->currentLocaleCode) {
                    $active = 'show active';
                }
                echo '<li class="nav-item"><a class="nav-link btn-sm ' . $active . '" href="#' . $locale['pk_c_code']
                    . '" data-bs-toggle="tab">'
                    . $locale['s_name'] . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    /**
     * Print Item Title Input
     *
     * @param                                   $locale
     * @param array                             $item
     */
    private function printItemTitleInput($locale, array $item = null)
    {
        $sessionTitle = $this->Session->_getForm('title');
        $value        = $sessionTitle[$locale['pk_c_code']] ?? $item['locale'][$locale['pk_c_code']]['s_title'] ?? '';
        $value        = osc_apply_filter('admin_item_title', $value, $item, $locale);
        $name         = 'title' . '[' . $locale['pk_c_code'] . ']';
        $attributes   = [
            'id'          => $name,
            'placeholder' => __('Enter title here') . ' *',
        ];
        $options      = [
            'sanitize' => 'html',
            'label'    => __('Title') . ' *'
        ];
        try {
            echo $this->text($name, $value, $attributes, $options);
        } catch (Exception $e) {
            if (OSC_DEBUG) {
                trigger_error($e->getTraceAsString());
            }
        }
    }

    /**
     * Print Item Description Text Area
     *
     * @param                                   $locale
     * @param array                             $item
     */
    private function printItemDescriptionInput($locale, array $item = null)
    {
        $sessionDesc = $this->Session->_getForm('description');
        $value       = $sessionDesc[$locale['pk_c_code']] ?? $item['locale'][$locale['pk_c_code']]['s_description'] ?? '';
        $value       = osc_apply_filter('admin_item_description', $value, $item, $locale);
        $name        = 'description' . '[' . $locale['pk_c_code'] . ']';
        $attributes  = [
            'id'       => $name,
            'rows'     => '20'
        ];
        $options     = [
            'label'    => __('Description') . ' *',
            'sanitize' => null,
        ];
        try {
            echo $this->textarea($name, $value, $attributes, $options);
        } catch (Exception $e) {
            if (OSC_DEBUG) {
                trigger_error($e->getTraceAsString());
            }
        }
    }

    /**
     * print price field and Currency Select Input
     *
     * @param array|null $currencies
     * @param array|null $item
     */
    public function itemPrice()
    {
        if (osc_price_enabled_at_items()) {
            $currencies = osc_get_currencies();
            $item       = osc_item();
            echo '<label>' . __('Price') . '</label>';
            echo '<div class="item-price input-group input-group-sm">';
            $this->printPriceInput($item);
            // Create currency select
            if ($this->Session->_getForm('currency')) {
                $item['fk_c_currency_code'] = $this->Session->_getForm('currency');
            }
            if (count($currencies) > 1) {
                $default_key = null;
                $currency    = \Preference::newInstance()->get('currency');
                if (isset($item['fk_c_currency_code'])) {
                    $default_key = $item['fk_c_currency_code'];
                } elseif (isset($currency)) {
                    $default_key = $currency;
                }
                $attributes['id']             = 'currency';
                $attributes['style']          = 'max-width:150px';
                $options['defaultValue']      = $default_key;
                $options['selectPlaceholder'] = __('Select Currency');
                $values                       = [];
                foreach ($currencies as $i) {
                    $values[$i['pk_c_code']] = $i['s_description'];
                }
                echo $this->select('currency', $values, $attributes, $options);
            } elseif (count($currencies) === 1) {
                echo $this->hidden('currency', $currencies[0]['pk_c_code']);
                echo '<div class="input-group-append">';
                echo $currencies[0]['s_description'];
                echo '</div>';
            }
            echo '</div>';
        }
    }

    /**
     * Print Price Input without currency select
     * @param array $item
     *
     */
    private function printPriceInput(array $item = null)
    {
        if ($this->Session->_getForm('price')) {
            $item['i_price'] = $this->Session->_getForm('price');
        }
        $attr['id']           = 'price';
        $attr['maxlength']    = null;
        $attr['placeholder']  = __('Enter price');
        $attr['autocomplete'] = 'off';

        try {
            echo (new self())->text('price', isset($item['i_price']) ? osc_prepare_price($item['i_price']) : null, $attr);
        } catch (Exception $e) {
            if (OSC_DEBUG) {
                trigger_error($e->getTraceAsString());
            }
        }
        unset($attr);
    }
}
