<?php // templates/dashboard-patient.php
if (!current_user_can('patient')) wp_die('Acceso denegado');

// Control de acceso temprano: verificar ANTES de cargar cualquier template
use Openmind\Core\AccessControl;

$user_id = get_current_user_id();
$current_page = $_GET['view'] ?? 'inicio';

// Páginas que requieren cuenta activa
$protected_pages = ['actividades', 'actividad-detalle', 'mensajeria', 'bitacora', 'bitacora-detalle', 'diario', 'diario-nuevo', 'diario-detalle'];

// Si el paciente intenta acceder a página protegida y está inactivo
if (in_array($current_page, $protected_pages) && !AccessControl::patientCanAccess($current_page)) {
    // Guardar mensaje
    set_transient('openmind_toast_' . $user_id, [
            'message' => 'Debes activar tu cuenta para acceder a esta sección',
            'type' => 'error'
    ], 10);

    // Redirect (aquí SÍ funciona porque es antes de get_header)
    wp_redirect(home_url('/dashboard-paciente/?view=inicio'));
    exit;
}

get_header();
include OPENMIND_PATH . 'templates/components/toast.php';
?>

    <div class="openmind-dashboard patient">
        <?php include OPENMIND_PATH . 'templates/components/sidebar-patient.php'; ?>

        <div class="dashboard-main">
            <div class="dashboard-content">
                <?php
                // Mapeo de vistas a archivos
                $view_files = [
                        'inicio' => 'inicio.php',
                        'actividades' => 'actividades.php',
                        'actividad-detalle' => 'actividad-detalle.php',
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
                    // Vista no reconocida, cargar inicio
                    include OPENMIND_PATH . 'templates/pages/patient/inicio.php';
                }
                ?>
            </div>
        </div>
    </div>

<?php // Bottom Tab Bar - Solo mobile ?>
<?php include OPENMIND_PATH . 'templates/components/bottom-tab-bar.php'; ?>

<?php get_footer(); ?>