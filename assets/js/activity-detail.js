/**
 * Gestión de respuestas en detalle de actividad
 */
const OpenmindActivityDetail = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        const form = document.getElementById('activity-response-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleEdit(e));
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDelete(e));
        });

        const cancelBtn = document.getElementById('cancel-edit');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.cancelEdit());
        }
    },

    async handleSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#submit-response');
        const formData = new FormData(form);

        formData.append('action', 'openmind_submit_response');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.data.message);
                location.reload(); // Recargar para mostrar respuesta
            } else {
                alert('Error: ' + data.data.message);
            }
        } catch (error) {
            alert('Error de conexión');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Respuesta';
        }
    },

    handleEdit(e) {
        const responseId = e.currentTarget.dataset.responseId;
        const responseItem = document.querySelector(`.response-item[data-response-id="${responseId}"]`);
        const content = responseItem.querySelector('.response-content').innerHTML;

        // Cargar contenido en editor
        if (typeof tinymce !== 'undefined') {
            tinymce.get('response_content').setContent(content);
        } else {
            document.getElementById('response_content').value = content;
        }

        // Cambiar a modo edición
        document.getElementById('response_id').value = responseId;
        document.getElementById('submit-response').textContent = 'Actualizar Respuesta';
        document.getElementById('cancel-edit').style.display = 'inline-block';

        // Scroll al formulario
        document.querySelector('.response-form-section').scrollIntoView({ behavior: 'smooth' });
    },

    async handleDelete(e) {
        if (!confirm('¿Estás seguro de eliminar esta respuesta?')) return;

        const responseId = e.currentTarget.dataset.responseId;

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'openmind_delete_response',
                    response_id: responseId,
                    nonce: openmindData.nonces.delete_response
                })
            });

            const data = await response.json();

            if (data.success) {
                document.querySelector(`.response-item[data-response-id="${responseId}"]`).remove();
                alert('Respuesta eliminada');
            } else {
                alert('Error: ' + data.data.message);
            }
        } catch (error) {
            alert('Error de conexión');
        }
    },

    cancelEdit() {
        document.getElementById('response_id').value = '0';
        document.getElementById('submit-response').textContent = 'Enviar Respuesta';
        document.getElementById('cancel-edit').style.display = 'none';

        if (typeof tinymce !== 'undefined') {
            tinymce.get('response_content').setContent('');
        } else {
            document.getElementById('response_content').value = '';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => OpenmindActivityDetail.init());