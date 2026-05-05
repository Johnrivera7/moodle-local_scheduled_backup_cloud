<?php
/**
 * Database upgrades.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_scheduled_backup_cloud_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026050600) {
        $table = new xmldb_table('local_scheduled_backup_cloud_log');

        if (!$dbman->table_exists($table)) {
            $table->add_field(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
            $table->add_field(new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->add_field(new xmldb_field('courseshortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null));
            $table->add_field(new xmldb_field('originalfilename', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, null));
            $table->add_field(new xmldb_field('remotefilename', XMLDB_TYPE_CHAR, '500', null, XMLDB_NOTNULL, null, null));
            $table->add_field(new xmldb_field('filehash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null));
            $table->add_field(new xmldb_field('filesize', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0'));
            $table->add_field(new xmldb_field('status', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, 'unknown'));
            $table->add_field(new xmldb_field('timerun', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->add_field(new xmldb_field('timeuploaded', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->add_field(new xmldb_field('remotepath', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null));
            $table->add_field(new xmldb_field('errormessage', XMLDB_TYPE_TEXT, 'medium', null, null, null, null));

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('timerun_ix', XMLDB_INDEX_NOTUNIQUE, ['timerun']);
            $table->add_index('courseid_ix', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
            $table->add_index('status_ix', XMLDB_INDEX_NOTUNIQUE, ['status']);

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026050600, 'local', 'scheduled_backup_cloud');
    }

    return true;
}
