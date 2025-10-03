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
$entries = \Openmind\Repositories\DiaryRepository::getPsychologistEntries($user_id, $per_page, $offset);
$total_entries = \Openmind\Repositories\DiaryRepository::countPsychologistEntries($user_id);

$base_url = add_query_arg(['view' => 'bitacora'], home_url('/dashboard-paciente/'));
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-book mr-3 text-primary-500"></i>
            Bitácora de Sesiones
        </h1>
        <p class="text-gray-600 m-0">
            Registro de tus sesiones terapéuticas
        </p>
    </div>

    <?php if ($psychologist): ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-info-circle text-blue-600 mr-3 mt-1"></i>
                <div>
                    <p class="text-sm text-blue-800 m-0">
                        <strong>Tu psicólogo/a <?php echo esc_html($psychologist->display_name); ?></strong> registra aquí
                        el contenido y avances de cada sesión terapéutica.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-book text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-blue-700 m-0">Total Sesiones</p>
                    <p class="text-3xl font-bold text-blue-900 m-0"><?php echo $total_entries; ?></p>
                </div>
            </div>
        </div>

        <?php
        $latest_entry = \Openmind\Repositories\DiaryRepository::getLatestEntry($user_id);
        ?>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-purple-700 m-0">Última Sesión</p>
                    <p class="text-lg font-bold text-purple-900 m-0">
                        <?php echo $latest_entry ? date('d/m/Y', strtotime($latest_entry->created_at)) : 'N/A'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Bitácoras -->
    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <?php
        $args = [
                'patient_id' => $user_id,
                'entries' => $entries,
                'total' => $total_entries,
                'per_page' => $per_page,
                'current_page' => $current_page,
                'show_actions' => false, // El paciente NO puede editar/eliminar
                'context' => 'patient',
                'base_url' => $base_url
        ];
        include OPENMIND_PATH . 'templates/components/bitacora-list.php';
        ?>
    </div>
</div>