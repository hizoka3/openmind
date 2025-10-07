<?php // templates/dashboard-psychologist.php
if (!current_user_can('manage_patients')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$current_page = $_GET['view'] ?? 'inicio';

get_header();
?>

    <div class="openmind-dashboard psychologist">
        <?php include OPENMIND_PATH . 'templates/components/sidebar-psychologist.php'; ?>

        <div class="dashboard-main">

            <div class="dashboard-content">
                <?php
                // Mapeo de vistas a archivos
                $view_files = [
                        'inicio' => 'inicio.php',
                        'pacientes' => 'pacientes.php',
                        'paciente-detalle' => 'paciente-detalle.php',
                        'actividades' => 'actividades.php',
                        'mensajeria' => 'mensajeria.php',
                        'bitacora' => 'bitacora.php',
                        'bitacora-nueva' => 'bitacora-nueva.php',
                        'bitacora-editar' => 'bitacora-editar.php',
                        'bitacora-detalle' => 'bitacora-detalle.php',
                        'diario-detalle' => 'diario-detalle.php',
                        'perfil' => 'perfil.php'
                ];

                // Determinar qué archivo cargar
                if (array_key_exists($current_page, $view_files)) {
                    $file_path = OPENMIND_PATH . 'templates/pages/psychologist/' . $view_files[$current_page];

                    if (file_exists($file_path)) {
                        include $file_path;
                    } else {
                        // Si no existe en psychologist, buscar en pages general
                        $general_path = OPENMIND_PATH . 'templates/pages/' . $view_files[$current_page];

                        if (file_exists($general_path)) {
                            include $general_path;
                        } else {
                            echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        Página no encontrada: ' . esc_html($current_page) . '
                    </div>';
                        }
                    }
                } else {
                    // Vista no reconocida, cargar inicio
                    include OPENMIND_PATH . 'templates/pages/psychologist/inicio.php';
                }
                ?>
            </div>
        </div>
    </div>

<?php get_footer(); ?>