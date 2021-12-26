<?php
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

use mindstellar\upgrade\Osclass;
use mindstellar\upgrade\Upgrade;
use mindstellar\utility\Utils;

define('IS_AJAX', true);

/**
 * Class CAdminAjax
 */
class CAdminAjax extends AdminSecBaseModel
{

    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        if ($this->isModerator()
            && !in_array($this->action, array('items', 'media', 'comments', 'custom', 'runhook'))
        ) {
            $this->action = 'error_permissions';
        }
    }

    //Business Layer...
    public function doModel()
    {
        //specific things for this class
        switch ($this->action) {
            case 'bulk_actions':
                break;
            case 'regions': //Return regions given a countryId
                $regions = Region::newInstance()->findByCountry(Params::getParam('countryId'));
                echo json_encode($regions);
                break;
            case 'cities': //Returns cities given a regionId
                $cities = City::newInstance()->findByRegion(Params::getParam('regionId'));
                echo json_encode($cities);
                break;
            case 'location': // This is the autocomplete AJAX
                $cities = City::newInstance()->ajax(Params::getParam('term'));
                echo json_encode($cities);
                break;
            case 'userajax': // This is the autocomplete AJAX
                $users = User::newInstance()->ajax(Params::getParam('term'));
                if (count($users) == 0) {
                    echo json_encode(array(
                        0 => array(
                            'id'    => '',
                            'label' => __('No results'),
                            'value' => __('No results')
                        )
                    ));
                } else {
                    echo json_encode($users);
                }
                break;
            case 'date_format':
                echo json_encode(array(
                    'format'        => Params::getParam('format'),
                    'str_formatted' => osc_format_date(date('Y-m-d H:i:s'), Params::getParam('format'))
                ));
                break;
            case 'runhook': // run hooks
                $hook = Params::getParam('hook');

                if ($hook == '') {
                    echo json_encode(array('error' => 'hook parameter not defined'));
                    break;
                }

                switch ($hook) {
                    case 'item_form':
                        osc_run_hook('item_form', Params::getParam('catId'));
                        break;
                    case 'item_edit':
                        $catId  = Params::getParam('catId');
                        $itemId = Params::getParam('itemId');
                        osc_run_hook('item_edit', $catId, $itemId);
                        break;
                    default:
                        osc_run_hook('ajax_admin_' . $hook);
                        break;
                }
                break;
            case 'categories_order': // Save the order of the categories
                osc_csrf_check();
                $aIds  = json_decode(Params::getParam('list'), true);
                $order = array();
                $error = 0;

                $catManager  = Category::newInstance();
                $aRecountCat = array();
                foreach ($aIds as $cat) {
                    if (isset($cat['c'])) {
                        if (!isset($order[$cat['p']])) {
                            $order[$cat['p']] = 0;
                        }

                        $res = $catManager->update(
                            array(
                                'fk_i_parent_id' => ($cat['p'] === 'root' ? null : $cat['p']),
                                'i_position'     => $order[$cat['p']]
                            ),
                            array('pk_i_id' => $cat['c'])
                        );
                        if (is_bool($res) && !$res) {
                            $error = 1;
                        } elseif ($res == 1) {
                            $aRecountCat[] = $cat['c'];
                        }
                        ++$order[$cat['p']];
                    }
                }

                // update category stats
                foreach ($aRecountCat as $rId) {
                    Utils::updateCategoryStatsById($rId);
                }

                if ($error) {
                    $result = array('error' => __('An error occurred'));
                } else {
                    $result = array('ok' => __('Order saved'));
                }

                osc_run_hook('edited_category_order', $error);

                echo json_encode($result);
                break;
            case 'category_edit_iframe':
                $this->_exportVariableToView(
                    'category',
                    Category::newInstance()->findByPrimaryKey(Params::getParam('id'), 'all')
                );
                if (count(Category::newInstance()->findSubcategories(Params::getParam('id'))) > 0) {
                    $this->_exportVariableToView('has_subcategories', true);
                } else {
                    $this->_exportVariableToView('has_subcategories', false);
                }
                $this->_exportVariableToView('languages', OSCLocale::newInstance()->listAllEnabled());
                $this->doView('categories/iframe.php');
                break;
            case 'field_categories_iframe':
                $selected = Field::newInstance()->categories(Params::getParam('id'));
                if ($selected == null) {
                    $selected = array();
                }
                $this->_exportVariableToView('selected', $selected);
                $this->_exportVariableToView('field', Field::newInstance()->findByPrimaryKey(Params::getParam('id')));
                $this->_exportVariableToView('categories', Category::newInstance()->toTreeAll());
                $this->doView('fields/iframe.php');
                break;
            case 'field_categories_post':
                osc_csrf_check();
                $error = 0;
                $field = Field::newInstance()->findByName(Params::getParam('s_name'));

                if (!isset($field['pk_i_id'])
                    || (isset($field['pk_i_id'])
                        && $field['pk_i_id'] == Params::getParam('id'))
                ) {
                    // remove categories from a field
                    Field::newInstance()->cleanCategoriesFromField(Params::getParam('id'));
                    // no error... continue updating fields
                    if ($error == 0) {
                        $slug     = Params::getParam('field_slug') != '' ? Params::getParam('field_slug')
                            : Params::getParam('s_name');
                        $slug     =
                            preg_replace('|([-]+)|', '-', preg_replace('|[^a-z0-9_-]|', '-', strtolower($slug)));
                        $slug_tmp = $slug;
                        $slug_k   = 0;
                        while (true) {
                            $field = Field::newInstance()->findBySlug($slug);
                            if (!$field || $field['pk_i_id'] == Params::getParam('id')) {
                                break;
                            }

                            $slug_k++;
                            $slug = $slug_tmp . '_' . $slug_k;
                        }
                        // prepare multi locale data
                        $currentAdminLocale = osc_current_admin_locale();
                        $aMetaNames        = Params::getParam('meta_s_name');
                        $metaLocale = array();
                        foreach ($aMetaNames as $k => $v) {
                            if (!empty($v)) {
                                $metaLocale[$k] = array(
                                    's_name' => trim($v),
                                );
                            }
                        }

                        $metaOptions     = Params::getParam('s_options');

                        // trim all csv options
                        if (!empty($metaOptions)) {
                            $tmpValues = explode(',', $metaOptions);
                            foreach ($tmpValues as $k => $v) {
                                $tmpValues[$k] = trim($v);
                            }
                            $metaOptions = implode(',', $tmpValues);
                            unset($tmpValues);
                        }

                        $res = Field::newInstance()->update(
                            array(
                                's_name'       => $aMetaNames[$currentAdminLocale],
                                'e_type'       => Params::getParam('field_type'),
                                's_slug'       => $slug,
                                'b_required'   => Params::getParam('field_required') == '1' ? 1 : 0,
                                'b_searchable' => Params::getParam('field_searchable') == '1' ? 1 : 0,
                                's_options'    => $metaOptions,
                            ),
                            array('pk_i_id' => Params::getParam('id'))
                        );
                        Field::newInstance()->updateJsonMeta(Params::getParam('id'), 'b_new_tab', Params::getParam('b_new_tab'));
                        Field::newInstance()->updateJsonMeta(Params::getParam('id'), 'locale', $metaLocale);
                        if (is_bool($res) && !$res) {
                            $error = 1;
                        }
                    }
                    // no error... continue inserting categories-field
                    if ($error == 0) {
                        $aCategories = Params::getParam('categories');
                        if (is_array($aCategories) && count($aCategories) > 0) {
                            $res = Field::newInstance()->insertCategories(Params::getParam('id'), $aCategories);
                            if (!$res) {
                                $error = 1;
                            }
                        }
                    }
                    // error while updating?
                    if ($error == 1) {
                        $message = __('An error occurred while updating');
                    }
                } else {
                    $error   = 1;
                    $message = __('Sorry, you already have a field with that name');
                }

                if ($error) {
                    $result = array('error' => $message);
                } else {
                    $result = array(
                        'ok'       => __('Saved'),
                        'text'     => $aMetaNames[$currentAdminLocale],
                        'field_id' => Params::getParam('id')
                    );
                }

                echo json_encode($result);

                break;
            case 'delete_field':
                osc_csrf_check();
                $res = Field::newInstance()->deleteByPrimaryKey(Params::getParam('id'));

                if ($res > 0) {
                    $result = array('ok' => __('The custom field has been deleted'));
                } else {
                    $result = array('error' => __('An error occurred while deleting'));
                }

                echo json_encode($result);
                break;
            case 'add_field':
                osc_csrf_check();
                $s_name   = __('NEW custom field');
                $slug     = preg_replace('|([-]+)|', '-', preg_replace('|[^a-z0-9_-]|', '-', strtolower($s_name)));
                $slug_tmp = $slug;
                $slug_k   = 0;
                while (true) {
                    $field = Field::newInstance()->findBySlug($slug);
                    if (!$field || $field['pk_i_id'] == Params::getParam('id')) {
                        break;
                    }

                    $slug_k++;
                    $slug = $slug_tmp . '_' . $slug_k;
                }
                $fieldManager = Field::newInstance();
                $result       = $fieldManager->insertField($s_name, 'TEXT', $slug, 0, '', array());
                if ($result) {
                    echo json_encode(array(
                        'error'      => 0,
                        'field_id'   => $fieldManager->dao->insertedId(),
                        'field_name' => $s_name
                    ));
                } else {
                    echo json_encode(array('error' => 1));
                }
                break;
            case 'fields_order':
                osc_csrf_check();
                $aIds  = json_decode(Params::getParam('list'), true);
                $order = array();
                $error = 0;

                $fieldManager = Field::newInstance();
                foreach ($aIds as $pos => $field) {
                    $res = $fieldManager->update(
                        array(
                            'i_position' => $pos
                        ),
                        array('pk_i_id' => $field)
                    );
                    if (is_bool($res) && !$res) {
                        $error = 1;
                    }
                }

                if ($error) {
                    $result = array('error' => __('An error occurred'));
                } else {
                    $result = array('ok' => __('Order saved'));
                }

                echo json_encode($result);
                break;
            case 'enable_category':
                osc_csrf_check();
                $id       = strip_tags(Params::getParam('id'));
                $enabled  = (Params::getParam('enabled') != '') ? Params::getParam('enabled') : 0;
                $error    = 0;
                $result   = array();
                $aUpdated = array();

                $mCategory = Category::newInstance();
                $aCategory = $mCategory->findByPrimaryKey($id);

                if ($aCategory == false) {
                    $result = array('error' => sprintf(__('No category with id %d exists'), $id));
                    echo json_encode($result);
                    break;
                }

                // root category
                if ($aCategory['fk_i_parent_id'] == '') {
                    $mCategory->update(array('b_enabled' => $enabled), array('pk_i_id' => $id));
                    $mCategory->update(array('b_enabled' => $enabled), array('fk_i_parent_id' => $id));

                    $subCategories = $mCategory->findSubcategories($id);

                    $aIds       = array($id);
                    $aUpdated[] = array('id' => $id);
                    foreach ($subCategories as $subcategory) {
                        $aIds[]     = $subcategory['pk_i_id'];
                        $aUpdated[] = array('id' => $subcategory['pk_i_id']);
                    }

                    Item::newInstance()->enableByCategory($enabled, $aIds);

                    if ($enabled) {
                        $result = array(
                            'ok' => __('The category as well as its subcategories have been enabled')
                        );
                    } else {
                        $result = array(
                            'ok' => __('The category as well as its subcategories have been disabled')
                        );
                    }
                    $result['affectedIds'] = $aUpdated;
                    echo json_encode($result);
                    break;
                }

                // subcategory
                $parentCategory = $mCategory->findRootCategory($id);
                if (!$parentCategory['b_enabled']) {
                    $result = array('error' => __('Parent category is disabled, you can not enable that category'));
                    echo json_encode($result);
                    break;
                }

                $mCategory->update(array('b_enabled' => $enabled), array('pk_i_id' => $id));
                if ($enabled) {
                    $result = array(
                        'ok' => __('The subcategory has been enabled')
                    );
                } else {
                    $result = array(
                        'ok' => __('The subcategory has been disabled')
                    );
                }
                $result['affectedIds'] = array(array('id' => $id));
                echo json_encode($result);

                break;
            case 'delete_category':
                osc_csrf_check();
                $id    = Params::getParam('id');
                $error = 0;

                $categoryManager = Category::newInstance();
                $res             = $categoryManager->deleteByPrimaryKey($id);

                if ($res > 0) {
                    $message = __('The categories have been deleted');
                } else {
                    $error   = 1;
                    $message = __('An error occurred while deleting');
                }

                if ($error) {
                    $result = array('error' => $message);
                } else {
                    $result = array('ok' => $message);
                }
                echo json_encode($result);

                break;
            case 'edit_category_post':
                osc_csrf_check();
                $id                             = Params::getParam('id');
                $fields['i_expiration_days']    =
                    (Params::getParam('i_expiration_days') != '') ? Params::getParam('i_expiration_days') : 0;
                $fields['b_price_enabled']      = (Params::getParam('b_price_enabled') != '') ? 1 : 0;
                $apply_changes_to_subcategories =
                    Params::getParam('apply_changes_to_subcategories') == 1 ? true : false;

                $error         = 0;
                $has_one_title = 0;
                $postParams    = Params::getParamsAsArray();
                foreach ($postParams as $k => $v) {
                    if (preg_match('|(.+?)#(.+)|', $k, $m)) {
                        if ($m[2] === 's_name') {
                            if ($v != '') {
                                $has_one_title                    = 1;
                                $aFieldsDescription[$m[1]][$m[2]] = $v;
                                $s_text                           = $v;
                            } else {
                                $aFieldsDescription[$m[1]][$m[2]] = null;
                                $error                            = 1;
                            }
                        } else {
                            $aFieldsDescription[$m[1]][$m[2]] = $v;
                        }
                    }
                }

                $l = osc_language();
                if ($error == 0 || ($error == 1 && $has_one_title == 1)) {
                    $categoryManager = Category::newInstance();
                    $res             = $categoryManager->updateByPrimaryKey(array(
                        'fields'             => $fields,
                        'aFieldsDescription' => $aFieldsDescription
                    ), $id);
                    $categoryManager->updateExpiration(
                        $id,
                        $fields['i_expiration_days'],
                        $apply_changes_to_subcategories
                    );
                    $categoryManager->updatePriceEnabled(
                        $id,
                        $fields['b_price_enabled'],
                        $apply_changes_to_subcategories
                    );
                    if (is_bool($res)) {
                        $error = 2;
                    }
                }

                osc_run_hook('edited_category', (int)($id), $error);

                if ($error == 0) {
                    $msg = __('Category updated correctly');
                } elseif ($error == 1) {
                    if ($has_one_title == 1) {
                        $error = 4;
                        $msg   = __('Category updated correctly, but some titles are empty');
                    } else {
                        $msg = __('Sorry, including at least a title is mandatory');
                    }
                } elseif ($error == 2) {
                    $msg = __('An error occurred while updating');
                }
                echo json_encode(array('error' => $error, 'msg' => $msg, 'text' => $aFieldsDescription[$l]['s_name']));

                break;
            case 'custom': // Execute via AJAX custom file
                if (Params::existParam('route')) {
                    $routes = Rewrite::newInstance()->getRoutes();
                    $rid    = Params::getParam('route');
                    $file   = $routes[$rid]['file'] ?? '../';
                } else {
                    $file = Params::getParam('ajaxfile');
                }

                if ($file == '') {
                    echo json_encode(array('error' => 'no action defined'));
                    break;
                }

                // valid file?
                if (strpos($file, '../') !== false || strpos($file, '..\\') !== false) {
                    echo json_encode(array('error' => 'no valid file'));
                    break;
                }

                if (!file_exists(osc_plugins_path() . $file)) {
                    echo json_encode(array('error' => "file doesn't exist"));
                    break;
                }

                require_once osc_plugins_path() . $file;
                break;
            case 'test_mail':
                $title = sprintf(__('Test email, %s'), osc_page_title());
                $body  = __('Test email') . '<br><br>' . osc_page_title();

                $emailParams = array(
                    'subject'  => $title,
                    'to'       => osc_contact_email(),
                    'to_name'  => 'admin',
                    'body'     => $body,
                    'alt_body' => $body
                );

                $array = array();
                if (osc_sendMail($emailParams)) {
                    $array = array('status' => '1', 'html' => __('Email sent successfully'));
                } else {
                    $array = array('status' => '0', 'html' => __('An error occurred while sending email'));
                }
                echo json_encode($array);
                break;
            case 'test_mail_template':
                // replace por valores por defecto
                $email = Params::getParam('email');
                $title = Params::getParam('title');
                $body  = Params::getParam('body', false, false);

                $emailParams = array(
                    'subject'  => $title,
                    'to'       => $email,
                    'to_name'  => 'admin',
                    'body'     => $body,
                    'alt_body' => $body
                );

                $array = array();
                if (osc_sendMail($emailParams)) {
                    $array = array('status' => '1', 'html' => __('Email sent successfully'));
                } else {
                    $array = array('status' => '0', 'html' => __('An error occurred while sending email'));
                }
                echo json_encode($array);
                break;
            case 'order_pages':
                osc_csrf_check();
                $order = Params::getParam('order');
                $id    = Params::getParam('id');
                if ($order != '' && $id != '') {
                    $mPages       = Page::newInstance();
                    $actual_page  = $mPages->findByPrimaryKey($id);
                    $actual_order = $actual_page['i_order'];

                    $array     = array();
                    $condition = array();
                    $new_order = $actual_order;

                    if ($order === 'up') {
                        $page = $mPages->findPrevPage($actual_order);
                    } elseif ($order === 'down') {
                        $page = $mPages->findNextPage($actual_order);
                    }
                    if (isset($page['i_order'])) {
                        $mPages->update(array('i_order' => $page['i_order']), array('pk_i_id' => $id));
                        $mPages->update(array('i_order' => $actual_order), array('pk_i_id' => $page['pk_i_id']));
                    }
                }
                break;
            case 'check_version':
                try {
                    $package_json = Osclass::getPackageInfo(true);
                } catch (Exception $e) {
                        echo json_encode(array('error' => 1, 'msg' => $e->getMessage()));
                }
                if (isset($package_json) && is_array($package_json)) {
                    $upgradeOsclass    = new Osclass($package_json);
                    $upgrade_available = $upgradeOsclass->isUpgradable();
                    if ($upgrade_available) {
                        osc_set_preference('update_core_available', $upgradeOsclass->isUpgradable());
                        echo json_encode(array('error' => 0, 'msg' => __('Update available')));
                    } else {
                        osc_set_preference('update_core_available', '');
                        echo json_encode(array('error' => 0, 'msg' => __('No update available')));
                    }
                    osc_set_preference('update_core_json', json_encode($package_json));
                    osc_set_preference('last_version_check', time());
                } else {
                    echo json_encode(array('error' => 1, 'msg' => __('Network error')));
                }

                break;
            case 'check_languages':
                $total = _osc_check_languages_update();
                echo json_encode(array('msg' => __('Checked updates'), 'total' => $total));
                break;
            case 'check_themes':
                $total = _osc_check_themes_update();
                echo json_encode(array('msg' => __('Checked updates'), 'total' => $total));
                break;
            case 'check_plugins':
                $total = _osc_check_plugins_update();
                echo json_encode(array('msg' => __('Checked updates'), 'total' => $total));
                break;

            /******************************
             ** COMPLETE UPGRADE PROCESS **
             ******************************/
            case 'upgrade': // AT THIS POINT WE KNOW IF THERE'S AN UPDATE OR NOT
                osc_csrf_check();
                if (defined('DEMO')) {
                    $msg    = __('This action cannot be done because it is a demo site');
                    $result = array('error' => 6, 'message' => $msg);
                    osc_add_flash_warning_message($msg, 'admin');
                } else {
                    $osclassUpgradeObj = new Osclass(Osclass::getPackageInfo());
                    $upgradeOsclass = new Upgrade($osclassUpgradeObj);
                    try {
                        $upgradeOsclass->doUpgrade();
                        $db_upgrade_result = json_decode($osclassUpgradeObj::upgradeDB(Params::getParam('skipdb')), true);
                        $result            = ['error' => $db_upgrade_result['error'], 'message' => $db_upgrade_result['message']];
                    } catch (Exception $e) {
                        $result = ['error' => 1, 'message' => $e->getMessage()];
                        osc_add_flash_error_message($e->getMessage(), 'admin');
                    }
                    if (isset($db_upgrade_result) && $db_upgrade_result['error'] == 1) {
                        $result = ['error' => 5, 'message' => $db_upgrade_result['message']];
                        osc_add_flash_warning_message(__('Error occurred while upgrading osclass Database.'), 'admin');
                    }
                }
                echo json_encode($result);
                break;
            case 'reinstall_osclass': // We are forcing an update
                osc_csrf_check();
                if (defined('DEMO')) {
                    $msg    = __('This action cannot be done because it is a demo site');
                    $result = array('error' => 6, 'message' => $msg);
                    osc_add_flash_warning_message($msg, 'admin');
                } else {
                    $osclassUpgradeObj = new Osclass(Osclass::getPackageInfo(), true);
                    $upgradeOsclass = new Upgrade($osclassUpgradeObj);
                    try {
                        $upgradeOsclass->doUpgrade();
                        $db_upgrade_result = json_decode($osclassUpgradeObj::upgradeDB(), true);
                        $result            = ['error' => 0, 'message' => __('Osclass upgraded successfully.')];
                    } catch (Exception $e) {
                        $result = ['error' => 1, 'message' => $e->getMessage()];
                        osc_add_flash_error_message($e->getMessage(), 'admin');
                    }
                    if (isset($db_upgrade_result) && $db_upgrade_result['status'] !== true) {
                        $result = ['error' => 5, 'message' => $db_upgrade_result['message']];
                        osc_add_flash_warning_message(__('Error occurred while upgrading osclass Database.'), 'admin');
                    }
                }
                echo json_encode($result);
                break;
            case 'upgrade_db':
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true));
                }
                $this->ajax     = true;
                $upgrade_result = Osclass::upgradeDB(Params::getParam('skipdb'));
                echo $upgrade_result;
                break;
            case 'location_stats':
                osc_csrf_check();
                $workToDo = osc_update_location_stats();
                if ($workToDo > 0) {
                    $array['status']  = 'more';
                    $array['pending'] = $workToDo;
                } else {
                    $array['status'] = 'done';
                }
                echo json_encode($array);
                break;
            case 'country_slug':
                $exists = Country::newInstance()->findBySlug(Params::getParam('slug'));
                if (isset($exists['s_slug'])) {
                    echo json_encode(array('error' => 1, 'country' => $exists));
                } else {
                    echo json_encode(array('error' => 0));
                }
                break;
            case 'region_slug':
                $exists = Region::newInstance()->findBySlug(Params::getParam('slug'));
                if (isset($exists['s_slug'])) {
                    echo json_encode(array('error' => 1, 'region' => $exists));
                } else {
                    echo json_encode(array('error' => 0));
                }
                break;
            case 'city_slug':
                $exists = City::newInstance()->findBySlug(Params::getParam('slug'));
                if (isset($exists['s_slug'])) {
                    echo json_encode(array('error' => 1, 'city' => $exists));
                } else {
                    echo json_encode(array('error' => 0));
                }
                break;
            case 'error_permissions':
                echo json_encode(array('error' => __("You don't have the necessary permissions")));
                break;
            default:
                echo json_encode(array('error' => __('no action defined')));
                break;
        }
        // clear all keep variables into session
        Session::newInstance()->_dropKeepForm();
        Session::newInstance()->_clearVariables();
    }

    //hopefully generic...

    /**
     * @param $file
     *
     * @return void
     */
    public function doView($file)
    {
        osc_current_admin_theme_path($file);
    }
}
