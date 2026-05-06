<?php
/**
 * Orquesta el escaneo de .mbz y la subida a la nube.
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\cloud;

defined('MOODLE_INTERNAL') || die();

use local_scheduled_backup_cloud\helper\course_from_backup_filename;
use local_scheduled_backup_cloud\local\upload_status;
use local_scheduled_backup_cloud\oauth\google_drive_uploader;

/**
 * Lógica principal invocada desde la tarea programada.
 */
class upload_manager {

    /** @var \progress_trace */
    protected $trace;

    public function __construct(?\progress_trace $trace = null) {
        $this->trace = $trace ?? new \text_progress_trace();
    }

    /**
     * Ejecuta el proceso completo.
     */
    public function run(): void {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/scheduled_backup_cloud/lib.php');

        $provider = (string) get_config('local_scheduled_backup_cloud', 'provider');
        if ($provider === '') {
            $provider = 'google';
        }

        if ($provider === 'microsoft') {
            $this->trace->output(get_string('task_trace_microsoft_pending', 'local_scheduled_backup_cloud'));
            return;
        }

        $clientid = (string) get_config('local_scheduled_backup_cloud', 'clientid');
        $secret = (string) get_config('local_scheduled_backup_cloud', 'clientsecret');
        $refresh = (string) get_config('local_scheduled_backup_cloud', 'oauth_refresh_token');

        if ($clientid === '' || $secret === '' || $refresh === '') {
            $this->trace->output(get_string('task_trace_oauth_incomplete', 'local_scheduled_backup_cloud'));
            return;
        }

        $storage = \local_scheduled_backup_cloud_get_scheduled_backup_storage();
        if ($storage === 0) {
            $this->trace->output(get_string('task_trace_storage_course_only', 'local_scheduled_backup_cloud'));
            return;
        }

        $scan = \local_scheduled_backup_cloud_resolve_scan_path();
        if ($scan === false || $scan === '') {
            $this->trace->output(get_string('task_trace_scan_empty', 'local_scheduled_backup_cloud'));
            return;
        }
        if (!is_dir($scan)) {
            $this->trace->output(get_string('task_trace_scan_not_dir', 'local_scheduled_backup_cloud', $scan));
            return;
        }

        $uploader = new google_drive_uploader($clientid, $secret, $refresh);
        $access = $uploader->get_access_token();
        if ($access === null) {
            $this->trace->output(get_string('task_trace_token_google_failed', 'local_scheduled_backup_cloud'));
            return;
        }

        $siteslug = \local_scheduled_backup_cloud_get_site_folder_slug();
        $strategy = (string) get_config('local_scheduled_backup_cloud', 'folder_strategy');
        if ($strategy === '') {
            $strategy = 'site_shortname';
        }

        $deleteafter = \local_scheduled_backup_cloud_get_bool_config('delete_local_after_upload', true);

        $files = glob($scan . '/*.mbz') ?: [];
        usort($files, static function (string $a, string $b): int {
            $ta = @filemtime($a) ?: 0;
            $tb = @filemtime($b) ?: 0;
            if ($ta === $tb) {
                return strnatcasecmp(basename($a), basename($b));
            }
            return $ta <=> $tb;
        });

        $manifestraw = get_config('local_scheduled_backup_cloud', 'upload_manifest');
        $manifest = [];
        if (is_string($manifestraw) && $manifestraw !== '') {
            $decoded = json_decode($manifestraw, true);
            if (is_array($decoded)) {
                $manifest = $decoded;
            }
        }

        foreach ($files as $filepath) {
            $basename = basename($filepath);
            $timerun = time();
            $sig = sha1_file($filepath);
            if ($sig === false) {
                continue;
            }

            $filesize = file_exists($filepath) ? (int) filesize($filepath) : 0;

            $courseid = course_from_backup_filename::extract_course_id($basename);
            $shortname = 'unknown';
            $rawshort = '';
            $idnumber = '';
            if ($courseid > 0) {
                $rec = $DB->get_record('course', ['id' => $courseid], 'shortname,idnumber', IGNORE_MISSING);
                if ($rec) {
                    $rawshort = trim((string) $rec->shortname);
                    $shortname = $rawshort !== '' ? $rawshort : ('id' . $courseid);
                    $idnumber = (string) $rec->idnumber;
                }
            }

            $coursefolder = $shortname;
            if ($strategy === 'site_idnumber' && $idnumber !== '') {
                $coursefolder = clean_filename($idnumber);
            } else {
                $coursefolder = clean_filename($coursefolder);
            }

            if (isset($manifest[$sig])) {
                $this->insert_log_row([
                    'courseid' => $courseid,
                    'courseshortname' => $shortname,
                    'originalfilename' => $basename,
                    'remotefilename' => '',
                    'filehash' => $sig,
                    'filesize' => $filesize,
                    'status' => upload_status::SKIPPED_DUPLICATE,
                    'timerun' => $timerun,
                    'timeuploaded' => 0,
                    'remotepath' => '',
                    'errormessage' => get_string('skipped_duplicate_detail', 'local_scheduled_backup_cloud'),
                ]);
                $this->trace->output(get_string('task_trace_skipped_duplicate', 'local_scheduled_backup_cloud', $basename));
                continue;
            }

            $uploadname = \local_scheduled_backup_cloud_build_remote_filename(
                $rawshort !== '' ? $rawshort : 'unknown',
                $courseid
            );

            $segments = [$siteslug, $coursefolder];
            $folderid = $uploader->ensure_path($access, $segments);
            if ($folderid === null) {
                $this->insert_log_row([
                    'courseid' => $courseid,
                    'courseshortname' => $shortname,
                    'originalfilename' => $basename,
                    'remotefilename' => $uploadname,
                    'filehash' => $sig,
                    'filesize' => $filesize,
                    'status' => upload_status::UPLOAD_FAILED,
                    'timerun' => $timerun,
                    'timeuploaded' => 0,
                    'remotepath' => implode('/', [$siteslug, $coursefolder, $uploadname]),
                    'errormessage' => get_string('error_remote_folder', 'local_scheduled_backup_cloud'),
                ]);
                $this->trace->output(get_string('task_trace_remote_folder_failed', 'local_scheduled_backup_cloud', $basename));
                continue;
            }

            $virtualpath = implode('/', [$siteslug, $coursefolder, $uploadname]);

            $ok = $uploader->upload_file($access, $folderid, $filepath, $uploadname);
            if (!$ok) {
                $this->insert_log_row([
                    'courseid' => $courseid,
                    'courseshortname' => $shortname,
                    'originalfilename' => $basename,
                    'remotefilename' => $uploadname,
                    'filehash' => $sig,
                    'filesize' => $filesize,
                    'status' => upload_status::UPLOAD_FAILED,
                    'timerun' => $timerun,
                    'timeuploaded' => 0,
                    'remotepath' => $virtualpath,
                    'errormessage' => get_string('error_upload_api', 'local_scheduled_backup_cloud'),
                ]);
                $this->trace->output(get_string('task_trace_upload_failed', 'local_scheduled_backup_cloud', $basename));
                continue;
            }

            $manifest[$sig] = time();
            set_config('upload_manifest', json_encode($manifest), 'local_scheduled_backup_cloud');

            $timup = time();
            $status = upload_status::UPLOAD_OK_KEPT;
            $err = '';

            if ($deleteafter) {
                $deleteres = \local_scheduled_backup_cloud_safe_delete_local_copy($scan, $filepath, $sig, $basename);
                if ($deleteres['ok']) {
                    $status = upload_status::UPLOAD_OK_DELETED;
                } else {
                    $status = upload_status::UPLOAD_OK_DELETE_FAILED;
                    $err = get_string('error_delete_local_verify', 'local_scheduled_backup_cloud');
                    $this->trace->output(get_string('task_trace_delete_skipped', 'local_scheduled_backup_cloud',
                        (object)['reason' => $deleteres['reason'], 'file' => $basename]));
                }
            }

            $this->insert_log_row([
                'courseid' => $courseid,
                'courseshortname' => $shortname,
                'originalfilename' => $basename,
                'remotefilename' => $uploadname,
                'filehash' => $sig,
                'filesize' => $filesize,
                'status' => $status,
                'timerun' => $timerun,
                'timeuploaded' => $timup,
                'remotepath' => $virtualpath,
                'errormessage' => $err,
            ]);

            $this->trace->output(get_string('task_trace_uploaded', 'local_scheduled_backup_cloud',
                (object)['name' => $uploadname, 'status' => $status]));
        }
    }

    /**
     * Inserta fila en historial (si la tabla existe).
     *
     * @param array<string,mixed> $row
     */
    protected function insert_log_row(array $row): void {
        global $DB;

        if (!$DB->get_manager()->table_exists(new \xmldb_table('local_scheduled_backup_cloud_log'))) {
            return;
        }

        $record = new \stdClass();
        $record->courseid = (int) ($row['courseid'] ?? 0);
        $record->courseshortname = substr((string) ($row['courseshortname'] ?? ''), 0, 255);
        $record->originalfilename = substr((string) ($row['originalfilename'] ?? ''), 0, 500);
        $record->remotefilename = substr((string) ($row['remotefilename'] ?? ''), 0, 500);
        $record->filehash = substr((string) ($row['filehash'] ?? ''), 0, 40);
        $record->filesize = (int) ($row['filesize'] ?? 0);
        $record->status = substr((string) ($row['status'] ?? 'unknown'), 0, 32);
        $record->timerun = (int) ($row['timerun'] ?? time());
        $record->timeuploaded = (int) ($row['timeuploaded'] ?? 0);
        $record->remotepath = substr((string) ($row['remotepath'] ?? ''), 0, 1333);
        $msg = $row['errormessage'] ?? '';
        if ($msg !== null && (string) $msg !== '') {
            $record->errormessage = (string) $msg;
        }

        $DB->insert_record('local_scheduled_backup_cloud_log', $record);
    }
}
