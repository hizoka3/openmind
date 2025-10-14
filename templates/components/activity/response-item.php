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
$is_owner = $response->user_id == $current_user_id;
$show_hidden_placeholder = ($is_hidden && $is_owner);
?>

<?php if ($show_hidden_placeholder): ?>
    <!-- Mensaje oculto del paciente: SOLO placeholder gris -->
    <div class="flex items-center justify-between p-4 bg-gray-100 border border-gray-300 rounded-lg"
         data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
        <div>
            <p class="text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-eye-slash text-gray-400"></i>
                Mensaje ocultado
            </p>
            <p class="text-xs text-gray-500">Solo tu psicólogo puede verlo. Se conserva por motivos clínicos.</p>
        </div>
        <button class="btn-toggle-hidden px-4 py-2 bg-primary-500 text-white rounded-lg text-sm font-medium hover:bg-primary-400 transition-colors whitespace-nowrap"
                data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
            <i class="fa-solid fa-eye mr-2"></i>
            Ver mensaje
        </button>
    </div>

    <!-- Contenido real (oculto, se muestra al hacer toggle) -->
    <div class="hidden-response-content hidden border-l-4 <?php echo $is_patient ? 'border-green-500 bg-green-50' : 'border-blue-500 bg-blue-50'; ?> rounded-lg p-4 mt-2"
         data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
                <img src="<?php echo esc_url(get_avatar_url($response->user_id, ['size' => 32])); ?>"
                     alt="Avatar"
                     class="w-8 h-8 rounded-full object-cover">
                <div>
                    <p class="text-sm font-semibold text-gray-900 m-0">
                        <?php echo esc_html($author->display_name); ?>
                    </p>
                    <p class="text-xs text-gray-600 m-0">
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
        </div>

        <div class="response-content prose prose-sm max-w-none text-gray-700">
            <?php echo wpautop(wp_kses_post($response->comment_content)); ?>
        </div>

        <?php
        $files = get_comment_meta($response->comment_ID, '_response_files', true);
        if ($files && is_array($files)):
            $attachments_args = [
                    'files' => $files,
                    'border_color' => $is_patient ? 'border-green-200' : 'border-blue-200'
            ];
            include OPENMIND_PATH . 'templates/components/activity/attachments.php';
        endif;
        ?>

        <div class="mt-4 pt-4 border-t border-gray-200">
            <button class="btn-toggle-hidden px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
                    data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                <i class="fa-solid fa-eye-slash mr-2"></i>
                Ocultar nuevamente
            </button>
        </div>
    </div>

<?php else: ?>
    <!-- Mensaje normal: con borde y fondo -->
    <div class="border-l-4 <?php echo $is_patient ? 'border-green-500 bg-green-50' : 'border-blue-500 bg-blue-50'; ?> rounded-lg p-4"
         data-response-id="<?php echo esc_attr($response->comment_ID); ?>">

        <?php if ($is_hidden && !$is_owner): ?>
            <!-- Vista del psicólogo: badge naranja -->
            <div class="mb-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                <p class="text-sm font-semibold text-orange-800 m-0 flex items-center gap-2">
                    <i class="fa-solid fa-eye-slash"></i> Oculto por el paciente
                    <?php
                    $hidden_at = get_comment_meta($response->comment_ID, '_hidden_at', true);
                    if ($hidden_at):
                        ?>
                        <span class="font-normal text-orange-700">
                            - <?php echo date('d/m/Y H:i', strtotime($hidden_at)); ?>
                        </span>
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

            <?php if ($show_actions): ?>
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

        <div class="response-content prose prose-sm max-w-none text-gray-700">
            <?php echo wpautop(wp_kses_post($response->comment_content)); ?>
        </div>

        <?php
        $files = get_comment_meta($response->comment_ID, '_response_files', true);
        if ($files && is_array($files)):
            $attachments_args = [
                    'files' => $files,
                    'border_color' => $is_patient ? 'border-green-200' : 'border-blue-200'
            ];
            include OPENMIND_PATH . 'templates/components/activity/attachments.php';
        endif;
        ?>
    </div>
<?php endif; ?>