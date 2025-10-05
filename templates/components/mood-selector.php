<?php
// templates/components/mood-selector.php
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
$color = $mood_args['color'] ?? 'purple';
$required = $mood_args['required'] ?? false;

$mood_options = [
    'feliz' => ['emoji' => '😊', 'label' => 'Feliz'],
    'triste' => ['emoji' => '😢', 'label' => 'Triste'],
    'ansioso' => ['emoji' => '😰', 'label' => 'Ansioso'],
    'neutral' => ['emoji' => '😐', 'label' => 'Neutral'],
    'enojado' => ['emoji' => '😠', 'label' => 'Enojado'],
    'calmado' => ['emoji' => '😌', 'label' => 'Calmado']
];

// Colores según el theme
$colors = [
    'purple' => [
        'border' => 'border-purple-500',
        'bg' => 'bg-purple-50',
        'text' => 'text-purple-700'
    ],
    'primary' => [
        'border' => 'border-primary-500',
        'bg' => 'bg-primary-50',
        'text' => 'text-primary-700'
    ]
];

$theme = $colors[$color] ?? $colors['purple'];
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
                <div class="flex flex-col items-center gap-2 p-3 border-2 border-gray-200 rounded-lg transition-all peer-checked:<?php echo $theme['border']; ?> peer-checked:<?php echo $theme['bg']; ?> hover:border-gray-300 hover:shadow-sm">
                    <span class="text-3xl"><?php echo $mood['emoji']; ?></span>
                    <span class="text-xs font-medium text-gray-700 peer-checked:<?php echo $theme['text']; ?>">
                        <?php echo esc_html($mood['label']); ?>
                    </span>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>