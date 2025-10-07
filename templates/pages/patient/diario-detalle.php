<?php
/**
 * Vista de detalle completo de entrada de diario
 * URL: ?view=diario-detalle&entry_id=123
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('write_diary')) {
    wp_die('Acceso denegado');
}

$entry_id = intval($_GET['entry_id'] ?? 0);
$user_id = get_current_user_id();

// Obtener entrada
$entry = \Openmind\Repositories\DiaryRepository::getById($entry_id);

if (!$entry || $entry->author_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada o no tienes permisos para verla.
    </div>';
    return;
}

$is_shared = $entry->is_private == 0;
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;

$mood_emojis = [
    'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
    'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>"
           class="inline-flex items-center gap-2 text-purple-600 text-sm font-medium transition-colors hover:text-purple-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Diario
        </a>
    </div>

    <!-- Entry Card -->
    <div class="bg-white rounded-2xl p-8 shadow-sm border-2 <?php echo $is_shared ? 'border-green-200' : 'border-gray-200'; ?>">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 pb-6 border-b border-gray-200">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold text-gray-900 m-0">
                        <?php echo date('d', strtotime($entry->created_at)); ?>
                        <span class="text-xl font-normal text-gray-500">
                            de <?php echo date('F, Y', strtotime($entry->created_at)); ?>
                        </span>
                    </h1>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span>
                        <i class="fa-solid fa-clock mr-1"></i>
                        <?php echo date('H:i', strtotime($entry->created_at)); ?>
                    </span>
                    <?php if ($entry->mood): ?>
                        <span class="inline-flex items-center gap-2 bg-purple-100 text-purple-700 px-3 py-1.5 rounded-full font-medium">
                            <span class="text-xl"><?php echo $mood_emojis[$entry->mood] ?? ''; ?></span>
                            <?php echo esc_html(ucfirst($entry->mood)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Badge Estado -->
            <?php if ($is_shared): ?>
                <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fa-solid fa-share-nodes"></i>
                    Compartido con <?php echo $psychologist ? esc_html($psychologist->display_name) : 'psicólogo'; ?>
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-semibold">
                    <i class="fa-solid fa-lock"></i>
                    Privado
                </span>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="prose max-w-none mb-8">
            <div class="text-gray-800 leading-relaxed text-lg">
                <?php echo wp_kses_post(wpautop($entry->content)); ?>
            </div>
        </div>

        <!-- Metadata -->
        <?php if (isset($entry->updated_at) && $entry->updated_at !== $entry->created_at): ?>
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 italic m-0">
                    <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                    Última edición: <?php echo date('d/m/Y H:i', strtotime($entry->updated_at)); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200 mt-6">
            <div class="flex gap-3">
                <button
                    class="toggle-share-btn inline-flex items-center gap-2 px-4 py-2 <?php echo $is_shared ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-primary-500 text-white'; ?> rounded-lg border-0 cursor-pointer text-sm font-medium transition-all shadow-none"
                    data-entry-id="<?php echo $entry->id; ?>"
                    data-is-shared="<?php echo $is_shared ? '1' : '0'; ?>">
                    <i class="fa-solid <?php echo $is_shared ? 'fa-lock' : 'fa-share-nodes'; ?>"></i>
                    <?php echo $is_shared ? 'Mover a Privado' : 'Compartir con ' . ($psychologist ? $psychologist->display_name : 'Psicólogo'); ?>
                </button>

                <button
                    class="delete-diary-entry inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-50 rounded-lg border-0 cursor-pointer text-sm font-medium transition-colors hover:bg-red-100 shadow-none"
                    data-entry-id="<?php echo $entry->id; ?>">
                    <i class="fa-solid fa-trash"></i>
                    Eliminar Entrada
                </button>
            </div>

            <a href="<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-purple-600 shadow-none no-underline">
                <i class="fa-solid fa-book-open"></i>
                Ver todas las entradas
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle compartir
        document.querySelector('.toggle-share-btn')?.addEventListener('click', async function() {
            const entryId = this.getAttribute('data-entry-id');
            const isShared = this.getAttribute('data-is-shared') === '1';

            const confirmMsg = isShared
                ? '¿Mover esta entrada a privado? Tu psicólogo ya no podrá verla.'
                : '¿Compartir esta entrada con tu psicólogo?';

            if (!confirm(confirmMsg)) return;

            const formData = new FormData();
            formData.append('action', 'openmind_toggle_share_diary');
            formData.append('nonce', openmindData.nonce);
            formData.append('entry_id', entryId);

            try {
                const response = await fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    if (typeof OpenmindApp !== 'undefined') {
                        OpenmindApp.showNotification(data.data.message, 'success');
                    }
                    setTimeout(() => location.reload(), 500);
                } else {
                    throw new Error(data.data?.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        // Eliminar entrada
        document.querySelector('.delete-diary-entry')?.addEventListener('click', async function() {
            if (!confirm('¿Eliminar esta entrada permanentemente? Esta acción no se puede deshacer.')) return;

            const entryId = this.getAttribute('data-entry-id');

            const formData = new FormData();
            formData.append('action', 'openmind_delete_diary');
            formData.append('nonce', openmindData.nonce);
            formData.append('entry_id', entryId);

            try {
                const response = await fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    if (typeof OpenmindApp !== 'undefined') {
                        OpenmindApp.showNotification('Entrada eliminada', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = '<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>';
                    }, 500);
                } else {
                    throw new Error(data.data?.message);
                }
            } catch (error) {
                alert('Error al eliminar: ' + error.message);
            }
        });
    });
</script>