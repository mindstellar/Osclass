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
 * Time: 13:56
 * License is provided in root directory.
 */

namespace mindstellar\form\base;

use Exception;
use mindstellar\utility\Escape;
use mindstellar\utility\Sanitize;

/**
 * Class BaseInputs
 * Generate Basic Form Inputs
 *
 * @package mindstellar\form
 */
class FormInputs implements InputInterface
{
    /**
     * @var \mindstellar\utility\Escape
     */
    private $escape;

    /**
     * @var \mindstellar\utility\Sanitize
     */
    private $sanitize;

    protected $textClass = 'form-control';
    protected $selectClass = 'form-select';
    protected $passwordClass = 'form-control';
    protected $checkboxClass = 'form-check-input';
    protected $radioClass = 'form-check-input';
    protected $radioContainerClass = 'form-check';
    protected $checkboxContainerClass = 'form-check';
    protected $textareaClass = 'form-control';
    protected $submitClass = 'btn btn-primary';
    protected $fileClass = 'form-control';
    protected $labelClass = 'form-label';
    /**
     * Sanitize method available in /mindstellar/utility/Sanitize class
     *
     * @var string
     */
    //protected $sanitizeType = 'string';
    //protected $escapeHtml = true;
    //protected $divClass;
    /**
     * Default common options
     *
     * @var array
     */
    protected $options = [
        'sanitize'   => 'string',
        // default sanitize method for values is string, Check /mindstellar/utility/Sanitize for more details
        'escapeHTML' => true,
        // default escapeHTML is true
    ];

    /**
     * FormInputs constructor.
     *
     * @param \mindstellar\utility\Escape   $escape
     * @param \mindstellar\utility\Sanitize $sanitize
     */
    public function __construct(Escape $escape = null, Sanitize $sanitize = null)
    {
        if ($escape === null) {
            $this->escape = new Escape();
        } else {
            $this->escape = $escape;
        }
        if ($sanitize === null) {
            $this->sanitize = new Sanitize();
        } else {
            $this->sanitize = $sanitize;
        }
    }

    /**
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function text(string $name, $value, array $attributes = [], array $options = [])
    : string
    {
        if (!isset($attributes['type'])) {
            $attributes['type'] = 'text';
        }
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->textClass;
        }

        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Common method for generating all inputs type
     *
     * @param string $name
     * @param null   $values
     * @param array  $attributes                    input tag attributes
     * @param array  $options                       This contains flag for input
     *                                              Supported flag in $options array :
     *                                              'defaultValue' : default value for input
     *                                              'selectPlaceholder' : placeholder for select input
     *                                              'label' : label for input
     *                                              'divClass' : css class for input div container, default not set
     *                                              'escapeHtml' : escape html for input attributes, default is true
     *                                              'sanitize'   : sanitize method for input value, default is 'string'
     *                                              'optGroupLevel' :int $optgroupLevel -1 = no optgroup, 0 = first level, 1 = second
     *                                              level, etc. for select box
     *                                              'optGroupKey': SubOption key in array
     *                                              Throw exception if $name is not set
     *
     * @return string
     * @throws \Exception
     */
    private function generateInput(string $name, $values = null, array $attributes = [], array $options = [])
    : string
    {
        if (!isset($name)) {
            throw new Exception('Input Name is not set');
        }
        $this->handleOptions($options);

        // $attributes to String
        $attributesString = $this->attributesToString($attributes);

        $input = isset($options['divClass']) ? '<div class="' . $options['divClass'] . '">' : '';
        //Add label if $options['label'] is set
        if (isset($options['label'])) {
            $input .= $this->label($options['label'], $name);
        }

        // Sanitize input values if needed
        if ($values !== null) {
            $values = $this->sanitizeInputValues($values, $options['sanitize']);
        }

        // Generate input HTML with given $options['type']
        switch ($attributes['type']) {
            // Generate input with type=radio or type=checkbox
            case 'radio':
                $i = 0;
                if (is_string($values)) {
                    $values = explode(',', $values);
                    //rename $values array key to value
                    foreach ($values as $k => $v) {
                        $values[$v] = $v;
                        unset($values[$k]);
                    }
                }
                foreach ($values as $v => $l) {
                    $checked = isset($options['defaultValue']) && $v == $options['defaultValue'] ? ' checked' : '';
                    $i++;
                    $input .= sprintf('<div%s>', $attributesString);
                    $input .= sprintf(
                        '<input class="%s" type="%s" name="%s" id="%s" value="%s"%s>',
                        $attributes['type'] === 'radio' ? $this->radioClass : $this->checkboxClass,
                        $attributes['type'],
                        $name,
                        $name . $i,
                        $v,
                        $checked
                    );
                    // if $options['noCheckboxLabel'] is set, don't add label after checkbox]
                    if (!isset($options['noCheckboxLabel'])) {
                        //Add label if $label is set or use $name as $label
                        if (!isset($l)) {
                            $label = $name;
                        }
                        $input .= $this->label($l, $name . $i, 'form-check-label');
                    }
                    $input .= '</div>';
                }
                break;
            case 'checkbox':
                $i = 0;
                if (is_string($values)) {
                    $values = explode(',', $values);
                    //rename $values array key to value
                    foreach ($values as $k => $v) {
                        $values[$v] = $v;
                        unset($values[$k]);
                    }
                }
                foreach ($values as $v => $l) {
                    if ((isset($options['defaultValue']) && $v === $options['defaultValue']) ||(isset($options['checkboxChecked']) &&
                                                                                                $options['checkboxChecked'])) {
                        $checked = ' checked';
                    } else {
                        $checked = '';
                    }
                    $i++;
                    $input .= sprintf('<div%s>', $attributesString);
                    $input .= sprintf(
                        '<input class="%s" type="%s" name="%s" id="%s" value="%s"%s>',
                        $this->checkboxClass,
                        $attributes['type'],
                        $name,
                        $name . $i,
                        $v,
                        $checked
                    );
                    // if $options['noCheckboxLabel'] is set, don't add label after checkbox]
                    if (!isset($options['noCheckboxLabel'])) {
                        //Add label if $label is set or use $name as $label
                        if (!isset($l)) {
                            $label = $name;
                        }
                        $input .= $this->label($l, $name . $i, 'form-check-label');
                    }
                    $input .= '</div>';
                }
                break;
            // Generate input with type=select
            case 'select':
                $input .= sprintf('<select name="%s"%s>', $name, $attributesString);
                // Add selectPlaceholder option or create a new placeholder if not set
                if (isset($options['selectPlaceholder']) && $options['selectPlaceholder']) {
                    $input .= sprintf('<option value="">%s</option>', $options['selectPlaceholder']);
                } elseif ($options['selectPlaceholder'] !== false) {
                    $input .= sprintf('<option value="">%s</option>', 'Select Option');
                }

                $input .= $this->getOptionsString($values, $options);
                $input .= '</select>';
                unset($defaultValue, $optGroupLevel);
                break;
            // Generate input with type=textarea
            case 'textarea':
                $input .= sprintf('<textarea name="%s"%s>%s</textarea>', $name, $attributesString, $values);
                break;
            // Generate input with type=file
            case 'file':
                $input .= sprintf('<input name="%s"%s>', $name, $attributesString);
                break;
            // Generate input with type=submit
            case 'submit':
                $input .= sprintf('<input type="submit"  value="%s"%s>', $values, $attributesString);
                break;
            // Generate default input
            default:
                $input .= sprintf('<input name="%s" %s value="%s">', $name, $attributesString, $values);
                break;
        }
        if (isset($options['customHtml'])) {
            $input .= $this->addHtml($options['customHtml']);
        }
        $input .= isset($options['divClass']) ? '</div>' : '';

        unset($attributesString, $defaultInputValue, $label, $selectPlaceholder);

        return $input;
    }

    /**
     * Private function for handling select options
     *
     * @param array|string $selectOptions
     * @param              $options ['optgroupLevel'] -1 = no optgroup, 0 = first level, 1 = second level, etc
     */
    private function getOptionsString($selectOptions, $options)
    : string
    {
        // get defaultValue, optGroupLevel options if set or set default
        $defaultValue  = $options['defaultValue'] ?? '';
        $optGroupLevel = $options['optGroupLevel'] ?? -1;

        // if $selectOptions is a csv string, Convert csv options to array
        if (is_string($selectOptions)) {
            $selectOptions = explode(',', $selectOptions);
        }
        $selectOptionsString = '';
        // $selectOptions is an array, loop through it
        if (is_array($selectOptions)) {
            foreach ($selectOptions as $k => $v) {
                // Check if this array is in multilevel format i.e. option and children are set
                if (isset($v['option'])) {
                    $optionValue = $v['option']['value']??'';
                    $optionLabel = $v['option']['label']??'';
                    // if $optgroupLevel is set, add optgroup
                    if ($optGroupLevel === 0) {
                        $selectOptionsString .= sprintf('<optgroup label="%s">', $optionLabel);
                    } else {
                        $selected = isset($defaultValue) && $defaultValue === $optionValue ? ' selected' : '';
                        $selectOptionsString .= sprintf('<option value="%s"%s>%s</option>', $optionValue, $selected, $optionLabel) . PHP_EOL;
                        unset($selected);
                    }
                    if (isset($v['children'])) {
                        $selectOptionsString .= $this->getOptionsString($v['children'], $optGroupLevel - 1) . PHP_EOL;
                    }
                    // if $optgroupLevel is set, add optgroup
                    if ($optGroupLevel === 0) {
                        $selectOptionsString .= '</optgroup>';
                    }
                } else {
                    $optionValue = $k;
                    $optionLabel = $v;
                    // check if default value is set and if it matches the current value
                    $selected = isset($defaultValue) && $defaultValue === $optionValue ? ' selected' : '';
                    $selectOptionsString .= sprintf('<option value="%s"%s>%s</option>', $optionValue, $selected, $optionLabel) . PHP_EOL;
                    unset($selected);
                }
            }
        }

        return $selectOptionsString;
    }

    /**
     * Handler for given input option array
     *
     * @param array $options
     * @param array $attributes
     *
     */
    private function handleOptions(array &$options)
    {
        // Overwrite default options with given options
        $options = array_merge($this->options, $options);
    }

    /**
     * Generate attributes string from given attributes array
     *
     * @param array $attributes
     *
     * @return string
     */
    private function attributesToString(array $attributes)
    : string
    {
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            // escape html special chars if escapeHtml is true
            if ($value === true) {
                $value = $this->escape::html($value);
            }
            $attributesString .= sprintf(' %s="%s"', $key, $value);
        }

        return $attributesString;
    }

    /**
     * Common method for generating label
     *
     * @param string $label
     * @param string $for
     * @param string $class
     *
     * @return string
     */
    private function label(string $label, string $for, string $class = null)
    : string
    {
        if ($class === null) {
            $class = $this->labelClass;
        }

        return '<label class="' . $class . '" for="' . $for . '">' . $this->escape::html($label) . '</label>';
    }

    /**
     * Sanitize input values
     *
     * @param mixed       $values
     * @param string|null $sanitizeType Sanitize method name, See /mindstellar/utility/Sanitize.php for supported types
     *
     * @return mixed
     */
    private function sanitizeInputValues($values, string $sanitizeType = null)
    {
        if ($sanitizeType === null) {
            return $values;
        }
        if (is_array($values)) {
            foreach ($values as $value) {
                $this->sanitizeInputValues($value);
            }
        } else {
            $values = $this->sanitize->$sanitizeType($values);
        }

        return $values;
    }

    /**
     * Common method for printing customHtml after input tag
     *
     * @param string $label
     * @param string $for
     * @param string $class
     *
     * @return string
     */
    private function addHtml(string $htmlContent)
    : string
    {
        return $this->escape::html($htmlContent);
    }

    /**
     * Generate Text Area Input
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function textarea(string $name, $value, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'textarea';

        if (isset($attributes['class'])) {
            $attributes['class'] = $this->textareaClass;
        }
        if (!isset($options['sanitize'])) {
            $options['sanitize'] = 'html';
        }
        if (!isset($attributes['row'])) {
            $attributes['rows'] = 10;
        }
        if (!isset($attributes['columns'])) {
            $attributes['columns'] = 5;
        }

        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Generate Checkbox Input
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function checkbox(string $name, $value, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'checkbox';
        // add css class if not set
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->checkboxContainerClass;
        }
        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Generate Select Input
     *
     * @param string       $name
     * @param array|string $values array or csv string
     *                             [['value' => 'label], ...]
     * @param array        $attributes
     * @param array        $options
     *
     * @return string
     * @throws \Exception
     */
    public function select(string $name, $values, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'select';
        // add class if not set
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->selectClass;
        }

        return $this->generateInput($name, $values, $attributes, $options);
    }

    /**
     * Generate Password Input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function password(string $name, string $value, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'password';
        // add class if not set
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->passwordClass;
        }

        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Generate radio input
     *
     * @param string $name
     * @param        $values
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function radio(string $name, $values, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'radio';
        // add css class if not set
        if (!isset($options['class'])) {
            $attributes['class'] = $this->radioContainerClass;
        }

        return $this->generateInput($name, $values, $attributes, $options);
    }

    /**
     * Generate hidden input
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function hidden(string $name, $value, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'hidden';

        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Generate submit input
     *
     * @param string $name
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function submit(string $name, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'submit';
        // add css class if not set
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->submitClass;
        }

        return $this->generateInput($name, $name, $attributes, $options);
    }

    /**
     * Generate file input
     *
     * @param string $name
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function file(string $name, array $attributes = [], array $options = [])
    : string
    {
        $attributes['type'] = 'file';
        if (!isset($attributes['class'])) {
            $attributes['class'] = $this->fileClass;
        }
        $options['escapeHTML'] = false;
        $options['sanitize']   = null;

        return $this->generateInput($name, null, $attributes, $options);
    }
}
