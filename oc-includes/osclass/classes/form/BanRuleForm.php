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
 * Class BanRuleForm
 */
class BanRuleForm extends Form
{

    /**
     * @param $rule
     */
    public static function primary_input_hidden($rule)
    {
        parent::generic_input_hidden('id', (isset($rule['pk_i_id']) ? $rule['pk_i_id'] : ''));
    }

    /**
     * @param null $rule
     */
    public static function name_text($rule = null)
    {
        parent::generic_input_text('s_name', isset($rule['s_name']) ? $rule['s_name'] : '');
    }

    /**
     * @param null $rule
     */
    public static function ip_text($rule = null)
    {
        parent::generic_input_text('s_ip', isset($rule['s_ip']) ? $rule['s_ip'] : '');
    }

    /**
     * @param null $rule
     */
    public static function email_text($rule = null)
    {
        parent::generic_input_text('s_email', isset($rule['s_email']) ? $rule['s_email'] : '');
    }
}
