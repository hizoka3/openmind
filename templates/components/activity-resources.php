<?php
/**
 * Componente: Recursos de Actividad
 *
 * Muestra múltiples recursos (PDF, YouTube, Links) de una actividad
 *
 * @param array $resources_args [
 *     'resources' => array - Array de recursos de la actividad
 * ]
 */

if (!defined('ABSPATH')) exit;

$resources = $resources_args['resources'] ?? [];

if (empty($resources)) {
    echo '<p class="text-gray-500 italic">No hay recursos disponibles para esta actividad.</p>';
    return;
}
?>

    <div class="space-y-4">
        <?php foreach ($resources as $index => $resource):
            $type = $resource['type'] ?? '';
            $title = $resource['title'] ?? '';
            $url = $resource['url'] ?? '';
            $file_id = $resource['file_id'] ?? 0;

            // Generar título por defecto si no hay
            if (empty($title)) {
                $title = match($type) {
                    'pdf' => 'Documento PDF',
                    'youtube' => 'Video',
                    'link' => 'Recurso externo',
                    default => 'Recurso'
                };
            }
            ?>

            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">

                <?php if ($type === 'youtube'):
                    // Extraer video ID de la URL
                    $video_id = '';
                    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
                        $video_id = $matches[1];
                    }
                    ?>

                    <div class="aspect-video bg-gray-900">
                        <?php if ($video_id): ?>
                            <iframe
                                class="w-full h-full"
                                src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-white">
                                <p>Video no disponible</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($title): ?>
                    <div class="p-4">
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                            </svg>
                            <span class="font-medium"><?php echo esc_html($title); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php elseif ($type === 'pdf' && $file_id):
                    $file_url = wp_get_attachment_url($file_id);
                    $file_name = basename(get_attached_file($file_id));
                    ?>

                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 mb-1"><?php echo esc_html($title); ?></h4>
                                <p class="text-sm text-gray-500 mb-3"><?php echo esc_html($file_name); ?></p>

                                <div class="flex gap-2">
                                    <a href="<?php echo esc_url($file_url); ?>"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver PDF
                                    </a>

                                    <a href="<?php echo esc_url($file_url); ?>"
                                       download
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($type === 'link' && $url):
                    // Extraer dominio para mostrar
                    $domain = parse_url($url, PHP_URL_HOST);
                    ?>

                    <a href="<?php echo esc_url($url); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="block p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 mb-1"><?php echo esc_html($title); ?></h4>
                                <p class="text-sm text-gray-500 truncate"><?php echo esc_html($domain); ?></p>

                                <div class="mt-3 inline-flex items-center gap-2 text-blue-600 text-sm font-medium">
                                    <span>Abrir recurso</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>
    </div>

<?php if (count($resources) > 1): ?>
    <p class="text-sm text-gray-500 mt-4">
        📚 <?php echo count($resources); ?> recursos disponibles
    </p>
<?php endif; ?>