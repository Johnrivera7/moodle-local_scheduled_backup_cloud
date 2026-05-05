<?php
/**
 * Informe de subidas a la nube.
 *
 * @package   local_scheduled_backup_cloud
 */

require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('local/scheduled_backup_cloud:configure', context_system::instance());

global $DB, $OUTPUT, $PAGE;

$PAGE->set_url(new moodle_url('/local/scheduled_backup_cloud/report.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('report_title', 'local_scheduled_backup_cloud'));
$PAGE->set_heading(get_string('report_title', 'local_scheduled_backup_cloud'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_title', 'local_scheduled_backup_cloud'));

$linksettings = new moodle_url('/admin/settings.php', ['section' => 'local_scheduled_backup_cloud']);
$linkauto = new moodle_url('/admin/settings.php', ['section' => 'automated']);
echo $OUTPUT->box(
    get_string('report_intro', 'local_scheduled_backup_cloud') . '<br/>' .
    html_writer::link($linksettings, get_string('report_plugin_settings', 'local_scheduled_backup_cloud')) . ' · ' .
    html_writer::link($linkauto, get_string('report_link_automated', 'local_scheduled_backup_cloud'))
);

if (!$DB->get_manager()->table_exists(new xmldb_table('local_scheduled_backup_cloud_log'))) {
    echo $OUTPUT->notification(get_string('report_table_missing', 'local_scheduled_backup_cloud'), 'notifywarning');
    echo $OUTPUT->footer();
    exit;
}

$limit = 300;
$records = $DB->get_records_sql(
    "SELECT * FROM {local_scheduled_backup_cloud_log} ORDER BY id DESC",
    [],
    0,
    $limit
);

$table = new html_table();
$table->head = [
    get_string('report_col_time', 'local_scheduled_backup_cloud'),
    get_string('report_col_course', 'local_scheduled_backup_cloud'),
    get_string('report_col_original', 'local_scheduled_backup_cloud'),
    get_string('report_col_remote', 'local_scheduled_backup_cloud'),
    get_string('report_col_status', 'local_scheduled_backup_cloud'),
    get_string('report_col_size', 'local_scheduled_backup_cloud'),
    get_string('report_col_path', 'local_scheduled_backup_cloud'),
    get_string('report_col_detail', 'local_scheduled_backup_cloud'),
];
$table->attributes['class'] = 'generaltable';
$table->colclasses = array_fill(0, count($table->head), '');

foreach ($records as $r) {
    $statkey = 'status_' . $r->status;
    $statuslabel = get_string_manager()->string_exists($statkey, 'local_scheduled_backup_cloud')
        ? get_string($statkey, 'local_scheduled_backup_cloud')
        : $r->status;

    $timehdr = $r->timerun ? userdate($r->timerun, get_string('strftimedatetimeshort', 'langconfig')) : '–';
    $timeup = $r->timeuploaded ? userdate($r->timeuploaded, get_string('strftimedatetimeshort', 'langconfig')) : '–';

    $coursecell = $r->courseid ? ((string) $r->courseid . ' · ' . s($r->courseshortname)) : s($r->courseshortname);

    $detail = $r->errormessage ?? '';
    $row = new html_table_row([
        $timehdr . html_writer::empty_tag('br') . html_writer::tag('small', $timeup, ['class' => 'text-muted']),
        $coursecell,
        s($r->originalfilename),
        s($r->remotefilename),
        $statuslabel,
        display_size((int) $r->filesize),
        s($r->remotepath),
        $detail !== '' ? s($detail) : '–',
    ]);
    $table->data[] = $row;
}

if (!$records) {
    echo $OUTPUT->notification(get_string('report_empty', 'local_scheduled_backup_cloud'), 'notifymessage');
} else {
    echo html_writer::table($table);
    echo $OUTPUT->container(get_string('report_limit_note', 'local_scheduled_backup_cloud', $limit), 'text-muted mt-3');
}

echo $OUTPUT->footer();
