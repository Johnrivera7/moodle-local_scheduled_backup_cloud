<?php
/**
 * Inicia el flujo OAuth2 (Google) para Drive.
 *
 * @package   local_scheduled_backup_cloud
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('local/scheduled_backup_cloud:configure', \context_system::instance());

$provider = (string) get_config('local_scheduled_backup_cloud', 'provider');
if ($provider === 'microsoft') {
    \core\notification::error('OAuth para Microsoft Graph no está implementado en esta versión.');
    redirect(new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']));
}

$clientid = (string) get_config('local_scheduled_backup_cloud', 'clientid');
if ($clientid === '') {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']));
}

$redirect = new moodle_url('/local/scheduled_backup_cloud/oauth_callback.php');
$params = [
    'client_id' => $clientid,
    'redirect_uri' => $redirect->out(false),
    'response_type' => 'code',
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'access_type' => 'offline',
    'prompt' => 'consent',
    'include_granted_scopes' => 'true',
    'state' => sesskey(),
];

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
redirect($url);
