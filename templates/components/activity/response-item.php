<?php // templates/components/activity/response-item.php
/**
 * Component: Activity Response Item
 * Props: $response_args = [
 *   'response' => WP_Comment,
 *   'is_patient_response' => bool,
 *   'show_actions' => bool,
 *   'current_user_id' => int (para verificar ownership)
 * ]
 */

if (!defined('ABSPATH')) exit;

$response = $response_args['response'];
$is_patient = $response_args['is_patient_response'];
$show_actions = $response_args['show_actions'] ?? false;
$current_user_id = $response_args['current_user_id'] ?? get_current_user_id();
$author = get_userdata($response->user_id);

$is_hidden = $response->comment_approved === 'hidden';
$border_color = $is_patient ? 'border-green-500' : 'border-blue-500';
$bg_color = $is_patient ? 'bg-green-50' : 'bg-blue-50';
$attachments_border = $is_patient ? 'border-green-200' : 'border-blue-200';

// Si está oculta y el usuario actual es el paciente (dueño), aplicar opacity
$is_owner = $response->user_id == $current_user_id;
$hidden_class = ($is_hidden && $is_owner) ? 'opacity-50' : '';
?>

<div class="border-l-4 <?php echo $border_color; ?> <?php echo $bg_color; ?> <?php echo $hidden_class; ?> rounded-lg p-4"
     data-response-id="<?php echo esc_attr($response->comment_ID); ?>">

    <?php if ($is_hidden): ?>
        <div class="mb-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
            <p class="text-sm font-semibold text-orange-800 m-0 flex items-center gap-2">
                <i class="fa-solid fa-eye-slash"></i>
                <?php if ($is_owner): ?>
                    Has ocultado esta respuesta. Solo tu psicólogo puede verla.
                <?php else: ?>
                    🔒 Oculto por el paciente
                    <?php
                    $hidden_at = get_comment_meta($response->comment_ID, '_hidden_at', true);
                    if ($hidden_at):
                        ?>
                        <span class="font-normal text-orange-700">
                            - <?php echo date('d/m/Y H:i', strtotime($hidden_at)); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2">
            <img src="<?php echo esc_url(get_avatar_url($response->user_id, ['size' => 32])); ?>"
                 alt="Avatar"
                 class="w-8 h-8 rounded-full object-cover">
            <div>
                <p class="text-sm font-semibold text-gray-900 m-0">
                    <?php echo esc_html($author->display_name); ?>
                    <?php if (!$is_patient): ?>
                        <span class="text-xs font-normal text-blue-600">(Psicólogo)</span>
                    <?php endif; ?>
                </p>
                <p class="text-xs text-gray-600 m-0">
                    <i class="fa-solid fa-clock mr-1"></i>
                    <?php echo date('d/m/Y H:i', strtotime($response->comment_date)); ?>
                    <?php
                    $original = get_comment_meta($response->comment_ID, '_original_content', true);
                    if ($original):
                        ?>
                        <span class="text-xs text-gray-500 ml-2">
                            <i class="fa-solid fa-pencil"></i>
                            Editado
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if ($show_actions && !$is_hidden): ?>
            <div class="flex gap-2">
                <button class="btn-edit text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                        data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                    <i class="fa-solid fa-edit mr-1"></i>
                    Editar
                </button>
                <button class="btn-hide text-orange-600 hover:text-orange-800 text-sm font-medium transition-colors"
                        data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                    <i class="fa-solid fa-eye-slash mr-1"></i>
                    Ocultar
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="response-content prose max-w-none text-gray-700">
        <?php echo wp_kses_post($response->comment_content); ?>
    </div>

    <?php
    $files = get_comment_meta($response->comment_ID, '_response_files', true);
    if ($files && is_array($files)):
        $attachments_args = [
                'files' => $files,
                'border_color' => $attachments_border
        ];
        include OPENMIND_PATH . 'templates/components/activity/attachments.php';
    endif;
    ?>
</div>