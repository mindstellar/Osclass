<?php
/*
 *      Osclass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2014 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$category = (array)__get('category');
if (!isset($category['pk_i_id'])) {
    $category['pk_i_id'] = null;
}

?>
<div id="sidebar">
    <?php osc_alert_form(); ?>
    <div class="filters">
        <form action="<?php echo osc_base_url(true); ?>" method="get" class="nocsrf">
            <input type="hidden" name="page" value="search"/>
            <input type="hidden" name="sOrder" value="<?php echo osc_search_order(); ?>"/>
            <input name="iOrderType" type="hidden"
                   value="<?php $allowedTypesForSorting = Search::getAllowedTypesForSorting();
                    echo $allowedTypesForSorting[osc_search_order_type()]; ?>"/>
            <?php foreach (osc_search_user() as $userId) { ?>
                <input type="hidden" name="sUser[]" value="<?php echo $userId; ?>"/>
            <?php } ?>
            <fieldset class="first">
                <h3><?php _e('Your search', 'bender'); ?></h3>
                <div class="row">
                    <input class="input-text" id="query" name="sPattern" type="text"
                           value="<?php echo osc_esc_html(osc_search_pattern()); ?>"/>
                </div>
            </fieldset>
            <fieldset>
                <h3><?php _e('City', 'bender'); ?></h3>
                <div class="row">
                    <input class="input-text" id="sRegion" name="sRegion" type="hidden"
                           value="<?php echo osc_esc_html(Params::getParam('sRegion')); ?>"/>
                    <input class="input-text" id="sCity" name="sCity" type="text"
                           value="<?php echo osc_esc_html(osc_search_city()); ?>"/>
                </div>
            </fieldset>
            <?php if (osc_images_enabled_at_items()) { ?>
                <fieldset>
                    <h3><?php _e('Show only', 'bender'); ?></h3>
                    <div class="row">
                        <input id="withPicture" name="bPic" type="checkbox" value="1" <?php echo(osc_search_has_pic()
                            ? 'checked' : ''); ?> />
                        <label for="withPicture"><?php _e('listings with pictures', 'bender'); ?></label>
                    </div>
                </fieldset>
            <?php } ?>
            <?php if (osc_price_enabled_at_items()) { ?>
                <fieldset>
                    <div class="row price-slice">
                        <h3><?php _e('Price', 'bender'); ?></h3>
                        <span><?php _e('Min', 'bender'); ?>.</span>
                        <input class="input-text" id="priceMin" maxlength="6" name="sPriceMin"
                               size="6" type="text" value="<?php echo osc_esc_html(osc_search_price_min()); ?>"/>
                        <span><?php _e('Max', 'bender'); ?>.</span>
                        <input class="input-text" id="priceMax" maxlength="6" name="sPriceMax"
                               size="6" type="text" value="<?php echo osc_esc_html(osc_search_price_max()); ?>"/>
                    </div>
                </fieldset>
            <?php } ?>
            <div class="plugin-hooks">
                <?php
                if (osc_search_category_id()) {
                    osc_run_hook('search_form', osc_search_category_id());
                } else {
                    osc_run_hook('search_form');
                }
                ?>
            </div>
            <?php
            $aCategories = osc_search_category();
            foreach ($aCategories as $cat_id) { ?>
                <input type="hidden" name="sCategory[]" value="<?php echo osc_esc_html($cat_id); ?>"/>
            <?php } ?>
            <div class="actions">
                <button type="submit"><?php _e('Apply', 'bender'); ?></button>
            </div>
        </form>
        <fieldset>
            <div class="row ">
                <h3><?php _e('Refine category', 'bender'); ?></h3>
                <?php bender_sidebar_category_search($category['pk_i_id']); ?>
            </div>
        </fieldset>
    </div>
</div>
