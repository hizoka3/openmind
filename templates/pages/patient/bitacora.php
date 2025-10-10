<?php
// templates/pages/patient/bitacora.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;

// Paginación
$per_page = 10;
$current_page = max(1, intval($_GET['paged'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Obtener bitácoras escritas por el psicólogo
$entries = \Openmind\Repositories\SessionNoteRepository::getByPatient($user_id, $per_page, $offset);
$total_entries = \Openmind\Repositories\SessionNoteRepository::countByPatient($user_id);

$base_url = add_query_arg(['view' => 'bitacora'], home_url('/dashboard-paciente/'));
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-normal text-gray-900 m-0 mb-2">
            Bitácora de Sesiones
        </h1>
        <p class="text-gray-600 m-0">
            Registro de tus sesiones terapéuticas
        </p>
    </div>

    <?php if ($psychologist): ?>
        <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fa-solid fa-info-circle text-primary-500 mr-3"></i>
                <div>
                    <p class="text-sm text-dark-gray-300-500 m-0">
                        <strong>Tu psicólogo/a <?php echo esc_html($psychologist->display_name); ?></strong> registra aquí
                        el contenido y avances de cada sesión terapéutica.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($entries)): ?>
        <div class="text-center py-16 text-gray-400">
            <div class="text-6xl mb-4">📖</div>
            <p class="text-lg not-italic text-gray-600">
                Aún no hay sesiones registradas.
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Las entradas aparecerán aquí después de cada sesión con tu psicólogo/a.
            </p>
        </div>
    <?php else: ?>
        <?php
        $args = [
                'patient_id' => $user_id,
                'entries' => $entries,
                'total' => $total_entries,
                'per_page' => $per_page,
                'current_page' => $current_page,
                'show_actions' => false,
                'context' => 'patient',
                'base_url' => $base_url
        ];
        include OPENMIND_PATH . 'templates/components/bitacora-list.php';
        ?>
    <?php endif; ?>
</div>