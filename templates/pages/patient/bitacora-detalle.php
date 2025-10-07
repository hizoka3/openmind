<?php
// templates/pages/patient/bitacora-detalle.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$note_id = intval($_GET['note_id'] ?? 0);

if (!$note_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada.
    </div>';
    return;
}

$note = \Openmind\Repositories\SessionNoteRepository::getById($note_id);

if (!$note || $note->patient_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        No tienes permisos para ver esta bitácora.
    </div>';
    return;
}

$psychologist = get_userdata($note->psychologist_id);
$attachments = \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $note_id);

$mood_emojis = [
    'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
    'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];

$back_url = add_query_arg('view', 'bitacora', home_url('/dashboard-paciente/'));
?>

<div class="max-w-4xl mx-auto">
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
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-flex items-center gap-2 bg-primary-500 text-white px-4 py-2 rounded-full text-sm font-bold">
                    Sesión #<?php echo $note->session_number; ?>
                </span>

                <?php if ($note->mood_assessment): ?>
                    <span class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 px-4 py-2 rounded-full text-sm font-medium">
                        <span class="text-xl"><?php echo $mood_emojis[$note->mood_assessment] ?? '😐'; ?></span>
                        <?php echo ucfirst($note->mood_assessment); ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
                Bitácora de Sesión
            </h1>
            <div class="flex items-center gap-4 text-gray-600">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor"></i>
                    <?php echo esc_html($psychologist->display_name); ?>
                </span>
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar"></i>
                    <?php echo date('d/m/Y', strtotime($note->created_at)); ?>
                </span>
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-clock"></i>
                    <?php echo date('H:i', strtotime($note->created_at)); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="bg-white rounded-2xl p-8 shadow-sm mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-file-lines text-primary-500"></i>
            Contenido de la Sesión
        </h2>
        <div class="prose prose-sm max-w-none">
            <?php echo wp_kses_post($note->content); ?>
        </div>
    </div>

    <!-- Próximos pasos -->
    <?php if (!empty($note->next_steps)): ?>
        <div class="bg-green-50 border-l-4 border-green-400 rounded-r-xl p-6 shadow-sm mb-6">
            <h2 class="text-lg font-bold text-green-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-list-check"></i>
                Próximos Pasos
            </h2>
            <div class="text-green-900 whitespace-pre-wrap">
                <?php echo nl2br(esc_html($note->next_steps)); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Adjuntos -->
    <?php if (!empty($attachments)): ?>
        <div class="bg-white rounded-2xl p-8 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-paperclip text-primary-500"></i>
                Adjuntos (<?php echo count($attachments); ?>)
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($attachments as $att): ?>
                    <a href="<?php echo esc_url($att->file_path); ?>"
                       target="_blank"
                       class="block relative rounded-lg overflow-hidden group shadow-sm hover:shadow-md transition-shadow">
                        <img src="<?php echo esc_url($att->file_path); ?>"
                             alt="Adjunto"
                             class="w-full h-48 object-cover transition-transform group-hover:scale-105">
                        <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity pointer-events-none"></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>