<?php
// templates/pages/diario-detalle.php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
$entry_id = intval($_GET['entry_id'] ?? 0);

if (!$entry_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada.
    </div>';
    return;
}

// Obtener la entrada
$entry = \Openmind\Repositories\DiaryRepository::getById($entry_id);

if (!$entry) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada.
    </div>';
    return;
}

// Determinar si el usuario es el dueño o el psicólogo
$is_owner = $entry->patient_id == $current_user_id && $entry->author_id == $current_user_id;
$psychologist_id = get_user_meta($entry->patient_id, 'psychologist_id', true);
$is_psychologist = $psychologist_id == $current_user_id;

// Verificar permisos
if (!$is_owner && !$is_psychologist) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        No tienes permisos para ver esta entrada.
    </div>';
    return;
}

// Si es psicólogo, solo puede ver entradas compartidas (is_private = 0) Y escritas por el paciente (author_id = patient_id)
if ($is_psychologist && ($entry->is_private == 1 || $entry->author_id != $entry->patient_id)) {
    echo '<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-yellow-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        Esta entrada es privada y no ha sido compartida contigo.
    </div>';
    return;
}

$patient = get_userdata($entry->patient_id);
$mood_emojis = [
    'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
    'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
];

// URLs de retorno según el rol
if ($is_owner) {
    $back_url = add_query_arg('view', 'diario', home_url('/dashboard-paciente/'));
    $back_text = 'Volver a Mi Diario';
} else {
    $back_url = add_query_arg(['view' => 'paciente-detalle', 'patient_id' => $entry->patient_id, 'tab' => 'diario'], home_url('/dashboard-psicologo/'));
    $back_text = 'Volver al Diario de ' . esc_html($patient->display_name);
}
?>

    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="<?php echo esc_url($back_url); ?>"
               class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                <?php echo $back_text; ?>
            </a>
        </div>

        <!-- Header Card -->
        <div class="bg-primary-50 rounded-2xl p-8 mb-8 border-2 border-primary-600">
            <div class="flex items-start justify-between gap-6 mb-6">
                <div class="flex items-center gap-4">
                    <?php if ($is_psychologist): ?>
                        <img id="avatar-preview"
                             src="<?php echo esc_url(get_avatar_url($entry->patient_id, ['size' => 64])); ?>"
                             alt="Avatar"
                             class="w-16 h-16 rounded-full border-4 border-primary-100 object-cover">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 m-0 mb-1">
                                Entrada de <?php echo esc_html($patient->display_name); ?>
                            </h1>
                            <p class="text-sm text-gray-600 m-0">
                                <?php echo date('l, d \d\e F \d\e Y \a \l\a\s H:i', strtotime($entry->created_at)); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
                                <i class="fa-solid fa-book-open mr-2 text-purple-500"></i>
                                Entrada de Diario
                            </h1>
                            <p class="text-sm text-gray-600 m-0">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                <?php echo date('l, d \d\e F \d\e Y \a \l\a\s H:i', strtotime($entry->created_at)); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Estado y Mood -->
                <div class="flex flex-col items-end gap-3">
                    <?php if ($entry->mood): ?>
                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full border-2 border-primary-200 shadow-sm">
                            <span class="text-3xl"><?php echo $mood_emojis[$entry->mood] ?? ''; ?></span>
                            <span class="text-sm font-semibold text-gray-700">
                            <?php echo esc_html(ucfirst($entry->mood)); ?>
                        </span>
                        </div>
                    <?php endif; ?>

                    <?php if ($entry->is_private == 0): ?>
                        <span class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <i class="fa-solid fa-share-nodes"></i>
                        Compartido
                    </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <i class="fa-solid fa-lock"></i>
                        Privado
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="prose prose-lg max-w-none">
                <?php echo wp_kses_post(wpautop($entry->content)); ?>
            </div>
        </div>

        <!-- Acciones (solo para el dueño) -->
        <?php if ($is_owner): ?>
            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Esta entrada fue creada el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($entry->created_at)); ?>
                    </div>

                    <div class="flex gap-3">
                        <button class="toggle-share-btn inline-flex items-center gap-2 px-5 py-2.5 <?php echo $entry->is_private == 0 ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'bg-blue-500 text-white hover:bg-blue-600'; ?> rounded-lg border-0 cursor-pointer text-sm font-medium transition-all shadow-none"
                                data-entry-id="<?php echo $entry->id; ?>"
                                data-is-shared="<?php echo $entry->is_private == 0 ? '1' : '0'; ?>">
                            <i class="fa-solid <?php echo $entry->is_private == 0 ? 'fa-lock' : 'fa-share-nodes'; ?>"></i>
                            <?php echo $entry->is_private == 0 ? 'Mover a privado' : 'Compartir con psicólogo'; ?>
                        </button>

                        <button class="delete-diary-entry inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-red-200 shadow-none"
                                data-entry-id="<?php echo $entry->id; ?>">
                            <i class="fa-solid fa-trash"></i>
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($is_psychologist): ?>
            <div class="bg-primary-50 rounded-xl p-6 border border-primary-200">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-info-circle text-primary-500 text-xl"></i>
                    <div>
                        <p class="text-sm font-semibold text-dark-gray-300 m-0 mb-1">
                            Entrada compartida por el paciente
                        </p>
                        <p class="text-sm text-dark-gray-300 m-0">
                            <?php echo esc_html($patient->display_name); ?> compartió esta entrada contigo
                            el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($entry->created_at)); ?>.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php if ($is_owner): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle compartir/descompartir
            const shareBtn = document.querySelector('.toggle-share-btn');
            if (shareBtn) {
                shareBtn.addEventListener('click', async function() {
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
            }

            // Eliminar entrada
            const deleteBtn = document.querySelector('.delete-diary-entry');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', async function() {
                    if (!confirm('¿Eliminar esta entrada? Esta acción no se puede deshacer.')) return;

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
            }
        });
    </script>
<?php endif; ?>