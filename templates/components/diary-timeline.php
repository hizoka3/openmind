<?php // templates/components/diary-timeline.php
/**
 * Componente reutilizable para mostrar timeline de entradas de diario
 *
 * @param array $args {
 *     @type array   $entries        Array de entradas a mostrar
 *     @type string  $context        'patient' o 'psychologist'
 *     @type int     $patient_id     ID del paciente (para links de detalle)
 *     @type bool    $show_actions   Mostrar acciones (compartir/eliminar)
 * }
 */

$entries = $args['entries'] ?? [];
$context = $args['context'] ?? 'patient';
$patient_id = $args['patient_id'] ?? get_current_user_id();
$show_actions = $args['show_actions'] ?? true;

$mood_emojis = [
    'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
    'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];

$is_psychologist = $context === 'psychologist';

// URL base para el detalle
if ($is_psychologist) {
    $detail_base_url = add_query_arg([
        'view' => 'diario-detalle-paciente',
        'patient_id' => $patient_id
    ], home_url('/dashboard-psicologo/'));
} else {
    $detail_base_url = add_query_arg('view', 'diario-detalle', home_url('/dashboard-paciente/'));
}
?>

<?php if (empty($entries)): ?>
    <div class="text-center py-16">
        <div class="text-6xl mb-4">📖</div>
        <p class="text-lg text-gray-600 m-0 mb-2">
            <?php echo $is_psychologist ? 'El paciente no ha compartido entradas de su diario aún.' : 'Aún no has escrito ninguna entrada personal.'; ?>
        </p>
        <?php if (!$is_psychologist): ?>
            <p class="text-sm text-gray-500 m-0 mb-6">
                Las entradas aparecerán aquí cuando decidas compartirlas.
            </p>
            <a
                href="<?php echo add_query_arg('view', 'diario-nuevo', home_url('/dashboard-paciente/')); ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-500 text-white rounded-lg border-0 text-sm font-medium transition-all hover:bg-purple-600 shadow-none no-underline">
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

            // Preview corto (150 caracteres)
            $preview = wp_trim_words(strip_tags($entry->content), 25, '...');

            // Color de la línea según contexto
            if ($is_psychologist) {
                $timeline_color = 'from-blue-400 to-indigo-500';
                $border_color = 'border-blue-100 hover:border-blue-200';
            } else {
                $timeline_color = $is_shared ? 'from-green-400 to-green-500' : 'from-purple-400 to-purple-500';
                $border_color = $is_shared ? 'border-green-100 hover:border-green-200' : 'border-gray-100 hover:border-gray-200';
            }
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
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b <?php echo $timeline_color; ?> rounded-full"></div>

                <!-- Card -->
                <div class="ml-6 bg-white border-2 <?php echo $border_color; ?> rounded-xl p-5 transition-all hover:shadow-lg">
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
                                    href="<?php echo add_query_arg('entry_id', $entry->id, $detail_base_url); ?>"
                                    class="inline-flex items-center gap-1 text-sm font-medium <?php echo $is_psychologist ? 'text-blue-600 hover:text-blue-700' : 'text-purple-600 hover:text-purple-700'; ?> transition-colors no-underline">
                                    <i class="fa-solid fa-book-open"></i>
                                    Leer entrada completa
                                </a>

                                <?php if ($show_actions && !$is_psychologist): ?>
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
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right: Badge Estado -->
                        <div class="flex-shrink-0">
                            <?php if ($is_psychologist): ?>
                                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-xs font-semibold">
                                    <i class="fa-solid fa-share-nodes"></i>
                                    Compartido
                                </span>
                            <?php else: ?>
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
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($show_actions && !$is_psychologist): ?>
        <!-- Script solo para paciente (acciones) -->
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
    <?php endif; ?>
<?php endif; ?>