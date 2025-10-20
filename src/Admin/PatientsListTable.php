<?php // src/Admin/PatientsListTable.php
namespace Openmind\Admin;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PatientsListTable extends \WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'paciente',
            'plural' => 'pacientes',
            'ajax' => false
        ]);
    }

    public function get_columns(): array {
        return [
            'name' => 'Paciente',
            'email' => 'Correo',
            'psychologist' => 'Psicólogo',
            'status' => 'Estado',
            'registered' => 'Registro',
            'actions' => 'Acciones'
        ];
    }

    public function prepare_items(): void {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Query de pacientes
        $args = ['role' => 'patient', 'number' => $per_page, 'offset' => ($current_page - 1) * $per_page];

        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['display_name', 'user_email'];
        }

        $user_query = new \WP_User_Query($args);
        $patients = $user_query->get_results();
        $total_items = $user_query->get_total();

        $this->items = $patients;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);

        $this->_column_headers = [$this->get_columns(), [], []];
    }

    public function column_name($patient): string {
        return '<strong>' . esc_html($patient->display_name) . '</strong>';
    }

    public function column_email($patient): string {
        return '<a href="mailto:' . esc_attr($patient->user_email) . '">' . esc_html($patient->user_email) . '</a>';
    }

    public function column_psychologist($patient): string {
        $psychologist_id = get_user_meta($patient->ID, 'psychologist_id', true);

        if (!$psychologist_id) {
            return '<span style="color: #999;">Sin asignar</span>';
        }

        $psychologist = get_userdata($psychologist_id);
        return $psychologist ? esc_html($psychologist->display_name) : '<span style="color: #999;">Sin asignar</span>';
    }

    public function column_status($patient): string {
        $status = get_user_meta($patient->ID, 'openmind_status', true);

        if ($status === 'active') {
            return '<span style="color: #46b450; font-weight: 500;">● Activo</span>';
        }

        return '<span style="color: #f0b849; font-weight: 500;">● Inactivo</span>';
    }

    public function column_registered($patient): string {
        return date('d/m/Y', strtotime($patient->user_registered));
    }

    public function column_actions($patient): string {
        $psychologist_id = get_user_meta($patient->ID, 'psychologist_id', true);

        if (!$psychologist_id) {
            return '—';
        }

        $psychologist = get_userdata($psychologist_id);

        return sprintf(
            '<a href="#" class="unlink-patient button button-small" data-patient-id="%d" data-patient-name="%s" data-psychologist-name="%s">Desvincular</a>',
            $patient->ID,
            esc_attr($patient->display_name),
            esc_attr($psychologist ? $psychologist->display_name : 'psicólogo')
        );
    }

    public function column_default($patient, $column_name) {
        return '—';
    }
}