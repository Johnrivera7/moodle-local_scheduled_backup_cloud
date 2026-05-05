<?php
/**
 * Funciones de librería.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Añade al final de «Copia de seguridad programada» (section=automated) un bloque con enlace a los ajustes de subida a la nube.
 * Los plugins locales se cargan después del núcleo; entonces locate('automated') ya existe.
 *
 * @param admin_root $ADMIN árbol de administración
 */
function local_scheduled_backup_cloud_extend_scheduled_backup_page(admin_root $ADMIN): void {
    static $done = false;
    if ($done) {
        return;
    }
    $page = $ADMIN->locate('automated');
    if (!$page instanceof admin_settingpage) {
        return;
    }
    $done = true;

    $page->add(new admin_setting_heading(
        'local_scheduled_backup_cloud/automated_hook_heading',
        get_string('automated_cloud_section_heading', 'local_scheduled_backup_cloud'),
        get_string('automated_cloud_section_desc', 'local_scheduled_backup_cloud')
    ));

    $settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);
    $page->add(new admin_setting_description(
        'local_scheduled_backup_cloud/automated_hook_link',
        '',
        html_writer::link($settingsurl, get_string('automated_cloud_open_settings', 'local_scheduled_backup_cloud'))
    ));
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
 * Carpeta local donde el respaldo automático del núcleo guarda los .mbz (siempre backup_auto_destination).
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
