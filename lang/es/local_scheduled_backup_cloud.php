<?php
/**
 * Cadenas en español.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Copia de seguridad programada (nube)';
$string['plugindesc'] = 'Subida a la nube de los .mbz que genera la Copia de seguridad programada del núcleo. La programación del respaldo está en esa página; aquí: OAuth, rutas y borrado local. Al final de «Copia de seguridad programada» hay un enlace a estos ajustes.';

$string['settings_general'] = 'General';
$string['enabled'] = 'Activar subidas';
$string['enabled_desc'] = 'Si está desactivado, la tarea programada no hace nada.';
$string['provider'] = 'Proveedor en la nube';
$string['provider_desc'] = 'Elija dónde se guardarán los archivos de respaldo subidos.';
$string['provider_google'] = 'Google Drive';
$string['provider_microsoft'] = 'Microsoft (SharePoint / OneDrive vía Graph)';

$string['oauth_heading'] = 'OAuth (Google Drive)';
$string['oauth_intro'] = 'En Google Cloud Console cree credenciales OAuth para aplicación web, habilite la API de Google Drive y registre la URL de redirección que figura abajo. Pegue el ID de cliente y el secreto, guarde los cambios y pulse «Conectar cuenta» para autorizar el acceso a Drive.';
$string['clientid'] = 'ID de cliente';
$string['clientsecret'] = 'Secreto de cliente';
$string['oauth_redirect'] = 'URL de redirección OAuth (cópiela en el registro de la aplicación)';
$string['oauth_connect'] = 'Conectar cuenta';
$string['oauth_connected'] = 'La cuenta en la nube está conectada (token de renovación guardado).';
$string['oauth_not_connected'] = 'Sin conexión. Guarde ID y secreto y pulse «Conectar cuenta».';
$string['tokens_encrypted'] = 'Tokens almacenados (cifrados)';
$string['tokens_encrypted_desc'] = 'Déjelo vacío salvo restauración desde copia. La conexión escribe aquí automáticamente.';

$string['automated_cloud_section_heading'] = 'Subida a la nube (este plugin)';
$string['automated_cloud_section_desc'] = 'OAuth, carpetas en la nube y opciones de subida están en la página enlazada. Los controles anteriores son solo la copia de seguridad programada del núcleo.';
$string['automated_cloud_open_settings'] = 'Abrir ajustes de subida a la nube';

$string['cloud_heading'] = 'Subida a la nube (este plugin)';
$string['cloud_heading_desc'] = 'Active la tarea de subida, elija proveedor y OAuth. Esto no crea los .mbz: el cron del núcleo los genera en la carpeta «Guardar en» de arriba; esta tarea los toma en orden (por fecha del archivo), sube uno, y solo entonces pasa al siguiente.';

$string['paths_heading'] = 'Rutas y nombres en la nube';
$string['paths_heading_desc'] = 'Los archivos .mbz se buscan solo en la carpeta «Guardar en» del respaldo automático (arriba). No se usa otra ruta local: así coincide siempre con lo que Moodle acaba de generar.';
$string['folder_strategy'] = 'Estructura de carpetas remotas';
$string['folder_strategy_desc'] = 'Cómo organizar carpetas en el almacenamiento en la nube.';
$string['folder_site_shortname'] = 'Carpeta del sitio / shortname del curso / archivo';
$string['folder_site_idnumber'] = 'Carpeta del sitio / idnumber del curso / archivo (shortname si falta)';
$string['filename_preview_heading'] = 'Nombre del archivo en la nube';
$string['filename_pattern_fixed_desc'] = 'Formato: shortname_courseid_N_AAAAMMDD-HHMMSS.mbz (N = id numérico del curso; la palabra courseid va literal). Sin shortname: unknown_courseid_N_fecha. Sin curso detectado: backup_courseid_0_fecha. Ejemplo al cargar esta página:';
$string['filename_preview_example_file'] = 'Archivo de ejemplo:';
$string['filename_preview_example_path'] = 'Ruta de ejemplo (sitio / curso / archivo):';
$string['filename_preview_sample_short'] = 'MAT-101';

$string['site_folder_slug'] = 'Nombre de carpeta del sitio (opcional)';
$string['site_folder_slug_desc'] = 'Vacío para generar automáticamente desde wwwroot (nombre seguro por URL de Moodle).';

$string['task_upload'] = 'Subir respaldos de curso al almacenamiento en la nube';

$string['after_upload_heading'] = 'Tras subir a la nube';
$string['after_upload_desc'] = 'Para usar la nube como archivo principal de respaldos y liberar espacio en el servidor.';

$string['delete_local_after_upload'] = 'Borrar la copia local tras subir correctamente';
$string['delete_local_after_upload_desc'] = 'Solo borra el mismo archivo que acaba de subirse: debe estar dentro de la carpeta del respaldo automático, conservar el mismo nombre y la misma huella SHA-1 que antes de la subida. Si algo no coincide, no se elimina (evita borrar otro fichero por error).';

$string['report_title'] = 'Informe de respaldos en la nube';
$string['report_open'] = 'Ver informe de subidas';
$string['report_plugin_settings'] = 'Ajustes del plugin';
$string['report_intro'] = 'Registro de cada archivo procesado: subido, omitido (ya estaba en la nube) o error. Si un curso no tuvo cambios y el núcleo no generó .mbz, no habrá fila aquí.';
$string['report_link_automated'] = 'Copia de seguridad programada';
$string['report_col_time'] = 'Momento / subida';
$string['report_col_course'] = 'Curso';
$string['report_col_original'] = 'Archivo original';
$string['report_col_remote'] = 'Nombre en la nube';
$string['report_col_status'] = 'Estado';
$string['report_col_size'] = 'Tamaño';
$string['report_col_path'] = 'Ruta remota';
$string['report_col_detail'] = 'Detalle';
$string['report_empty'] = 'Aún no hay registros.';
$string['report_limit_note'] = 'Se muestran como máximo {$a} filas más recientes.';
$string['report_table_missing'] = 'Ejecute la actualización de la base de datos para crear la tabla de historial.';

$string['status_upload_ok_deleted'] = 'Subido y borrado en servidor';
$string['status_upload_ok_kept'] = 'Subido (copia local conservada)';
$string['status_upload_ok_delete_failed'] = 'Subido (no se pudo borrar local)';
$string['status_upload_failed'] = 'Error al subir';
$string['status_skipped_duplicate'] = 'Omitido (ya estaba subido)';

$string['skipped_duplicate_detail'] = 'Mismo contenido (SHA-1) registrado como ya enviado a la nube.';
$string['error_remote_folder'] = 'No se pudo crear o localizar la carpeta remota.';
$string['error_upload_api'] = 'La API de subida devolvió error o respuesta vacía.';
$string['error_delete_local'] = 'No se pudo eliminar el archivo local (permisos o ruta).';
$string['error_delete_local_verify'] = 'La copia local no se borró: la verificación de seguridad no pasó o falló unlink (revise permisos). Detalle en el registro / salida del cron.';

$string['privacy:metadata'] = 'El plugin almacena tokens OAuth, un manifiesto de huellas de archivos ya subidos y una tabla de historial con identificadores de curso y nombres de archivo para informes de administración.';
