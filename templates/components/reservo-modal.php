<?php // templates/components/reservo-modal.php
/**
 * Modal con iframe de Reservo
 * Se muestra cuando el paciente no tiene psicÃ³logo asignado
 */

if (!defined('ABSPATH')) exit;
?>

<script>
    // Definir funciones ANTES del HTML para evitar "undefined"
    window.openReservoModal = function() {
        ModalUtils.openModal('reservo-modal');
    }

    window.closeReservoModal = function() {
        ModalUtils.closeModal('reservo-modal');
    }
</script>

<!-- Modal Reservo -->
<div id="reservo-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" data-lenis-prevent style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden" data-lenis-prevent>
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Agenda tu primera cita</h3>
                <p class="text-gray-600 mt-1">Selecciona fecha y hora para comenzar tu tratamiento</p>
            </div>
            <button type="button"
                    onclick="closeReservoModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>

        <!-- Iframe Reservo -->
        <div class="p-6" data-lenis-prevent>
            <iframe
                    src="<?php echo esc_url(OPENMIND_RESERVO_URL); ?>"
                    width="100%"
                    height="600"
                    frameborder="0"
                    class="rounded-lg"
                    style="overflow: hidden; border-radius: 10px;">
            </iframe>
        </div>

        <!-- Footer -->
        <div class="px-6 pb-6 text-center text-sm text-gray-500">
            <a href="https://www.reservo.cl" target="_blank" class="hover:text-primary-600 transition-colors">
                Powered by Reservo
            </a>
        </div>
    </div>
</div>

<style>
    #reservo-modal {
        display: none;
    }
</style>

<script>
    // Event listeners (despuÃ©s de que el DOM cargue)
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeReservoModal();
            }
        });

        // Cerrar al hacer clic fuera del contenido
        const modal = document.getElementById('reservo-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    window.closeReservoModal();
                }
            });
        }
    });
</script>