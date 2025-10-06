<?php
// templates/components/header.php
$current_user = wp_get_current_user();
$role = $args['role'] ?? 'patient';
$title = $role === 'psychologist' ? 'Panel de Psicólogo' : 'Mi Espacio';
$base_url = get_permalink();
$messages_url = add_query_arg('view', 'mensajeria', $base_url);
?>

<header class="bg-primary-100 border-b border-gray-200 sticky top-0 z-10">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Título
            <h1 class="text-2xl font-bold text-gray-900 m-0"><?php echo esc_html($title); ?></h1>
            -->
            <div></div>

            <!-- Acciones del Header -->
            <div class="flex items-center gap-4">

                <!-- Mensajes -->
                <a href="<?php echo esc_url($messages_url); ?>"
                   class="bg-primary-500 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all no-underline"
                   title="Mensajes">
                    <i class="fa-solid fa-message text-xl"></i>
                    <span class="hidden md:inline">Mensajes</span>
                    <span class="absolute -top-1 -right-1 bg-primary-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
                          id="header-messages-badge"
                          style="display:none;">
                        0
                    </span>
                </a>

                <!-- Botón Salir -->
                <a href="<?php echo wp_logout_url(home_url()); ?>"
                   class="bg-primary-500 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all no-underline">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden md:inline">Salir</span>
                </a>
            </div>
        </div>
    </div>
</header>