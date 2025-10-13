<?php // templates/components/activity/attachments.php
/**
 * Component: Activity Attachments
 * Props: $attachments_args = [
 *   'files' => array (attachment IDs),
 *   'border_color' => string (tailwind class)
 * ]
 */

if (!defined('ABSPATH')) exit;

$files = $attachments_args['files'] ?? [];
$border_color = $attachments_args['border_color'] ?? 'border-gray-300';

if (empty($files) || !is_array($files)) return;
?>

<div class="mt-4 pt-4 border-t <?php echo esc_attr($border_color); ?>">
    <p class="text-sm font-semibold text-gray-700 mb-2">Archivos adjuntos:</p>
    <div class="flex flex-wrap gap-2">
        <?php foreach($files as $file_id): ?>
            <a href="<?php echo esc_url(wp_get_attachment_url($file_id)); ?>"
               target="_blank"
               class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border <?php echo esc_attr($border_color); ?> rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors no-underline">
                <i class="fa-solid fa-paperclip"></i>
                <?php echo esc_html(basename(get_attached_file($file_id))); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>