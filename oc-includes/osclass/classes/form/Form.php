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

/**
 * Class Form
 */
class Form
{
    /**
     * @param $name
     * @param $items
     * @param $fld_key
     * @param $fld_name
     * @param $default_item
     * @param $id
     */
    protected static function generic_select($name, $items, $fld_key, $fld_name, $default_item, $id)
    {
        $name = osc_esc_html($name);
        echo '<select name="' . $name . '" id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name)
            . '">';
        if (isset($default_item)) {
            echo '<option value="">' . $default_item . '</option>';
        }
        foreach ($items as $i) {
            if (isset($fld_key) && isset($fld_name)) {
                echo '<option value="' . osc_esc_html($i[$fld_key]) . '"' . (($id == $i[$fld_key])
                        ? ' selected="selected"' : '') . '>' . $i[$fld_name] . '</option>';
            }
        }
        echo '</select>';
    }

    /**
     * @param      $name
     * @param      $value
     * @param null $maxLength
     * @param bool $readOnly
     * @param bool $autocomplete
     */
    protected static function generic_input_text(
        $name,
        $value,
        $maxLength = null,
        $readOnly = false,
        $autocomplete = true
    ) {
        $name = osc_esc_html($name);
        echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" type="text" name="'
            . $name . '" value="' . osc_esc_html(htmlentities($value, ENT_COMPAT, 'UTF-8')) . '"';
        if (isset($maxLength)) {
            echo ' maxlength="' . osc_esc_html($maxLength) . '"';
        }
        if (!$autocomplete) {
            echo ' autocomplete="off"';
        }
        if ($readOnly) {
            echo ' disabled="disabled" readonly="readonly"';
        }
        echo ' />';
    }

    /**
     * @param      $name
     * @param      $value
     * @param null $maxLength
     * @param bool $readOnly
     */
    protected static function generic_password($name, $value, $maxLength = null, $readOnly = false)
    {
        $name = osc_esc_html($name);
        echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name)
            . '" type="password" name="' . $name . '" value="' . osc_esc_html(htmlentities(
                $value,
                ENT_COMPAT,
                'UTF-8'
            )) . '"';
        if (isset($maxLength)) {
            echo ' maxlength="' . osc_esc_html($maxLength) . '"';
        }
        if ($readOnly) {
            echo ' disabled="disabled" readonly="readonly"';
        }
        echo ' autocomplete="off" />';
    }

    /**
     * @param $name
     * @param $value
     */
    protected static function generic_input_hidden($name, $value)
    {
        $name = osc_esc_html($name);
        echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name)
            . '" type="hidden" name="' . $name . '" value="' . osc_esc_html(htmlentities(
                $value,
                ENT_COMPAT,
                'UTF-8'
            )) . '" />';
    }

    /**
     * @param      $name
     * @param      $value
     * @param bool $checked
     */
    protected static function generic_input_checkbox($name, $value, $checked = false)
    {
        $name = osc_esc_html($name);
        echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name)
            . '" type="checkbox" name="' . $name . '" value="' . osc_esc_html(htmlentities(
                $value,
                ENT_COMPAT,
                'UTF-8'
            )) . '"';
        if ($checked) {
            echo ' checked="checked"';
        }
        echo ' />';
    }

    /**
     * @param $name
     * @param $value
     */
    protected static function generic_textarea($name, $value)
    {
        $name = osc_esc_html($name);
        echo '<textarea id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" name="' . $name
            . '" rows="10">' . $value . '</textarea>';
    }
}
