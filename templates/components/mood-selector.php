<?php // templates/components/mood-selector.php
/**
 * Componente reutilizable para seleccionar estado de ánimo
 *
 * @param array $mood_args {
 *     @type string $name          Nombre del campo (default: 'mood')
 *     @type string $selected      Valor seleccionado
 *     @type string $label         Label del campo (default: '¿Cómo te sientes hoy?')
 *     @type string $color         Color theme: 'purple' | 'primary' (default: 'purple')
 *     @type bool   $required      Si es requerido (default: false)
 * }
 */

$name = $mood_args['name'] ?? 'mood';
$selected = $mood_args['selected'] ?? '';
$label = $mood_args['label'] ?? '¿Cómo te sientes hoy?';
$required = $mood_args['required'] ?? false;

$mood_options = [
        'feliz' => ['emoji' => '😊', 'label' => 'Feliz'],
        'triste' => ['emoji' => '😢', 'label' => 'Triste'],
        'ansioso' => ['emoji' => '😰', 'label' => 'Ansioso'],
        'neutral' => ['emoji' => '😐', 'label' => 'Neutral'],
        'enojado' => ['emoji' => '😠', 'label' => 'Enojado'],
        'calmado' => ['emoji' => '😌', 'label' => 'Calmado']
];

?>

<div class="mb-6">
    <label class="block text-sm font-semibold text-gray-700 mb-3">
        <i class="fa-solid fa-face-smile mr-2"></i>
        <?php echo esc_html($label); ?>
        <?php if ($required): ?>
            <span class="text-red-500">*</span>
        <?php endif; ?>
    </label>

    <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
        <?php foreach ($mood_options as $value => $mood): ?>
            <label class="cursor-pointer">
                <input type="radio"
                       name="<?php echo esc_attr($name); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       class="peer sr-only"
                        <?php checked($selected, $value); ?>
                        <?php echo $required ? 'required' : ''; ?>>

                <div class="flex flex-col items-center gap-2 p-3 border-2 border-gray-200 rounded-lg transition-all hover:border-gray-300 hover:shadow-sm peer-checked:border-primary-500 peer-checked:bg-primary-50">
                    <span class="text-3xl"><?php echo $mood['emoji']; ?></span>
                    <span class="text-xs font-medium text-gray-600 peer-checked:text-gray-800">
                        <?php echo esc_html($mood['label']); ?>
                    </span>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>