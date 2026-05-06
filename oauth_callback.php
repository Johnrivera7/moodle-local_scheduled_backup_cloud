<?php
/**
 * Callback OAuth2 Google: intercambia código por tokens.
 *
 * @package   local_scheduled_backup_cloud
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_capability('local/scheduled_backup_cloud:configure', \context_system::instance());

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);

$error = optional_param('error', '', PARAM_RAW);
$errordescription = optional_param('error_description', '', PARAM_RAW_TRIMMED);
$code = optional_param('code', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);

if ($error !== '') {
    $msg = $error;
    if ($errordescription !== '') {
        $msg .= ': ' . $errordescription;
    }
    \core\notification::warning(get_string('oauth_redirect_error', 'local_scheduled_backup_cloud', $msg));
    redirect($settingsurl);
}

if ($state === '' || $state !== sesskey()) {
    throw new moodle_exception('invalidsesskey');
}

if ($code === '') {
    redirect($settingsurl);
}

$clientid = (string) get_config('local_scheduled_backup_cloud', 'clientid');
$secret = (string) get_config('local_scheduled_backup_cloud', 'clientsecret');
$redirect = new moodle_url('/local/scheduled_backup_cloud/oauth_callback.php');

$params = [
    'code' => $code,
    'client_id' => $clientid,
    'client_secret' => $secret,
    'redirect_uri' => $redirect->out(false),
    'grant_type' => 'authorization_code',
];

$curl = new curl();
$response = $curl->post('https://oauth2.googleapis.com/token', $params);
$data = json_decode($response, true);

if (!is_array($data)) {
    \core\notification::error(get_string('oauth_token_invalid_response', 'local_scheduled_backup_cloud'));
    redirect($settingsurl);
}

if (!empty($data['error'])) {
    $msg = $data['error'];
    if (!empty($data['error_description'])) {
        $msg .= ': ' . $data['error_description'];
    }
    \core\notification::error(get_string('oauth_token_error', 'local_scheduled_backup_cloud', $msg));
    redirect($settingsurl);
}

if (!empty($data['refresh_token'])) {
    set_config('oauth_refresh_token', $data['refresh_token'], 'local_scheduled_backup_cloud');
    \core\notification::success(get_string('oauth_success_connected', 'local_scheduled_backup_cloud'));
} else if (!empty($data['access_token'])) {
    \core\notification::warning(get_string('oauth_warning_no_refresh', 'local_scheduled_backup_cloud'));
} else {
    \core\notification::error(get_string('oauth_token_invalid_response', 'local_scheduled_backup_cloud'));
}

redirect($settingsurl);
