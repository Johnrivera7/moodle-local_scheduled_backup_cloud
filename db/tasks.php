<?php
/**
 * Tareas programadas.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_scheduled_backup_cloud\\task\\upload_course_backups_task',
        'blocking'  => 0,
        'minute'    => '15',
        'hour'      => '*',
        'day'       => '*',
        'dayofweek' => '*',
        'month'     => '*',
    ],
];
