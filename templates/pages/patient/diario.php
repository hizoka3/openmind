<?php // templates/pages/patient/diario.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Paginación
$per_page = 10;
$current_page = max(1, intval($_GET['paged'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Obtener entradas con paginación
$entries = \Openmind\Repositories\DiaryRepository::getByPatient($user_id, $per_page, false, $offset);
$total_entries = \Openmind\Repositories\DiaryRepository::countByPatient($user_id, false);
$total_pages = ceil($total_entries / $per_page);

$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;

$mood_emojis = [
        'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
        'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-book-open mr-3 text-purple-500"></i>
            Mi Diario Personal
        </h1>
        <p class="text-gray-600 m-0">
            Tu espacio privado para registrar pensamientos y emociones día a día
        </p>
    </div>

    <!-- Info Box -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-400 p-4 mb-6 rounded-lg">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-lock text-purple-600 text-xl mt-1"></i>
            <div class="flex-1">
                <p class="text-sm font-semibold text-purple-900 m-0 mb-1">
                    Privado por defecto
                </p>
                <p class="text-sm text-purple-800 m-0">
                    Tus entradas son privadas. Puedes compartir entradas específicas con
                    <?php echo $psychologist ? '<strong>' . esc_html($psychologist->display_name) . '</strong>' : 'tu psicólogo'; ?>.
                </p>
            </div>
        </div>
    </div>

    <!-- Botón Nueva Entrada -->
    <div class="mb-8">
        <a
                href="<?php echo add_query_arg('view', 'diario-nuevo', home_url('/dashboard-paciente/')); ?>"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg border-0 text-sm font-semibold transition-all hover:from-purple-600 hover:to-pink-600 hover:-translate-y-0.5 hover:shadow-lg shadow-none no-underline">
            <i class="fa-solid fa-plus"></i>
            Nueva Entrada
        </a>
    </div>

    <!-- Timeline de Entradas -->
    <?php if (empty($entries)): ?>
        <div class="text-center py-16">
            <div class="text-6xl mb-4">✍️</div>
            <p class="text-lg text-gray-600 mb-6 m-0">
                Aún no has escrito ninguna entrada personal.
            </p>
            <a
                    href="<?php echo add_query_arg('view', 'diario-nuevo', home_url('/dashboard-paciente/')); ?>"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-500 text-white rounded-lg border-0 text-sm font-medium transition-all hover:bg-purple-600 shadow-none no-underline">
                <i class="fa-solid fa-pen"></i>
                Comenzar a escribir
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php
            $last_date = '';

            foreach ($entries as $entry):
                $is_shared = $entry->is_private == 0;
                $entry_date = date('Y-m-d', strtotime($entry->created_at));
                $show_date_separator = $entry_date !== $last_date;
                $last_date = $entry_date;

                // Preview corto (150 caracteres)
                $preview = wp_trim_words(strip_tags($entry->content), 25, '...');
                ?>

                <?php if ($show_date_separator): ?>
                <!-- Separador de Fecha -->
                <div class="flex items-center gap-4 mt-8 mb-4">
                    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent flex-1"></div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">
                            <?php echo date('d', strtotime($entry->created_at)); ?>
                        </div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <?php echo date('M Y', strtotime($entry->created_at)); ?>
                        </div>
                    </div>
                    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent flex-1"></div>
                </div>
            <?php endif; ?>

                <!-- Entry Card Horizontal -->
                <div class="group relative">
                    <!-- Línea de Timeline -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b <?php echo $is_shared ? 'from-green-400 to-green-500' : 'from-purple-400 to-purple-500'; ?> rounded-full"></div>

                    <!-- Card -->
                    <div class="ml-6 bg-white border-2 <?php echo $is_shared ? 'border-green-100 hover:border-green-200' : 'border-gray-100 hover:border-gray-200'; ?> rounded-xl p-5 transition-all hover:shadow-lg">
                        <div class="flex gap-4">
                            <!-- Left: Time & Mood -->
                            <div class="flex-shrink-0 text-center">
                                <div class="text-2xl font-bold text-gray-900">
                                    <?php echo date('H:i', strtotime($entry->created_at)); ?>
                                </div>
                                <?php if ($entry->mood): ?>
                                    <div class="mt-2 text-4xl">
                                        <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                    </div>
                                    <div class="text-xs font-medium text-gray-600 mt-1">
                                        <?php echo esc_html(ucfirst($entry->mood)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Center: Content Preview -->
                            <div class="flex-1 min-w-0">
                                <!-- Preview Text -->
                                <p class="text-gray-700 leading-relaxed m-0 mb-3">
                                    <?php echo esc_html($preview); ?>
                                </p>

                                <!-- Actions -->
                                <div class="flex items-center gap-3 flex-wrap">
                                    <a
                                            href="<?php echo add_query_arg(['view' => 'diario-detalle', 'entry_id' => $entry->id], home_url('/dashboard-paciente/')); ?>"
                                            class="inline-flex items-center gap-1 text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors no-underline">
                                        <i class="fa-solid fa-book-open"></i>
                                        Leer entrada completa
                                    </a>

                                    <span class="text-gray-300">•</span>

                                    <button
                                            class="toggle-share-btn inline-flex items-center gap-1 text-sm font-medium <?php echo $is_shared ? 'text-gray-600 hover:text-gray-700' : 'text-blue-600 hover:text-blue-700'; ?> transition-colors border-0 bg-transparent cursor-pointer p-0 shadow-none"
                                            data-entry-id="<?php echo $entry->id; ?>"
                                            data-is-shared="<?php echo $is_shared ? '1' : '0'; ?>">
                                        <i class="fa-solid <?php echo $is_shared ? 'fa-lock' : 'fa-share-nodes'; ?>"></i>
                                        <?php echo $is_shared ? 'Mover a privado' : 'Compartir'; ?>
                                    </button>

                                    <span class="text-gray-300">•</span>

                                    <button
                                            class="delete-diary-entry inline-flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-700 transition-colors border-0 bg-transparent cursor-pointer p-0 shadow-none"
                                            data-entry-id="<?php echo $entry->id; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                        Eliminar
                                    </button>
                                </div>
                            </div>

                            <!-- Right: Badge Estado -->
                            <div class="flex-shrink-0">
                                <?php if ($is_shared): ?>
                                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-semibold">
                                        <i class="fa-solid fa-share-nodes"></i>
                                        Compartido
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-semibold">
                                        <i class="fa-solid fa-lock"></i>
                                        Privado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center items-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>"
                       class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline">
                        <i class="fa-solid fa-chevron-left"></i>
                        Anterior
                    </a>
                <?php endif; ?>

                <div class="flex gap-1">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i === $current_page): ?>
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-purple-500 text-white rounded-lg text-sm font-medium shadow-none">
                                <?php echo $i; ?>
                            </span>
                        <?php elseif ($i === 1 || $i === $total_pages || abs($i - $current_page) <= 2): ?>
                            <a href="<?php echo add_query_arg('paged', $i); ?>"
                               class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline">
                                <?php echo $i; ?>
                            </a>
                        <?php elseif (abs($i - $current_page) === 3): ?>
                            <span class="inline-flex items-center justify-center w-10 h-10 text-gray-400">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?php echo add_query_arg('paged', $current_page + 1); ?>"
                       class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline">
                        Siguiente
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="mt-4 text-center text-sm text-gray-500">
                Mostrando <?php echo (($current_page - 1) * $per_page) + 1; ?>
                - <?php echo min($current_page * $per_page, $total_entries); ?>
                de <?php echo $total_entries; ?> entradas
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle compartir/descompartir
        document.querySelectorAll('.toggle-share-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
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
        });

        // Eliminar entrada
        document.querySelectorAll('.delete-diary-entry').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('¿Eliminar esta entrada? Esta acción no se puede deshacer.')) return;

                const entryId = this.getAttribute('data-entry-id');
                const entryCard = this.closest('.group');

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
                        entryCard.style.opacity = '0';
                        entryCard.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            entryCard.remove();
                            const remainingEntries = document.querySelectorAll('.group');
                            if (remainingEntries.length === 0) {
                                location.reload();
                            }
                        }, 300);

                        if (typeof OpenmindApp !== 'undefined') {
                            OpenmindApp.showNotification('Entrada eliminada', 'success');
                        }
                    } else {
                        throw new Error(data.data?.message);
                    }
                } catch (error) {
                    alert('Error al eliminar: ' + error.message);
                }
            });
        });
    });
</script>