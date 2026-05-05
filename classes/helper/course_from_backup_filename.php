<?php
/**
 * Obtiene shortname / idnumber del curso a partir del nombre del .mbz estándar de Moodle.
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Utilidades de nombre de archivo de respaldo.
 */
class course_from_backup_filename {

    /**
     * Patrón habitual: backup-moodle2-course-{id}-...
     *
     * @param string $filename solo nombre de archivo
     * @return int 0 si no coincide
     */
    public static function extract_course_id(string $filename): int {
        if (preg_match('/backup-moodle2-course-(\d+)-/i', $filename, $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}
