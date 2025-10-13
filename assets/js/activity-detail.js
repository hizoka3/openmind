// assets/js/activity-detail.js
/**
 * Activity Detail - Manejo unificado para paciente y psicólogo
 * Modales en HTML - JS solo maneja show/hide/submit
 */

const OpenmindActivityDetail = {
    config: {
        role: '',
        ajaxUrl: '',
        nonce: '',
        currentEditId: null
    },

    init(config) {
        this.config = { ...this.config, ...config };

        // 🔧 FIX: Leer nonce del form (ya generado por PHP)
        const nonceField = document.querySelector('input[name="response_nonce"]');
        if (nonceField) {
            this.config.nonce = nonceField.value;
        }

        this.bindEvents();
        this.initModalListeners();
    },

    bindEvents() {
        // Formulario de respuesta principal
        const forms = ['activity-response-form', 'psychologist-response-form'];
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => this.handleSubmit(e));
            }
        });

        // Botones de editar
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => this.openEditModal(e));
        });

        // Botones de ocultar
        document.querySelectorAll('.btn-hide').forEach(btn => {
            btn.addEventListener('click', (e) => this.openHideModal(e));
        });
    },

    initModalListeners() {
        // Modal Editar
        const editModal = document.getElementById('edit-response-modal');
        if (editModal) {
            document.getElementById('close-edit-modal')?.addEventListener('click', () => this.closeEditModal());
            document.getElementById('cancel-edit-modal')?.addEventListener('click', () => this.closeEditModal());
            document.getElementById('edit-response-form')?.addEventListener('submit', (e) => this.submitEdit(e));

            // Cerrar al hacer clic fuera
            editModal.addEventListener('click', (e) => {
                if (e.target.id === 'edit-response-modal') {
                    this.closeEditModal();
                }
            });
        }

        // Modal Ocultar
        const hideModal = document.getElementById('hide-response-modal');
        if (hideModal) {
            document.getElementById('confirm-hide')?.addEventListener('click', () => this.confirmHide());
            document.getElementById('cancel-hide')?.addEventListener('click', () => this.closeHideModal());

            // Cerrar al hacer clic fuera
            hideModal.addEventListener('click', (e) => {
                if (e.target.id === 'hide-response-modal') {
                    this.closeHideModal();
                }
            });
        }

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeEditModal();
                this.closeHideModal();
            }
        });
    },

    // ========== SUBMIT RESPONSE (Form principal) ==========
    async handleSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#submit-response');
        const formData = new FormData(form);

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

        if (!content || content.trim() === '' || content === '<p><br data-mce-bogus="1"></p>') {
            Toast.show('Por favor escribe un comentario', 'error');
            return;
        }

        formData.set(editorId === 'psychologist_response' ? 'psychologist_response' : 'response_content', content);

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
                Toast.show(data.data || 'Error al enviar respuesta', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexión', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    // ========== EDITAR RESPUESTA ==========
    openEditModal(e) {
        const responseId = e.currentTarget.dataset.responseId;
        const responseItem = document.querySelector(`[data-response-id="${responseId}"]`);
        const content = responseItem.querySelector('.response-content').innerHTML;

        // Poblar datos del modal
        document.getElementById('edit-response-id').value = responseId;
        document.getElementById('edit-assignment-id').value = document.querySelector('input[name="assignment_id"]')?.value || '';

        // Mostrar modal
        const modal = document.getElementById('edit-response-modal');
        modal.classList.remove('hidden');

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
            } else {
                document.getElementById('edit_response_content').value = content;
            }
        }, 100);

        this.config.currentEditId = responseId;
    },

    closeEditModal() {
        const modal = document.getElementById('edit-response-modal');
        if (modal) {
            modal.classList.add('hidden');

            // Destruir TinyMCE
            if (typeof tinymce !== 'undefined' && tinymce.get('edit_response_content')) {
                tinymce.get('edit_response_content').remove();
            }

            // Limpiar form
            document.getElementById('edit-response-form').reset();
        }
        this.config.currentEditId = null;
    },

    async submitEdit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#save-edit-response');
        const formData = new FormData(form);

        // Obtener contenido del editor
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
            Toast.show('Error de conexión', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    // ========== OCULTAR RESPUESTA ==========
    openHideModal(e) {
        const responseId = e.currentTarget.dataset.responseId;

        // Poblar ID en el modal
        document.getElementById('hide-response-id').value = responseId;

        // Mostrar modal
        document.getElementById('hide-response-modal').classList.remove('hidden');
    },

    closeHideModal() {
        document.getElementById('hide-response-modal').classList.add('hidden');
    },

    async confirmHide() {
        const responseId = document.getElementById('hide-response-id').value;

        if (!responseId) {
            Toast.show('Error: ID de respuesta no encontrado', 'error');
            return;
        }

        this.closeHideModal();

        try {
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'openmind_hide_response',
                    response_id: responseId,
                    response_nonce: this.config.nonce // 🔧 FIX: cambiado de 'nonce' a 'response_nonce'
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
            Toast.show('Error de conexión', 'error');
        }
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    if (typeof openmindData !== 'undefined') {
        OpenmindActivityDetail.init({
            ajaxUrl: openmindData.ajaxUrl,
            nonce: openmindData.nonce,
            role: openmindData.userRole || 'patient'
        });
    }
});