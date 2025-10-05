<?php
// templates/components/bitacora-list.php

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
    <div class="space-y-4">
        <?php
        $last_date = '';
        foreach ($entries as $entry):
            $entry_date = date('Y-m-d', strtotime($entry->created_at));
            $show_date_separator = $entry_date !== $last_date;
            $last_date = $entry_date;

            $attachments = \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $entry->id);
            $has_attachments = !empty($attachments);

            // URL de detalle
            if ($context === 'patient') {
                $detail_url = add_query_arg(['view' => 'bitacora-detalle', 'note_id' => $entry->id], home_url('/dashboard-paciente/'));
            } else {
                $detail_url = add_query_arg(['view' => 'bitacora-detalle', 'note_id' => $entry->id, 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'));
            }

            $preview = wp_trim_words(strip_tags($entry->content), 30, '...');
            ?>

            <?php if ($show_date_separator): ?>
            <div class="flex items-center gap-4 mt-8 mb-4 first:mt-0">
                <div class="h-px bg-gradient-to-r from-transparent via-primary-300 to-transparent flex-1"></div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">
                        <?php echo date('d', strtotime($entry->created_at)); ?>
                    </div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <?php echo date('M Y', strtotime($entry->created_at)); ?>
                    </div>
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-primary-300 to-transparent flex-1"></div>
            </div>
        <?php endif; ?>

            <div class="group relative">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-primary-400 to-primary-500 rounded-full"></div>

                <div class="ml-6 bg-white border-2 border-primary-100 rounded-xl p-5 transition-all hover:shadow-md hover:border-primary-200 cursor-pointer"
                     onclick="window.location.href='<?php echo esc_url($detail_url); ?>'">

                    <!-- Header con fecha y hora predominante -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-flex items-center gap-2 bg-primary-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                    Sesión #<?php echo $entry->session_number; ?>
                                </span>

                                <?php if ($entry->mood_assessment): ?>
                                    <span class="text-2xl">
                                        <?php echo $mood_emojis[$entry->mood_assessment] ?? '😐'; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-2xl font-bold text-primary-600">
                                <?php echo date('H:i', strtotime($entry->created_at)); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo date('l, d \d\e F \d\e Y', strtotime($entry->created_at)); ?>
                            </div>
                        </div>

                        <?php if ($show_actions): ?>
                            <div class="flex gap-2" onclick="event.stopPropagation()">
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

                    <!-- Preview del contenido -->
                    <div class="text-gray-600 mb-3">
                        <?php echo esc_html($preview); ?>
                    </div>

                    <!-- Footer con indicadores -->
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <?php if ($has_attachments): ?>
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-paperclip"></i>
                                <?php echo count($attachments); ?> adjunto<?php echo count($attachments) > 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($entry->next_steps)): ?>
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-list-check"></i>
                                Próximos pasos
                            </span>
                        <?php endif; ?>

                        <span class="ml-auto flex items-center gap-1 text-primary-600">
                            Ver detalles
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>
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
                    button.closest('.group').remove();
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