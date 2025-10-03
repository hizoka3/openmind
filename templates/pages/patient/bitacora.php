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

<div class="tw-max-w-5xl tw-mx-auto">
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-2">
            <i class="fa-solid fa-book tw-mr-3 tw-text-primary-500"></i>
            Bitácora de Sesiones
        </h1>
        <p class="tw-text-gray-600 tw-m-0">
            Registro de tus sesiones terapéuticas
        </p>
    </div>

    <?php if ($psychologist): ?>
        <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4 tw-mb-6 tw-rounded-lg">
            <div class="tw-flex tw-items-start">
                <i class="fa-solid fa-info-circle tw-text-blue-600 tw-mr-3 tw-mt-1"></i>
                <div>
                    <p class="tw-text-sm tw-text-blue-800 tw-m-0">
                        <strong>Tu psicólogo/a <?php echo esc_html($psychologist->display_name); ?></strong> registra aquí
                        el contenido y avances de cada sesión terapéutica.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-8">
        <div class="tw-bg-gradient-to-br tw-from-blue-50 tw-to-blue-100 tw-p-6 tw-rounded-xl tw-border tw-border-blue-200">
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-12 tw-h-12 tw-bg-blue-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-book tw-text-white tw-text-xl"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-text-blue-700 tw-m-0">Total Sesiones</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-blue-900 tw-m-0"><?php echo $total_entries; ?></p>
                </div>
            </div>
        </div>

        <?php
        $latest_entry = \Openmind\Repositories\DiaryRepository::getLatestEntry($user_id);
        ?>
        <div class="tw-bg-gradient-to-br tw-from-purple-50 tw-to-purple-100 tw-p-6 tw-rounded-xl tw-border tw-border-purple-200">
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-12 tw-h-12 tw-bg-purple-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-calendar tw-text-white tw-text-xl"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-text-purple-700 tw-m-0">Última Sesión</p>
                    <p class="tw-text-lg tw-font-bold tw-text-purple-900 tw-m-0">
                        <?php echo $latest_entry ? date('d/m/Y', strtotime($latest_entry->created_at)) : 'N/A'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Bitácoras -->
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-sm">
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