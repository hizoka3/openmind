<?php
namespace Openmind\Admin;

class PsychologistProfile {

    private static $fields = [
        'descripcion_corta' => ['type' => 'text', 'label' => 'Descripción Corta', 'maxlength' => 200],
        'descripcion_larga' => ['type' => 'editor', 'label' => 'Descripción Larga'],
        'video_de_perfil_youtube' => ['type' => 'url', 'label' => 'Video de Perfil (YouTube)'],
        'universidad' => ['type' => 'text', 'label' => 'Universidad'],
        'id_reservo' => ['type' => 'text', 'label' => 'ID Reservo'],
        'linea_de_especializacion' => ['type' => 'text', 'label' => 'Línea de Especialización']
    ];

    public static function register(): void {
        add_action('show_user_profile', [self::class, 'renderFields']);
        add_action('edit_user_profile', [self::class, 'renderFields']);
        add_action('personal_options_update', [self::class, 'saveFields']);
        add_action('edit_user_profile_update', [self::class, 'saveFields']);
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
                            <?php if ($config['type'] === 'editor'): ?>
                                <?php
                                wp_editor($value, $key, [
                                    'textarea_name' => $key,
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny' => true
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
            if (!isset($_POST[$key])) continue;

            $value = $_POST[$key];

            // Sanitización según tipo
            switch ($config['type']) {
                case 'editor':
                    $value = wp_kses_post($value);
                    break;
                case 'url':
                    $value = esc_url_raw($value);
                    // Validar YouTube
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