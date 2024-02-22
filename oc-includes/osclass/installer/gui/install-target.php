<?php if (!defined('ABS_PATH')) {
    exit('ABS_PATH is not loaded. Direct access is not allowed.');
}

$internet_error = false;
require_once LIB_PATH . 'osclass/helpers/hUtils.php';
$country_list = osc_file_get_contents(osc_get_locations_json_url());
$country_list = json_decode($country_list, false);
$country_list = $country_list->locations;

$country_ip = '';
if (preg_match(
    '|([a-z]{2})-([A-Z]{2})|',
    Params::getServerParam('HTTP_ACCEPT_LANGUAGE'),
    $match
)) {
    $country_ip = $match[2];
}

if (!isset($country_list[0]->s_country_name)) {
    $internet_error = true;
}
?>
<form class="p-3" id="target_form" name="target_form" action="#" method="post" onsubmit="return false;">
    <h2 class="display-6"><?php _e('Information needed'); ?></h2>
    <div class="form-table">
        <h4 class="title"><?php _e('Admin user'); ?></h4>
        <div class="admin-user mb-3">
            <div class="row mb-3">
                <label class="col-md-3 col-sm-6 col-form-label" for="admin_user"><?php _e('Username'); ?></label>
                <div class="col-md-4 col-sm-6">
                    <input class="form-control" size="25" id="admin_user" name="s_name" type="text" value="admin" />
                    <span id="admin-user-error" class="error" aria-hidden="true" style="display:none;"><?php _e('Admin user is required'); ?></span>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-3 col-sm-6 col-form-label" for="s_passwd"><?php _e('Password'); ?></label>
                <div class="col-md-4 col-sm-6">
                    <input size="25" class=" form-control password_test" name="s_passwd" id="s_passwd" type="password" value="" autocomplete="off" />
                </div>
                <td></td>
            </div>
        </div>
        <div class="admin-user mb-3">
            <?php _e('A password will be automatically generated for you if you leave this blank.'); ?>
            <i class="bi bi-question-circle-fill vtip" title="<?php echo osc_esc_html(__('You can modify username and password if you like, just change the input value.')); ?>">
            </i>
        </div>
        <h4 class="title"><?php _e('Contact information'); ?></h4>
        <div class="contact-info">
            <div class="row mb-3">
                <label class="col-md-3 col-sm-6 col-form-label" for="webtitle"><?php _e('Web title'); ?></label>
                <div class="col-md-4 col-sm-6"><input class="form-control" type="text" id="webtitle" name="webtitle" size="25" /></div>
                <td></td>
            </div>
            <div class="row mb-3">
                <label class="col-md-3 col-sm-6 col-form-label" for="email"><?php _e('Contact e-mail'); ?></label>
                <div class="col-md-4 col-sm-6">
                    <input class="form-control" type="text" id="email" name="email" size="25" />
                    <span id="email-error" class="error" style="display:none;"><?php _e('Put your e-mail here'); ?></span>
                </div>
                <span id="email-error" class="error" style="display:none;"><?php _e('Put your e-mail here'); ?></span>
            </div>
        </div>
        <h4 class="title"><?php _e('Location'); ?></h4>
        <p class="space-left-25 left no-bottom"><?php _e('Choose a country where your target users are located'); ?></p>
        <div id="location">
            <?php if (!$internet_error) { ?>
                <input type="hidden" id="skip-location-input" name="skip-location-input" value="<?php echo $country_ip ? 0 : 1; ?>" />
                <div class="col-md-3 col-sm-6" id="country-box">
                    <select class="form-select" name="location-json" id="location-json">
                        <option value="skip"><?php _e("Skip location"); ?></option>
                        <?php foreach ($country_list as $c) : ?>
                            <option value="<?php echo $c->s_file_name; ?>" <?php echo ($country_ip && strpos($c->s_file_name, $country_ip) === 0) ? 'selected="selected"' : ''; ?>>
                                <?php echo $c->s_country_name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php } else { ?>
                <div id="location-error">
                    <div class="alert alert-danger">
                        <?php _e('No internet connection. You can continue the installation and insert countries later.'); ?>
                    </div>
                    <input type="hidden" id="skip-location-input" name="skip-location-input" value="1" />
                </div>
            <?php }; ?>
        </div>
    </div>
    <div class="mt-3">
        <a href="#" class="btn btn-primary" onclick="validate_form();">Next</a>
    </div>
</form>
<div id="lightbox" style="display:none;">
    <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
    </div>
</div>