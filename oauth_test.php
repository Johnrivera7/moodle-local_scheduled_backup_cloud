<?php
/**
 * Prueba la conexión a Google Drive con el refresh token guardado.
 *
 * @package   local_scheduled_backup_cloud
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_capability('local/scheduled_backup_cloud:configure', \context_system::instance());
require_sesskey();

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);

$provider = (string) get_config('local_scheduled_backup_cloud', 'provider');
if ($provider !== 'google') {
    \core\notification::error(get_string('oauth_test_provider_google_only', 'local_scheduled_backup_cloud'));
    redirect($settingsurl);
}

$clientid = (string) get_config('local_scheduled_backup_cloud', 'clientid');
$secret = (string) get_config('local_scheduled_backup_cloud', 'clientsecret');
$refresh = (string) get_config('local_scheduled_backup_cloud', 'oauth_refresh_token');

if ($clientid === '' || $secret === '') {
    \core\notification::error(get_string('oauth_test_missing_credentials', 'local_scheduled_backup_cloud'));
    redirect($settingsurl);
}

if ($refresh === '') {
    \core\notification::warning(get_string('oauth_test_no_token', 'local_scheduled_backup_cloud'));
    redirect($settingsurl);
}

$uploader = new \local_scheduled_backup_cloud\oauth\google_drive_uploader($clientid, $secret, $refresh);
$result = $uploader->test_drive_connection();

if (!empty($result['ok'])) {
    $email = $result['email'] ?? '';
    \core\notification::success(get_string('oauth_test_success', 'local_scheduled_backup_cloud', $email));
    redirect($settingsurl);
}

$reason = $result['errormessage'] ?? 'unknown';
if ($reason === 'refresh_failed') {
    \core\notification::error(get_string('oauth_test_refresh_failed', 'local_scheduled_backup_cloud'));
} else if ($reason === 'bad_response') {
    \core\notification::error(get_string('oauth_test_bad_response', 'local_scheduled_backup_cloud'));
} else {
    \core\notification::error(get_string('oauth_test_failed_detail', 'local_scheduled_backup_cloud', $reason));
}

redirect($settingsurl);
