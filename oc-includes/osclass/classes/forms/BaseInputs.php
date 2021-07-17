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
class BaseInputs
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
     * BaseInputs constructor.
     *
     * @param \mindstellar\utility\Escape   $escape
     * @param \mindstellar\utility\Sanitize $sanitize
     */
    public function __construct(Escape $escape, Sanitize $sanitize)
    {
        $this->escape   = $escape;
        $this->sanitize = $sanitize;
    }

    /**
     * @param string $name
     * @param string,int,float $value
     * @param array  $attributes
     *
     * @return string
     */
    public function text(string $name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'text';

        return $this->generateInput($name, $value, $attributes);
    }

    /**
     * Common method for generating all inputs type
     *
     * @param string $name
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return string
     */
    private function generateInput(string $name, $values = null, array $attributes = [])
    : string {
        // remove defaultValue from $attributes and save it
        $defaultInputValue = null;
        if (isset($attributes['defaultValue'])) {
            $defaultInputValue = $attributes['defaultValue'];
            unset($attributes['defaultValue']);
        }

        // remove Select Placeholder from $attributes and save it for later
        if (isset($attributes['selectPlaceholder'])) {
            $selectPlaceholder = $attributes['selectPlaceholder'];
            unset($attributes['selectPlaceholder']);
        }
        $attributes = $this->handleAttributes($attributes);

        // Sanitize input values if needed

        if ($values !== null) {
            $values = $this->sanitizeInputValues($values, $attributes['sanitize']);
        }
        // $attributes to String
        $attributesString = $this->attributesToString($attributes);

        $input = '';
        // Generate input HTML with given $attributes['type']
        switch ($attributes['type']) {
            // Generate boostrap5 input with type=radio or type=checkbox
            case 'radio':
            case 'checkbox':
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
                    $input .= sprintf('<label class="form-check-label" for="%s">', $name . $i);
                    $input .= $label;
                    $input .= '</label>';
                    $input .= '</div>';
                }
                break;
            // Generate bootstrap5 input with type=select
            case 'select':
                $input .= sprintf('<select name="%s"%s>', $name, $attributesString);
                // Add selectPlaceholder option or create a new placeholder if not set
                if (isset($selectPlaceholder)) {
                    $input .= sprintf('<option value="%s">%s</option>', $selectPlaceholder, $selectPlaceholder);
                } else {
                    $input .= sprintf('<option value="">%s</option>', 'Select Option');
                }
                // Add options
                foreach ($values as $label => $value) {
                    $selected = $defaultInputValue !== null && $value === $defaultInputValue ? ' selected' : '';
                    $input    .= sprintf('<option value="%s"%s>%s</option>', $value, $selected, $label);
                }
                $input .= '</select>';
                break;
            // Generate input with type=textarea
            case 'textarea':
                $input .= sprintf('<textarea %s>%s</textarea>', $attributesString, $values);
                break;
            case 'submit':
                $input .= sprintf('<button%s>%s</button>', $attributes, $values);
                break;
            // Generate default input
            default:
                $input .= sprintf('<input%s value="%s">', $attributesString, $values);
                break;
        }

        return $input;
    }

    /**
     * Handler for given input attributes array
     *
     * @param array $attributes
     *
     * @return array
     */
    private function handleAttributes(array $attributes)
    : array {
        // set default input css class
        $inputClass = 'form-control';
        // check if attributes['type'] is set than add set default class based on it's type
        if (isset($attributes['type'])) {
            switch ($attributes['type']) {
                case 'radio':
                case 'checkbox':
                    $inputClass = 'form-check-input';
                    break;
                case 'select':
                    $inputClass = 'form-select';
                    break;
                case 'submit':
                    $inputClass = 'btn btn-primary';
            }
        }
        // default input attributes array
        $defaultAttributes = [
            'class'      => $inputClass, // default input css class
            'type'       => 'text', // default input type
            'sanitize'   => 'string', // default sanitize method for values is string, Check /mindstellar/utility/Sanitize for more details
            'escapeHTML' => true, // default escapeHTML is true
        ];

        // merge default attributes with given attributes if key doesn't exist and return
        return array_merge($defaultAttributes, $attributes);
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
        if (is_array($values) && !empty($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = $this->sanitize->$sanitizeType($value);
            }
        } else {
            $values = $this->sanitize->$sanitizeType($values);
        }

        return $values;
    }

    /**
     * Generate attributes string from given attributes array
     *
     * @param array $attributes
     *
     * @return string
     */
    private function attributesToString(array $attributes)
    : string {
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
     * @param array  $attributes
     *
     * @return string
     */
    public function textarea(string $name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'textarea';

        return sprintf('<textarea name="%s"%s>%s</textarea>', $name, $attributes, $value);
    }

    /**
     * Generate Checkbox Input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $attributes
     *
     * @return string
     */
    public function checkbox(string $name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'checkbox';

        return $this->generateInput($name, $value, $attributes);
    }

    /**
     * Generate Select Input
     *
     * @param string $name
     * @param array  $values
     * @param array  $attributes
     *
     * @return string
     */
    public function select(string $name, array $values, array $attributes = [])
    : string {
        $attributes['type'] = 'select';

        return $this->generateInput($name, $values, $attributes);
    }

    /**
     * Generate Password Input
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     *
     * @return string
     */
    public function password(string $name, string $value, array $attributes = [])
    : string {
        $attributes['type'] = 'password';

        return $this->generateInput($name, $value, $attributes);
    }

    /**
     * Generate radio input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $attributes
     *
     * @return string
     */
    public function radio(string $name, array $values, array $attributes = [])
    : string {
        $attributes['type'] = 'radio';

        return $this->generateInput($name, $values, $attributes);
    }

    /**
     * Generate submit button
     *
     * @param string $label
     * @param array  $attributes
     *
     * @return string
     */
    public function submit(string $label, array $attributes = [])
    : string {
        return $this->generateInput($label, null, $attributes);
    }

    /**
     * Generate hidden input
     *
     * @param string $name
     * @param string,int,float $value
     * @param array  $attributes
     *
     * @return string
     */
    public function hidden(string $name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'hidden';

        return $this->generateInput($name, $value, $attributes);
    }
}
