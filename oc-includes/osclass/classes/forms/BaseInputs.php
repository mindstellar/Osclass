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
     * BaseInputs constructor.
     *
     * @param \mindstellar\utility\Escape $escape
     */
    public function __construct(Escape $escape)
    {
        $this->escape = $escape;
    }

    /**
     * @param       $name
     * @param       $value
     * @param array $attributes
     *
     * @return string
     */
    public function text($name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'text';

        return $this->generic($name, $value, $attributes);
    }

    /**
     * Generate generic input with support for type argument
     *
     * @param       $name
     * @param       $values
     * @param array $attributes
     *
     * @return string
     */
    private function generic($name, $values, array $attributes = [])
    : string {
        $defaultInputValue = null;
        $attributesString  = '';
        // remove defaultValue from $attributes and save it
        if (isset($attributes['defaultValue'])) {
            $defaultInputValue = $attributes['defaultValue'];
            unset($attributes['defaultValue']);
        }

        // remove Select Placeholder from $attributes and save it for later
        if (isset($attributes['selectPlaceholder'])) {
            $selectPlaceholder = $attributes['selectPlaceholder'];
            unset($attributes['selectPlaceholder']);
        }

        if ($attributes !== null) {
            $attributesString = $this->handleAttributes($attributes);
        }

        $input = '';
        // Generate input HTML with given $attributes['type']
        switch ($attributes['type']) {
            case 'radio':
            case 'checkbox':
                $i = 0;
                foreach ($values as $label => $value) {
                    $checked = $defaultInputValue !== null && $value === $defaultInputValue ? ' checked' : '';
                    $i++;
                    $input .= sprintf('<div%s>', $attributesString);
                    $input .= sprintf('<input class="form-check-input" type="radio" name="%s" id="%s" value=%s"%s>', $name,
                                      $name . $i,
                                      $value, $checked);
                    $input .= sprintf('<label class="form-check-label" for="%s">', $name . $i);
                    $input .= $label;
                    $input .= '</label>';
                    $input .= '</div>';
                }
                break;
            case 'select':
                $input .= sprintf('<select name="%s" %s>', $name, $attributesString);
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
            case 'textarea':
                $input .= sprintf('<textarea %s>%s</textarea>', $attributesString, $values);
                break;
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
     * @return string
     */
    private function handleAttributes(array $attributes)
    : string {
        // check if attributes['type'] is set than add set default class based on it's type
        if (isset($attributes['type'])) {
            switch ($attributes['type']) {
                case 'radio':
                case 'checkbox':
                    $attributes['class'] = 'form-check-input';
                    break;
                case 'select':
                    $attributes['class'] = 'form-select';
                    break;
                default:
                    $attributes['class'] = 'form-control';
            }
        }
        // default input attributes array
        $defaultAttributes = [
            'class'      => 'form-control',
            'type'       => 'text',
            'id'         => null,
            'name'       => null,
            'escapeHtml' => true,
        ];
        // if given attributes has class array then make it space seprated string
        if (isset($attributes['class'])) {
            $attributes['class'] = implode(' ', $attributes['class']);
        }
        // merge default attributes with given attributes
        $attributes = array_merge($defaultAttributes, $attributes);


        return $this->attributesToString($attributes);
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
     * Generate Text Area Input
     *
     * @param      $name
     * @param      $value
     * @param null $attributes
     *
     * @return string
     */
    public function textarea($name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'textarea';

        return sprintf('<textarea name="%s"%s>%s</textarea>', $name, $attributes, $value);
    }

    /**
     * Generate Checkbox Input
     *
     * @param       $name
     * @param       $value
     * @param array $attributes
     *
     * @return string
     */
    public function checkbox($name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'checkbox';

        return $this->generic($name, $value, $attributes);
    }

    /**
     * Generate Select Input
     *
     * @param       $name
     * @param array $values
     * @param array $attributes
     *
     * @return string
     */
    public function select($name, array $values, array $attributes = [])
    : string {
        $attributes['type'] = 'select';

        return $this->generic($name, $values, $attributes);
    }

    /**
     * Generate Password Input
     *
     * @param       $name
     * @param       $value
     * @param array $attributes
     *
     * @return string
     */
    public function password($name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'password';

        return $this->generic($name, $value, $attributes);
    }

    /**
     * Generate radio input
     *
     * @param       $name
     * @param array $values
     * @param array $attributes
     *
     * @return string
     */
    public function radio($name, array $values, array $attributes = [])
    : string {
        $attributes['type'] = 'radio';

        return $this->generic($name, $values, $attributes);
    }

    /**
     * Generate submit button
     *
     * @param       $name
     * @param array $attributes
     *
     * @return string
     */
    public function submit($name, array $attributes = [])
    : string {
        $attributes['type'] = 'submit';
        if ($attributes !== null) {
            $this->handleAttributes($attributes);
        }

        return sprintf('<button%s>%s</button>', $attributes, $name);
    }

    /**
     * Generate hidden input
     *
     * @param       $name
     * @param       $value
     * @param array $attributes
     *
     * @return string
     */
    public function hidden($name, $value, array $attributes = [])
    : string {
        $attributes['type'] = 'hidden';

        return $this->generic($name, $value, $attributes);
    }

}