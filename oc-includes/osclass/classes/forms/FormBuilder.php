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
 * Date: 12-07-2021
 * Time: 13:02
 * License is provided in root directory.
 */

namespace mindstellar\forms;


use mindstellar\Csrf;
use Plugins;

/**
 * Class Form
 *
 * @package mindstellar\forms
 */
class FormBuilder
{
    /**
     * @var \mindstellar\forms\formInputs
     */
    private $formInputs;

    /**
     * @var array $formSchema ;
     */
    private $formSchema;

    /**
     * @var \mindstellar\Csrf
     */
    private $csrf;

    /**
     * Form constructor.
     *
     * @param \mindstellar\forms\formInputs $input
     * @param array                         $formSchema
     *                                  $exampleFormSchema = [
     *                                  'csrf' => true // if csrf is required, default true
     *                                  'attributes' => [
     *                                  'class' => 'form-horizontal', // default
     *                                  'role' => 'form', // default
     *                                  'method' => 'post', // default
     *                                  'action' => '', // default
     *                                  'enctype' => 'multipart/form-data', // default
     *                                  'name' => 'form-name', //required from user
     *                                  ],
     *                                  'inputSchema' => [
     *                                  // array list of inputs with it's own options, You can use add method for generating schema
     *                                  // or provide it here
     *                                  [
     *                                  'type' => 'text',
     *                                  'name' => 'text-name',
     *                                  'label' => 'Text Label',
     *                                  'options' => [
     *                                  'attributes' => [
     *                                  'placeholder' => 'placeholder',
     *                                  ],
     *                                  ],
     *                                  ],
     *                                  ],
     *                                  'submitSchema' => [ // Provide submit button options , Optional
     *                                  'value' => 'submit-name', // required from user
     *                                  'attributes' => [ // array list of attributes for submit button]
     *                                  ]
     *                                  ]
     *
     *
     *
     * inputSchema is a array list of inputs with it's own properties
     * Please See mindstellar\forms\formInputs for more information and available inputs.
     */
    public function __construct(FormInputs $formInputs = null, array $formSchema = [])
    {
        if ($formInputs === null) {
            $this->formInputs = new FormInputs();
        } else {
            $this->formInputs = $formInputs;
        }
        $this->formInputs = $formInputs;
        $this->csrf       = Csrf::newInstance();

        $defaultSchema    = [
            'attributes'  => [
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'action'  => '',
                'class'   => 'form-horizontal',
                'role'    => 'form',
            ],
            'inputSchema' => [],
            'submit'      => 'Submit',
            'csrf'        => true
        ];
        $this->formSchema = array_merge($defaultSchema, $formSchema);
    }

    /**
     * AddForm metthod, it will generate basic formschema
     *
     * @param string $name        required
     * @param bool   $csrf        default true
     * @param array  $attributes  Optional
     * @param array  $inputSchema [optional] if already set then it will override the default inputSchema
     */
    public function addForm(string $name, bool $csrf = true, array $attributes = [], array $inputSchema = [])
    {
        $this->formSchema['attributes']['name'] = $name;
        $this->formSchema['csrf']               = $csrf;
        $this->formSchema['attributes']         = array_merge($this->formSchema['attributes'], $attributes);
        $this->formSchema['inputSchema']        = array_merge($this->formSchema['inputSchema'], $inputSchema);
    }

    /**
     * Run hook to inputSchema which will be called while rendering form
     *
     * @param string $hookName
     * @param array  $hookParams
     */
    public function runHook(string $hookName, ...$hookParams)
    {
        $this->formSchema['inputSchema'][] = [
            'name'   => $hookName,
            'type'   => 'runhook',
            'params' => $hookParams
        ];
    }

    /**
     * Add a new text input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     *
     */
    public function addTextInput(string $name, string $value = '', array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'text',
            'value'   => $value,
            'options' => $options
        ];
    }

    /**
     * Add a new password input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     */
    public function addPasswordInput(string $name, string $value = '', array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'password',
            'value'   => $value,
            'options' => $options
        ];
    }

    /**
     * Add a new textarea input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     */
    public function addTextareaInput(string $name, string $value = '', array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'textarea',
            'value'   => $value,
            'options' => $options
        ];
    }

    /**
     * Add a new select input
     *
     * @param string       $name
     * @param array|string $values
     * @param array        $attributes
     */
    public function addSelectInput(string $name, $values, array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'select',
            'value'   => $values,
            'options' => $options
        ];
    }

    /**
     * Add a new checkbox input
     *
     * @param string $name
     * @param array  $value
     * @param array  $attributes
     */
    public function addCheckboxInput(string $name, array $value = [], array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'checkbox',
            'value'   => $value,
            'options' => $options
        ];
    }


    /**
     * Add a new radio input
     *
     * @param string $name
     * @param array  $value
     * @param array  $attributes
     */
    public function addRadioInput(string $name, array $value = [], array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'radio',
            'value'   => $value,
            'options' => $options
        ];
    }

    /**
     * Add a new hidden input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     */
    public function addHiddenInput(string $name, string $value = '', array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'hidden',
            'value'   => $value,
            'options' => $options
        ];
    }

    /**
     * Add a new file input
     *
     * @param string $name
     * @param array  $attributes
     */
    public function addFileInput(string $name, array $options = [])
    {
        $this->formSchema['inputSchema'][] = [
            'name'    => $name,
            'type'    => 'file',
            'options' => $options
        ];
    }

    /**
     * Add a new custom input
     *
     * @param callable $callable
     * @param          ...$args
     */
    public function addCustomInput(callable $callable, ...$args)
    {
        $this->formSchema['inputSchema'][] = [
            'name'  => $callable,
            'type'  => 'custom',
            'value' => $args
        ];
    }

    /**
     * render form using $this-formSchema with given inputs and add crsf token
     * Throw exception if name attributes is not set
     *
     * @return string
     * @throws \Exception
     */
    public function renderForm()
    : string
    {
        if (!isset($this->formSchema['attributes']['name'])) {
            throw new \Exception('Form name attribute is not set');
        }
        $str = '<form' . $this->formAttributesString() . '>';
        $str .= $this->csrf->tokenForm();
        $str .= $this->renderInputs();
        $str .= $this->renderSubmit();
        $str .= '</form>';

        return $str;
    }

    /**
     * Form attributes string from given schema
     *
     * @return string
     */
    private function formAttributesString()
    : string
    {
        $str = '';
        // if form attributes are given
        if (!empty($this->formSchema['attributes'])) {
            foreach ($this->formSchema['attributes'] as $key => $value) {
                $str .= ' ' . $key . '="' . $value . '"';
            }
        }

        return $str;
    }

    /**
     * render form inputs from given schema
     *
     * @return string
     */
    private function renderInputs()
    : string
    {
        if (!empty($this->formSchema['inputSchema'])) {
            $str = '';
            foreach ($this->formSchema['inputSchema'] as $input) {
                switch ($input['type']) {
                    case 'custom':
                        // call $input['type] as FormInput method and pass $input['name'], $input['value'] as arguments
                        $str .= call_user_func([$this->formInputs, $input['type']], $input['name'], $input['value']);
                        break;
                    case 'runhook':
                        Plugins::runHook($input['name'], ...$input['params']);
                        break;
                    default:
                        // call $input['type] as FormInput method and pass $input['name'], $input['value'] and $input['options'] as arguments
                        $str .= call_user_func([$this->formInputs, $input['type']], $input['name'], $input['value'], $input['options']);
                        break;
                }
            }

            return $str;
        }

        return '';
    }

    /**
     * render submit button
     *
     * @return string
     * @throws \Exception
     */
    private function renderSubmit()
    : string
    {
        $options = [];
        $value   = 'Submit';

        if (isset($this->formSchema['submitSchema']['value'], $this->formSchema['submitSchema']['attributes'])
        ) {
            $value                 = $this->formSchema['submitSchema']['value'];
            $options['attributes'] = $this->formSchema['submitSchema']['attributes'];
        }

        return $this->formInputs->submit($value, $options);
    }
}
