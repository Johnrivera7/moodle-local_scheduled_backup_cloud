<?php
/**
 * Funciones de librería.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extiende «Copia de seguridad programada» (section=automated) con los ajustes de subida a la nube.
 * Los plugins locales se cargan después del núcleo; entonces locate('automated') ya existe.
 *
 * @param admin_root $ADMIN árbol de administración
 */
function local_scheduled_backup_cloud_extend_scheduled_backup_page(admin_root $ADMIN): void {
    global $CFG;

    static $done = false;
    if ($done) {
        return;
    }
    require_once($CFG->dirroot . '/lib/adminlib.php');
    require_once(__DIR__ . '/adminsettings.php');

    $page = $ADMIN->locate('automated');
    if (!$page instanceof admin_settingpage) {
        return;
    }
    $done = true;

    $page->add(new local_scheduled_backup_cloud_admin_setting_section_heading(
        'local_scheduled_backup_cloud/automated_hook_heading',
        get_string('automated_cloud_section_heading', 'local_scheduled_backup_cloud'),
        get_string('automated_cloud_section_desc', 'local_scheduled_backup_cloud')
    ));

    $enabled = new admin_setting_configcheckbox(
        'local_scheduled_backup_cloud/enabled',
        get_string('enabled', 'local_scheduled_backup_cloud'),
        get_string('enabled_desc', 'local_scheduled_backup_cloud'),
        0
    );
    $page->add($enabled);

    $provider = new admin_setting_configselect(
        'local_scheduled_backup_cloud/provider',
        get_string('provider', 'local_scheduled_backup_cloud'),
        get_string('provider_desc', 'local_scheduled_backup_cloud'),
        'google',
        [
            'google' => get_string('provider_google', 'local_scheduled_backup_cloud'),
            'microsoft' => get_string('provider_microsoft', 'local_scheduled_backup_cloud'),
        ]
    );
    $page->add($provider);

    $oauthheading = new local_scheduled_backup_cloud_admin_setting_section_heading(
        'local_scheduled_backup_cloud/oauth',
        get_string('oauth_heading', 'local_scheduled_backup_cloud'),
        get_string('oauth_intro', 'local_scheduled_backup_cloud')
    );
    $page->add($oauthheading);

    $redirect = new moodle_url('/local/scheduled_backup_cloud/oauth_callback.php');
    $oauthredirect = new local_scheduled_backup_cloud_admin_setting_form_description(
        'local_scheduled_backup_cloud/oauth_redirect_desc',
        get_string('oauth_redirect', 'local_scheduled_backup_cloud'),
        html_writer::tag('code', $redirect->out(false))
    );
    $page->add($oauthredirect);

    $clientid = new admin_setting_configtext(
        'local_scheduled_backup_cloud/clientid',
        get_string('clientid', 'local_scheduled_backup_cloud'),
        '',
        '',
        PARAM_RAW
    );
    $page->add($clientid);

    $clientsecret = new admin_setting_configpasswordunmask(
        'local_scheduled_backup_cloud/clientsecret',
        get_string('clientsecret', 'local_scheduled_backup_cloud'),
        '',
        ''
    );
    $page->add($clientsecret);

    $refresh = get_config('local_scheduled_backup_cloud', 'oauth_refresh_token');
    $tokmsg = (is_string($refresh) && $refresh !== '')
        ? get_string('oauth_connected', 'local_scheduled_backup_cloud')
        : get_string('oauth_not_connected', 'local_scheduled_backup_cloud');
    $oauthstatus = new local_scheduled_backup_cloud_admin_setting_form_description(
        'local_scheduled_backup_cloud/oauth_status',
        '',
        $tokmsg
    );
    $page->add($oauthstatus);

    $connecturl = new moodle_url('/local/scheduled_backup_cloud/oauth_start.php');
    $oauthconnect = new local_scheduled_backup_cloud_admin_setting_action_button(
        'local_scheduled_backup_cloud/oauth_connect_link',
        get_string('oauth_connect', 'local_scheduled_backup_cloud'),
        $connecturl
    );
    $page->add($oauthconnect);

    $testurl = new moodle_url('/local/scheduled_backup_cloud/oauth_test.php', ['sesskey' => sesskey()]);
    $oauthtest = new local_scheduled_backup_cloud_admin_setting_action_button(
        'local_scheduled_backup_cloud/oauth_test_link',
        get_string('oauth_test_connection', 'local_scheduled_backup_cloud'),
        $testurl,
        get_string('oauth_test_connection', 'local_scheduled_backup_cloud'),
        'btn btn-secondary'
    );
    $page->add($oauthtest);

    $pathsheading = new local_scheduled_backup_cloud_admin_setting_section_heading(
        'local_scheduled_backup_cloud/paths',
        get_string('paths_heading', 'local_scheduled_backup_cloud'),
        get_string('paths_heading_desc', 'local_scheduled_backup_cloud')
    );
    $page->add($pathsheading);

    $sitefolderslug = new admin_setting_configtext(
        'local_scheduled_backup_cloud/site_folder_slug',
        get_string('site_folder_slug', 'local_scheduled_backup_cloud'),
        get_string('site_folder_slug_desc', 'local_scheduled_backup_cloud'),
        '',
        PARAM_TEXT
    );
    $page->add($sitefolderslug);

    $folderstrategy = new admin_setting_configselect(
        'local_scheduled_backup_cloud/folder_strategy',
        get_string('folder_strategy', 'local_scheduled_backup_cloud'),
        get_string('folder_strategy_desc', 'local_scheduled_backup_cloud'),
        'site_shortname',
        [
            'site_shortname' => get_string('folder_site_shortname', 'local_scheduled_backup_cloud'),
            'site_idnumber' => get_string('folder_site_idnumber', 'local_scheduled_backup_cloud'),
        ]
    );
    $page->add($folderstrategy);

    $filenamepreview = new local_scheduled_backup_cloud_admin_setting_form_description(
        'local_scheduled_backup_cloud/filename_preview',
        get_string('filename_preview_heading', 'local_scheduled_backup_cloud'),
        get_string('filename_pattern_fixed_desc', 'local_scheduled_backup_cloud') . html_writer::empty_tag('br') .
        local_scheduled_backup_cloud_get_filename_preview_html()
    );
    $page->add($filenamepreview);

    $afterheading = new local_scheduled_backup_cloud_admin_setting_section_heading(
        'local_scheduled_backup_cloud/after_upload',
        get_string('after_upload_heading', 'local_scheduled_backup_cloud'),
        get_string('after_upload_desc', 'local_scheduled_backup_cloud')
    );
    $page->add($afterheading);

    $deletelocal = new admin_setting_configcheckbox(
        'local_scheduled_backup_cloud/delete_local_after_upload',
        get_string('delete_local_after_upload', 'local_scheduled_backup_cloud'),
        get_string('delete_local_after_upload_desc', 'local_scheduled_backup_cloud'),
        1
    );
    $page->add($deletelocal);

    $settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);
    $openplugin = new local_scheduled_backup_cloud_admin_setting_form_description(
        'local_scheduled_backup_cloud/automated_hook_link',
        '',
        html_writer::link($settingsurl, get_string('automated_cloud_open_settings', 'local_scheduled_backup_cloud'))
    );
    $page->add($openplugin);

    // Hide/show instantly (client-side) based on the enabled checkbox.
    // This uses the same dependency system as core admin settings.
    $dependenton = 'local_scheduled_backup_cloud/enabled';
    $page->hide_if('local_scheduled_backup_cloud/provider', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/oauth', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/oauth_redirect_desc', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/clientid', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/clientsecret', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/oauth_status', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/oauth_connect_link', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/oauth_test_link', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/paths', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/site_folder_slug', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/folder_strategy', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/filename_preview', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/after_upload', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/delete_local_after_upload', $dependenton, 'notchecked');
    $page->hide_if('local_scheduled_backup_cloud/automated_hook_link', $dependenton, 'notchecked');
}

/**
 * Genera un nombre de carpeta estable por sitio a partir de wwwroot.
 *
 * @return string segmento seguro para rutas en la nube
 */
function local_scheduled_backup_cloud_get_site_folder_slug(): string {
    global $CFG;

    $override = get_config('local_scheduled_backup_cloud', 'site_folder_slug');
    $override = is_string($override) ? trim($override) : '';
    if ($override !== '') {
        return clean_filename($override);
    }

    $host = parse_url($CFG->wwwroot, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        $host = 'moodle';
    }
    $hash = substr(sha1($CFG->wwwroot), 0, 8);

    return clean_filename($host . '-' . $hash);
}

/**
 * Carpeta local donde el respaldo automático del núcleo copia los .mbz (backup_auto_destination).
 * Solo se usa si el almacenamiento incluye directorio (no solo área del curso).
 *
 * @return string|false ruta absoluta o false
 */
function local_scheduled_backup_cloud_resolve_scan_path() {
    $dest = get_config('backup', 'backup_auto_destination');
    if (!is_string($dest)) {
        return false;
    }
    $dest = trim($dest);
    return $dest !== '' ? $dest : false;
}

/**
 * Modo de almacenamiento del respaldo programado del núcleo (backup_auto_storage).
 *
 * @return int 0 = solo área del curso; 1 = carpeta; 2 = ambos
 */
function local_scheduled_backup_cloud_get_scheduled_backup_storage(): int {
    $v = get_config('backup', 'backup_auto_storage');
    if ($v === null || $v === '') {
        return 0;
    }
    return (int) $v;
}

/**
 * Construye el nombre del archivo remoto: shortname_courseid_{id}_fechahora.mbz
 *
 * Ejemplos: MAT-101_courseid_42_20260105-120000.mbz, unknown_courseid_42_….mbz
 *
 * @param string $shortname shortname real del curso; vacío o «unknown» si no hay
 * @param int $courseid id numérico del curso (0 si no se conoce)
 */
function local_scheduled_backup_cloud_build_remote_filename(string $shortname, int $courseid): string {
    $shortname = trim($shortname);
    if ($shortname !== '' && $shortname !== 'unknown') {
        $sn = clean_filename($shortname);
    } else if ($courseid > 0) {
        $sn = 'unknown';
    } else {
        $sn = 'backup';
    }

    $cidpart = (string) max(0, (int) $courseid);

    $datepart = userdate(time(), '%Y%m%d-%H%M%S', 99, false);

    return clean_filename($sn . '_courseid_' . $cidpart . '_' . $datepart . '.mbz');
}

/**
 * HTML de vista previa del nombre remoto (ajustes de administración).
 *
 * @return string HTML seguro
 */
function local_scheduled_backup_cloud_get_filename_preview_html(): string {
    $sampleshort = get_string('filename_preview_sample_short', 'local_scheduled_backup_cloud');

    $name = local_scheduled_backup_cloud_build_remote_filename($sampleshort, 999);

    $sitefolder = local_scheduled_backup_cloud_get_site_folder_slug();
    $virtpath = $sitefolder . '/' . clean_filename($sampleshort) . '/' . $name;

    $out = '';
    $out .= html_writer::div(
        html_writer::tag('strong', get_string('filename_preview_example_file', 'local_scheduled_backup_cloud')) . ' ' .
        html_writer::tag('code', s($name)),
        'mb-1'
    );
    $out .= html_writer::div(
        html_writer::tag('strong', get_string('filename_preview_example_path', 'local_scheduled_backup_cloud')) . ' ' .
        html_writer::tag('code', s($virtpath)),
        'mb-1 text-muted small'
    );

    return $out;
}

/**
 * Borra solo el archivo local que acaba de subirse: misma carpeta raíz, mismo nombre, mismo SHA-1.
 *
 * @param string $scanroot raíz permitida (backup_auto_destination), sin trailing slash
 * @param string $filepath ruta absoluta del fichero a borrar
 * @param string $expectedsha1 huella SHA-1 calculada antes de la subida
 * @param string $expectedbasename basename esperado al iniciar el proceso
 * @return array{ok:bool, reason:string}
 */
function local_scheduled_backup_cloud_safe_delete_local_copy(
    string $scanroot,
    string $filepath,
    string $expectedsha1,
    string $expectedbasename
): array {
    $scanroot = rtrim($scanroot, "\\/");
    if ($scanroot === '' || $filepath === '') {
        return ['ok' => false, 'reason' => 'empty_path'];
    }

    if (!preg_match('/\.mbz$/i', $expectedbasename)) {
        return ['ok' => false, 'reason' => 'not_mbz'];
    }

    $realroot = realpath($scanroot);
    if ($realroot === false || !is_dir($realroot)) {
        return ['ok' => false, 'reason' => 'bad_root'];
    }

    $realfile = realpath($filepath);
    if ($realfile === false || !is_file($realfile)) {
        return ['ok' => false, 'reason' => 'missing'];
    }

    $base = basename($realfile);
    if ($base !== $expectedbasename) {
        return ['ok' => false, 'reason' => 'basename_mismatch'];
    }

    $prefix = $realroot . DIRECTORY_SEPARATOR;
    if (strpos($realfile, $prefix) !== 0 && $realfile !== $realroot) {
        return ['ok' => false, 'reason' => 'outside_root'];
    }

    $hash = sha1_file($realfile);
    if ($hash === false || $hash !== $expectedsha1) {
        return ['ok' => false, 'reason' => 'hash_mismatch'];
    }

    if (!@unlink($realfile)) {
        return ['ok' => false, 'reason' => 'unlink_failed'];
    }

    return ['ok' => true, 'reason' => ''];
}

/**
 * Interpreta checkbox del plugin cuando aún no se ha guardado configuración (null = usar valor por defecto).
 */
function local_scheduled_backup_cloud_get_bool_config(string $name, bool $default): bool {
    $v = get_config('local_scheduled_backup_cloud', $name);
    if ($v === null || $v === '') {
        return $default;
    }
    return (bool) (int) $v;
}
