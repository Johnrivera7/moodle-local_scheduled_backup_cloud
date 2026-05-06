<?php
/**
 * Scheduled backup (cloud) — local_scheduled_backup_cloud
 *
 * @package   local_scheduled_backup_cloud
 * @author    John Rivera Gonzalez <https://github.com/Johnrivera7>
 * @copyright John Rivera Gonzalez
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @link      https://github.com/Johnrivera7/moodle-local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026050603;
$plugin->requires  = 2024100700; // Moodle 4.5+.
$plugin->component = 'local_scheduled_backup_cloud';
$plugin->release   = '1.0.0';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->author    = 'John Rivera Gonzalez';
