<?php
/**
 * English strings.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Scheduled backup (cloud)';
$string['plugindesc'] = 'Uploads to the cloud the .mbz files produced by core Scheduled backup. Configure backup timing on that page; use this page for OAuth, paths, and local deletion. A link to these settings appears at the bottom of Scheduled backup.';

$string['settings_general'] = 'General';
$string['enabled'] = 'Enable uploads';
$string['enabled_desc'] = 'If disabled, the scheduled task does nothing.';
$string['provider'] = 'Cloud provider';
$string['provider_desc'] = 'Choose where uploaded backup files are stored.';
$string['provider_google'] = 'Google Drive';
$string['provider_microsoft'] = 'Microsoft (SharePoint / OneDrive via Graph)';

$string['oauth_heading'] = 'OAuth (Google Drive)';
$string['oauth_intro'] = 'In Google Cloud Console, create OAuth web application credentials, enable the Google Drive API, and register the redirect URL shown below. Enter the client ID and secret here, save changes, then click Connect account to authorize Drive access.';
$string['clientid'] = 'Client ID';
$string['clientsecret'] = 'Client secret';
$string['oauth_redirect'] = 'OAuth redirect URL (copy into app registration)';
$string['oauth_connect'] = 'Connect account';
$string['oauth_connected'] = 'Cloud account is connected (refresh token stored).';
$string['oauth_not_connected'] = 'Not connected. Save client ID and secret, then click Connect account.';
$string['tokens_encrypted'] = 'Stored tokens (encrypted)';
$string['tokens_encrypted_desc'] = 'Leave blank unless restoring from backup. Connection writes here automatically.';

$string['oauth_success_connected'] = 'Google account connected. The refresh token was saved; scheduled uploads can use Drive.';
$string['oauth_redirect_error'] = 'Google sign-in was not completed: {$a}';
$string['oauth_token_invalid_response'] = 'Invalid response from Google while exchanging the authorization code.';
$string['oauth_token_error'] = 'Could not obtain tokens: {$a}';
$string['oauth_warning_no_refresh'] = 'No refresh token was returned. Revoke the app access in your Google account, then connect again and accept all permissions (first-time consent).';

$string['oauth_test_connection'] = 'Test connection';
$string['oauth_test_success'] = 'Connection OK. Signed in to Google Drive as {$a}.';
$string['oauth_test_failed_detail'] = 'Connection test failed: {$a}';
$string['oauth_test_refresh_failed'] = 'Could not refresh the access token. Check the client ID, secret, and that a refresh token is stored.';
$string['oauth_test_bad_response'] = 'Drive API returned an unexpected response.';
$string['oauth_test_no_token'] = 'No refresh token is stored yet. Connect your Google account first (Connect account), then test again.';
$string['oauth_test_missing_credentials'] = 'Save the client ID and client secret before testing the connection.';
$string['oauth_test_provider_google_only'] = 'Connection test is only available for Google Drive in this version.';

$string['automated_cloud_section_heading'] = 'Cloud upload';
$string['automated_cloud_section_desc'] = 'OAuth, remote folders, and upload options are on the linked page. The controls above are core Scheduled backup only.';
$string['automated_cloud_open_settings'] = 'Open cloud upload settings';

$string['cloud_heading'] = 'Cloud upload';
$string['cloud_heading_desc'] = 'Enable the upload task, choose provider and OAuth. This does not create .mbz files — core cron generates them in the «Save to» folder above; this task picks them up in stable order (by file time), uploads one file, then moves on.';

$string['paths_heading'] = 'Cloud paths and naming';
$string['paths_heading_desc'] = '.mbz files are read only from the automated backup «Save to» folder on the server. In Scheduled backup, storage must include a specified directory (not course file area only), and that path must match this folder. No other local folder is used.';
$string['folder_strategy'] = 'Remote folder layout';
$string['folder_strategy_desc'] = 'How to build folders on the cloud storage.';
$string['folder_site_shortname'] = 'Site folder / course shortname / file';
$string['folder_site_idnumber'] = 'Site folder / course idnumber / file (fallback shortname)';
$string['filename_preview_heading'] = 'Cloud file name';
$string['filename_pattern_fixed_desc'] = 'Fixed format: shortname_courseid_{id}_YYYYMMDD-HHMMSS.mbz (the word courseid is literal in the file name). No shortname: unknown_courseid_{id}_date. No course: backup_courseid_0_date. Example (date/time when this page loads):';
$string['filename_preview_example_file'] = 'Example file:';
$string['filename_preview_example_path'] = 'Example path (site / course / file):';
$string['filename_preview_sample_short'] = 'MAT-101';

$string['site_folder_slug'] = 'Override site folder name';
$string['site_folder_slug_desc'] = 'Leave empty to auto-generate from this site\'s wwwroot (safe folder name per Moodle URL).';

$string['task_upload'] = 'Upload course backups to cloud storage';

$string['task_trace_microsoft_pending'] = 'Microsoft / Graph upload is not implemented in this version.';
$string['task_trace_oauth_incomplete'] = 'OAuth is not fully configured (client ID, secret, or refresh token missing).';
$string['task_trace_storage_course_only'] = 'Core scheduled backup only stores files in each course backup file area — nothing is copied to a server folder. Set storage to «Specified directory» or «Course backup filearea and specified directory», then set «Save course backups in» to an absolute path on this server (writable by the web/cron user). This plugin only uploads .mbz files from that folder.';
$string['task_trace_scan_empty'] = 'The automated backup «Save to» path is empty (config backup_auto_destination). Open Site administration → Courses → Backups → Scheduled backup and set a full server path where .mbz files are written.';
$string['task_trace_scan_not_dir'] = 'The configured scan path is not a directory or is not readable: {$a}';
$string['task_trace_token_google_failed'] = 'Could not obtain a Google access token (check client ID, secret, and refresh token).';
$string['task_trace_skipped_duplicate'] = 'Skipped (already uploaded before): {$a}';
$string['task_trace_remote_folder_failed'] = 'Could not create or resolve the remote folder for: {$a}';
$string['task_trace_upload_failed'] = 'Upload failed for: {$a}';
$string['task_trace_delete_skipped'] = 'Safe local delete skipped ({$a->reason}) for {$a->file}';
$string['task_trace_uploaded'] = 'Uploaded {$a->name} (status: {$a->status})';

$string['after_upload_heading'] = 'After cloud upload';
$string['after_upload_desc'] = 'Use cloud as the primary backup archive and free disk space on the server.';

$string['delete_local_after_upload'] = 'Delete local copy after successful upload';
$string['delete_local_after_upload_desc'] = 'Deletes only the exact file that was just uploaded: it must sit inside the automated backup folder, keep the same basename, and match the SHA-1 captured before upload. If checks fail, nothing is removed (avoids deleting the wrong file).';

$string['report_title'] = 'Cloud backup report';
$string['report_open'] = 'View upload report';
$string['report_plugin_settings'] = 'Plugin settings';
$string['report_intro'] = 'Log of each processed file: uploaded, skipped (already on cloud), or failed. If a course had no changes and core did not create a .mbz, there will be no row.';
$string['report_link_automated'] = 'Scheduled backup';
$string['report_col_time'] = 'Run / uploaded';
$string['report_col_course'] = 'Course';
$string['report_col_original'] = 'Original file';
$string['report_col_remote'] = 'Cloud file name';
$string['report_col_status'] = 'Status';
$string['report_col_size'] = 'Size';
$string['report_col_path'] = 'Remote path';
$string['report_col_detail'] = 'Detail';
$string['report_empty'] = 'No rows yet.';
$string['report_limit_note'] = 'Showing at most the {$a} most recent rows.';
$string['report_table_missing'] = 'Run database upgrade to create the history table.';

$string['status_upload_ok_deleted'] = 'Uploaded and deleted locally';
$string['status_upload_ok_kept'] = 'Uploaded (local copy kept)';
$string['status_upload_ok_delete_failed'] = 'Uploaded (could not delete local)';
$string['status_upload_failed'] = 'Upload failed';
$string['status_skipped_duplicate'] = 'Skipped (already uploaded)';

$string['skipped_duplicate_detail'] = 'Same content (SHA-1) was already uploaded to the cloud.';
$string['error_remote_folder'] = 'Could not create or resolve remote folder.';
$string['error_upload_api'] = 'Upload API returned an error or empty response.';
$string['error_delete_local'] = 'Could not delete local file (permissions or path).';
$string['error_delete_local_verify'] = 'Local copy was not deleted: safety checks failed or unlink failed (check permissions). See cron output for detail.';

$string['privacy:metadata'] = 'The plugin stores OAuth tokens, a manifest of file hashes already uploaded, and an admin report table with course identifiers and file names.';
