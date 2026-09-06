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

use mindstellar\storage\ProviderPresets;

$prefs         = __get('prefs');
$providers     = __get('provider_presets');
$queueStats    = __get('queue_stats');
$betterS3Active = __get('better_s3_active');
$betterS3Configured = __get('better_s3_configured');

//customize Head
$storage_js = static function () use ($providers) {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            var presets = <?php echo ProviderPresets::toJson(); ?>;
            var providerSelect = document.getElementById('storage_provider');
            var endpointField = document.querySelector('[name="storage_s3_endpoint"]');
            var regionField = document.querySelector('[name="storage_s3_region"]');
            var pathStyleField = document.querySelector('[name="storage_s3_path_style"]');
            var publicUrlHint = document.getElementById('storage_public_url_hint');

            if (!providerSelect) {
                return;
            }

            providerSelect.addEventListener('change', function () {
                var preset = presets[providerSelect.value];
                if (!preset) {
                    return;
                }

                if (endpointField) {
                    endpointField.value = preset.endpoint;
                }
                if (regionField) {
                    regionField.value = preset.region;
                    // readOnly (not disabled): a disabled field is never submitted, so a
                    // locked region (e.g. Cloudflare R2's "auto") would save blank. readOnly
                    // keeps the value fixed in the UI while still POSTing it.
                    regionField.readOnly = !!preset.region_locked;
                }
                if (pathStyleField) {
                    pathStyleField.checked = !!preset.path_style;
                }
                if (publicUrlHint) {
                    publicUrlHint.textContent = preset.public_url_hint;
                }
            });

            // Reflect the saved provider's locked state on load without clobbering the
            // saved connection values (only a provider change rewrites the fields).
            var current = presets[providerSelect.value];
            if (current && regionField) {
                regionField.readOnly = !!current.region_locked;
                if (publicUrlHint && current.public_url_hint) {
                    publicUrlHint.textContent = current.public_url_hint;
                }
            }
        });
    </script>
    <?php
};

osc_add_hook('admin_footer', $storage_js, 10);

osc_admin_page(array(
    'section' => __('Settings'),
    'title'   => __('Storage Settings'),
    'help'    => __('Configure S3-compatible object storage for listing images. When enabled, uploads are moved to your '
                    . 'bucket by the background storage queue; the local disk stays the default until you switch it on.'),
));

osc_current_admin_theme_path('parts/header.php'); ?>
    <div id="storage-settings">
        <?php osc_admin_page_head(__('Storage Settings')); ?>

        <?php if ($betterS3Active) { ?>
            <div class="flashmessage flashmessage-error">
                <?php _e('The <strong>Better S3</strong> plugin is currently active. Disable it before turning on '
                         . 'core S3 storage below, otherwise both will rewrite resource URLs and uploads will break.'); ?>
            </div>
        <?php } ?>

        <?php osc_admin_form_open(array(
    'name'   => 'storage_form',
    'page'   => 'settings',
    'action' => 'storage_post',
)); ?>
            <?php
            osc_admin_select(array(
                'name'     => 'storage_active',
                'label'    => __('Active storage'),
                'selected' => $prefs['storage_active'] === 's3' ? 's3' : 'local',
                'options'  => array(
                    'local' => __('Local disk'),
                    's3'    => __('Amazon S3-compatible'),
                ),
            ));

            $providerOptions = array();
            foreach ($providers as $id => $preset) {
                $providerOptions[$id] = $preset['label'];
            }
            osc_admin_select(array(
                'id'       => 'storage_provider',
                'name'     => 'storage_s3_provider',
                'label'    => __('Provider'),
                'selected' => $prefs['storage_s3_provider'],
                'options'  => $providerOptions,
                'help'     => __('Prefills the connection fields below with a starting point for the selected provider. '
                                 . 'Review every field before saving.'),
            ));
            osc_admin_text(array(
                'name'  => 'storage_s3_bucket',
                'label' => __('Bucket'),
                'value' => $prefs['storage_s3_bucket'],
            ));
            osc_admin_text(array(
                'name'  => 'storage_s3_region',
                'label' => __('Region'),
                'value' => $prefs['storage_s3_region'],
            ));
            osc_admin_field(array(
                'type'  => 'url',
                'name'  => 'storage_s3_endpoint',
                'label' => __('Endpoint'),
                'value' => $prefs['storage_s3_endpoint'],
                'width' => 'key',
                'help'  => __('Leave the provider-specific placeholders (e.g. {region}, {account_id}) filled in '
                              . 'with your own values.'),
            ));
            osc_admin_text(array(
                'name'  => 'storage_s3_access_key',
                'label' => __('Access key'),
                'value' => $prefs['storage_s3_access_key'],
                'width' => 'key',
            ));
            // Never rendered back into the page; blank means "keep the saved one",
            // which is what the controller already does with an empty value.
            osc_admin_secret(array(
                'name'        => 'storage_s3_secret_key',
                'label'       => __('Secret key'),
                'value'       => '',
                'reveal'      => true,
                'placeholder' => __('Leave blank to keep the currently saved secret key'),
                'attrs'       => array('autocomplete' => 'new-password'),
            ));
            osc_admin_field(array(
                'type'      => 'checkbox',
                'row_label' => __('Path-style URLs'),
                'id'        => 'storage_s3_path_style',
                'name'      => 'storage_s3_path_style',
                'label'     => __('Use path-style bucket URLs.'),
                'checked'   => $prefs['storage_s3_path_style'],
                'help'      => __('Required by MinIO and most self-hosted setups; leave off for AWS.'),
            ));
            osc_admin_field(array(
                'type'      => 'url',
                'name'      => 'storage_s3_public_url',
                'label'     => __('Public URL'),
                'value'     => $prefs['storage_s3_public_url'],
                'width'     => 'key',
                'help_html' => '<span id="storage_public_url_hint">'
                    . osc_esc_html(__('Optional. Overrides the URL used to serve files, e.g. a CDN domain in front of the bucket.'))
                    . '</span>',
            ));
            osc_admin_field(array(
                'type'      => 'checkbox',
                'row_label' => __('Signed URLs'),
                'id'        => 'storage_s3_signed_urls',
                'name'      => 'storage_s3_signed_urls',
                'label'     => __('Serve files through time-limited signed URLs.'),
                'checked'   => $prefs['storage_s3_signed_urls'],
                'help'      => __('Use this for a private bucket. Leave off for a public bucket or CDN.'),
            ));
            osc_admin_number(array(
                'name'   => 'storage_s3_signed_ttl',
                'label'  => __('Signed URL TTL'),
                'value'  => $prefs['storage_s3_signed_ttl'],
                'min'    => 60,
                'max'    => 604800,
                'suffix' => __('seconds'),
                'help'   => __('How long a signed URL stays valid (60-604800).'),
            ));
            osc_admin_select(array(
                'name'     => 'storage_keep_local',
                'label'    => __('Local copies'),
                'selected' => $prefs['storage_keep_local'] === 'none' ? 'none' : 'all',
                'options'  => array(
                    'all'  => __('Keep local copies'),
                    'none' => __('Delete after upload'),
                ),
            )); ?>
            <div class="clear"></div>
                    <?php osc_admin_form_close(array()); ?>

        <div class="form-horizontal">
            <?php osc_admin_page_head(__('Connection test')); ?>
            <?php osc_admin_form_row_open(''); ?>
                    <p><?php _e('Runs a small write/read/delete probe against the saved connection settings above.'); ?></p>
                    <?php
                    osc_admin_form_open(array(
                        'name'       => 'storage_test_form',
                        'page'   => 'settings',
                        'action'     => 'storage_test_post',
                        'horizontal' => false,
                    ));
                    osc_admin_action_button(array(
                        'label'   => __('Test connection'),
                        'type'    => 'submit',
                        'variant' => 'dim',
                    ));
                    osc_admin_form_close(null, array('horizontal' => false)); ?>
            <?php osc_admin_form_row_close(); ?>

            <?php osc_admin_page_head(__('Storage queue')); ?>
            <?php osc_admin_form_row_open(''); ?>
                    <p>
                        <?php echo sprintf(
                            osc_esc_html(__('Pending jobs: %d &middot; Failed jobs: %d')),
                            (int) $queueStats['pending'],
                            (int) $queueStats['error']
                        ); ?>
                    </p>
                    <?php
                    osc_admin_form_open(array(
                        'name'       => 'storage_queue_form',
                        'page'   => 'settings',
                        'action'     => 'storage_queue_run',
                        'horizontal' => false,
                    ));
                    osc_admin_action_button(array(
                        'label'   => __('Process queue now'),
                        'type'    => 'submit',
                        'variant' => 'dim',
                    ));
                    osc_admin_form_close(null, array('horizontal' => false)); ?>
                    <?php if (!empty($queueStats['dead_letters'])) { ?>
                        <div class="help-box">
                            <p><?php _e('Dead-lettered jobs (past the retry ceiling):'); ?></p>
                            <ul>
                                <?php foreach ($queueStats['dead_letters'] as $job) { ?>
                                    <li>
                                        #<?php echo osc_esc_html($job['pk_i_id']); ?>
                                        &mdash; <?php echo osc_esc_html($job['s_type']); ?>
                                        (<?php echo osc_esc_html($job['s_last_error']); ?>)
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
            <?php osc_admin_form_row_close(); ?>

            <?php osc_admin_page_head(__('Migration')); ?>
            <?php osc_admin_form_row_open(''); ?>
                    <p><?php _e('Backfill existing images between local disk and remote storage. Each action queues '
                                 . 'jobs processed by the storage queue above (or by cron) rather than running immediately.'); ?></p>

                    <?php
                    osc_admin_form_open(array(
                        'name'       => 'storage_offload_all_form',
                        'page'   => 'settings',
                        'action'     => 'storage_migrate_post',
                        'fields'     => array('op' => 'offload_all'),
                        'horizontal' => false,
                    ));
                    osc_admin_action_button(array(
                        'label'   => __('Offload all local images to remote storage'),
                        'variant' => 'dim',
                        'attrs'   => array('data-osc-dialog-open' => '#storage-offload-dialog'),
                    ));
                    osc_admin_form_close(null, array('horizontal' => false)); ?>
                    <div class="help-box">
                        <?php _e('Backfills every image still on local disk to the active remote storage backend. '
                                 . 'Existing images are queued for upload; new uploads are already handled automatically.'); ?>
                    </div>

                    <?php
                    osc_admin_form_open(array(
                        'name'       => 'storage_restore_all_form',
                        'page'   => 'settings',
                        'action'     => 'storage_migrate_post',
                        'fields'     => array('op' => 'restore_all'),
                        'horizontal' => false,
                    ));
                    osc_admin_action_button(array(
                        'label'   => __('Download all remote images back to local (offline copy)'),
                        'variant' => 'dim',
                        'attrs'   => array('data-osc-dialog-open' => '#storage-restore-dialog'),
                    ));
                    osc_admin_form_close(null, array('horizontal' => false)); ?>
                    <div class="help-box">
                        <?php _e('Brings every remote image back to local disk and switches it back to local storage. '
                                 . 'Use this to keep a local copy, or before disabling remote storage.'); ?>
                    </div>

                    <?php if ($betterS3Configured) { ?>
                        <?php
                        osc_admin_form_open(array(
                            'name'       => 'storage_adopt_better_s3_form',
                            'page'   => 'settings',
                            'action'     => 'storage_migrate_post',
                            'fields'     => array('op' => 'adopt_better_s3'),
                            'horizontal' => false,
                        ));
                        osc_admin_action_button(array(
                            'label'   => __('Adopt existing Better S3 images'),
                            'variant' => 'dim',
                            'attrs'   => array('data-osc-dialog-open' => '#storage-adopt-dialog'),
                        ));
                        osc_admin_form_close(null, array('horizontal' => false)); ?>
                        <div class="help-box">
                            <?php _e('Imports your Better S3 connection settings and marks images already uploaded to that '
                         . 'bucket as remote, without re-uploading them.'); ?>
                        </div>
                    <?php } ?>
            <?php osc_admin_form_row_close(); ?>
        </div>
    </div>

<?php
osc_admin_confirm_dialog(array(
    'id'      => 'storage-offload-dialog',
    'tone'    => 'plain',
    'fields'  => array('page' => 'settings', 'action' => 'storage_migrate_post', 'op' => 'offload_all'),
    'title'   => __('Queue every local image for upload?'),
    'text'    => __('Each image still on local disk is queued for upload to the active remote backend. '
                    . 'The jobs run through the storage queue rather than immediately, and a local file is '
                    . 'only dropped once its upload has succeeded.'),
    'confirm' => __('Queue uploads'),
));
osc_admin_confirm_dialog(array(
    'id'      => 'storage-restore-dialog',
    'tone'    => 'plain',
    'fields'  => array('page' => 'settings', 'action' => 'storage_migrate_post', 'op' => 'restore_all'),
    'title'   => __('Download every remote image back to local disk?'),
    'text'    => __('Each remote image is queued for download and switched back to local storage. '
                    . 'Check this server has room for them first.'),
    'confirm' => __('Queue downloads'),
));
osc_admin_confirm_dialog(array(
    'id'      => 'storage-adopt-dialog',
    'tone'    => 'plain',
    'fields'  => array('page' => 'settings', 'action' => 'storage_migrate_post', 'op' => 'adopt_better_s3'),
    'title'   => __('Import Better S3 settings?'),
    'text'    => __('Your Better S3 configuration is copied into these settings and images already in that '
                    . 'bucket are adopted as remote. Nothing in the bucket is moved or deleted.'),
    'confirm' => __('Import settings'),
));
?>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>
