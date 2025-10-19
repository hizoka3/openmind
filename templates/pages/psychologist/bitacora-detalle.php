<?php
// templates/pages/psychologist/bitacora-detalle.php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_patients')) {
    wp_die('Acceso denegado');
}

$note_id = intval($_GET['note_id'] ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$psychologist_id = get_current_user_id();

if (!$note_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada.
    </div>';
    return;
}

$note = \Openmind\Repositories\SessionNoteRepository::getById($note_id);

if (!$note || $note->psychologist_id != $psychologist_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        No tienes permisos para ver esta bitácora.
    </div>';
    return;
}

$patient = get_userdata($note->patient_id);
$attachments = \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $note_id);
$has_public_content = !empty(trim($note->public_content ?? ''));

$mood_emojis = [
        'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
        'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];

$back_url = $patient_id
        ? add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
        : add_query_arg('view', 'bitacora', home_url('/dashboard-psicologo/'));

$edit_url = add_query_arg([
        'view' => 'bitacora-editar',
        'note_id' => $note_id,
        'patient_id' => $note->patient_id
], home_url('/dashboard-psicologo/'));
?>

<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="<?php echo esc_url($back_url); ?>"
           class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Bitácora
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="inline-flex items-center gap-2 bg-primary-500 text-white px-4 py-2 rounded-full text-sm font-bold">
                        Sesión #<?php echo $note->session_number; ?>
                    </span>

                    <?php if ($note->mood_assessment): ?>
                        <span class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 px-4 py-2 rounded-full text-sm font-medium">
                            <span class="text-xl"><?php echo $mood_emojis[$note->mood_assessment] ?? ''; ?></span>
                            <?php echo ucfirst($note->mood_assessment); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 m-0 mb-2">
                    Bitácora: <?php echo esc_html($patient->display_name); ?>
                </h1>
                <p class="text-gray-600 m-0">
                    <i class="fa-solid fa-calendar mr-1"></i>
                    <?php echo date('d/m/Y H:i', strtotime($note->created_at)); ?>
                </p>
            </div>

            <a href="<?php echo esc_url($edit_url); ?>"
               class="px-5 py-2.5 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold transition-all hover:bg-blue-100 no-underline">
                <i class="fa-solid fa-pen mr-2"></i>
                Editar
            </a>
        </div>
    </div>

    <!-- Notas Privadas -->
    <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-lock text-gray-500"></i>
            Notas Privadas
            <span class="text-xs font-normal text-gray-500">(Solo tú puedes ver esto)</span>
        </h2>
        <div class="prose prose-sm max-w-none bg-gray-50 rounded-lg p-6 border border-gray-200">
            <?php echo wp_kses_post($note->private_notes); ?>
        </div>
    </div>

    <!-- Retroalimentación para el Paciente -->
    <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-eye text-primary-500"></i>
            Retroalimentación para el Paciente
            <span class="text-xs font-normal text-gray-500">(Visible para el paciente)</span>
        </h2>

        <?php if ($has_public_content): ?>
            <div class="prose prose-sm max-w-none bg-primary-50/50 rounded-lg p-6 border border-primary-200">
                <?php echo wp_kses_post($note->public_content); ?>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-700 m-0">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    No has compartido retroalimentación con el paciente para esta sesión.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Imágenes Adjuntas -->
    <?php if (!empty($attachments)): ?>
        <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fa-solid fa-images mr-2 text-gray-600"></i>
                Archivos Adjuntos
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
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

    <!-- Footer -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500 m-0">
                <i class="fa-solid fa-clock mr-1"></i>
                Última actualización: <?php echo date('d/m/Y H:i', strtotime($note->updated_at)); ?>
            </p>
            <button type="button"
                    onclick="deleteSessionNote(<?php echo $note->id; ?>)"
                    class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium transition-all hover:bg-red-100">
                <i class="fa-solid fa-trash mr-1"></i>
                Eliminar Bitácora
            </button>
        </div>
    </div>
</div>

<script>
    function deleteSessionNote(noteId) {
        if (!confirm('¿Eliminar esta entrada de bitácora? Esta acción no se puede deshacer.')) return;

        fetch(openmindData.ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'openmind_delete_session_note',
                nonce: openmindData.nonce,
                note_id: noteId
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Toast.show(data.data.message || 'Bitácora eliminada', 'success');
                    window.location.href = '<?php echo esc_js($back_url); ?>';
                } else {
                    Toast.show(data.data || 'Error al eliminar', 'error');
                }
            });
    }
</script>