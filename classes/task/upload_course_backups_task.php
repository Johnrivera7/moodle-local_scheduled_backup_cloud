<?php
/**
 * Tarea programada: subir respaldos de curso.
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\task;

defined('MOODLE_INTERNAL') || die();

use local_scheduled_backup_cloud\cloud\upload_manager;

/**
 * Cron de subida.
 */
class upload_course_backups_task extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_upload', 'local_scheduled_backup_cloud');
    }

    public function execute(): void {
        if (!(bool) get_config('local_scheduled_backup_cloud', 'enabled')) {
            return;
        }
        $manager = new upload_manager(new \text_progress_trace());
        $manager->run();
    }
}
