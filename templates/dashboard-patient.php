<?php // templates/dashboard-patient.php
if (!current_user_can('view_activities')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$current_page = $_GET['view'] ?? 'actividades';

get_header();
?>

    <div class="openmind-dashboard patient">
        <?php include OPENMIND_PATH . 'templates/components/sidebar-patient.php'; ?>

        <div class="dashboard-main">
            <?php
            $role = 'patient';
            include OPENMIND_PATH . 'templates/components/header.php';
            ?>

            <div class="dashboard-content">
                <?php
                $page_file = OPENMIND_PATH . "templates/pages/patient/{$current_page}.php";

                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include OPENMIND_PATH . 'templates/pages/patient/actividades.php';
                }
                ?>
            </div>
        </div>
    </div>

<?php get_footer(); ?>