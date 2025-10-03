<?php // templates/dashboard-psychologist.php
if (!current_user_can('manage_patients')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$current_page = $_GET['view'] ?? 'inicio';

get_header();
?>

    <div class="openmind-dashboard psychologist">
        <?php include OPENMIND_PATH . 'templates/components/sidebar-psychologist.php'; ?>

        <div class="dashboard-main">
            <?php
            $role = 'psychologist';
            include OPENMIND_PATH . 'templates/components/header.php';
            ?>

            <div class="dashboard-content">
                <?php
                // Lista de vistas permitidas
                $allowed_views = [
                        'inicio',
                        'pacientes',
                        'actividades',
                        'mensajeria',
                        'bitacora',
                        'bitacora-nueva',      // NUEVO
                        'bitacora-editar',     // NUEVO
                        'perfil'
                ];

                // Determinar qué archivo cargar
                if (in_array($current_page, $allowed_views)) {
                    $page_file = OPENMIND_PATH . "templates/pages/psychologist/{$current_page}.php";

                    if (file_exists($page_file)) {
                        include $page_file;
                    } else {
                        include OPENMIND_PATH . 'templates/pages/psychologist/inicio.php';
                    }
                } else {
                    include OPENMIND_PATH . 'templates/pages/psychologist/inicio.php';
                }
                ?>
            </div>
        </div>
    </div>

<?php get_footer(); ?>