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

<div class="tw-max-w-7xl tw-mx-auto">
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-2">
            <i class="fa-solid fa-comments tw-mr-3 tw-text-primary-500"></i>
            Mensajería
        </h1>
        <p class="tw-text-gray-600 tw-m-0">
            <?php echo $is_psychologist ? 'Conversaciones con tus pacientes' : 'Conversación con tu psicólogo'; ?>
        </p>
    </div>

    <div class="messages-layout">
        <!-- Sidebar de conversaciones -->
        <div class="conversations-sidebar">
            <div class="tw-p-4 tw-border-b tw-border-gray-200 tw-bg-gray-50">
                <h3 class="tw-text-sm tw-font-semibold tw-text-gray-700 tw-m-0">
                    <?php echo $is_psychologist ? 'Pacientes' : 'Psicólogos'; ?>
                </h3>
            </div>
            <div id="conversations-list" class="tw-overflow-y-auto" style="max-height: 600px;">
                <div class="tw-flex tw-items-center tw-justify-center tw-py-8 tw-text-gray-400">
                    <i class="fa-solid fa-spinner fa-spin tw-mr-2"></i>
                    Cargando conversaciones...
                </div>
            </div>
        </div>

        <!-- Thread de mensajes -->
        <div class="message-thread">
            <div id="message-thread">
                <?php if ($other_user_id): ?>
                    <div class="tw-flex tw-items-center tw-justify-center tw-py-8 tw-text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin tw-mr-2"></i>
                        Cargando mensajes...
                    </div>
                <?php else: ?>
                    <div class="empty-thread tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-16 tw-text-gray-400">
                        <i class="fa-solid fa-comments tw-text-6xl tw-mb-4 tw-text-gray-300"></i>
                        <p class="tw-text-lg tw-not-italic tw-text-gray-600">
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