<?php // src/Admin/PatientsAdmin.php
namespace Openmind\Admin;

class PatientsAdmin {

    public static function init(): void {
        add_action('admin_menu', [self::class, 'registerMenu']);
        add_action('wp_ajax_openmind_admin_unlink_patient', [self::class, 'unlinkPatient']);
        add_filter('set-screen-option', [self::class, 'saveScreenOptions'], 10, 3);
    }

    public static function registerMenu(): void {
        $hook = add_menu_page(
                'Pacientes OpenMind',
                'Pacientes OpenMind',
                'manage_options',
                'openmind-patients',
                [self::class, 'renderPage'],
                'dashicons-groups',
                26
        );

        // CRÍTICO: Hook para agregar screen options cuando la página se carga
        add_action("load-{$hook}", [self::class, 'addScreenOptions']);
    }

    public static function addScreenOptions(): void {
        add_screen_option('per_page', [
                'label' => 'Pacientes por página',
                'default' => 20,
                'option' => 'patients_per_page'
        ]);

        // Instanciar la tabla aquí para que WP la reconozca
        $table = new PatientsListTable();
    }

    public static function saveScreenOptions($status, string $option, $value) {
        // Validar que sea nuestra opción y retornar el valor
        if ($option === 'patients_per_page') {
            return (int) $value;
        }
        return $status;
    }

    public static function renderPage(): void {
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos para acceder a esta página');
        }

        // Cargar WP_List_Table
        if (!class_exists('WP_List_Table')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        $table = new PatientsListTable();
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Pacientes OpenMind</h1>
            <hr class="wp-header-end">

            <form method="get">
                <input type="hidden" name="page" value="openmind-patients">
                <?php $table->search_box('Buscar paciente', 'patient'); ?>
            </form>

            <form method="post">
                <?php
                wp_nonce_field('bulk-patients');
                $table->display();
                ?>
            </form>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Confirmación antes de desvincular
                $('.unlink-patient').on('click', function(e) {
                    e.preventDefault();

                    const patientName = $(this).data('patient-name');
                    const psychologistName = $(this).data('psychologist-name');

                    if (!confirm(`¿Desvincular a ${patientName} de ${psychologistName}?`)) {
                        return;
                    }

                    const patientId = $(this).data('patient-id');
                    const $row = $(this).closest('tr');

                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'openmind_admin_unlink_patient',
                            nonce: '<?php echo wp_create_nonce('openmind_admin_unlink'); ?>',
                            patient_id: patientId
                        },
                        beforeSend: function() {
                            $row.css('opacity', '0.5');
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + (response.data || 'No se pudo desvincular'));
                                $row.css('opacity', '1');
                            }
                        },
                        error: function() {
                            alert('Error de conexión');
                            $row.css('opacity', '1');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    public static function unlinkPatient(): void {
        check_ajax_referer('openmind_admin_unlink', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sin permisos', 403);
        }

        $patient_id = intval($_POST['patient_id'] ?? 0);

        if (!$patient_id) {
            wp_send_json_error('ID de paciente inválido');
        }

        // Obtener psicólogo asignado
        $psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);

        if (!$psychologist_id) {
            wp_send_json_error('El paciente no tiene psicólogo asignado');
        }

        // Eliminar relación
        global $wpdb;
        $deleted = $wpdb->delete(
                $wpdb->prefix . 'openmind_relationships',
                ['patient_id' => $patient_id],
                ['%d']
        );

        if ($deleted) {
            delete_user_meta($patient_id, 'psychologist_id');

            // Desactivar paciente
            update_user_meta($patient_id, 'openmind_status', 'inactive');

            wp_send_json_success('Paciente desvinculado correctamente');
        }

        wp_send_json_error('Error al desvincular paciente');
    }
}