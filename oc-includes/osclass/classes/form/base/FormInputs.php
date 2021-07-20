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
        $attributes['type'] = 'text';

        return $this->generateInput($name, $value, $attributes, $options);
    }

    /**
     * Common method for generating all inputs type
     *
     * @param string $name
     * @param null   $values
     * @param array  $attributes input tag attributes
     * @param array  $options    This contains flag for input
     *                           Supported flag in $options array :
     *                           'defaultValue' : default value for input
     *                           'selectPlaceholder' : placeholder for select input
     *                           'label' : label for input
     *                           'divClass' : css class for input div container, default not set
     *                           'escapeHtml' : escape html for input attributes, default is true
     *                           'sanitize'   : sanitize method for input value, default is 'string'
     *                           Throw exception if $name is not set
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

        $this->handleOptions($options, $attributes);

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
                    $checked = isset($options['defaultValue']) && $v === $options['defaultValue'] ? ' checked' : '';
                    $i++;
                    $input .= sprintf('<div%s>', $attributesString);
                    $input .= sprintf(
                        '<input class="form-check-input" type="%s" name="%s" id="%s" value="%s"%s>',
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
                    //Add label if $label is set or use $name as $label
                    $input .= '</div>';
                }
                break;
            // Generate input with type=select
            case 'select':

                $input .= sprintf('<select name="%s"%s>', $name, $attributesString);
                // Add selectPlaceholder option or create a new placeholder if not set
                if (isset($options['selectPlaceholder'])) {
                    $input .= sprintf('<option value="">%s</option>', $options['selectPlaceholder']);
                } else {
                    $input .= sprintf('<option value="">%s</option>', 'Select Option');
                }
                // if value is a string, Convert csv options to array and set value as label, make it a clousure
                if (is_string($values)) {
                    $values = explode(',', $values);
                    //rename $values array key to value
                    foreach ($values as $k => $v) {
                        $values[$v] = $v;
                        unset($values[$k]);
                    }
                }

                foreach ($values as $v => $l) {
                    $selected = isset($options['defaultValue']) && $v === $options['defaultValue'] ? ' selected' : '';
                    $input    .= sprintf('<option value="%s"%s>%s</option>', $v, $selected, $l);
                }

                $input .= '</select>';
                break;
            // Generate input with type=textarea
            case 'textarea':
                $input .= sprintf('<textarea %s>%s</textarea>', $attributesString, $values);
                break;
            // Generate input with type=file
            case 'file':
                $input .= sprintf('<input name="%s"%s>', $name, $attributesString);
                break;
            // Generate input with type=submit
            case 'submit':
                $input .= sprintf('<input type="submit" value="%s"%s>', $values, $attributesString);
                break;
            // Generate default input
            default:
                $input .= sprintf('<input%s value="%s">', $attributesString, $values);
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
     * Handler for given input option array
     *
     * @param array $options
     * @param array $attributes
     *
     */
    private function handleOptions(array &$options, array &$attributes)
    {
        // default input attributes array
        $attributes = array_merge([
                                      'class' => 'form-control', // default input css class
                                      'type'  => 'text', // default input type
                                  ], $attributes);
        $options    = array_merge([
                                      'sanitize'   => 'string',
                                      // default sanitize method for values is string, Check /mindstellar/utility/Sanitize for more details
                                      'escapeHTML' => true,
                                      // default escapeHTML is true
                                  ], $options);
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
    private function label(string $label, string $for, string $class = 'form-label')
    : string
    {
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
            if (!empty($values)) {
                foreach ($values as $key => $value) {
                    $values[$key] = $this->sanitize->$sanitizeType($value);
                }
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
            $attributes['class'] = 'form-check';
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
            $attributes['class'] = 'form-select';
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
            $attributes['class'] = 'form-check';
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
            $attributes['class'] = 'btn btn-primary';
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

        $options['escapeHTML'] = false;
        $options['sanitize']   = null;

        return $this->generateInput($name, null, $attributes, $options);
    }
}
