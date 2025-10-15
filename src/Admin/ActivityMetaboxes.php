<?php // src/Admin/ActivityMetaboxes.php
namespace Openmind\Admin;

class ActivityMetaboxes {

    public static function register(): void {
        add_meta_box(
                'activity_resources_metabox',
                'Recursos de la Actividad',
                [self::class, 'renderResourcesMetabox'],
                'activity',
                'normal',
                'high'
        );
    }

    public static function renderResourcesMetabox($post): void {
        wp_nonce_field('openmind_activity_meta', 'openmind_activity_nonce');

        $resources = get_post_meta($post->ID, '_activity_resources', true) ?: [];

        // Fallback para actividades antiguas
        if (empty($resources)) {
            $old_type = get_post_meta($post->ID, '_activity_type', true);
            if ($old_type) {
                $resources = [[
                        'type' => $old_type,
                        'file_id' => get_post_meta($post->ID, '_activity_file', true) ?: '',
                        'url' => get_post_meta($post->ID, '_activity_url', true) ?: '',
                        'title' => '',
                        'order' => 0
                ]];
            }
        }

        $resource_count = count($resources);
        ?>

        <div id="activity-resources-wrapper">
            <div style="margin-bottom: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
                <p style="margin: 0; font-size: 13px;">
                    <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                    <strong>Puedes agregar hasta 5 recursos</strong> (videos de YouTube, PDFs o links externos)
                </p>
                <p style="margin: 8px 0 0 0; font-size: 13px; color: #646970;">
                    Arrastra las filas para reordenar • <span id="resource-counter"><?php echo $resource_count; ?>/5</span> recursos
                </p>
            </div>

            <div id="resources-container">
                <?php if (!empty($resources)): ?>
                    <?php foreach ($resources as $index => $resource): ?>
                        <?php self::renderResourceRow($index, $resource); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="add-resource-btn" class="button button-secondary" style="margin-top: 15px;">
                <span class="dashicons dashicons-plus-alt" style="vertical-align: middle; margin-top: 3px;"></span>
                Agregar Recurso
            </button>
        </div>

        <?php self::renderScripts(); ?>
        <?php
    }

    private static function renderResourceRow(int $index, array $resource = []): void {
        $type = $resource['type'] ?? 'pdf';
        $file_id = $resource['file_id'] ?? '';
        $url = $resource['url'] ?? '';
        $title = $resource['title'] ?? '';
        $order = $resource['order'] ?? $index;

        $show_file = ($type === 'pdf');
        $show_url = in_array($type, ['youtube', 'link']);
        ?>
        <div class="resource-row" data-index="<?php echo $index; ?>" draggable="true">
            <div class="resource-header">
                <span class="drag-handle dashicons dashicons-menu" title="Arrastra para reordenar"></span>
                <span class="resource-number">#<?php echo $index + 1; ?></span>
                <button type="button" class="remove-resource" title="Eliminar recurso">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <div class="resource-content">
                <!-- Tipo de recurso -->
                <div class="resource-field">
                    <label>Tipo de Recurso *</label>
                    <select name="activity_resources[<?php echo $index; ?>][type]" class="resource-type-select" required>
                        <option value="pdf" <?php selected($type, 'pdf'); ?>>📄 PDF</option>
                        <option value="youtube" <?php selected($type, 'youtube'); ?>>🎬 YouTube</option>
                        <option value="link" <?php selected($type, 'link'); ?>>🔗 Link Externo</option>
                    </select>
                </div>

                <!-- Upload PDF -->
                <div class="resource-field file-upload-field" style="<?php echo $show_file ? '' : 'display:none;'; ?>">
                    <label>Archivo PDF *</label>
                    <button type="button" class="button upload-pdf-btn">
                        <span class="dashicons dashicons-upload" style="vertical-align: middle;"></span>
                        Seleccionar PDF
                    </button>
                    <input type="hidden" class="pdf-file-id" name="activity_resources[<?php echo $index; ?>][file_id]" value="<?php echo esc_attr($file_id); ?>">

                    <div class="pdf-preview" style="<?php echo $file_id ? '' : 'display:none;'; ?>">
                        <?php if ($file_id):
                            $file_url = wp_get_attachment_url($file_id);
                            $file_name = basename(get_attached_file($file_id));
                            ?>
                            <span class="dashicons dashicons-media-document"></span>
                            <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($file_name); ?></a>
                            <button type="button" class="remove-pdf">×</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Input URL -->
                <div class="resource-field url-input-field" style="<?php echo $show_url ? '' : 'display:none;'; ?>">
                    <label>URL *</label>
                    <input type="url" class="resource-url" name="activity_resources[<?php echo $index; ?>][url]"
                           value="<?php echo esc_attr($url); ?>"
                           placeholder="https://">
                    <p class="description url-hint"></p>
                </div>

                <!-- Título opcional -->
                <div class="resource-field">
                    <label>Título descriptivo (opcional)</label>
                    <input type="text" name="activity_resources[<?php echo $index; ?>][title]"
                           value="<?php echo esc_attr($title); ?>"
                           placeholder="Ej: Guía de ejercicios">
                </div>

                <input type="hidden" name="activity_resources[<?php echo $index; ?>][order]" value="<?php echo $order; ?>" class="resource-order">
            </div>
        </div>
        <?php
    }

    private static function renderScripts(): void {
        ?>
        <style>
            #resources-container {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .resource-row {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 15px;
                cursor: move;
                transition: all 0.2s;
            }

            .resource-row:hover {
                border-color: #2271b1;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            .resource-row.dragging {
                opacity: 0.5;
                transform: scale(0.98);
            }

            .resource-row.drag-over {
                border-color: #2271b1;
                border-style: dashed;
                background: #f0f6fc;
            }

            .resource-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #dcdcde;
            }

            .drag-handle {
                color: #8c8f94;
                cursor: move;
                font-size: 20px;
            }

            .drag-handle:hover {
                color: #2271b1;
            }

            .resource-number {
                font-weight: 600;
                color: #2271b1;
                font-size: 14px;
            }

            .remove-resource {
                margin-left: auto;
                background: none;
                border: none;
                cursor: pointer;
                padding: 4px;
                color: #d63638;
                font-size: 18px;
            }

            .remove-resource:hover {
                color: #b32d2e;
            }

            .resource-content {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .resource-field label {
                display: block;
                font-weight: 600;
                font-size: 13px;
                margin-bottom: 6px;
            }

            .resource-field input[type="text"],
            .resource-field input[type="url"],
            .resource-field select {
                width: 100%;
                max-width: 500px;
            }

            .pdf-preview {
                margin-top: 10px;
                padding: 10px;
                background: #f0f0f1;
                border-radius: 4px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .pdf-preview a {
                text-decoration: none;
                font-weight: 500;
            }

            .pdf-preview a:hover {
                text-decoration: underline;
            }

            .remove-pdf {
                background: none;
                border: none;
                color: #d63638;
                font-size: 18px;
                cursor: pointer;
                padding: 0 4px;
            }

            .url-hint {
                font-size: 12px;
                color: #646970;
                margin-top: 5px;
            }

            #add-resource-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>

        <script>
            (function() {
                let resourceIndex = <?php echo count(get_post_meta(get_the_ID(), '_activity_resources', true) ?: []); ?>;
                const maxResources = 5;
                let draggedElement = null;

                // Actualizar contador
                function updateCounter() {
                    const count = document.querySelectorAll('.resource-row').length;
                    document.getElementById('resource-counter').textContent = `${count}/${maxResources}`;
                    document.getElementById('add-resource-btn').disabled = (count >= maxResources);
                }

                // Actualizar números
                function updateResourceNumbers() {
                    document.querySelectorAll('.resource-row').forEach((row, index) => {
                        row.dataset.index = index;
                        row.querySelector('.resource-number').textContent = `#${index + 1}`;
                        row.querySelector('.resource-order').value = index;

                        // Actualizar nombres de inputs
                        row.querySelectorAll('[name^="activity_resources"]').forEach(input => {
                            const name = input.getAttribute('name');
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                        });
                    });
                }

                // Agregar recurso
                document.getElementById('add-resource-btn').addEventListener('click', function() {
                    if (document.querySelectorAll('.resource-row').length >= maxResources) return;

                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=openmind_render_resource_row&index=${resourceIndex}&nonce=<?php echo wp_create_nonce('openmind_resource_row'); ?>`
                    })
                        .then(res => res.text())
                        .then(html => {
                            const container = document.getElementById('resources-container');
                            container.insertAdjacentHTML('beforeend', html);
                            resourceIndex++;
                            updateCounter();
                            updateResourceNumbers();
                            initResourceRow(container.lastElementChild);
                        });
                });

                // Eliminar recurso
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-resource')) {
                        if (confirm('¿Eliminar este recurso?')) {
                            e.target.closest('.resource-row').remove();
                            updateCounter();
                            updateResourceNumbers();
                        }
                    }

                    if (e.target.closest('.remove-pdf')) {
                        const row = e.target.closest('.resource-row');
                        row.querySelector('.pdf-file-id').value = '';
                        row.querySelector('.pdf-preview').style.display = 'none';
                    }
                });

                // Drag & Drop
                document.addEventListener('dragstart', function(e) {
                    if (e.target.classList.contains('resource-row')) {
                        draggedElement = e.target;
                        e.target.classList.add('dragging');
                    }
                });

                document.addEventListener('dragend', function(e) {
                    if (e.target.classList.contains('resource-row')) {
                        e.target.classList.remove('dragging');
                        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                    }
                });

                document.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(document.getElementById('resources-container'), e.clientY);
                    const dragging = document.querySelector('.dragging');

                    if (afterElement == null) {
                        document.getElementById('resources-container').appendChild(dragging);
                    } else {
                        document.getElementById('resources-container').insertBefore(dragging, afterElement);
                    }
                });

                document.addEventListener('drop', function(e) {
                    e.preventDefault();
                    updateResourceNumbers();
                });

                function getDragAfterElement(container, y) {
                    const draggableElements = [...container.querySelectorAll('.resource-row:not(.dragging)')];

                    return draggableElements.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = y - box.top - box.height / 2;

                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: child };
                        } else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                }

                // Inicializar row
                function initResourceRow(row) {
                    const typeSelect = row.querySelector('.resource-type-select');
                    const fileField = row.querySelector('.file-upload-field');
                    const urlField = row.querySelector('.url-input-field');
                    const urlHint = row.querySelector('.url-hint');

                    typeSelect.addEventListener('change', function() {
                        const type = this.value;

                        if (type === 'pdf') {
                            fileField.style.display = 'block';
                            urlField.style.display = 'none';
                        } else {
                            fileField.style.display = 'none';
                            urlField.style.display = 'block';

                            if (type === 'youtube') {
                                urlHint.textContent = 'Ejemplo: https://www.youtube.com/watch?v=VIDEO_ID';
                            } else {
                                urlHint.textContent = 'URL completa del recurso externo';
                            }
                        }
                    });

                    // Upload PDF
                    const uploadBtn = row.querySelector('.upload-pdf-btn');
                    uploadBtn.addEventListener('click', function() {
                        let frame = wp.media({
                            title: 'Seleccionar PDF',
                            button: { text: 'Usar este PDF' },
                            multiple: false,
                            library: { type: 'application/pdf' }
                        });

                        frame.on('select', function() {
                            const attachment = frame.state().get('selection').first().toJSON();
                            row.querySelector('.pdf-file-id').value = attachment.id;
                            row.querySelector('.pdf-preview').innerHTML = `
                            <span class="dashicons dashicons-media-document"></span>
                            <a href="${attachment.url}" target="_blank">${attachment.filename}</a>
                            <button type="button" class="remove-pdf">×</button>
                        `;
                            row.querySelector('.pdf-preview').style.display = 'inline-flex';
                        });

                        frame.open();
                    });
                }

                // Inicializar rows existentes
                document.querySelectorAll('.resource-row').forEach(initResourceRow);
                updateCounter();
            })();
        </script>
        <?php
    }

    public static function save($post_id, $post): void {
        if (!isset($_POST['openmind_activity_nonce']) ||
                !wp_verify_nonce($_POST['openmind_activity_nonce'], 'openmind_activity_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('manage_activity_library')) return;

        $resources = [];

        if (isset($_POST['activity_resources']) && is_array($_POST['activity_resources'])) {
            foreach ($_POST['activity_resources'] as $resource) {
                $type = sanitize_text_field($resource['type'] ?? '');

                if (!in_array($type, ['pdf', 'youtube', 'link'])) continue;

                $clean_resource = [
                        'type' => $type,
                        'file_id' => absint($resource['file_id'] ?? 0),
                        'url' => esc_url_raw($resource['url'] ?? ''),
                        'title' => sanitize_text_field($resource['title'] ?? ''),
                        'order' => absint($resource['order'] ?? 0)
                ];

                // Validar que tenga contenido
                if ($type === 'pdf' && $clean_resource['file_id'] > 0) {
                    $resources[] = $clean_resource;
                } elseif (in_array($type, ['youtube', 'link']) && !empty($clean_resource['url'])) {
                    $resources[] = $clean_resource;
                }
            }

            // Limitar a 5
            $resources = array_slice($resources, 0, 5);
        }

        update_post_meta($post_id, '_activity_resources', $resources);
    }

    // AJAX: Renderizar nueva fila
    public static function ajaxRenderResourceRow(): void {
        check_ajax_referer('openmind_resource_row', 'nonce');

        if (!current_user_can('manage_activity_library')) {
            wp_die('No autorizado');
        }

        $index = absint($_GET['index'] ?? 0);
        self::renderResourceRow($index);
        wp_die();
    }
}