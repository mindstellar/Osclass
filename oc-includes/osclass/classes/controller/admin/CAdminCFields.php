<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

/*
 *  Copyright 2020 Mindstellar Osclass
 *  Maintained and supported by Mindstellar Community
 *  https://github.com/mindstellar/Osclass
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Class CAdminCFields
 */
class CAdminCFields extends AdminSecBaseModel
{
    //specific for this class
    private $fieldManager;

    public function __construct()
    {
        parent::__construct();

        //specific things for this class
        $this->fieldManager = Field::newInstance();
        osc_run_hook('init_admin_fields');
    }

    //Business Layer...
    public function doModel()
    {
        parent::doModel();

        //specific things for this class
        switch ($this->action) {
            default:
                $categories = Category::newInstance()->toTreeAll();
                $selected   = array();
                foreach ($categories as $c) {
                    $selected[] = $c['pk_i_id'];
                    foreach ($c['categories'] as $cc) {
                        $selected[] = $cc['pk_i_id'];
                    }
                }
                $this->_exportVariableToView('categories', $categories);
                $this->_exportVariableToView('default_selected', $selected);
                $this->_exportVariableToView('fields', $this->fieldManager->listAll());
                $this->doView('fields/index.php');
                break;
        }
    }

    //hopefully generic...

}

/* file end: ./oc-admin/CAdminCFields.php */
