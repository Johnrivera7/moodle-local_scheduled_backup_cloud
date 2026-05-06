<?php
/**
 * Configuración del plugin.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {
    local_scheduled_backup_cloud_extend_scheduled_backup_page($ADMIN);

    $settings = new admin_settingpage('local_scheduled_backup_cloud', get_string('pluginname', 'local_scheduled_backup_cloud'));

    $settings->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/general',
        get_string('settings_general', 'local_scheduled_backup_cloud'),
        get_string('plugindesc', 'local_scheduled_backup_cloud')
    ));

    require_once($CFG->dirroot . '/lib/adminlib.php');
    require_once(__DIR__ . '/adminsettings.php');

    $linkreport = new moodle_url('/local/scheduled_backup_cloud/report.php');
    $settings->add(new local_scheduled_backup_cloud_admin_setting_action_button(
        'local_scheduled_backup_cloud/report_link_desc',
        get_string('report_title', 'local_scheduled_backup_cloud'),
        $linkreport,
        get_string('report_open', 'local_scheduled_backup_cloud')
    ));

    $testurl = new moodle_url('/local/scheduled_backup_cloud/oauth_test.php', ['sesskey' => sesskey()]);
    $settings->add(new local_scheduled_backup_cloud_admin_setting_action_button(
        'local_scheduled_backup_cloud/oauth_test_link_settings',
        get_string('oauth_test_connection', 'local_scheduled_backup_cloud'),
        $testurl,
        get_string('oauth_test_connection', 'local_scheduled_backup_cloud'),
        'btn btn-secondary'
    ));

    // Aparece en Administración del sitio → Cursos → Copias de seguridad (junto a «Copia de seguridad programada», etc.).
    $ADMIN->add('backups', $settings);
}
