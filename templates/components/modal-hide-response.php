<?php
// templates/components/modal-hide-response.php
if (!defined('ABSPATH')) exit;
?>

<!-- Modal: Ocultar Respuesta -->
<div id="hide-response-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-eye-slash text-orange-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-[#333333] m-0">Â¿Ocultar este mensaje?</h3>
        </div>

        <div class="mb-6 space-y-3">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-check text-green-600 mt-1"></i>
                <p class="text-sm text-gray-700 m-0">Se ocultarÃ¡ de tu vista</p>
            </div>
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-user-doctor text-primary-500 mt-1"></i>
                <p class="text-sm text-gray-700 m-0">Tu psicÃ³logo seguirÃ¡ viÃ©ndolo</p>
            </div>
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-shield-halved text-primary-400 mt-1"></i>
                <p class="text-sm text-gray-700 m-0">Se conserva por motivos clÃ­nicos y legales</p>
            </div>
        </div>

        <input type="hidden" id="hide-response-id">

        <div class="flex gap-3">
            <button id="confirm-hide"
                    class="flex-1 px-4 py-3 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition-colors">
                <i class="fa-solid fa-eye-slash mr-2"></i>
                Ocultar
            </button>
            <button id="cancel-hide"
                    class="flex-1 px-4 py-3 bg-gray-200 text-[#333333] rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    // Cerrar modal con botón Cancelar
    document.getElementById('cancel-hide')?.addEventListener('click', () => {
        ModalUtils.closeModal('hide-response-modal');
    });

    // Cerrar al hacer clic fuera del contenido
    document.getElementById('hide-response-modal')?.addEventListener('click', (e) => {
        if (e.target.id === 'hide-response-modal') {
            ModalUtils.closeModal('hide-response-modal');
        }
    });
</script>