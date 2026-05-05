<?php
/**
 * Valores de estado guardados en local_scheduled_backup_cloud_log.status.
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Estados del historial de subida.
 */
final class upload_status {

    /** Subida OK y archivo local eliminado. */
    public const UPLOAD_OK_DELETED = 'upload_ok_deleted';

    /** Subida OK pero el archivo local se conservó (opción desactivada). */
    public const UPLOAD_OK_KEPT = 'upload_ok_kept';

    /** Subida OK pero no se pudo borrar el archivo local. */
    public const UPLOAD_OK_DELETE_FAILED = 'upload_ok_delete_failed';

    /** Fallo de API o red al subir. */
    public const UPLOAD_FAILED = 'upload_failed';

    /** Mismo contenido (SHA-1) ya constaba como subido; no se repite. */
    public const SKIPPED_DUPLICATE = 'skipped_duplicate';
}
