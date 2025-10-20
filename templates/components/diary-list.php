<?php
// templates/components/diary-list.php
if (!defined('ABSPATH')) exit;

/**
 * Componente reutilizable para listar entradas de diario
 *
 * @param array $args {
 *     @type int    $user_id          ID del usuario dueño del diario (requerido)
 *     @type bool   $show_shared_only Mostrar solo compartidas (default: false)
 *     @type bool   $is_psychologist  Vista de psicólogo (default: false)
 *     @type int    $per_page         Entradas por página (default: 10)
 * }
 */
$args = wp_parse_args($args ?? [], [
        'user_id' => 0,
        'show_shared_only' => false,
        'is_psychologist' => false,
        'per_page' => 10
]);

if (!$args['user_id']) return;

// Extraer args a variables locales
extract($args);

// Paginación
$current_page = max(1, intval($_GET['paged'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Obtener entradas
// IMPORTANTE: Si es psicólogo, necesitamos solo compartidas (is_private = 0)
// Pero getByPatient() espera $private_only, que es lo OPUESTO
if ($is_psychologist) {
    // Para psicólogo: usar getSharedByPatient que filtra is_private = 0
    $entries = \Openmind\Repositories\DiaryRepository::getSharedByPatient($user_id, $per_page);
    $total_entries = count($entries); // Aproximado, necesitaríamos un count específico
} else {
    // Para paciente: mostrar todas sus entradas (privadas y compartidas)
    $entries = \Openmind\Repositories\DiaryRepository::getByPatient(
            $user_id,
            $per_page,
            false, // private_only = false para mostrar todas
            $offset
    );
    $total_entries = \Openmind\Repositories\DiaryRepository::countByPatient($user_id, false);
}

$total_pages = ceil($total_entries / $per_page);

$mood_emojis = [
        'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
        'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];
?>

<?php if (empty($entries)): ?>
    <div class="bg-white rounded-xl shadow-sm p-8 md:p-16 text-center">
        <p class="text-base md:text-lg text-gray-600 mb-6 m-0">
            <?php echo $is_psychologist
                    ? 'Este paciente aún no ha compartido entradas.'
                    : 'Aún no has escrito ninguna entrada personal.'; ?>
        </p>
        <?php if (!$is_psychologist): ?>
            <a href="<?php echo add_query_arg('view', 'diario-nuevo', home_url('/dashboard-paciente/')); ?>"
               class="inline-flex items-center gap-2 px-4 md:px-5 py-2 md:py-2.5 bg-primary-500 text-white rounded-lg border-0 text-sm font-medium no-underline">
                <i class="fa-solid fa-pen"></i>
                Comenzar a escribir
            </a>
        <?php endif; ?>
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
            $preview = wp_trim_words(strip_tags($entry->content), 25, '...');

            // URL de detalle unificada para ambos roles
            if ($is_psychologist) {
                $detail_url = add_query_arg(['view' => 'diario-detalle', 'entry_id' => $entry->id], home_url('/dashboard-psicologo/'));
            } else {
                $detail_url = add_query_arg(['view' => 'diario-detalle', 'entry_id' => $entry->id], home_url('/dashboard-paciente/'));
            }
            ?>

            <?php if ($show_date_separator): ?>
            <div class="flex items-center gap-3 md:gap-4 mt-6 md:mt-8 mb-3 md:mb-4">
                <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent flex-1"></div>
                <div class="text-center flex-shrink-0">
                    <div class="text-xl md:text-2xl font-medium text-gray-900">
                        <?php echo date('d', strtotime($entry->created_at)); ?>
                    </div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <?php echo date('M Y', strtotime($entry->created_at)); ?>
                    </div>
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent flex-1"></div>
            </div>
        <?php endif; ?>

            <div class="group relative">
                <div class="bg-white border-2 <?php echo $is_shared ? 'border-gray-100 hover:border-gray-200' : 'border-gray-100 hover:border-gray-200'; ?> rounded-xl p-4 md:p-5 transition-all hover:shadow-lg">
                    <div class="flex flex-col md:flex-row gap-3 md:gap-4">
                        <!-- Time & Mood -->
                        <div class="flex md:flex-col items-center md:items-start gap-3 md:gap-0 flex-shrink-0">
                            <div class="text-lg md:text-xl font-normal text-gray-900">
                                <?php echo date('H:i', strtotime($entry->created_at)); ?>
                            </div>
                            <?php if ($entry->mood): ?>
                                <div class="text-3xl md:text-4xl md:mt-2">
                                    <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                </div>
                                <div class="text-xs font-medium text-gray-600 md:mt-1">
                                    <?php echo esc_html(ucfirst($entry->mood)); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Badge Estado en mobile -->
                            <div class="md:hidden ml-auto">
                                <?php if ($is_shared): ?>
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                                        <i class="fa-solid fa-share-nodes"></i>
                                        Compartido
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                                        <i class="fa-solid fa-lock"></i>
                                        Privado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content Preview -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm md:text-base text-gray-700 leading-relaxed m-0 mb-3">
                                <?php echo esc_html($preview); ?>
                            </p>

                            <div class="flex items-center gap-2 md:gap-3 flex-wrap">
                                <a href="<?php echo esc_url($detail_url); ?>"
                                   class="inline-flex items-center gap-1 text-xs md:text-sm font-medium text-primary-500 no-underline">
                                    Leer diario
                                </a>

                                <?php if (!$is_psychologist): ?>
                                    <span class="text-gray-300 hidden md:inline">•</span>
                                    <button class="toggle-share-btn inline-flex items-center gap-1 text-xs md:text-sm font-medium <?php echo $is_shared ? 'text-gray-600 hover:text-gray-700' : 'text-blue-600 hover:text-blue-700'; ?> transition-colors border-0 bg-transparent cursor-pointer p-0 shadow-none whitespace-nowrap"
                                            data-entry-id="<?php echo $entry->id; ?>"
                                            data-is-shared="<?php echo $is_shared ? '1' : '0'; ?>">
                                        <i class="fa-solid <?php echo $is_shared ? 'fa-lock' : 'fa-share-nodes'; ?>"></i>
                                        <span class="hidden sm:inline"><?php echo $is_shared ? 'Mover a privado' : 'Compartir'; ?></span>
                                        <span class="sm:hidden"><?php echo $is_shared ? 'Privado' : 'Compartir'; ?></span>
                                    </button>

                                    <span class="text-gray-300 hidden md:inline">•</span>
                                    <button class="delete-diary-entry inline-flex items-center gap-1 text-xs md:text-sm font-medium text-red-600 hover:text-red-700 transition-colors border-0 bg-transparent cursor-pointer p-0 shadow-none whitespace-nowrap"
                                            data-entry-id="<?php echo $entry->id; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                        <span class="hidden sm:inline">Eliminar</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Badge Estado - solo desktop -->
                        <div class="hidden md:flex flex-shrink-0 items-start">
                            <?php if ($is_shared): ?>
                                <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap">
                                    <i class="fa-solid fa-share-nodes"></i>
                                    Compartido
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap">
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
        <div class="mt-8 flex flex-col md:flex-row justify-center items-center gap-3 md:gap-2">
            <?php if ($current_page > 1): ?>
                <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>"
                   class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline w-full md:w-auto">
                    <i class="fa-solid fa-chevron-left"></i>
                    Anterior
                </a>
            <?php endif; ?>

            <div class="flex gap-1 overflow-x-auto max-w-full">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $current_page): ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-purple-500 text-white rounded-lg text-sm font-medium shadow-none flex-shrink-0">
                            <?php echo $i; ?>
                        </span>
                    <?php elseif ($i === 1 || $i === $total_pages || abs($i - $current_page) <= 2): ?>
                        <a href="<?php echo add_query_arg('paged', $i); ?>"
                           class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline flex-shrink-0">
                            <?php echo $i; ?>
                        </a>
                    <?php elseif (abs($i - $current_page) === 3): ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 text-gray-400 flex-shrink-0">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo add_query_arg('paged', $current_page + 1); ?>"
                   class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 shadow-none no-underline w-full md:w-auto">
                    Siguiente
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-4 text-center text-xs md:text-sm text-gray-500">
            Mostrando <?php echo (($current_page - 1) * $per_page) + 1; ?>
            - <?php echo min($current_page * $per_page, $total_entries); ?>
            de <?php echo $total_entries; ?> entradas
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!$is_psychologist): ?>
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
                            Toast.show(data.data.message, 'success');
                            setTimeout(() => location.reload(), 500);
                        } else {
                            throw new Error(data.data?.message);
                        }
                    } catch (error) {
                        Toast.show('Error: ' + error.message, 'error');
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
                                if (document.querySelectorAll('.group').length === 0) {
                                    location.reload();
                                }
                            }, 300);

                            Toast.show('Entrada eliminada', 'success');
                        } else {
                            throw new Error(data.data?.message);
                        }
                    } catch (error) {
                        Toast.show('Error al eliminar: ' + error.message, 'error');
                    }
                });
            });
        });
    </script>
<?php endif; ?>