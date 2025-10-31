<?php
namespace Openmind\Admin;

class PsychologistProfile {

    private static $fields = [
            'descripcion_corta' => ['type' => 'text', 'label' => 'Descripción Corta', 'maxlength' => 200],
            'descripcion_larga' => ['type' => 'editor', 'label' => 'Descripción Larga'],
            'video_de_perfil_youtube' => ['type' => 'url', 'label' => 'Video de Perfil (YouTube)'],
            'universidad' => ['type' => 'text', 'label' => 'Universidad'],
            'id_reservo' => ['type' => 'text', 'label' => 'ID Reservo'],
            'linea_de_especializacion' => ['type' => 'repeater', 'label' => 'Líneas de Especialización'],
            'registro_superintendencia' => ['type' => 'text', 'label' => 'Registro Superintendencia'],
            'valor_consulta' => ['type' => 'number', 'label' => 'Valor Consulta (CLP)'],
            'tipos_de_atencion' => [
                    'type' => 'checkbox',
                    'label' => 'Tipos de Atención',
                    'options' => ['Adultos', 'Infanto-Juvenil', 'Parejas', 'Talleres']
            ],
            'foto_profesional' => ['type' => 'image', 'label' => 'Foto Profesional']
    ];

    public static function register(): void {
        add_action('show_user_profile', [self::class, 'renderFields']);
        add_action('edit_user_profile', [self::class, 'renderFields']);
        add_action('personal_options_update', [self::class, 'saveFields']);
        add_action('edit_user_profile_update', [self::class, 'saveFields']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueMediaUploader']);
    }

    public static function enqueueMediaUploader($hook): void {
        if ($hook === 'profile.php' || $hook === 'user-edit.php') {
            wp_enqueue_media();
            wp_enqueue_script(
                    'openmind-profile-uploader',
                    OPENMIND_URL . 'assets/js/profile-uploader.js',
                    ['jquery'],
                    OPENMIND_VERSION,
                    true
            );
        }
    }

    public static function renderFields($user): void {
        // Solo mostrar para psicólogos
        if (!in_array('psychologist', $user->roles)) return;

        // Verificar permisos
        if (!current_user_can('edit_user', $user->ID)) return;
        ?>

        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">
                Información Profesional del Psicólogo
            </h2>

            <table class="form-table" role="presentation">
                <tbody>
                <?php foreach (self::$fields as $key => $config):
                    $value = get_user_meta($user->ID, $key, true);
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($config['label']); ?>
                            </label>
                        </th>
                        <td>
                            <?php if ($config['type'] === 'repeater'): ?>
                                <?php
                                $items = is_array($value) && !empty($value) ? $value : [''];
                                ?>
                                <div id="<?php echo esc_attr($key); ?>-container" class="repeater-container">
                                    <?php foreach ($items as $index => $item): ?>
                                        <div class="repeater-item" style="margin-bottom: 10px; display: flex; gap: 8px; align-items: center;">
                                            <input
                                                    type="text"
                                                    name="<?php echo esc_attr($key); ?>[]"
                                                    value="<?php echo esc_attr($item); ?>"
                                                    class="regular-text"
                                                    placeholder="Ej: Terapia Cognitivo-Conductual"
                                            />
                                            <button
                                                    type="button"
                                                    class="button button-link-delete remove-repeater-item"
                                                    data-container="<?php echo esc_attr($key); ?>-container"
                                                    style="color: #b32d2e;"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button
                                        type="button"
                                        class="button button-secondary add-repeater-item"
                                        data-container="<?php echo esc_attr($key); ?>-container"
                                        data-name="<?php echo esc_attr($key); ?>"
                                        style="margin-top: 8px;"
                                >
                                    + Agregar línea de especialización
                                </button>

                            <?php elseif ($config['type'] === 'number'): ?>
                                <input
                                        type="text"
                                        name="<?php echo esc_attr($key); ?>"
                                        id="<?php echo esc_attr($key); ?>"
                                        value="<?php echo $value ? '$' . number_format($value, 0, ',', '.') : ''; ?>"
                                        class="regular-text clp-input"
                                        placeholder="$30.000"
                                        data-raw-value="<?php echo esc_attr($value); ?>"
                                />
                                <p class="description">
                                    Ingresa el valor en pesos chilenos (CLP)
                                </p>

                            <?php elseif ($config['type'] === 'checkbox'):
                                $selected = is_array($value) ? $value : [];
                                ?>
                                <fieldset>
                                    <?php foreach ($config['options'] as $option): ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input
                                                    type="checkbox"
                                                    name="<?php echo esc_attr($key); ?>[]"
                                                    value="<?php echo esc_attr($option); ?>"
                                                    <?php checked(in_array($option, $selected)); ?>
                                            />
                                            <?php echo esc_html($option); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </fieldset>
                            <?php elseif ($config['type'] === 'image'): ?>
                                <div class="openmind-image-upload">
                                    <input
                                            type="hidden"
                                            name="<?php echo esc_attr($key); ?>"
                                            id="<?php echo esc_attr($key); ?>"
                                            value="<?php echo esc_attr($value); ?>"
                                    />

                                    <div class="image-preview-wrapper" style="margin-bottom: 10px;">
                                        <?php if ($value):
                                            $image_url = wp_get_attachment_url($value);
                                            ?>
                                            <img
                                                    src="<?php echo esc_url($image_url); ?>"
                                                    id="<?php echo esc_attr($key); ?>_preview"
                                                    style="max-width: 200px; height: auto; display: block; border: 2px solid #ddd; border-radius: 8px;"
                                            />
                                        <?php else: ?>
                                            <img
                                                    src=""
                                                    id="<?php echo esc_attr($key); ?>_preview"
                                                    style="max-width: 200px; height: auto; display: none; border: 2px solid #ddd; border-radius: 8px;"
                                            />
                                        <?php endif; ?>
                                    </div>

                                    <button
                                            type="button"
                                            class="button button-secondary openmind-upload-image"
                                            data-target="<?php echo esc_attr($key); ?>"
                                    >
                                        <?php echo $value ? 'Cambiar Imagen' : 'Seleccionar Imagen'; ?>
                                    </button>

                                    <?php if ($value): ?>
                                        <button
                                                type="button"
                                                class="button button-link-delete openmind-remove-image"
                                                data-target="<?php echo esc_attr($key); ?>"
                                                style="margin-left: 10px; color: #b32d2e;"
                                        >
                                            Eliminar
                                        </button>
                                    <?php endif; ?>

                                    <p class="description">
                                        Selecciona una imagen desde la biblioteca de medios
                                    </p>
                                </div>

                            <?php elseif ($config['type'] === 'editor'): ?>
                                <?php
                                wp_editor($value, $key, [
                                        'textarea_name' => $key,
                                        'textarea_rows' => 8,
                                        'media_buttons' => false,
                                        'teeny' => true,
                                        'quicktags' => false
                                ]);
                                ?>

                            <?php elseif ($config['type'] === 'url'): ?>
                                <input
                                        type="url"
                                        name="<?php echo esc_attr($key); ?>"
                                        id="<?php echo esc_attr($key); ?>"
                                        value="<?php echo esc_url($value); ?>"
                                        class="regular-text"
                                        placeholder="https://youtube.com/watch?v=..."
                                />
                                <p class="description">
                                    Ingresa una URL válida de YouTube
                                </p>

                            <?php else: ?>
                                <input
                                        type="text"
                                        name="<?php echo esc_attr($key); ?>"
                                        id="<?php echo esc_attr($key); ?>"
                                        value="<?php echo esc_attr($value); ?>"
                                        class="regular-text"
                                        <?php echo isset($config['maxlength']) ? 'maxlength="' . $config['maxlength'] . '"' : ''; ?>
                                />
                                <?php if (isset($config['maxlength'])): ?>
                                    <p class="description">
                                        Máximo <?php echo $config['maxlength']; ?> caracteres
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function saveFields($user_id): void {
        // Verificar permisos
        if (!current_user_can('edit_user', $user_id)) return;

        // Verificar nonce
        check_admin_referer('update-user_' . $user_id);

        // Verificar que sea psicólogo
        $user = get_userdata($user_id);
        if (!in_array('psychologist', $user->roles)) return;

        foreach (self::$fields as $key => $config) {
            if (!isset($_POST[$key])) {
                // Si es repeater y no viene, guardar array vacío
                if ($config['type'] === 'repeater') {
                    update_user_meta($user_id, $key, []);
                }
                continue;
            }

            $value = $_POST[$key];

            // Sanitización según tipo
            switch ($config['type']) {
                case 'repeater':
                    // Filtrar valores vacíos y sanitizar
                    $value = isset($_POST[$key]) && is_array($_POST[$key])
                            ? array_values(array_filter(array_map('sanitize_text_field', $_POST[$key])))
                            : [];
                    break;
                case 'number':
                    // Remover formato: $30.000 -> 30000
                    $value = preg_replace('/[^0-9]/', '', $value);
                    $value = absint($value);
                    break;
                case 'checkbox':
                    $value = isset($_POST[$key]) && is_array($_POST[$key])
                            ? array_map('sanitize_text_field', $_POST[$key])
                            : [];
                    break;
                case 'image':
                    $value = absint($value);
                    break;
                case 'editor':
                    $value = wp_kses_post($value);
                    break;
                case 'url':
                    $value = esc_url_raw($value);
                    if ($value && !self::validateYoutubeUrl($value)) {
                        add_action('user_profile_update_errors', function($errors) use ($config) {
                            $errors->add('invalid_youtube', 'La URL de YouTube no es válida');
                        });
                        continue 2;
                    }
                    break;
                default:
                    $value = sanitize_text_field($value);
            }

            update_user_meta($user_id, $key, $value);
        }
    }

    private static function validateYoutubeUrl($url): bool {
        $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/).+$/';
        return preg_match($pattern, $url) === 1;
    }
}