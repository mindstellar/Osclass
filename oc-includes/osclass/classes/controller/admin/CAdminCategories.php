<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
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

/**
 * Class CAdminCategories
 */
class CAdminCategories extends AdminSecBaseModel
{
    //specific for this class
    private $categoryManager;

    public function __construct()
    {
        parent::__construct();

        //specific things for this class
        $this->categoryManager = Category::newInstance(osc_current_admin_locale());
        osc_run_hook('init_admin_categories');
    }

    //Business Layer...
    public function doModel()
    {
        parent::doModel();

        //specific things for this class
        switch ($this->action) {
            case ('add_post_default'): // add default category and reorder parent categories
                osc_csrf_check();
                $fields['fk_i_parent_id']    = null;
                $fields['i_expiration_days'] = 0;
                $fields['i_position']        = 0;
                $fields['b_enabled']         = 1;
                $fields['b_price_enabled']   = 1;

                $default_locale                                = osc_language();
                $aFieldsDescription[$default_locale]['s_name'] = 'NEW CATEGORY, EDIT ME!';

                $categoryId = $this->categoryManager->insert($fields, $aFieldsDescription);

                // reorder parent categories. NEW category first
                $rootCategories = $this->categoryManager->findRootCategories();
                foreach ($rootCategories as $cat) {
                    $order = $cat['i_position'];
                    $order++;
                    $this->categoryManager->updateOrder($cat['pk_i_id'], $order);
                }
                $this->categoryManager->updateOrder($categoryId, '0');

                osc_run_hook('add_category', (int)($categoryId));

                $this->redirectTo(osc_admin_base_url(true) . '?page=categories');
                break;
            default:                //
                $this->_exportVariableToView('categories', $this->categoryManager->toTreeAll());
                $this->doView('categories/index.php');
        }
    }

    //hopefully generic...

}

/* file end: ./oc-admin/CAdminCategories.php */
