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

namespace mindstellar\forms;

use mindstellar\utility\Escape;
use mindstellar\utility\Sanitize;

/**
 * Class BaseInputs
 * Generate Basic Form Inputs
 *
 * @package mindstellar\forms
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
     * @param string,int,float $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function text(string $name, $value, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'text');

        return $this->generateInput($name, $value, $options);
    }

    /**
     * Set input attributes and pass reference
     *
     * @param array  $options
     * @param string $key
     * @param mixed  $value
     *
     */
    private function setAttribute(array &$options, string $key, $value)
    {
        $options['attributes'][$key] = $value;
    }

    /**
     * Common method for generating all inputs type
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $options This contains flag and attributes for input
     *                        Supported flag in $options array :
     *                        'defaultValue' : default value for input
     *                        'selectPlaceholder' : placeholder for select input
     *                        'label' : label for input
     *                        'divClass' : css class for input div container, default not set
     *                        'escapeHtml' : escape html for input attributes, default is true
     *                        'sanitize'   : sanitize method for input value, default is 'string'
     *                        Throw exception if $name is not set
     *
     * @return string
     * @throws \Exception
     */
    private function generateInput(string $name, $values = null, array $options = [])
    : string {
        if (!isset($name)) {
            throw new \Exception('Input Name is not set');
        }
        // remove defaultValue from $options and save it
        $defaultInputValue = null;
        if (isset($options['defaultValue'])) {
            $defaultInputValue = $options['defaultValue'];
            unset($options['defaultValue']);
        }

        // remove Select Placeholder from $options and save it for later
        if (isset($options['selectPlaceholder'])) {
            $selectPlaceholder = $options['selectPlaceholder'];
            unset($options['selectPlaceholder']);
        }

        // remove divClass from $options and save it for later
        if (isset($options['divClass'])) {
            $divClass = $options['divClass'];
            unset($options['divClass']);
        }

        $options = $this->handleOptions($options);

        // $options['attributes'] to String
        $attributesString = $this->attributesToString($options['attributes']);

        if (isset($divClass)) {
            $divtag = '<div class="' . $divClass . '">';
        } else {
            $divtag = '';
        }
        $input = $divtag;
        // Generate input HTML with given $options['type']
        switch ($options['attributes']['type']) {
            // Generate input with type=radio or type=checkbox
            case 'radio':
            case 'checkbox':
                // Sanitize input values if needed
                if ($values !== null) {
                    $values = $this->sanitizeInputValues($values, $options['sanitize']);
                }
                $i = 0;
                foreach ($values as $label => $value) {
                    $checked = $defaultInputValue !== null && $value === $defaultInputValue ? ' checked' : '';
                    $i++;
                    $input .= sprintf('<div%s>', $attributesString);
                    $input .= sprintf(
                        '<input class="form-check-input" type="radio" name="%s" id="%s" value=%s"%s>',
                        $name,
                        $name . $i,
                        $value,
                        $checked
                    );
                    //Add label if $label is set or use $name as $label
                    if (!isset($label)) {
                        $label = $name;
                    }
                    $input .= $this->label($label, $name . $i, 'form-check-label');
                    $input .= '</div>';
                }
                break;
            // Generate input with type=select
            case 'select':
                // Sanitize input values if needed
                if ($values !== null) {
                    $values = $this->sanitizeInputValues($values, $options['sanitize']);
                }

                //Add label if $options['label'] is set
                if (isset($options['label'])) {
                    $input .= $this->label($options['label'], $name);
                }

                $input .= sprintf('<select name="%s"%s>', $name, $attributesString);
                // Add selectPlaceholder option or create a new placeholder if not set
                if (isset($selectPlaceholder)) {
                    $input .= sprintf('<option value="%s">%s</option>', $selectPlaceholder, $selectPlaceholder);
                } else {
                    $input .= sprintf('<option value="">%s</option>', 'Select Option');
                }

                // Check if $values is array or string, if string, convert csv to array
                if (is_array($values)) {
                    foreach ($values as $label => $value) {
                        $input .= sprintf('<option value="%s">%s</option>', $value, $label);
                    }
                } else {
                    $values = explode(',', $values);
                    foreach ($values as $value) {
                        $input .= sprintf('<option value="%s">%s</option>', $value, $value);
                    }
                }
                $input .= '</select>';
                break;
            // Generate input with type=textarea
            case 'textarea':
                // Sanitize input values if needed
                if ($values !== null) {
                    $values = $this->sanitizeInputValues($values, $options['sanitize']);
                }

                if (isset($options['label'])) {
                    $input .= $this->label($options['label'], $name);
                }

                $input .= sprintf('<textarea %s>%s</textarea>', $attributesString, $values);
                break;
            // Generate input with type=file
            case 'file':
                if (isset($options['label'])) {
                    $input .= $this->label($options['label'], $name);
                }

                $input .= sprintf('<input name="%s"%s>', $name, $attributesString);
                break;
            // Generate input with type=submit
            case 'submit':
                $input .= sprintf('<input type="submit" value="%s"%s>', $values, $attributesString);
                break;
            // Generate default input
            default:
                // Sanitize input values if needed
                if ($values !== null) {
                    $values = $this->sanitizeInputValues($values, $options['sanitize']);
                }

                if (isset($options['label'])) {
                    $input .= $this->label($options['label'], $name);
                }
                $input .= sprintf('<input%s value="%s">', $attributesString, $values);
                break;
        }
        if ($divtag) {
            $input .= '</div>';
        }
        unset($options, $attributesString, $defaultInputValue, $label, $selectPlaceholder);

        return $input;
    }

    /**
     * Handler for given input option array
     *
     * @param array $options
     *
     * @return array
     */
    private function handleOptions(array $options)
    : array {
        // default input attributes array
        $defaultAttributes = [
            'attributes' => [
                'class' => 'form-control', // default input css class
                'type'  => 'text', // default input type
            ],
            'sanitize'   => 'string', // default sanitize method for values is string, Check /mindstellar/utility/Sanitize for more details
            'escapeHTML' => true, // default escapeHTML is true
        ];

        // merge default attributes with given attributes if key doesn't exist and return
        return array_merge($defaultAttributes, $options);
    }

    /**
     * Generate attributes string from given attributes array
     *
     * @param array $options
     *
     * @return string
     */
    private function attributesToString(array $options)
    : string {
        $attributesString = '';
        foreach ($options as $key => $value) {
            // escape html special chars if escapeHtml is true
            if ($value === true) {
                $value = $this->escape::html($value);
            }
            $attributesString .= sprintf(' %s="%s"', $key, $value);
        }

        return $attributesString;
    }

    /**
     * Sanitize input values
     *
     * @param mixed  $values
     * @param string $sanitizeType Sanitize method name, See /mindstellar/utility/Sanitize.php for supported types
     *
     * @return mixed
     */
    private function sanitizeInputValues($values, string $sanitizeType)
    {
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
     * Common method for generating lables
     *
     * @param string $label
     * @param string $for
     * @param string $class
     *
     * @return string
     */
    private function label(string $label, string $for, string $class = 'form-label')
    : string {
        return '<label class="' . $class . '" for="' . $for . '">' . $this->escape::html($label) . '</label>';
    }

    /**
     * Generate Custom Input
     *
     * @param callable $callable Callback function to generate input
     * @param mixed    ...$args  Arguments to pass to callback function
     *
     * @return mixed
     */
    public function custom(callable $callable, ...$args)
    {
        // call callback function with given arguments
        return $callable(...$args);
    }

    /**
     * Generate Text Area Input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function textarea(string $name, $value, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'textarea');

        return $this->generateInput($name, $value, $options);
    }

    /**
     * Generate Checkbox Input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function checkbox(string $name, $value, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'checkbox');
        $options['type'] = 'checkbox';
        // add css class if not set
        if (!isset($options['attributes']['class'])) {
            $this->setAttribute($options, 'class', 'form-check-input');
        }

        return $this->generateInput($name, $value, $options);
    }

    /**
     * Generate Select Input
     *
     * @param string       $name
     * @param array|string $values array or csv string
     * @param array        $options
     *
     * @return string
     * @throws \Exception
     */
    public function select(string $name, $values, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'select');
        // add class if not set
        if (!isset($options['attributes']['class'])) {
            $this->setAttribute($options, 'class', 'form-select');
        }

        return $this->generateInput($name, $values, $options);
    }

    /**
     * Generate Password Input
     *
     * @param string $name
     * @param string $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function password(string $name, string $value, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'password');

        return $this->generateInput($name, $value, $options);
    }

    /**
     * Generate radio input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function radio(string $name, array $values, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'radio');
        // add css class if not set
        if (!isset($options['class'])) {
            $this->setAttribute($options, 'class', 'form-check-input');
        }

        return $this->generateInput($name, $values, $options);
    }

    /**
     * Generate hidden input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function hidden(string $name, $value, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'hidden');

        return $this->generateInput($name, $value, $options);
    }

    /**
     * Generate submit input
     *
     * @param string $name
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function submit(string $name, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'submit');
        // add css class if not set
        if (!isset($options['attributes']['class'])) {
            $this->setAttribute($options, 'class', 'btn btn-primary');
        }

        $options['escapeHTML'] = false;
        $options['sanitize']   = null;

        return $this->generateInput($name, $name, $options);
    }

    /**
     * Generate file input
     *
     * @param string $name
     * @param array  $options
     *
     * @return string
     * @throws \Exception
     */
    public function file(string $name, array $options = [])
    : string {
        $this->setAttribute($options, 'type', 'file');

        $options['escapeHTML'] = false;
        $options['sanitize']   = null;

        return $this->generateInput($name, null, $options);
    }
}
