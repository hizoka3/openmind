<?php
/**
 * Componente reutilizable para mostrar lista de bitácoras
 *
 * @param array $args {
 *     @type int    $patient_id      ID del paciente
 *     @type array  $entries         Array de entradas
 *     @type int    $total           Total de entradas (para paginación)
 *     @type int    $per_page        Entradas por página
 *     @type int    $current_page    Página actual
 *     @type bool   $show_actions    Mostrar botones editar/eliminar
 *     @type string $context         'psychologist' o 'patient-detail'
 *     @type string $base_url        URL base para paginación
 * }
 */

$patient_id = $args['patient_id'] ?? 0;
$entries = $args['entries'] ?? [];
$total = $args['total'] ?? count($entries);
$per_page = $args['per_page'] ?? 10;
$current_page = $args['current_page'] ?? 1;
$show_actions = $args['show_actions'] ?? true;
$context = $args['context'] ?? 'psychologist';
$base_url = $args['base_url'] ?? '';

$mood_emojis = [
    'feliz' => '😊',
    'triste' => '😢',
    'ansioso' => '😰',
    'neutral' => '😐',
    'enojado' => '😠',
    'calmado' => '😌'
];

$total_pages = ceil($total / $per_page);
?>

<?php if (empty($entries)): ?>
    <div class="text-center py-16 text-gray-400">
        <div class="text-6xl mb-4">📖</div>
        <p class="text-lg not-italic text-gray-600">Aún no hay entradas de bitácora.</p>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($entries as $entry): ?>
            <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex gap-3 items-center flex-wrap">
                        <?php if ($entry->mood): ?>
                            <span class="inline-flex items-center gap-2 bg-primary-50 text-primary-700 px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                <?php echo esc_html(ucfirst($entry->mood)); ?>
                            </span>
                        <?php endif; ?>

                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-clock mr-1"></i>
                            <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                        </span>

                        <?php if (isset($entry->author_name) && $context === 'patient'): ?>
                            <span class="text-sm text-gray-600">
                                <i class="fa-solid fa-user-doctor mr-1"></i>
                                Por: <?php echo esc_html($entry->author_name); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($show_actions): ?>
                        <div class="flex gap-2">
                            <a href="?view=bitacora-editar&entry_id=<?php echo $entry->id; ?>&patient_id=<?php echo $patient_id; ?>"
                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg transition-colors hover:bg-blue-100 no-underline"
                               title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Editar
                            </a>
                            <button type="button"
                                    class="delete-diary-entry inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg transition-colors hover:bg-red-100 border-0 cursor-pointer"
                                    data-entry-id="<?php echo $entry->id; ?>"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contenido -->
                <div class="text-gray-700 leading-relaxed">
                    <?php echo wp_kses_post(wpautop($entry->content)); ?>
                </div>

                <?php if (isset($entry->updated_at) && $entry->updated_at !== $entry->created_at): ?>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <span class="text-xs text-gray-400 italic">
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                            Editado: <?php echo date('d/m/Y H:i', strtotime($entry->updated_at)); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center items-center gap-2">
            <?php if ($current_page > 1): ?>
                <a href="<?php echo add_query_arg('paged', $current_page - 1, $base_url); ?>"
                   class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 no-underline">
                    <i class="fa-solid fa-chevron-left"></i>
                    Anterior
                </a>
            <?php endif; ?>

            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $current_page): ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-primary-500 text-white rounded-lg text-sm font-medium">
                            <?php echo $i; ?>
                        </span>
                    <?php elseif ($i === 1 || $i === $total_pages || abs($i - $current_page) <= 2): ?>
                        <a href="<?php echo add_query_arg('paged', $i, $base_url); ?>"
                           class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 no-underline">
                            <?php echo $i; ?>
                        </a>
                    <?php elseif (abs($i - $current_page) === 3): ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 text-gray-400">
                            ...
                        </span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo add_query_arg('paged', $current_page + 1, $base_url); ?>"
                   class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 no-underline">
                    Siguiente
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-4 text-center text-sm text-gray-500">
            Mostrando <?php echo (($current_page - 1) * $per_page) + 1; ?>
            - <?php echo min($current_page * $per_page, $total); ?>
            de <?php echo $total; ?> entradas
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    // Manejo de eliminación
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-diary-entry').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('¿Estás seguro de eliminar esta entrada? Esta acción no se puede deshacer.')) {
                    return;
                }

                const entryId = this.getAttribute('data-entry-id');
                const entryCard = this.closest('.bg-white');

                const formData = new FormData();
                formData.append('action', 'openmind_delete_diary');
                formData.append('nonce', openmindData.nonce);
                formData.append('entry_id', entryId);

                fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            entryCard.style.opacity = '0';
                            entryCard.style.transform = 'translateX(-20px)';
                            setTimeout(() => {
                                entryCard.remove();

                                // Verificar si quedan entradas
                                const remainingEntries = document.querySelectorAll('.bg-white.border.border-gray-200');
                                if (remainingEntries.length === 0) {
                                    location.reload();
                                }
                            }, 300);

                            if (typeof OpenmindApp !== 'undefined') {
                                OpenmindApp.showNotification('Entrada eliminada correctamente', 'success');
                            }
                        } else {
                            alert(data.data?.message || 'Error al eliminar la entrada');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar la entrada');
                    });
            });
        });
    });
</script>