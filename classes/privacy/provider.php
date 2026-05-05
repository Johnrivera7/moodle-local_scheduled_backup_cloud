<?php
/**
 * Proveedor de privacidad.
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\null_provider;

/**
 * Este plugin guarda tokens OAuth y metadatos de configuración en config_plugins.
 */
class provider implements null_provider {

    /**
     * @return array<string, string>
     */
    public static function get_reason(): array {
        return [
            'privacy:metadata' => 'privacy:metadata',
        ];
    }
}
