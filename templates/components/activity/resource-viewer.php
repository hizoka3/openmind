<?php // templates/components/activity/resource-viewer.php
/**
 * Component: Activity Resource Viewer
 * Props: $resource_args = [
 *   'activity' => WP_Post,
 *   'activity_type' => string,
 *   'activity_file' => int (attachment ID),
 *   'activity_url' => string
 * ]
 */

if (!defined('ABSPATH')) exit;

$activity = $resource_args['activity'];
$activity_type = $resource_args['activity_type'];
$activity_file = $resource_args['activity_file'];
$activity_url = $resource_args['activity_url'];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
        Recurso: <?php echo esc_html($activity->post_title); ?>
    </h3>

    <?php if ($activity->post_content): ?>
        <div class="prose max-w-none text-gray-600 mb-4">
            <?php echo wp_kses_post($activity->post_content); ?>
        </div>
    <?php endif; ?>

    <div class="pt-4 border-t border-gray-200">
        <?php
        switch($activity_type):
            case 'pdf':
                $file_url = wp_get_attachment_url($activity_file);
                ?>
                <a href="<?php echo esc_url($file_url); ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors no-underline"
                   download target="_blank">
                    <i class="fa-solid fa-file-pdf"></i>
                    Descargar PDF
                </a>
                <?php
                break;

            case 'video':
                $file_url = wp_get_attachment_url($activity_file);
                ?>
                <video controls class="w-full rounded-lg">
                    <source src="<?php echo esc_url($file_url); ?>" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>
                <?php
                break;

            case 'youtube':
                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $activity_url, $matches);
                $youtube_id = $matches[1] ?? '';
                if ($youtube_id):
                    ?>
                    <div class="relative pb-[56.25%] h-0 overflow-hidden rounded-lg">
                        <iframe class="absolute top-0 left-0 w-full h-full"
                                src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                        </iframe>
                    </div>
                <?php endif;
                break;

            case 'link':
                ?>
                <a href="<?php echo esc_url($activity_url); ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors no-underline"
                   target="_blank" rel="noopener">
                    <i class="fa-solid fa-external-link-alt"></i>
                    Abrir recurso externo
                </a>
                <?php
                break;
        endswitch;
        ?>
    </div>
</div>