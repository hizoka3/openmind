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
    <div class="tw-text-center tw-py-16 tw-text-gray-400">
        <div class="tw-text-6xl tw-mb-4">📖</div>
        <p class="tw-text-lg tw-not-italic tw-text-gray-600">Aún no hay entradas de bitácora.</p>
    </div>
<?php else: ?>
    <div class="tw-space-y-6">
        <?php foreach ($entries as $entry): ?>
            <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-p-6 tw-transition-all hover:tw-shadow-md">
                <!-- Header -->
                <div class="tw-flex tw-justify-between tw-items-start tw-mb-4">
                    <div class="tw-flex tw-gap-3 tw-items-center tw-flex-wrap">
                        <?php if ($entry->mood): ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-primary-50 tw-text-primary-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium">
                                <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                <?php echo esc_html(ucfirst($entry->mood)); ?>
                            </span>
                        <?php endif; ?>

                        <span class="tw-text-sm tw-text-gray-500">
                            <i class="fa-solid fa-clock tw-mr-1"></i>
                            <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                        </span>

                        <?php if (isset($entry->author_name) && $context === 'patient'): ?>
                            <span class="tw-text-sm tw-text-gray-600">
                                <i class="fa-solid fa-user-doctor tw-mr-1"></i>
                                Por: <?php echo esc_html($entry->author_name); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($show_actions): ?>
                        <div class="tw-flex tw-gap-2">
                            <a href="?view=bitacora-editar&entry_id=<?php echo $entry->id; ?>&patient_id=<?php echo $patient_id; ?>"
                               class="tw-inline-flex tw-items-center tw-gap-1 tw-px-3 tw-py-1 tw-text-xs tw-font-medium tw-text-blue-600 tw-bg-blue-50 tw-rounded-lg tw-transition-colors hover:tw-bg-blue-100 tw-no-underline"
                               title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Editar
                            </a>
                            <button type="button"
                                    class="delete-diary-entry tw-inline-flex tw-items-center tw-gap-1 tw-px-3 tw-py-1 tw-text-xs tw-font-medium tw-text-red-600 tw-bg-red-50 tw-rounded-lg tw-transition-colors hover:tw-bg-red-100 tw-border-0 tw-cursor-pointer"
                                    data-entry-id="<?php echo $entry->id; ?>"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contenido -->
                <div class="tw-text-gray-700 tw-leading-relaxed">
                    <?php echo wp_kses_post(wpautop($entry->content)); ?>
                </div>

                <?php if (isset($entry->updated_at) && $entry->updated_at !== $entry->created_at): ?>
                    <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-100">
                        <span class="tw-text-xs tw-text-gray-400 tw-italic">
                            <i class="fa-solid fa-clock-rotate-left tw-mr-1"></i>
                            Editado: <?php echo date('d/m/Y H:i', strtotime($entry->updated_at)); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
        <div class="tw-mt-8 tw-flex tw-justify-center tw-items-center tw-gap-2">
            <?php if ($current_page > 1): ?>
                <a href="<?php echo add_query_arg('paged', $current_page - 1, $base_url); ?>"
                   class="tw-inline-flex tw-items-center tw-gap-1 tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-transition-colors hover:tw-bg-gray-50 tw-no-underline">
                    <i class="fa-solid fa-chevron-left"></i>
                    Anterior
                </a>
            <?php endif; ?>

            <div class="tw-flex tw-gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $current_page): ?>
                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium">
                            <?php echo $i; ?>
                        </span>
                    <?php elseif ($i === 1 || $i === $total_pages || abs($i - $current_page) <= 2): ?>
                        <a href="<?php echo add_query_arg('paged', $i, $base_url); ?>"
                           class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-transition-colors hover:tw-bg-gray-50 tw-no-underline">
                            <?php echo $i; ?>
                        </a>
                    <?php elseif (abs($i - $current_page) === 3): ?>
                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-text-gray-400">
                            ...
                        </span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo add_query_arg('paged', $current_page + 1, $base_url); ?>"
                   class="tw-inline-flex tw-items-center tw-gap-1 tw-px-4 tw-py-2 tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm tw-font-medium tw-text-gray-700 tw-transition-colors hover:tw-bg-gray-50 tw-no-underline">
                    Siguiente
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="tw-mt-4 tw-text-center tw-text-sm tw-text-gray-500">
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
                const entryCard = this.closest('.tw-bg-white');

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
                                const remainingEntries = document.querySelectorAll('.tw-bg-white.tw-border.tw-border-gray-200');
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