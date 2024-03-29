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
 * Class LanguageForm
 */
class LanguageForm extends Form
{

    /**
     * @param $locale
     */
    public static function primary_input_hidden($locale)
    {
        parent::generic_input_hidden('pk_c_code', $locale['pk_c_code']);
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function name_input_text($locale = null)
    {
        parent::generic_input_text('s_name', isset($locale) ? $locale['s_name'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function short_name_input_text($locale = null)
    {
        parent::generic_input_text('s_short_name', isset($locale) ? $locale['s_short_name'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function description_input_text($locale = null)
    {
        parent::generic_input_text('s_description', isset($locale) ? $locale['s_description'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function currency_format_input_text($locale = null)
    {
        parent::generic_input_text(
            's_currency_format',
            isset($locale) ? $locale['s_currency_format'] : ''
        );

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function dec_point_input_text($locale = null)
    {
        parent::generic_input_text('s_dec_point', isset($locale) ? $locale['s_dec_point'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function num_dec_input_text($locale = null)
    {
        parent::generic_input_text('i_num_dec', isset($locale) ? $locale['i_num_dec'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function thousands_sep_input_text($locale = null)
    {
        parent::generic_input_text(
            's_thousands_sep',
            isset($locale) ? $locale['s_thousands_sep'] : ''
        );

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function date_format_input_text($locale = null)
    {
        parent::generic_input_text('s_date_format', isset($locale) ? $locale['s_date_format'] : '');

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function description_textarea($locale = null)
    {
        parent::generic_textarea('s_stop_words', $locale['s_stop_words']);

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function enabled_input_checkbox($locale = null)
    {
        parent::generic_input_checkbox('b_enabled', '1', $locale['b_enabled'] == 1);

        return true;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    public static function enabled_bo_input_checkbox($locale = null)
    {
        parent::generic_input_checkbox('b_enabled_bo', '1', $locale['b_enabled_bo'] == 1);

        return true;
    }

    public static function text_direction_select($aLocale = null)
    {
        $options['selectOptions'] = 'ltr,rtl';
        $attributes['id'] = 's_direction';
        $value = $aLocale['s_direction'];

        echo (new Form)->select('s_direction', $value, $attributes, $options );
    }

    /**
     * @param bool $admin
     */
    public static function js_validation($admin = false)
    {
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                // Code for form validation
                $("form[name=language_form]").validate({
                    rules: {
                        s_name: {
                            required: true,
                            minlength: 1
                        },
                        s_short_name: {
                            required: true,
                            minlength: 1
                        },
                        s_description: {
                            required: true,
                            minlength: 1
                        },
                        s_currency_format: {
                            required: true,
                            minlength: 1
                        },
                        i_num_dec: {
                            required: true,
                            digits: true
                        },
                        s_dec_point: {
                            required: true,
                            minlength: 1
                        },
                        s_thousand_sep: {
                            required: true,
                            minlength: 1
                        },
                        s_date_format: {
                            required: true,
                            minlength: 1
                        }
                    },
                    messages: {
                        s_name: {
                            required: "<?php _e('Name: this field is required'); ?>.",
                            minlength: "<?php _e('Name: this field is required'); ?>."
                        },
                        s_short_name: {
                            required: "<?php _e('Short name: this field is required'); ?>.",
                            minlength: "<?php _e('Short name: this field is required'); ?>."
                        },
                        s_description: {
                            required: "<?php _e('Description: this field is required'); ?>.",
                            minlength: "<?php _e('Description: this field is required'); ?>."
                        },
                        s_currency_format: {
                            required: "<?php _e('Currency format: this field is required'); ?>.",
                            minlength: "<?php _e('Currency format: this field is required'); ?>."
                        },
                        i_num_dec: {
                            required: "<?php _e('Number of decimals: this field is required'); ?>.",
                            digits: "<?php _e('Number of decimals: this field must only contain numeric characters'); ?>."
                        },
                        s_dec_point: {
                            required: "<?php _e('Decimal point: this field is required'); ?>.",
                            minlength: "<?php _e('Decimal point: this field is required'); ?>."
                        },
                        s_thousand_sep: {
                            required: "<?php _e('Thousands separator: this field is required'); ?>.",
                            minlength: "<?php _e('Thousands separator: this field is required'); ?>."
                        },
                        s_date_format: {
                            required: "<?php _e('Date format: this field is required'); ?>.",
                            minlength: "<?php _e('Date format: this field is required'); ?>."
                        }
                    },
                    wrapper: "li",
                    errorLabelContainer: "#error_list",
                    invalidHandler: function (form, validator) {
                        $('html,body').animate({scrollTop: $('h1').offset().top}, {
                            duration: 250,
                            easing: 'swing'
                        });
                    },
                    submitHandler: function (form) {
                        $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
                        form.submit();
                    }
                });
            });
        </script>
        <?php
    }
}
