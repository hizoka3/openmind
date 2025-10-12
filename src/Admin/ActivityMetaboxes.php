<?php // src/Admin/ActivityMetaboxes.php
namespace Openmind\Admin;

class ActivityMetaboxes {

    public static function register(): void {
        add_meta_box(
            'activity_resource_metabox',
            'Recurso de la Actividad',
            [self::class, 'renderResourceMetabox'],
            'activity',
            'normal',
            'high'
        );
    }

    public static function renderResourceMetabox($post): void {
        wp_nonce_field('openmind_activity_meta', 'openmind_activity_nonce');

        $type = get_post_meta($post->ID, '_activity_type', true) ?: 'pdf';
        $file_id = get_post_meta($post->ID, '_activity_file', true);
        $url = get_post_meta($post->ID, '_activity_url', true);

        $show_file = ($type === 'pdf');
        $show_url = in_array($type, ['youtube', 'link']);
        ?>

        <div style="padding: 15px 0;">
            <!-- Selector de Tipo -->
            <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
                <p style="margin-bottom: 12px; font-weight: 600; font-size: 14px;">Tipo de Recurso *</p>

                <label style="display: inline-block; margin-right: 20px; cursor: pointer; padding: 8px 12px; border: 2px solid #ddd; border-radius: 4px; transition: all 0.2s;">
                    <input type="radio" name="activity_type" value="pdf" <?php checked($type, 'pdf'); ?>
                           onchange="toggleResourceInput(this.value)" required style="margin-right: 6px;">
                    <span class="dashicons dashicons-media-document" style="color: #2271b1; vertical-align: middle;"></span>
                    <strong>PDF</strong>
                </label>

                <label style="display: inline-block; margin-right: 20px; cursor: pointer; padding: 8px 12px; border: 2px solid #ddd; border-radius: 4px; transition: all 0.2s;">
                    <input type="radio" name="activity_type" value="youtube" <?php checked($type, 'youtube'); ?>
                           onchange="toggleResourceInput(this.value)" style="margin-right: 6px;">
                    <span class="dashicons dashicons-video-alt3" style="color: #d63638; vertical-align: middle;"></span>
                    <strong>YouTube</strong>
                </label>

                <label style="display: inline-block; cursor: pointer; padding: 8px 12px; border: 2px solid #ddd; border-radius: 4px; transition: all 0.2s;">
                    <input type="radio" name="activity_type" value="link" <?php checked($type, 'link'); ?>
                           onchange="toggleResourceInput(this.value)" style="margin-right: 6px;">
                    <span class="dashicons dashicons-admin-links" style="color: #2271b1; vertical-align: middle;"></span>
                    <strong>Link Externo</strong>
                </label>
            </div>

            <!-- Upload de archivo (PDF/Video) -->
            <div id="file-upload-section" style="<?php echo $show_file ? '' : 'display:none;'; ?>">
                <p style="margin-bottom: 10px; font-weight: 600; font-size: 14px;">Archivo</p>

                <div style="margin-bottom: 15px;">
                    <button type="button" class="button button-primary" id="upload_file_button">
                        <span class="dashicons dashicons-upload" style="vertical-align: middle; margin-top: 3px;"></span>
                        Seleccionar Archivo
                    </button>
                    <input type="hidden" id="activity_file_id" name="activity_file" value="<?php echo esc_attr($file_id); ?>">
                </div>

                <div id="file_preview" style="<?php echo $file_id ? '' : 'display:none;'; ?>">
                    <?php if ($file_id):
                        $file_url = wp_get_attachment_url($file_id);
                        $file_name = basename(get_attached_file($file_id));
                        ?>
                        <div style="padding: 12px; background: #f0f0f1; border-radius: 4px; display: inline-block;">
                            <span class="dashicons dashicons-media-default" style="vertical-align: middle; color: #2271b1;"></span>
                            <a href="<?php echo esc_url($file_url); ?>" target="_blank" style="text-decoration: none; font-weight: 500;">
                                <?php echo esc_html($file_name); ?>
                            </a>
                            <button type="button" class="button-link-delete" id="remove_file_button" style="color: #d63638; margin-left: 10px;">
                                Eliminar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <p class="description" style="margin-top: 10px;">
                    Sube un archivo PDF
                </p>
            </div>

            <!-- Input de URL (YouTube/Link) -->
            <div id="url-input-section" style="<?php echo $show_url ? '' : 'display:none;'; ?>">
                <p style="margin-bottom: 10px; font-weight: 600; font-size: 14px;">URL del Recurso</p>

                <input type="url"
                       name="activity_url"
                       id="activity_url"
                       value="<?php echo esc_attr($url); ?>"
                       placeholder="https://..."
                       style="width: 100%; max-width: 600px; padding: 8px 12px; font-size: 14px;"
                       class="regular-text">

                <p class="description" style="margin-top: 10px;">
                    <span id="url-hint-youtube" style="<?php echo $type === 'youtube' ? '' : 'display:none;'; ?>">
                        <strong>YouTube:</strong> Ejemplo: https://www.youtube.com/watch?v=VIDEO_ID
                    </span>
                    <span id="url-hint-link" style="<?php echo $type === 'link' ? '' : 'display:none;'; ?>">
                        <strong>Link externo:</strong> URL completa del recurso (artículo, podcast, sitio web, etc.)
                    </span>
                </p>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Media Uploader
                let fileFrame;

                $('#upload_file_button').on('click', function(e) {
                    e.preventDefault();

                    if (fileFrame) {
                        fileFrame.open();
                        return;
                    }

                    fileFrame = wp.media({
                        title: 'Seleccionar PDF',
                        button: { text: 'Usar este PDF' },
                        multiple: false,
                        library: {
                            type: 'application/pdf'
                        }
                    });

                    fileFrame.on('select', function() {
                        const attachment = fileFrame.state().get('selection').first().toJSON();
                        $('#activity_file_id').val(attachment.id);

                        $('#file_preview').html(
                            '<div style="padding: 12px; background: #f0f0f1; border-radius: 4px; display: inline-block;">' +
                            '<span class="dashicons dashicons-media-default" style="vertical-align: middle; color: #2271b1;"></span> ' +
                            '<a href="' + attachment.url + '" target="_blank" style="text-decoration: none; font-weight: 500;">' +
                            attachment.filename +
                            '</a> ' +
                            '<button type="button" class="button-link-delete" id="remove_file_button" style="color: #d63638; margin-left: 10px;">Eliminar</button>' +
                            '</div>'
                        ).show();
                    });

                    fileFrame.open();
                });

                // Remover archivo
                $(document).on('click', '#remove_file_button', function(e) {
                    e.preventDefault();
                    $('#activity_file_id').val('');
                    $('#file_preview').html('').hide();
                });

                // Highlight del radio seleccionado
                function updateRadioStyles() {
                    $('input[name="activity_type"]').parent().css({
                        'border-color': '#ddd',
                        'background': 'transparent'
                    });
                    $('input[name="activity_type"]:checked').parent().css({
                        'border-color': '#2271b1',
                        'background': '#f0f6fc'
                    });
                }

                $('input[name="activity_type"]').on('change', updateRadioStyles);
                updateRadioStyles();
            });

            // Toggle entre File Upload y URL Input
            function toggleResourceInput(type) {
                const fileSection = document.getElementById('file-upload-section');
                const urlSection = document.getElementById('url-input-section');
                const urlHintYoutube = document.getElementById('url-hint-youtube');
                const urlHintLink = document.getElementById('url-hint-link');

                if (type === 'pdf') {
                    fileSection.style.display = 'block';
                    urlSection.style.display = 'none';
                } else {
                    fileSection.style.display = 'none';
                    urlSection.style.display = 'block';

                    if (type === 'youtube') {
                        urlHintYoutube.style.display = 'inline';
                        urlHintLink.style.display = 'none';
                    } else {
                        urlHintYoutube.style.display = 'none';
                        urlHintLink.style.display = 'inline';
                    }
                }
            }

            // Hacer función global
            window.toggleResourceInput = toggleResourceInput;
        </script>

        <style>
            .dashicons { vertical-align: middle; }
            #file_preview a:hover { text-decoration: underline !important; }
            input[name="activity_type"]:hover + span {
                opacity: 0.8;
            }
        </style>
        <?php
    }

    public static function save($post_id, $post): void {
        // Verificar nonce
        if (!isset($_POST['openmind_activity_nonce']) ||
            !wp_verify_nonce($_POST['openmind_activity_nonce'], 'openmind_activity_meta')) {
            return;
        }

        // Verificar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verificar permisos
        if (!current_user_can('manage_activity_library')) {
            return;
        }

        // Guardar tipo
        if (isset($_POST['activity_type'])) {
            $type = sanitize_text_field($_POST['activity_type']);
            update_post_meta($post_id, '_activity_type', $type);

            // Guardar archivo o URL según el tipo
            if ($type === 'pdf') {
                if (isset($_POST['activity_file']) && !empty($_POST['activity_file'])) {
                    update_post_meta($post_id, '_activity_file', absint($_POST['activity_file']));
                } else {
                    delete_post_meta($post_id, '_activity_file');
                }
                delete_post_meta($post_id, '_activity_url');
            } else {
                if (isset($_POST['activity_url']) && !empty($_POST['activity_url'])) {
                    update_post_meta($post_id, '_activity_url', esc_url_raw($_POST['activity_url']));
                } else {
                    delete_post_meta($post_id, '_activity_url');
                }
                delete_post_meta($post_id, '_activity_file');
            }
        }
    }
}