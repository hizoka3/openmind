<?php
// templates/components/modal-edit-response.php
if (!defined('ABSPATH')) exit;
?>

<!-- Modal: Editar Respuesta -->
<div id="edit-response-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-xl">
            <h3 class="text-xl font-bold text-[#333333] flex items-center gap-2">
                <i class="fa-solid fa-edit text-primary-500"></i>
                Editar respuesta
            </h3>
            <button id="close-edit-modal" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-times text-2xl"></i>
            </button>
        </div>

        <form id="edit-response-form" enctype="multipart/form-data" class="p-6">
            <input type="hidden" id="edit-response-id" name="response_id">
            <input type="hidden" id="edit-assignment-id" name="assignment_id">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Contenido de la respuesta
                </label>
                <textarea id="edit_response_content" name="response_content" rows="10" class="w-full border border-gray-300 rounded-lg p-3"></textarea>
            </div>

            <div class="mb-6">
                <label for="edit_response_files" class="block text-sm font-medium text-gray-700 mb-2">
                    Archivos adjuntos (máx. 5)
                </label>
                <input type="file"
                       name="response_files[]"
                       id="edit_response_files"
                       multiple
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                       max="5"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2">
                <p class="mt-1 text-xs text-gray-500">Formatos: PDF, DOC, DOCX, JPG, PNG, GIF</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-primary-500 text-white rounded-lg font-semibold hover:bg-primary-400 transition-colors"
                        id="save-edit-response">
                    <i class="fa-solid fa-save mr-2"></i>
                    Guardar cambios
                </button>
                <button type="button"
                        class="flex-1 px-6 py-3 bg-gray-200 text-[#333333] rounded-lg font-semibold hover:bg-gray-300 transition-colors"
                        id="cancel-edit-modal">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>