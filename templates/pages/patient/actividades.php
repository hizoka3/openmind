<?php
// templates/pages/patient/actividades.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

$activities = get_posts([
        'post_type' => 'activity',
        'meta_query' => [
                ['key' => 'assigned_to', 'value' => $user_id, 'compare' => '=']
        ],
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'due_date',
        'order' => 'ASC'
]);

// Separar pendientes y completadas
$pending = array_filter($activities, fn($a) => !get_post_meta($a->ID, 'completed', true));
$completed = array_filter($activities, fn($a) => get_post_meta($a->ID, 'completed', true));
?>

<div class="max-w-7xl">
    <h1 class="text-2xl font-normal text-gray-900 mb-6">Mis Actividades</h1>

    <!-- Tabs estilo consistente con paciente-detalle.php -->
    <div class="flex gap-2 border-b-2 border-gray-200 mb-8 overflow-x-auto">
        <button class="tab-activity active flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-primary-600 cursor-pointer text-sm font-medium transition-all whitespace-nowrap rounded-t-lg"
                data-filter="pending">
            <i class="fa-solid fa-hourglass-half"></i>
            Pendientes
            <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-primary-600 text-white text-xs font-semibold rounded-full"><?php echo count($pending); ?></span>
        </button>
        <button class="tab-activity flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-sm font-medium text-gray-500 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50"
                data-filter="completed">
            <i class="fa-solid fa-check-circle"></i>
            Completadas
            <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-gray-300 text-gray-700 text-xs font-semibold rounded-full"><?php echo count($completed); ?></span>
        </button>
    </div>

    <!-- Pendientes -->
    <div class="activities-list flex flex-col gap-4" data-status="pending">
        <?php if (empty($pending)): ?>
            <div class="text-center py-16 px-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <p class="text-gray-600 text-base m-0">🎉 ¡No tienes actividades pendientes!</p>
            </div>
        <?php else: ?>
            <?php foreach ($pending as $activity): ?>
                <?php openmind_template('components/activity-card', [
                        'activity' => $activity,
                        'completed' => false
                ]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Completadas -->
    <div class="activities-list flex flex-col gap-4 hidden" data-status="completed">
        <?php if (empty($completed)): ?>
            <div class="text-center py-16 px-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <p class="text-gray-600 text-base m-0">📋 Aún no has completado ninguna actividad.</p>
            </div>
        <?php else: ?>
            <?php foreach ($completed as $activity): ?>
                <?php openmind_template('components/activity-card', [
                        'activity' => $activity,
                        'completed' => true
                ]); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Tabs
    document.querySelectorAll('.tab-activity').forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Actualizar estilos de tabs
            document.querySelectorAll('.tab-activity').forEach(t => {
                const isActive = t.dataset.filter === filter;

                // Toggle border-bottom y colores
                if (isActive) {
                    t.classList.add('active');

                    // Cambiar color del badge
                    const badge = t.querySelector('span');
                    badge.classList.remove('bg-gray-300', 'text-gray-700')
                    badge.classList.add('bg-primary-600', 'text-white')
                } else {
                    t.classList.remove('active');

                    // Cambiar color del badge
                    const badge = t.querySelector('span');
                    badge.classList.remove('bg-primary-600', 'text-white');
                    badge.classList.add('bg-gray-300', 'text-gray-700');
                }
            });

            // Mostrar/ocultar listas
            document.querySelectorAll('.activities-list').forEach(list => {
                if (list.dataset.status === filter) {
                    list.classList.remove('hidden');
                } else {
                    list.classList.add('hidden');
                }
            });
        });
    });

    // Completar actividades
    document.addEventListener('click', async (e) => {
        if (!e.target.matches('[data-action="complete-activity"]')) return;

        const btn = e.target;
        const activityId = btn.dataset.activityId;

        btn.disabled = true;
        btn.textContent = 'Completando...';

        try {
            const formData = new FormData();
            formData.append('action', 'complete_activity');
            formData.append('activity_id', activityId);
            formData.append('nonce', openmindData.nonce);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert(data.data || 'Error al completar actividad');
                btn.disabled = false;
                btn.textContent = 'Marcar como completada';
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
            btn.disabled = false;
            btn.textContent = 'Marcar como completada';
        }
    });
</script>