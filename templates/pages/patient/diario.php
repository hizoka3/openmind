<?php
// templates/pages/patient/diario.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-normal text-gray-900 m-0">
            Mi Diario Personal
        </h1>
        <p class="text-dark-gray-300 m-0">
            Tu espacio privado para registrar pensamientos y emociones día a día
        </p>
    </div>

    <!-- Info Box -->
    <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-6 rounded-lg">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-lock text-dark-gray-300 text-xl mt-1"></i>
            <div class="flex-1">
                <p class="text-sm font-semibold text-dark-gray-300 m-0 mb-1">
                    Privado por defecto
                </p>
                <p class="text-sm text-dark-gray-300 m-0">
                    Tus entradas son privadas. Puedes compartir entradas específicas con
                    <?php echo $psychologist ? '<strong>' . esc_html($psychologist->display_name) . '</strong>' : 'tu psicólogo'; ?>.
                </p>
            </div>
        </div>
    </div>

    <!-- Botón Nueva Entrada -->
    <div class="mb-8">
        <a href="<?php echo add_query_arg('view', 'diario-nuevo', home_url('/dashboard-paciente/')); ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-lg border-0 text-sm font-semibold transition-all hover:-translate-y-0.5 hover:shadow-lg shadow-none no-underline">
            <i class="fa-solid fa-plus"></i>
            Nueva Entrada
        </a>
    </div>

    <!-- Lista de Entradas -->
    <?php
    $args = [
            'user_id' => $user_id,
            'show_shared_only' => false,
            'is_psychologist' => false,
            'per_page' => 10
    ];
    include OPENMIND_PATH . 'templates/components/diary-list.php';
    ?>
</div>