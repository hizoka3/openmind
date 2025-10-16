<?php // templates/pages/patient/inicio.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$current_user = wp_get_current_user();
$has_psychologist = get_user_meta($user_id, 'psychologist_id', true);
$is_active = get_user_meta($user_id, 'openmind_status', true) === 'active';
$base_url = get_permalink();

// Stats - USAR MÉTODO ACTUALIZADO
$assignments = \Openmind\Controllers\ActivityController::getPatientAssignments($user_id);
$unread_messages = \Openmind\Repositories\MessageRepository::getUnreadCount($user_id);
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Section - COMÚN PARA TODOS -->
    <div class="bg-gradient-to-r from-primary-50 to-purple-50 rounded-2xl p-8 mb-4 border-2 border-primary-200">
        <div class="flex items-center gap-4">
            <div class="user-avatar">
                <img id="avatar-preview"
                     src="<?php echo esc_url(get_avatar_url($current_user->ID, ['size' => 50])); ?>"
                     alt="Avatar"
                     class="w-16 h-16 rounded-full border-4 border-primary-100 object-cover">
            </div>
            <div>
                <h1 class="text-2xl font-normal text-gray-900 m-0">
                    Hola <?php echo esc_html($current_user->display_name); ?>
                </h1>
                <p class="text-gray-600 m-0">Bienvenido a tu espacio personal</p>
            </div>
        </div>
    </div>

    <?php if (!$has_psychologist): ?>
        <!-- Sin psicólogo: Card de Reserva Full Width -->
        <div class="bg-primary-500 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all group border-2 border-transparent hover:border-primary-200 mb-6">
            <div class="flex items-start gap-4">
                <div class="mt-2 w-14 h-14 bg-white rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-calendar text-2xl text-primary-500"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl text-white mb-3">Agenda tu primera cita</h2>
                    <p class="text-white text-sm m-0">
                        Para comenzar tu tratamiento, agenda una sesión con nuestros profesionales.
                    </p>
                </div>
                <button
                        onclick="openReservoModal()"
                        class="bg-white text-primary-500 font-semibold px-8 py-4 rounded-xl transition-all transform hover:scale-105 shadow-lg">
                    Agendar Cita
                </button>
            </div>
        </div>
        <?php include OPENMIND_PATH . 'templates/components/reservo-modal.php'; ?>
    <?php endif; ?>

    <!-- Cards de Capacidades - SIEMPRE VISIBLES -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Card Actividades -->
        <a href="<?php echo add_query_arg('view', 'actividades', $base_url); ?>"
           class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all group border-2 border-transparent hover:border-primary-200"
           style="text-decoration: none;">
            <div class="flex items-start gap-4">
                <div class="mt-2 w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-tasks text-2xl text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Actividades</h3>
                    <p class="text-gray-600 text-sm m-0">
                        Completa ejercicios y tareas terapéuticas diseñadas para tu bienestar emocional
                    </p>
                </div>
            </div>
        </a>

        <!-- Card Mensajería -->
        <a href="<?php echo add_query_arg('view', 'mensajeria', $base_url); ?>"
           class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all group border-2 border-transparent hover:border-primary-200"
           style="text-decoration: none;">
            <div class="flex items-start gap-4">
                <div class="mt-2 w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-comments text-2xl text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Mensajería</h3>
                    <p class="text-gray-600 text-sm m-0">
                        Comunícate directamente con tu psicólogo de forma segura y privada
                    </p>
                </div>
            </div>
        </a>

        <!-- Card Bitácora -->
        <a href="<?php echo add_query_arg('view', 'bitacora', $base_url); ?>"
           class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all group border-2 border-transparent hover:border-primary-200"
           style="text-decoration: none;">
            <div class="flex items-start gap-4">
                <div class="mt-2 w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-clipboard-check text-2xl text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Bitácora</h3>
                    <p class="text-gray-600 text-sm m-0">
                        Revisa tu historial de sesiones y el progreso de tu tratamiento
                    </p>
                </div>
            </div>
        </a>

        <!-- Card Diario de Vida -->
        <a href="<?php echo add_query_arg('view', 'diario', $base_url); ?>"
           class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all group border-2 border-transparent hover:border-primary-200"
           style="text-decoration: none;">
            <div class="flex items-start gap-4">
                <div class="mt-2 w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-book-open text-2xl text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Diario de Vida</h3>
                    <p class="text-gray-600 text-sm m-0">
                        Registra tus pensamientos, emociones y experiencias diarias en tu espacio personal
                    </p>
                </div>
            </div>
        </a>
    </div>

    <?php if (!$has_psychologist):
        // Ya manejado arriba
    elseif (!$is_active): ?>
    <!-- Con psicólogo pero inactivo -->
    <div class="bg-yellow-50 border-2 border-yellow-200 rounded-2xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="text-4xl">⏳</div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cuenta pendiente de activación</h3>
                <p class="text-gray-600 mb-3">
                    Tu psicólogo activará tu cuenta pronto. Mientras tanto, puedes actualizar tu perfil.
                </p>
                <a href="<?php echo add_query_arg('view', 'perfil', get_permalink()); ?>"
                   class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-2 rounded-lg transition-colors">
                    Ver mi perfil
                </a>
            </div>
        </div>
    </div>

    <?php else:
    // Paciente ACTIVO: Solo actividad reciente
    $psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
    $psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
    $recent_events = openmind_get_patient_recent_events($user_id);
    ?>

    <!-- Actividad Reciente -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-light text-gray-900 m-0">Actividad Reciente</h2>
        </div>

        <?php if (empty($recent_events)): ?>
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-inbox text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 m-0">No hay actividad reciente.</p>
            </div>
        <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($recent_events as $event): ?>
            <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-10 h-10 bg-<?php echo $event['color']; ?>-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-xl"><?php echo $event['icon']; ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-900 text-sm font-medium m-0"><?php echo esc_html($event['text']); ?></p>
                    <span class="text-gray-500 text-xs">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    <?php echo $event['time']; ?>
                                </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>