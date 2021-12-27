<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
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

function addHelp()
{
    echo '<p>' . __("Modify the options that allow your users to publish comments on your site's listings.") . '</p>';
}
osc_add_hook('help_box', 'addHelp');

function customPageHeader()
{
    ?>
    <h1><?php _e('Settings'); ?>
        <a class="ms-1 bi bi-question-circle-fill float-right" data-bs-target="#help-box" data-bs-toggle="collapse"
           href="#help-box"></a>
    </h1>
    <?php
}
osc_add_hook('admin_page_header', 'customPageHeader');

function customPageTitle($string)
{
    return sprintf(__('Cookies & GDPR Settings &raquo; %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>
<div id="general-settings">
    <form name="cookiesgdpr_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings"/>
        <input type="hidden" name="action" value="cookiesgdpr_post"/>
        <fieldset>
            <div class="form-horizontal">
                <h2 class="render-title"><?php _e('Cookie Settings'); ?></h2>
                <div class="form-row">
                    <div class="form-label"><?php _e('Enable consent'); ?></div>
                    <div class="form-controls">
                        <div class="form-label-checkbox">
                            <label>
                                <input type="checkbox" <?php echo(osc_cookie_consent_enabled() ? 'checked="checked"' : ''); ?>
                                       name="cookie_consent_enabled"
                                       value="1"/> <?php _e('Show cookie consent to users'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Non-mandatory cookies'); ?></div>
                    <div class="form-controls">
                        <div class="form-label-checkbox">
                            <label>
                                <input type="checkbox" <?php echo(osc_cookie_consent_nonmandatory() ? 'checked="checked"' : ''); ?>
                                       name="cookie_consent_nonmandatory"
                                       value="1"/> <?php _e('Allow users to opt-out from non-mandatory cookies (like Google Analytics)'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Cookie policy URL'); ?></div>
                    <div class="form-controls">
                        <input type="text" name="cookie_consent_url" list="staticpages" value="<?php echo osc_esc_html(osc_cookie_consent_url()); ?>"/>
                        <datalist id="staticpages">
                            <?php osc_reset_static_pages(); ?>
                            <?php while (osc_has_static_pages()) { ?>
                                <option value="<?php echo osc_static_page_url(); ?>" <?php echo $selected; ?>><?php echo osc_static_page_title();?></option>
                            <?php } ?>
                        </datalist>
                    </div>
                </div>

                <h2 class="render-title separate-top"><?php _e('GDPR Settings'); ?></h2>
                <div class="form-row">
                    <div class="form-label"><?php _e('Right to be forgotten') ?></div>
                    <div class="form-controls">
                        <div class="form-label-checkbox">
                            <label>
                                <input type="checkbox" <?php echo(osc_gdpr_delete_enabled() ? 'checked="checked"'
                                    : ''); ?> name="gdpr_delete_enabled"
                                       value="1"/> <?php _e('Show "Delete account" link in user menu'); ?>
                            </label>
                        </div>
                        <div class="help-box">
                            <?php _e('Some themes already show this link.'); ?>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Right to data portability') ?></div>
                    <div class="form-controls">
                        <div class="form-label-checkbox">
                            <label>
                                <input type="checkbox" <?php echo(osc_gdpr_download_enabled() ? 'checked="checked"'
                                    : ''); ?> name="gdpr_download_enabled"
                                       value="1"/> <?php _e('Show "Download data" link in user menu'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Checkboxes') ?></div>
                    <div class="form-controls">
                        <div class="form-label-checkbox">
                            <label>
                                <input type="checkbox" <?php echo(osc_gdpr_checkboxes_enabled() ? 'checked="checked"'
                                    : ''); ?> name="gdpr_checkboxes_enabled"
                                       value="1"/> <?php _e('Show checkboxes for "Terms and conditions" and "Privacy policy" to users'); ?>
                            </label>
                        </div>
                        <div class="help-box">
                            <?php _e("Once checked once, the checkboxes won't be shown anymore to the user."); ?>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Terms and conditions page'); ?></div>
                    <div class="form-controls">
                        <select class="form-select form-select-sm " name="gdpr_terms_page">
                            <option value="0" selected="selected"><?php _e('Select a page...'); ?></option>
                            <?php osc_reset_static_pages(); ?>
                            <?php while (osc_has_static_pages()) { ?>
                                <?php $selected = (osc_gdpr_terms_page() == osc_static_page_id()) ? 'selected' : ''; ?>
                                <option value="<?php echo osc_static_page_id(); ?>" <?php echo $selected; ?>><?php echo osc_static_page_title();?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-label"><?php _e('Privacy policy page'); ?></div>
                    <div class="form-controls">
                        <select class="form-select form-select-sm " name="gdpr_privacy_page">
                            <option value="0" selected="selected"><?php _e('Select a page...'); ?></option>
                            <?php osc_reset_static_pages(); ?>
                            <?php while (osc_has_static_pages()) { ?>
                                <?php $selected = (osc_gdpr_privacy_page() == osc_static_page_id()) ? 'selected' : ''; ?>
                                <option value="<?php echo osc_static_page_id(); ?>" <?php echo $selected; ?>><?php echo osc_static_page_title();?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>"
                           class="btn btn-submit"/>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
