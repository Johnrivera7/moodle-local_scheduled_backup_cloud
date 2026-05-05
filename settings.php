<?php
/**
 * Configuración del plugin.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {
    local_scheduled_backup_cloud_extend_scheduled_backup_page($ADMIN);

    $settings = new admin_settingpage('local_scheduled_backup_cloud', get_string('pluginname', 'local_scheduled_backup_cloud'));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/general',
        get_string('settings_general', 'local_scheduled_backup_cloud'),
        get_string('plugindesc', 'local_scheduled_backup_cloud')
    ));

    $linkreport = new moodle_url('/local/scheduled_backup_cloud/report.php');
    $settings->add(new admin_setting_description(
        'local_scheduled_backup_cloud/report_link_desc',
        get_string('report_title', 'local_scheduled_backup_cloud'),
        html_writer::link($linkreport, get_string('report_open', 'local_scheduled_backup_cloud'))
    ));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/cloud',
        get_string('cloud_heading', 'local_scheduled_backup_cloud'),
        get_string('cloud_heading_desc', 'local_scheduled_backup_cloud')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_scheduled_backup_cloud/enabled',
        get_string('enabled', 'local_scheduled_backup_cloud'),
        get_string('enabled_desc', 'local_scheduled_backup_cloud'),
        0
    ));

    $settings->add(new admin_setting_configselect(
        'local_scheduled_backup_cloud/provider',
        get_string('provider', 'local_scheduled_backup_cloud'),
        get_string('provider_desc', 'local_scheduled_backup_cloud'),
        'google',
        [
            'google' => get_string('provider_google', 'local_scheduled_backup_cloud'),
            'microsoft' => get_string('provider_microsoft', 'local_scheduled_backup_cloud'),
        ]
    ));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/oauth',
        get_string('oauth_heading', 'local_scheduled_backup_cloud'),
        get_string('oauth_intro', 'local_scheduled_backup_cloud')
    ));

    $redirect = new moodle_url('/local/scheduled_backup_cloud/oauth_callback.php');
    $settings->add(new admin_setting_description(
        'local_scheduled_backup_cloud/oauth_redirect_desc',
        get_string('oauth_redirect', 'local_scheduled_backup_cloud'),
        html_writer::tag('code', $redirect->out(false))
    ));

    $settings->add(new admin_setting_configtext(
        'local_scheduled_backup_cloud/clientid',
        get_string('clientid', 'local_scheduled_backup_cloud'),
        '',
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_scheduled_backup_cloud/clientsecret',
        get_string('clientsecret', 'local_scheduled_backup_cloud'),
        '',
        ''
    ));

    $refresh = get_config('local_scheduled_backup_cloud', 'oauth_refresh_token');
    $tokmsg = (is_string($refresh) && $refresh !== '')
        ? get_string('oauth_connected', 'local_scheduled_backup_cloud')
        : get_string('oauth_not_connected', 'local_scheduled_backup_cloud');
    $settings->add(new admin_setting_description(
        'local_scheduled_backup_cloud/oauth_status',
        '',
        $tokmsg
    ));

    $connecturl = new moodle_url('/local/scheduled_backup_cloud/oauth_start.php');
    $settings->add(new admin_setting_description(
        'local_scheduled_backup_cloud/oauth_connect_link',
        get_string('oauth_connect', 'local_scheduled_backup_cloud'),
        html_writer::link($connecturl, get_string('oauth_connect', 'local_scheduled_backup_cloud'))
    ));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/paths',
        get_string('paths_heading', 'local_scheduled_backup_cloud'),
        get_string('paths_heading_desc', 'local_scheduled_backup_cloud')
    ));

    $settings->add(new admin_setting_configtext(
        'local_scheduled_backup_cloud/site_folder_slug',
        get_string('site_folder_slug', 'local_scheduled_backup_cloud'),
        get_string('site_folder_slug_desc', 'local_scheduled_backup_cloud'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configselect(
        'local_scheduled_backup_cloud/folder_strategy',
        get_string('folder_strategy', 'local_scheduled_backup_cloud'),
        get_string('folder_strategy_desc', 'local_scheduled_backup_cloud'),
        'site_shortname',
        [
            'site_shortname' => get_string('folder_site_shortname', 'local_scheduled_backup_cloud'),
            'site_idnumber' => get_string('folder_site_idnumber', 'local_scheduled_backup_cloud'),
        ]
    ));

    $settings->add(new admin_setting_description(
        'local_scheduled_backup_cloud/filename_preview',
        get_string('filename_preview_heading', 'local_scheduled_backup_cloud'),
        get_string('filename_pattern_fixed_desc', 'local_scheduled_backup_cloud') . html_writer::empty_tag('br') .
        local_scheduled_backup_cloud_get_filename_preview_html()
    ));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/after_upload',
        get_string('after_upload_heading', 'local_scheduled_backup_cloud'),
        get_string('after_upload_desc', 'local_scheduled_backup_cloud')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_scheduled_backup_cloud/delete_local_after_upload',
        get_string('delete_local_after_upload', 'local_scheduled_backup_cloud'),
        get_string('delete_local_after_upload_desc', 'local_scheduled_backup_cloud'),
        1
    ));

    // Aparece en Administración del sitio → Cursos → Copias de seguridad (junto a «Copia de seguridad programada», etc.).
    $ADMIN->add('backups', $settings);
}
