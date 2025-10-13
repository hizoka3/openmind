<?php // templates/components/activity/header.php
/**
 * Component: Activity Header
 * Props: $header_args = [
 *   'assignment' => WP_Post,
 *   'user' => WP_User (paciente o psicólogo),
 *   'status' => string,
 *   'due_date' => string,
 *   'completed_at' => string,
 *   'back_url' => string,
 *   'back_text' => string
 * ]
 */

if (!defined('ABSPATH')) exit;

$assignment = $header_args['assignment'];
$user = $header_args['user'];
$status = $header_args['status'];
$due_date = $header_args['due_date'] ?? '';
$completed_at = $header_args['completed_at'] ?? '';
$back_url = $header_args['back_url'];
$back_text = $header_args['back_text'];
?>

<!-- Navegación -->
<div class="mb-6">
    <a href="<?php echo esc_url($back_url); ?>"
       class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
        <i class="fa-solid fa-arrow-left"></i>
        <?php echo esc_html($back_text); ?>
    </a>
</div>

<!-- Cabecera -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
            <img src="<?php echo esc_url(get_avatar_url($user->ID, ['size' => 48])); ?>"
                 alt="Avatar"
                 class="w-12 h-12 rounded-full object-cover">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 m-0"><?php echo esc_html($assignment->post_title); ?></h1>
                <p class="text-sm text-gray-600 m-0"><?php echo esc_html($user->display_name); ?></p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-4">
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold <?php echo $status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; ?>">
            <i class="fa-solid fa-<?php echo $status === 'completed' ? 'check' : 'clock'; ?>"></i>
            <?php echo $status === 'completed' ? 'Completada' : 'Pendiente'; ?>
        </span>

        <?php if ($due_date): ?>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                <i class="fa-solid fa-calendar"></i>
                Fecha límite: <?php echo date('d/m/Y', strtotime($due_date)); ?>
            </span>
        <?php endif; ?>

        <?php if ($completed_at): ?>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                <i class="fa-solid fa-check-circle"></i>
                Completada: <?php echo date('d/m/Y', strtotime($completed_at)); ?>
            </span>
        <?php endif; ?>
    </div>
</div>