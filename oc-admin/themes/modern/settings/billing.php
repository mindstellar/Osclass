<?php if (!defined('OC_ADMIN')) {
    exit('Direct access is not allowed.');
}
/*
 * This file is part of Shopclass (Mindstellar).
 * Copyright (c) 2021-2026 Mindstellar Community
 *
 * Distributed under the GNU General Public License v3.0 or later. See LICENSE.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Billing settings'),
    'help'    => __('Switch on the billing section, and see which payment plugins are installed. '
                    . 'Shopclass keeps the record of what was bought; a payment plugin handles the money.'),
));

$billingEnabled = (bool)__get('billing_enabled');
$gateways       = __get('gateways');
?>
<?php osc_current_admin_theme_path('parts/header.php'); ?>
<div id="general-settings">
    <?php osc_admin_form_open(array(
'page'   => 'settings',
'action' => 'billing_post',
    )); ?>
        <?php osc_admin_page_head(__('Billing')); ?>

        <p class="form-intro">
            <?php _e('Turn this on to sell things on your site — featured listings, posting credits, '
                     . 'or whatever a payment plugin offers. Leave it off and nothing changes: posting '
                     . 'stays free and unlimited, and the Billing menu stays hidden.'); ?>
        </p>

        <?php osc_admin_form_row_open(__('Selling on this site')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_enabled',
                'name'    => 'billing_enabled',
                'label'   => __('Enable billing'),
                'checked' => $billingEnabled,
                'help'    => __('Switching this off later hides the Billing menu but keeps every order '
                                . 'and balance exactly as it is. Nothing is deleted.'),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

                <?php osc_admin_form_close(array(
                    array('label' => __('Save settings'), 'type' => 'submit'),
                )); ?>

    <?php osc_admin_form_open(array(
'page'   => 'settings',
'action' => 'billing_pricing_post',
    )); ?>
        <?php osc_admin_page_head(__('Pricing')); ?>

        <p class="form-intro">
            <?php _e('What posting costs once the free quota runs out, and what a featured '
                     . 'listing costs. These apply whether a payment plugin is installed or an '
                     . 'admin grants credits by hand.'); ?>
        </p>

        <?php osc_admin_number(array(
            'name'  => 'billing_free_live_listings',
            'id'    => 'billing_free_live_listings',
            'label' => __('Free listings per seller'),
            'value' => (string) osc_billing_free_live_listings(),
            'min'   => 0,
            'help'  => __('How many of a seller\'s listings may be live '
                          . '(published, not yet expired) at once. 0 means '
                          . 'unlimited. A pending or admin-disabled listing '
                          . 'still counts -- only expiry or deletion frees a slot. '
                          . 'Lowering this never touches a listing a seller already '
                          . 'has -- it only changes whether their next post is allowed.'),
        )); ?>

        <?php osc_admin_form_row_open(__('Extra slots')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_slot_enabled',
                'name'    => 'billing_slot_enabled',
                'label'   => __('Sell extra listing slots'),
                'checked' => osc_billing_slot_enabled(),
                'help'    => __('Enabled with a price of 0 means every seller can raise their '
                                . 'own slot ceiling for free.'),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_slot_credits',
            'id'    => 'billing_slot_credits',
            'label' => __('Slot price in credits'),
            'value' => (string) osc_billing_slot_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_slot_quantity',
            'id'    => 'billing_slot_quantity',
            'label' => __('Slots per purchase'),
            'value' => (string) osc_billing_slot_quantity(),
            'min'   => 1,
        )); ?>

        <?php osc_admin_form_row_open(__('Featured listings')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_premium_enabled',
                'name'    => 'billing_premium_enabled',
                'label'   => __('Sell featured listings'),
                'checked' => osc_billing_premium_enabled(),
                'help'    => __('Enabled with a price of 0 means every seller can feature a listing for free.'),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_premium_credits',
            'id'    => 'billing_premium_credits',
            'label' => __('Featured listing price (credits)'),
            'value' => (string) osc_billing_premium_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_premium_days',
            'id'    => 'billing_premium_days',
            'label' => __('Featured listing duration (days)'),
            'value' => (string) osc_billing_premium_days(),
            'min'   => 1,
        )); ?>

        <?php osc_admin_text(array(
            'name'  => 'billing_currency',
            'id'    => 'billing_currency',
            'label' => __('Currency'),
            'value' => osc_billing_currency(),
            'width' => 'num',
            'attrs' => array('maxlength' => 3),
            'help'  => __('A 3-letter ISO 4217 code, e.g. USD, EUR.'),
        )); ?>

                <?php osc_admin_form_close(array(
                    array('label' => __('Save pricing'), 'type' => 'submit'),
                )); ?>

    <?php osc_admin_form_open(array(
'page'   => 'settings',
'action' => 'billing_offline_post',
    )); ?>
        <?php osc_admin_page_head(__('Bank transfer')); ?>

        <p class="form-intro">
            <?php _e('Core\'s built-in payment method — no card processor, no API keys. A buyer '
                     . 'sees these instructions at checkout and pays outside the site; you settle '
                     . 'the order by hand once the money arrives.'); ?>
        </p>

        <?php osc_admin_form_row_open(__('Availability')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_offline_enabled',
                'name'    => 'billing_offline_enabled',
                'label'   => __('Accept bank transfer / cash payment'),
                'checked' => osc_billing_offline_enabled(),
                'help'    => __('Stays hidden at checkout until instructions are written below — '
                                . 'there is nothing for a buyer to pay if they do not know where to send it.'),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_textarea(array(
            'id'    => 'billing_offline_instructions',
            'name'  => 'billing_offline_instructions',
            'label' => __('Payment instructions'),
            'value' => osc_billing_offline_instructions(),
            'rows'  => 5,
            'width' => 'key',
            'help'  => __('Bank details, or wherever a buyer sends the money. Shown to the '
                          . 'buyer exactly as written here.'),
        )); ?>

                <?php osc_admin_form_close(array(
                    array('label' => __('Save bank transfer settings'), 'type' => 'submit'),
                )); ?>

    <?php osc_admin_form_open(array(
'page'   => 'settings',
'action' => 'billing_upgrades_post',
    )); ?>
        <?php osc_admin_page_head(__('Upgrades')); ?>

        <p class="form-intro">
            <?php _e('Bump, highlight and urgent are optional item upgrades, each priced and switched '
                     . 'on separately. Every one ships off — turning it on with a price of 0 makes it '
                     . 'free to every seller, not the same as leaving it off.'); ?>
        </p>

        <?php osc_admin_form_row_open(__('Bump to top')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_bump_enabled',
                'name'    => 'billing_bump_enabled',
                'label'   => __('Sell bump to top'),
                'checked' => osc_billing_bump_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_bump_credits',
            'id'    => 'billing_bump_credits',
            'label' => __('Bump price (credits)'),
            'value' => (string) osc_billing_bump_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_bump_cooldown_hours',
            'id'    => 'billing_bump_cooldown_hours',
            'label' => __('Cooldown (hours)'),
            'value' => (string) osc_billing_bump_cooldown_hours(),
            'min'   => 1,
            'help'  => __('How long a listing must wait before it can be bumped again.'),
        )); ?>

        <?php osc_admin_form_row_open(__('Highlight')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_highlight_enabled',
                'name'    => 'billing_highlight_enabled',
                'label'   => __('Sell highlighting'),
                'checked' => osc_billing_highlight_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_highlight_credits',
            'id'    => 'billing_highlight_credits',
            'label' => __('Highlight price (credits)'),
            'value' => (string) osc_billing_highlight_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_highlight_days',
            'id'    => 'billing_highlight_days',
            'label' => __('Highlight duration (days)'),
            'value' => (string) osc_billing_highlight_days(),
            'min'   => 1,
        )); ?>

        <?php osc_admin_form_row_open(__('Urgent')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_urgent_enabled',
                'name'    => 'billing_urgent_enabled',
                'label'   => __('Sell marking a listing urgent'),
                'checked' => osc_billing_urgent_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_urgent_credits',
            'id'    => 'billing_urgent_credits',
            'label' => __('Urgent price (credits)'),
            'value' => (string) osc_billing_urgent_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_urgent_days',
            'id'    => 'billing_urgent_days',
            'label' => __('Urgent duration (days)'),
            'value' => (string) osc_billing_urgent_days(),
            'min'   => 1,
        )); ?>

                <?php osc_admin_form_close(array(
                    array('label' => __('Save upgrade settings'), 'type' => 'submit'),
                )); ?>

    <?php osc_admin_form_open(array(
'page'   => 'settings',
'action' => 'billing_limits_post',
    )); ?>
        <?php osc_admin_page_head(__('Seller limits')); ?>

        <p class="form-intro">
            <?php _e('Photo count, the posting wait, and listing runtime are global limits by '
                     . 'default. Turning one of these on lets a seller raise their own ceiling by '
                     . 'buying it — everyone else keeps the global limit unchanged.'); ?>
        </p>

        <?php osc_admin_form_row_open(__('Extra photos')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_photos_enabled',
                'name'    => 'billing_photos_enabled',
                'label'   => __('Sell a raised photo cap'),
                'checked' => osc_billing_photos_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_photos_credits',
            'id'    => 'billing_photos_credits',
            'label' => __('Price (credits)'),
            'value' => (string) osc_billing_photos_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_photos_quantity',
            'id'    => 'billing_photos_quantity',
            'label' => __('Photo cap'),
            'value' => (string) osc_billing_photos_quantity(),
            'min'   => 1,
            'help'  => __('Photos allowed per listing while the entitlement is held.'),
        )); ?>

        <?php osc_admin_form_row_open(__('Skip the posting wait')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_no_wait_enabled',
                'name'    => 'billing_no_wait_enabled',
                'label'   => __('Sell waiving the flood wait'),
                'checked' => osc_billing_no_wait_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_no_wait_credits',
            'id'    => 'billing_no_wait_credits',
            'label' => __('Price (credits)'),
            'value' => (string) osc_billing_no_wait_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_no_wait_days',
            'id'    => 'billing_no_wait_days',
            'label' => __('Duration (days)'),
            'value' => (string) osc_billing_no_wait_days(),
            'min'   => 1,
        )); ?>

        <?php osc_admin_form_row_open(__('Extra listing runtime')); ?>
            <?php osc_admin_checkbox(array(
                'id'      => 'billing_runtime_enabled',
                'name'    => 'billing_runtime_enabled',
                'label'   => __('Sell extra runtime beyond the category limit'),
                'checked' => osc_billing_runtime_enabled(),
            )); ?>
        <?php osc_admin_form_row_close(); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_runtime_credits',
            'id'    => 'billing_runtime_credits',
            'label' => __('Price (credits)'),
            'value' => (string) osc_billing_runtime_credits(),
            'min'   => 0,
        )); ?>

        <?php osc_admin_number(array(
            'name'  => 'billing_runtime_days',
            'id'    => 'billing_runtime_days',
            'label' => __('Extra days'),
            'value' => (string) osc_billing_runtime_days(),
            'min'   => 1,
            'help'  => __('Added on top of the category\'s own expiration ceiling.'),
        )); ?>

                <?php osc_admin_form_close(array(
                    array('label' => __('Save limits'), 'type' => 'submit'),
                )); ?>

    <?php osc_admin_page_head(__('Payment methods'));

    if (empty($gateways)) {
        osc_admin_empty(array(
            'icon'   => 'bi-credit-card',
            'title'  => __('No payment methods installed'),
            'text'   => __('Shopclass never handles card details itself. To take payments, install a '
                           . 'payment plugin — it adds itself here once activated.'),
            'action' => array(
                'label' => __('Browse plugins'),
                'url'   => osc_admin_base_url(true) . '?page=plugins',
                'icon'  => 'bi-plugin',
            ),
        ));
    } else { ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col"><?php _e('Payment method'); ?></th>
                    <th scope="col"><?php _e('Currencies'); ?></th>
                    <th scope="col"><?php _e('Status'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($gateways as $gateway) {
                    $ready = $gateway->isConfigured(); ?>
                    <tr>
                        <td>
                            <strong><?php echo osc_esc_html($gateway->getName()); ?></strong>
                            <div class="text-muted osc-mono"><?php echo osc_esc_html($gateway->getId()); ?></div>
                        </td>
                        <td><?php echo osc_esc_html(
                            implode(', ', $gateway->getSupportedCurrencies()) ?: __('None declared')
                        ); ?></td>
                        <td>
                            <?php osc_admin_status(
                                $ready ? 'active' : 'inactive',
                                $ready ? __('Ready') : __('Needs setting up')
                            ); ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
