<?php
// templates/pages/psychologist/inicio.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Stats
$patients = get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ]
]);

$activities = get_posts([
        'post_type' => 'activity',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids'
]);

$unread_messages = \Openmind\Repositories\MessageRepository::getUnreadCount($user_id);

// Log de eventos recientes
$recent_events = openmind_get_recent_events($user_id);
$base_url = get_permalink();
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
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
                <p class="text-gray-600 m-0">Aquí tienes un resumen de tu actividad</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <!-- Pacientes Card -->
        <a href="<?php echo add_query_arg('view', 'pacientes', $base_url) ?>" class="block bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all" style="text-decoration: none;">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-users text-3xl text-white"></i>
                </div>
                <div class="min-w-16 flex-1">
                    <h3 class="text-3xl font-normal text-gray-900 m-0 mb-1"><?php echo count($patients); ?></h3>
                    <p class="text-gray-600 text-sm m-0">Pacientes</p>
                </div>
            </div>
        </a>

        <!-- Actividades Card -->
        <a href="<?php echo add_query_arg('view', 'actividades', $base_url) ?>" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all" style="text-decoration: none;">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-clipboard-list text-3xl text-white"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-3xl font-normal text-gray-900 m-0 mb-1"><?php echo count($activities); ?></h3>
                    <p class="text-gray-600 text-sm m-0">Actividades totales</p>
                </div>
            </div>
        </a>

        <!-- Mensajes Card -->
        <a href="<?php echo add_query_arg('view', 'mensajeria', $base_url); ?>"
           class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all <?php echo $unread_messages > 0 ? 'border-2 border-primary-600' : ''; ?>"
           style="text-decoration: none;">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-primary-500 rounded-xl flex items-center justify-center flex-shrink-0 relative">
                    <i class="fa-solid fa-message text-3xl text-white"></i>
                    <?php if ($unread_messages > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-normal rounded-full w-6 h-6 flex items-center justify-center">
                            <?php echo $unread_messages; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-3xl font-normal text-gray-900 m-0 mb-1"><?php echo $unread_messages; ?></h3>
                    <p class="text-gray-600 text-sm m-0">Mensajes sin leer</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Actividad Reciente -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-light text-gray-900 m-0">Actividad Reciente</h2>
            <!--<a href="?view=mensajeria"
               class="text-primary-600 text-sm font-medium hover:text-primary-700 transition-colors no-underline">
                Ver todo
            </a>-->
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
                        <div class="w-10 h-10 bg-<?php echo $event['color'] ?? 'gray'; ?>-100 rounded-full flex items-center justify-center flex-shrink-0">
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
</div>