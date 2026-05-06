<?php
/**
 * Tipos de ajuste de administración (requieren adminlib).
 *
 * No incluir desde lib.php al inicio: Moodle carga lib.php antes de adminlib en el arranque.
 *
 * @package   local_scheduled_backup_cloud
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Encabezado de sección con input oculto para que showhidesettings (hide_if) oculte la fila.
 */
class local_scheduled_backup_cloud_admin_setting_section_heading extends admin_setting {

    public function __construct($name, $heading, $information) {
        $this->nosave = true;
        parent::__construct($name, $heading, $information, '');
    }

    public function get_setting() {
        return true;
    }

    public function get_defaultsetting() {
        return true;
    }

    public function write_setting($data) {
        return '';
    }

    public function output_html($data, $query = '') {
        $title = highlightfast($query, $this->visiblename);
        $descformatted = $this->description !== ''
            ? highlight($query, markdown_to_html($this->description))
            : '';

        $headinghtml = '';
        if ((string) $this->visiblename !== '') {
            $headinghtml = html_writer::tag('h3', $title, ['class' => 'main']);
        }
        $box = '';
        if ($descformatted !== '') {
            $box = html_writer::div($descformatted, 'box generalbox formsettingheading');
        }

        $marker = html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $this->get_full_name(),
            'value' => '1',
        ]);

        return html_writer::div(
            $marker . $headinghtml . $box,
            'form-item row',
            ['id' => 'admin-' . $this->name]
        );
    }
}

/**
 * Descripción en fila de formulario con marcador oculto para hide_if.
 */
class local_scheduled_backup_cloud_admin_setting_form_description extends admin_setting_description {

    public function output_html($data, $query = '') {
        $marker = html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $this->get_full_name(),
            'value' => '1',
        ]);
        $haslabel = ((string) $this->visiblename !== '');
        $title = $haslabel ? highlightfast($query, $this->visiblename) : '';

        return format_admin_setting($this, $title, $marker . $this->description, '', $haslabel, '', null, $query);
    }
}
