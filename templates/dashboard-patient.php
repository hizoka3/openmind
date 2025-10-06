<?php
// templates/dashboard-patient.php
if (!current_user_can('patient')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$current_page = $_GET['view'] ?? 'actividades';

get_header();
?>

    <div class="openmind-dashboard patient">
        <?php include OPENMIND_PATH . 'templates/components/sidebar-patient.php'; ?>

        <div class="dashboard-main">

            <div class="dashboard-content">
                <?php
                // Mapeo de vistas a archivos
                $view_files = [
                        'actividades' => 'actividades.php',
                        'mensajeria' => 'mensajeria.php',
                        'bitacora' => 'bitacora.php',
                        'bitacora-detalle' => 'bitacora-detalle.php',
                        'diario' => 'diario.php',
                        'diario-nuevo' => 'diario-nuevo.php',
                        'diario-detalle' => 'diario-detalle.php',
                        'perfil' => 'perfil.php'
                ];

                // Determinar qué archivo cargar
                if (array_key_exists($current_page, $view_files)) {
                    $file_path = OPENMIND_PATH . 'templates/pages/patient/' . $view_files[$current_page];

                    if (file_exists($file_path)) {
                        include $file_path;
                    } else {
                        // Si no existe en patient, buscar en pages general
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
                    // Vista no reconocida, cargar actividades
                    include OPENMIND_PATH . 'templates/pages/patient/actividades.php';
                }
                ?>
            </div>
        </div>
    </div>

<?php get_footer(); ?>