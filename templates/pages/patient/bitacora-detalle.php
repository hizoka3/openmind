<?php
// templates/pages/patient/bitacora-detalle.php
if (!defined('ABSPATH')) exit;

if (!current_user_can('patient')) {
    wp_die('Acceso denegado');
}

$note_id = intval($_GET['note_id'] ?? 0);
$patient_id = get_current_user_id();

// Obtener entrada
$entry = \Openmind\Repositories\SessionNoteRepository::getById($note_id);

// Verificar que la entrada existe y pertenece al paciente
if (!$entry || $entry->patient_id != $patient_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada o no tienes permisos para verla.
    </div>';
    return;
}

// Obtener attachments
$attachments = \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $note_id);
$has_public_content = !empty(trim($entry->public_content ?? ''));
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="?view=bitacora"
           class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Bitácora
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
                    Sesión #<?php echo $entry->session_number; ?>
                </h1>
                <p class="text-gray-600 m-0">
                    <i class="fa-solid fa-calendar mr-2"></i>
                    <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500 m-0">Registrado por</p>
                <p class="text-sm font-semibold text-gray-900 m-0">
                    <?php echo esc_html($entry->psychologist_name); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <?php if ($has_public_content): ?>
            <!-- Retroalimentación del psicólogo -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-comment-dots text-primary-500"></i>
                    Retroalimentación de tu Psicólogo
                </h2>
                <div class="prose prose-sm max-w-none bg-primary-50/50 rounded-lg p-6 border border-primary-100">
                    <?php echo wp_kses_post($entry->public_content); ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Sin contenido público -->
            <div class="bg-primary-50 border border-primary-500 rounded-xl p-6 mb-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-info-circle text-primary-500 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-dark-gray-300 m-0 mb-2">
                            Sesión Registrada
                        </h3>
                        <p class="text-dark-gray-300 m-0 mb-3">
                            Tu psicólogo <strong><?php echo esc_html($entry->psychologist_name); ?></strong>
                            registró esta sesión terapéutica.
                        </p>
                        <p class="text-dark-gray-300 m-0 text-sm">
                            Aún no hay retroalimentación compartida contigo. Puedes preguntarle más detalles
                            vía mensajes o en tu próxima sesión.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Imágenes adjuntas -->
        <?php if (!empty($attachments)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fa-solid fa-images mr-2 text-gray-600"></i>
                    Archivos Adjuntos
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach ($attachments as $att): ?>
                        <a href="<?php echo esc_url($att->file_path); ?>"
                           target="_blank"
                           class="block group">
                            <img src="<?php echo esc_url($att->file_path); ?>"
                                 alt="Adjunto"
                                 class="w-full h-40 object-cover rounded-lg border border-gray-200 group-hover:border-primary-500 transition-colors">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Footer info -->
        <div class="pt-6 border-t border-gray-100">
            <p class="text-xs text-gray-500 m-0">
                <i class="fa-solid fa-clock mr-1"></i>
                Última actualización: <?php echo date('d/m/Y H:i', strtotime($entry->updated_at)); ?>
            </p>
        </div>
    </div>

    <!-- Enlace a mensajes -->
    <div class="mt-6 text-center">
        <a href="?view=mensajeria"
           class="inline-flex items-center gap-2 px-5 py-3 bg-primary-500 text-white rounded-xl text-sm font-semibold transition-all hover:bg-primary-600 shadow-sm hover:shadow-md no-underline">
            <i class="fa-solid fa-comment"></i>
            Enviar mensaje a tu psicólogo
        </a>
    </div>
</div>