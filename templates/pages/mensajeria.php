<?php
/**
 * Template unificado de mensajería
 * Funciona para psicólogos y pacientes
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$is_psychologist = current_user_can('manage_patients');
$other_user_id = intval($_GET['user_id'] ?? 0);
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-comments mr-3 text-primary-500"></i>
            Mensajería
        </h1>
        <p class="text-gray-600 m-0">
            <?php echo $is_psychologist ? 'Conversaciones con tus pacientes' : 'Conversación con tu psicólogo'; ?>
        </p>
    </div>

    <div class="messages-layout">
        <!-- Sidebar de conversaciones -->
        <div class="conversations-sidebar">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700 m-0">
                    <?php echo $is_psychologist ? 'Pacientes' : 'Psicólogos'; ?>
                </h3>
            </div>
            <div id="conversations-list" class="overflow-y-auto" style="max-height: 600px;">
                <div class="flex items-center justify-center py-8 text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Cargando conversaciones...
                </div>
            </div>
        </div>

        <!-- Thread de mensajes -->
        <div class="message-thread">
            <div id="message-thread">
                <?php if ($other_user_id): ?>
                    <div class="flex items-center justify-center py-8 text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Cargando mensajes...
                    </div>
                <?php else: ?>
                    <div class="empty-thread flex flex-col items-center justify-center py-16 text-gray-400">
                        <i class="fa-solid fa-comments text-6xl mb-4 text-gray-300"></i>
                        <p class="text-lg not-italic text-gray-600">
                            Selecciona una conversación para ver los mensajes
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof OpenmindMessages !== 'undefined') {
            OpenmindMessages.init(<?php echo $other_user_id; ?>);
        }
    });
</script>