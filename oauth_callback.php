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

$code = optional_param('code', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);
$error = optional_param('error', '', PARAM_ALPHAEXT);

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);

if ($error !== '') {
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

if (is_array($data) && !empty($data['refresh_token'])) {
    set_config('oauth_refresh_token', $data['refresh_token'], 'local_scheduled_backup_cloud');
} else if (is_array($data) && !empty($data['access_token'])) {
    // Sin refresh_token (re-autorización parcial); mantener token anterior si existe.
    \core\notification::warning('No se recibió refresh_token; vuelva a conectar con consentimiento.');
} else {
    \core\notification::error('Respuesta OAuth inválida.');
}

redirect($settingsurl);
