<?php
// templates/components/bitacora-list.php
/**
 * @param array $args {
 *     @type int    $patient_id
 *     @type array  $entries
 *     @type int    $total
 *     @type int    $per_page
 *     @type int    $current_page
 *     @type bool   $show_actions
 *     @type string $context ('psychologist' | 'patient')
 *     @type string $base_url
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
        <?php foreach ($entries as $entry):
            $attachments = \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $entry->id);
            ?>
            <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex gap-3 items-center flex-wrap">
                        <span class="inline-flex items-center gap-2 bg-primary-50 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold">
                            Sesión #<?php echo $entry->session_number; ?>
                        </span>

                        <?php if ($entry->mood_assessment): ?>
                            <span class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo $mood_emojis[$entry->mood_assessment] ?? '😐'; ?>
                                <?php echo ucfirst($entry->mood_assessment); ?>
                            </span>
                        <?php endif; ?>

                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-calendar mr-1"></i>
                            <?php echo date('d/m/Y', strtotime($entry->created_at)); ?>
                        </span>
                    </div>

                    <?php if ($show_actions): ?>
                        <div class="flex gap-2">
                            <a href="<?php echo add_query_arg(['view' => 'bitacora-editar', 'note_id' => $entry->id, 'patient_id' => $patient_id, 'return' => $context === 'patient-detail' ? 'detalle' : 'lista'], home_url('/dashboard-psicologo/')); ?>"
                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors no-underline"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button onclick="deleteSessionNote(<?php echo $entry->id; ?>, this)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors border-0 bg-transparent cursor-pointer"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contenido -->
                <div class="prose prose-sm max-w-none mb-4">
                    <?php echo wp_kses_post($entry->content); ?>
                </div>

                <!-- Próximos pasos -->
                <?php if (!empty($entry->next_steps)): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-4">
                        <p class="text-xs font-semibold text-green-800 mb-2 uppercase tracking-wide">Próximos pasos</p>
                        <div class="text-sm text-green-900">
                            <?php echo nl2br(esc_html($entry->next_steps)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Imágenes adjuntas -->
                <?php if (!empty($attachments)): ?>
                    <div class="border-t pt-4">
                        <p class="text-xs font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                            <i class="fa-solid fa-paperclip mr-1"></i>
                            Adjuntos (<?php echo count($attachments); ?>)
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php foreach ($attachments as $att): ?>
                                <a href="<?php echo esc_url($att->file_path); ?>"
                                   target="_blank"
                                   class="block group relative">
                                    <img src="<?php echo esc_url($att->file_path); ?>"
                                         alt="Adjunto"
                                         class="w-full h-32 object-cover rounded-lg transition-transform group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all rounded-lg"></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="flex items-center gap-4 mt-4 pt-4 border-t text-xs text-gray-500">
                    <?php if ($context === 'psychologist'): ?>
                        <span>
                            <i class="fa-solid fa-user-doctor mr-1"></i>
                            <?php echo esc_html($entry->psychologist_name); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($entry->created_at !== $entry->updated_at): ?>
                        <span>
                            <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                            Editado el <?php echo date('d/m/Y H:i', strtotime($entry->updated_at)); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-8">
            <?php if ($current_page > 1): ?>
                <a href="<?php echo add_query_arg('paged', $current_page - 1, $base_url); ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors no-underline">
                    ← Anterior
                </a>
            <?php endif; ?>

            <span class="text-sm text-gray-600">
                Página <?php echo $current_page; ?> de <?php echo $total_pages; ?>
            </span>

            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo add_query_arg('paged', $current_page + 1, $base_url); ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors no-underline">
                    Siguiente →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    function deleteSessionNote(noteId, button) {
        if (!confirm('¿Estás seguro de eliminar esta entrada de bitácora?')) return;

        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

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
                    button.closest('.bg-white').remove();
                } else {
                    alert(data.data.message || 'Error al eliminar');
                    button.disabled = false;
                    button.innerHTML = '<i class="fa-solid fa-trash"></i>';
                }
            })
            .catch(() => {
                alert('Error de conexión');
                button.disabled = false;
                button.innerHTML = '<i class="fa-solid fa-trash"></i>';
            });
    }
</script>