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

// Nombres de meses en español
$meses = ['','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
// Nombres de días en español
$dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
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

            $preview = wp_trim_words(strip_tags($entry->content), 20, '...');

            // Obtener día, mes, año
            $dia_numero = date('d', strtotime($entry->created_at));
            $mes_numero = intval(date('n', strtotime($entry->created_at)));
            $anio = date('Y', strtotime($entry->created_at));
            $dia_semana_numero = intval(date('w', strtotime($entry->created_at)));
            ?>

            <?php if ($show_date_separator): ?>
            <div class="flex items-center gap-4 mt-8 first:mt-0">
                <div class="h-px bg-gray-300 flex-1"></div>
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                    <?php echo $dia_numero; ?> <?php echo $meses[$mes_numero]; ?> <?php echo $anio; ?>
                </div>
                <div class="h-px bg-gray-300 flex-1"></div>
            </div>
        <?php endif; ?>

            <div class="relative group">
                <!-- Borde izquierdo de color -->
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary-500 rounded-l-xl"></div>

                <!-- Contenedor principal -->
                <div class="ml-2 py-3 px-3 bg-white border-2 border-gray-200 rounded-xl transition-all hover:shadow-lg hover:border-primary-300 cursor-pointer flex"
                     onclick="window.location.href='<?php echo esc_url($detail_url); ?>'">

                    <!-- Fecha grande (izquierda) -->
                    <div class="flex-shrink-0 rounded-xl min-w-32 w-32 bg-primary-50 flex flex-col items-center justify-center">
                        <div class="text-4xl font-bold text-primary-600 leading-none">
                            <?php echo $dia_numero; ?>
                        </div>
                        <div class="text-xs font-semibold text-primary-400 uppercase tracking-wider mt-1">
                            <?php echo $meses[$mes_numero]; ?>
                        </div>
                    </div>

                    <!-- Contenido principal -->
                    <div class="flex-1 min-w-0">
                        <div class="flex gap-4">
                            <!-- Columna izquierda: Hora, día, sesión, mood -->
                            <div class="flex-shrink-0 px-4">
                                <!-- Hora y día -->
                                <div class="mb-2">
                                    <div class="text-xl font-bold text-gray-900 leading-none">
                                        <?php echo date('H:i', strtotime($entry->created_at)); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <?php echo $dias[$dia_semana_numero]; ?>
                                    </div>
                                </div>

                                <!-- Sesión # y Mood -->
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        Sesión #<?php echo $entry->session_number; ?>
                                    </span>

                                    <?php if ($entry->mood_assessment): ?>
                                        <span class="text-xl">
                                            <?php echo $mood_emojis[$entry->mood_assessment] ?? '😐'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Columna derecha: Preview, footer, botones -->
                            <div class="flex-1 pr-4 min-w-0">
                                <!-- Preview del contenido -->
                                <div class="text-gray-600 text-sm mb-2 line-clamp-2 leading-snug">
                                    <?php echo esc_html($preview); ?>
                                </div>

                                <!-- Footer con indicadores y botones -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
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

                                        <span class="text-xs font-semibold text-primary-500 flex items-center gap-1 ml-auto">
                                            Ver detalles →
                                        </span>
                                    </div>

                                    <!-- Botones de acción -->
                                    <?php if ($show_actions): ?>
                                        <div class="flex gap-1 ml-4" onclick="event.stopPropagation()">
                                            <a href="<?php echo add_query_arg(['view' => 'bitacora-editar', 'note_id' => $entry->id, 'patient_id' => $patient_id, 'return' => $context === 'patient-detail' ? 'detalle' : 'lista'], home_url('/dashboard-psicologo/')); ?>"
                                               class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-full transition-all hover:bg-blue-100 no-underline"
                                               title="Editar">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </a>
                                            <button onclick="deleteSessionNote(<?php echo $entry->id; ?>, this)"
                                                    class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 rounded-full transition-all hover:bg-red-100 border-0 cursor-pointer"
                                                    title="Eliminar">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
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

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

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
                    button.closest('.relative').remove();
                } else {
                    alert(data.data.message || 'Error al eliminar');
                    button.disabled = false;
                    button.innerHTML = '<i class="fa-solid fa-trash text-sm"></i>';
                }
            })
            .catch(() => {
                alert('Error de conexión');
                button.disabled = false;
                button.innerHTML = '<i class="fa-solid fa-trash text-sm"></i>';
            });
    }
</script>