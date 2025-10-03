<?php
// templates/pages/psychologist/bitacora.php
if (!defined('ABSPATH')) exit;

// Si hay patient_id, mostrar bitacora-paciente.php
if (isset($_GET['patient_id'])) {
    include OPENMIND_PATH . 'templates/pages/psychologist/bitacora-paciente.php';
    return;
}

// Vista principal: lista de pacientes
$user_id = get_current_user_id();

$patients = get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ]
]);
?>

<div class="tw-max-w-6xl tw-mx-auto">
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-2">
            <i class="fa-solid fa-book tw-mr-3 tw-text-primary-500"></i>
            Bitácora de Pacientes
        </h1>
        <p class="tw-text-gray-600 tw-m-0">
            Revisa y crea entradas de sesión para tus pacientes
        </p>
    </div>

    <?php if (empty($patients)): ?>
        <div class="tw-text-center tw-py-16 tw-text-gray-400">
            <div class="tw-text-6xl tw-mb-4">👥</div>
            <p class="tw-text-lg tw-not-italic tw-text-gray-600">No tienes pacientes asignados.</p>
            <a href="?view=pacientes" class="tw-inline-flex tw-items-center tw-gap-2 tw-mt-4 tw-px-5 tw-py-2.5 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 tw-no-underline">
                <i class="fa-solid fa-user-plus"></i>
                Agregar Paciente
            </a>
        </div>
    <?php else: ?>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <?php foreach ($patients as $patient):
                $latest_entry = \Openmind\Repositories\DiaryRepository::getLatestEntry($patient->ID);
                $total_entries = \Openmind\Repositories\DiaryRepository::countPsychologistEntries($patient->ID);
                ?>
                <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-p-6 tw-transition-all hover:tw-shadow-lg hover:tw--translate-y-1">
                    <!-- Header -->
                    <div class="tw-flex tw-items-start tw-gap-4 tw-mb-4">
                        <?php echo get_avatar($patient->ID, 60, '', '', ['class' => 'tw-rounded-xl tw-border-2 tw-border-gray-100']); ?>
                        <div class="tw-flex-1">
                            <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-m-0 tw-mb-1">
                                <?php echo esc_html($patient->display_name); ?>
                            </h3>
                            <p class="tw-text-sm tw-text-gray-500 tw-m-0">
                                <?php echo $total_entries; ?> sesión<?php echo $total_entries !== 1 ? 'es' : ''; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Última entrada -->
                    <?php if ($latest_entry): ?>
                        <div class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-mb-4">
                            <p class="tw-text-xs tw-text-gray-500 tw-mb-2 tw-m-0">
                                <i class="fa-solid fa-clock tw-mr-1"></i>
                                Última sesión: <?php echo date('d/m/Y', strtotime($latest_entry->created_at)); ?>
                            </p>
                            <p class="tw-text-sm tw-text-gray-700 tw-m-0 tw-line-clamp-2">
                                <?php echo wp_trim_words(strip_tags($latest_entry->content), 15); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="tw-bg-yellow-50 tw-rounded-lg tw-p-4 tw-mb-4 tw-text-center">
                            <p class="tw-text-sm tw-text-yellow-700 tw-m-0">
                                <i class="fa-solid fa-info-circle tw-mr-1"></i>
                                Sin sesiones registradas
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="tw-flex tw-gap-2">
                        <a href="?view=bitacora&patient_id=<?php echo $patient->ID; ?>"
                           class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-200 tw-no-underline">
                            <i class="fa-solid fa-book-open"></i>
                            Ver Bitácoras
                        </a>
                        <a href="?view=bitacora-nueva&patient_id=<?php echo $patient->ID; ?>&return=lista"
                           class="tw-flex-1 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 tw-no-underline">
                            <i class="fa-solid fa-plus"></i>
                            Nueva
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>