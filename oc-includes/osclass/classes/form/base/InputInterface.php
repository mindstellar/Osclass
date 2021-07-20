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
 * Date: 17-07-2021
 * Time: 15:31
 * License is provided in root directory.
 */

namespace mindstellar\form\base;


/**
 * Class BaseInputs
 * Generate Basic Form Inputs
 *
 * @package mindstellar\form
 */
interface InputInterface
{
    /**
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     */
    public function text(string $name, $value, array $attributes = [], array $options = []);

    /**
     * TextArea
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     */
    public function textarea(string $name, $value, array $attributes = [], array $options = []);

    /**
     * Checkbox
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     *
     * @return string
     */
    public function checkbox(string $name, $value, array $attributes = [], array $options = [])
    : string;

    /**
     * Select
     *
     * @param string       $name
     * @param array|string $values
     * @param array        $attributes
     * @param array        $options
     */
    public function select(string $name, $values, array $attributes = [], array $options = []);

    /**
     * Password
     *
     * @param string $name
     * @param string $value
     * @param array  $attributes
     * @param array  $options
     */
    public function password(string $name, string $value, array $attributes = [], array $options = []);

    /**
     * radio
     *
     * @param string       $name
     * @param array|string $values
     * @param array        $attributes
     * @param array        $options
     */
    public function radio(string $name, $values, array $attributes = [], array $options = []);

    /**
     * hidden
     *
     * @param string $name
     * @param        $value
     * @param array  $attributes
     * @param array  $options
     */
    public function hidden(string $name, $value, array $attributes = [], array $options = []);

    /**
     * submit
     *
     * @param string $name
     * @param array  $attributes
     * @param array  $options
     */
    public function submit(string $name, array $attributes = [], array $options = []);
}