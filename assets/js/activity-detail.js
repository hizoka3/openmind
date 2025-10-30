// assets/js/activity-detail.js
/**
 * Activity Detail - Manejo unificado para paciente y psicÃ³logo
 */

const OpenmindActivityDetail = {
    config: {
        role: '',
        ajaxUrl: '',
        nonce: '',
        editorId: '',
        currentEditId: null
    },

    init(config) {
        this.config = { ...this.config, ...config };
        this.bindEvents();
        this.initAccordions(); // ðŸ‘ˆ NUEVO: Inicializar acordeones explÃ­citamente
    },

    bindEvents() {
        // Formulario de respuesta
        const forms = ['activity-response-form', 'psychologist-response-form'];
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => this.handleSubmit(e));
            }
        });

        // Botones de editar
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleEdit(e));
        });

        // Botones de ocultar
        document.querySelectorAll('.btn-hide').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleHide(e));
        });

        // Botones de toggle hidden (mostrar/ocultar mensaje oculto)
        document.querySelectorAll('.btn-toggle-hidden').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleToggleHidden(e));
        });
    },

    initAccordions() {
        // Inicializar acordeones directamente
        document.querySelectorAll('.accordion-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleAccordionToggle(btn);
            });
        });
    },

    async handleSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#submit-response');
        const formData = new FormData(form);

        // Determinar action segÃºn el formulario
        const action = form.id === 'psychologist-response-form'
            ? 'openmind_psychologist_response'
            : 'openmind_submit_response';

        formData.append('action', action);

        // Obtener contenido del editor
        const editorId = form.id === 'psychologist-response-form' ? 'psychologist_response' : 'response_content';
        let content = '';

        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            content = tinymce.get(editorId).getContent();
        } else {
            const textarea = document.getElementById(editorId);
            content = textarea ? textarea.value : '';
        }

        // Validar contenido
        if (!content || content.trim() === '' || content === '<p><br data-mce-bogus="1"></p>') {
            Toast.show('Por favor escribe un comentario', 'error');
            return;
        }

        // Asegurar que el contenido se incluya en FormData
        formData.set(editorId, content);

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Enviando...';

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.show(data.data.message || 'Respuesta enviada correctamente', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.show(data.data?.message || 'Error al enviar respuesta', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexiÃ³n', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    handleEdit(e) {
        const responseId = e.currentTarget.dataset.responseId;
        const responseItem = document.querySelector(`[data-response-id="${responseId}"]`);
        const content = responseItem.querySelector('.response-content').innerHTML;

        this.config.currentEditId = responseId;
        this.openEditModal(content, responseId);
    },

    openEditModal(content, responseId) {
        // Crear modal
        const modal = document.createElement('div');
        modal.id = 'edit-response-modal';
        modal.className = 'hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-[#333333] flex items-center gap-2">
                        <i class="fa-solid fa-edit text-primary-500"></i>
                        Editar respuesta
                    </h3>
                    <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <form id="edit-response-form" enctype="multipart/form-data">
                        <input type="hidden" name="response_id" value="${responseId}">
                        <input type="hidden" name="assignment_id" value="${document.querySelector('input[name="assignment_id"]')?.value || ''}">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contenido de la respuesta
                            </label>
                            <textarea id="edit_response_content" name="response_content" rows="10" class="w-full border border-gray-300 rounded-lg p-3">${content}</textarea>
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
        `;

        document.body.appendChild(modal);

        // Abrir modal usando ModalUtils
        ModalUtils.openModal('edit-response-modal');

        // Inicializar TinyMCE en el modal
        setTimeout(() => {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#edit_response_content',
                    menubar: false,
                    height: 300,
                    plugins: 'lists link',
                    toolbar: 'bold italic underline | bullist numlist | link',
                    setup: (editor) => {
                        editor.on('init', () => {
                            editor.setContent(content);
                        });
                    }
                });
            }
        }, 100);

        // Event listeners del modal
        document.getElementById('close-edit-modal').addEventListener('click', () => this.closeEditModal());
        document.getElementById('cancel-edit-modal').addEventListener('click', () => this.closeEditModal());
        document.getElementById('edit-response-form').addEventListener('submit', (e) => this.submitEdit(e));

        // Cerrar al hacer click fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeEditModal();
            }
        });
    },

    closeEditModal() {
        const modal = document.getElementById('edit-response-modal');
        if (modal) {
            // Destruir TinyMCE
            if (typeof tinymce !== 'undefined' && tinymce.get('edit_response_content')) {
                tinymce.get('edit_response_content').remove();
            }

            // Cerrar modal con ModalUtils
            ModalUtils.closeModal('edit-response-modal');

            // Remover elemento del DOM
            modal.remove();
        }
        this.config.currentEditId = null;
    },

    async submitEdit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#save-edit-response');
        const formData = new FormData(form);

        // Obtener contenido del editor del modal
        let content = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('edit_response_content')) {
            content = tinymce.get('edit_response_content').getContent();
        } else {
            content = document.getElementById('edit_response_content').value;
        }

        if (!content || content.trim() === '' || content === '<p><br data-mce-bogus="1"></p>') {
            Toast.show('Por favor escribe un comentario', 'error');
            return;
        }

        formData.set('response_content', content);
        formData.append('action', 'openmind_submit_response');
        formData.append('response_nonce', this.config.nonce);

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Guardando...';

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.show(data.data.message || 'Respuesta actualizada correctamente', 'success');
                this.closeEditModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.show(data.data?.message || 'Error al actualizar respuesta', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexiÃ³n', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    handleHide(e) {
        const responseId = e.currentTarget.dataset.responseId;

        const modal = this.createHideModal();
        modal.id = 'hide-response-modal-dynamic';
        document.body.appendChild(modal);

        // Abrir modal con ModalUtils
        ModalUtils.openModal('hide-response-modal-dynamic');

        modal.querySelector('#confirm-hide').addEventListener('click', async () => {
            ModalUtils.closeModal('hide-response-modal-dynamic');
            modal.remove();
            await this.hideResponse(responseId);
        });

        modal.querySelector('#cancel-hide').addEventListener('click', () => {
            ModalUtils.closeModal('hide-response-modal-dynamic');
            modal.remove();
        });
    },

    createHideModal() {
        const modal = document.createElement('div');
        modal.className = 'hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl max-w-md w-full mx-4 p-6">
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
        `;
        return modal;
    },

    async hideResponse(responseId) {
        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'openmind_hide_response',
                    response_id: responseId,
                    nonce: this.config.nonce
                })
            });

            const data = await response.json();

            if (data.success) {
                Toast.show(data.data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                Toast.show(data.data || 'Error al ocultar respuesta', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexiÃ³n', 'error');
        }
    },

    handleToggleHidden(e) {
        const responseId = e.currentTarget.dataset.responseId;

        // Buscar el placeholder gris y el contenido oculto por data-response-id
        const placeholder = document.querySelector(`[data-response-id="${responseId}"].bg-gray-100`);
        const hiddenContent = document.querySelector(`.hidden-response-content[data-response-id="${responseId}"]`);

        if (hiddenContent && placeholder) {
            // Toggle visibility
            const isCurrentlyHidden = hiddenContent.classList.contains('hidden');

            if (isCurrentlyHidden) {
                // Mostrar contenido, ocultar placeholder
                hiddenContent.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                // Ocultar contenido, mostrar placeholder
                hiddenContent.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
        }
    },

    handleAccordionToggle(button) {
        const targetId = button.dataset.target;
        const content = document.getElementById(targetId);
        const icon = button.querySelector('.accordion-icon');

        if (content && icon) {
            const isHidden = content.classList.contains('hidden');

            if (isHidden) {
                // Abrir acordeÃ³n
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                // Cerrar acordeÃ³n
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    }
};

// Inicializar cuando el DOM estÃ© listo
document.addEventListener('DOMContentLoaded', () => {
    if (typeof openmindData !== 'undefined') {
        OpenmindActivityDetail.init({
            ajaxUrl: openmindData.ajaxUrl,
            nonce: openmindData.nonce,
            role: openmindData.userRole || 'patient'
        });
    }
});