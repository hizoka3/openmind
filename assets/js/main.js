// assets/js/main.js
const OpenmindApp = {
    init() {
        this.bindEvents();
        this.initModals();
    },

    bindEvents() {
        // Completar actividad
        document.querySelectorAll('[data-action="complete-activity"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.completeActivity(e));
        });

        // Ver paciente
        document.querySelectorAll('[data-action="view-patient"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewPatient(e));
        });

        // Mensajear paciente
        document.querySelectorAll('[data-action="message-patient"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.messagePatient(e));
        });

        // Agregar paciente
        const addPatientBtn = document.getElementById('add-patient');
        if (addPatientBtn) addPatientBtn.addEventListener('click', () => this.showAddPatientModal());

        // Crear actividad
        const createActivityBtn = document.getElementById('create-activity');
        if (createActivityBtn) createActivityBtn.addEventListener('click', () => this.showCreateActivityModal());

        // Asignar actividad
        document.querySelectorAll('[data-action="assign-activity"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.showAssignActivityModal(e));
        });

        // Editar actividad
        document.querySelectorAll('[data-action="edit-activity"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.editActivity(e));
        });

        // Nueva entrada de bitácora
        const diaryBtn = document.getElementById('new-diary-entry');
        if (diaryBtn) diaryBtn.addEventListener('click', () => this.showDiaryModal());

        // Eliminar entrada de bitácora
        document.querySelectorAll('[data-action="delete-diary"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.deleteDiaryEntry(e));
        });

        // Editar perfil
        const editProfileBtn = document.getElementById('edit-profile');
        if (editProfileBtn) editProfileBtn.addEventListener('click', () => this.showEditProfileModal());

        // Cambiar contraseña
        const changePasswordBtn = document.getElementById('change-password');
        if (changePasswordBtn) changePasswordBtn.addEventListener('click', () => this.showChangePasswordModal());

        // Conversaciones (mensajería)
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', (e) => this.loadConversation(e));
        });
    },

    initModals() {
        // Cerrar modales al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('openmind-modal')) {
                this.closeModal(e.target.id);
            }
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.openmind-modal[style*="display: flex"]');
                if (openModal) this.closeModal(openModal.id);
            }
        });
    },

    // === COMPLETAR ACTIVIDAD ===
    async completeActivity(e) {
        const btn = e.currentTarget;
        const activityId = btn.dataset.activityId;

        btn.disabled = true;
        btn.textContent = 'Guardando...';

        try {
            const formData = new FormData();
            formData.append('action', 'openmind_complete_activity');
            formData.append('nonce', openmindData.nonce);
            formData.append('activity_id', activityId);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const card = btn.closest('.activity-card');
                card.classList.add('completed');
                card.dataset.status = 'completed';
                btn.replaceWith('<span class="completed-badge">✅ Completada</span>');

                this.showNotification('¡Actividad completada!', 'success');
            } else {
                throw new Error(data.data?.message || 'Error al completar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al completar la actividad', 'error');
            btn.disabled = false;
            btn.textContent = 'Marcar como completada';
        }
    },

    // === AGREGAR PACIENTE ===
    showAddPatientModal() {
        const modal = this.createModal('add-patient-modal', 'Agregar Paciente', `
            <form id="add-patient-form">
                <div class="form-group">
                    <label>Email del paciente</label>
                    <input 
                        type="email" 
                        name="patient_email" 
                        class="form-control" 
                        placeholder="paciente@email.com"
                        required
                    >
                    <small class="form-help">Si el paciente no existe, se creará automáticamente</small>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="OpenmindApp.closeModal('add-patient-modal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-user-plus"></i>
                        Agregar
                    </button>
                </div>
            </form>
        `);

        document.getElementById('add-patient-form').addEventListener('submit', (e) => this.addPatient(e));
    },

    async addPatient(e) {
        e.preventDefault();
        const form = e.target;
        const email = form.patient_email.value;

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Agregando...';

        try {
            const formData = new FormData();
            formData.append('action', 'openmind_add_patient');
            formData.append('nonce', openmindData.nonce);
            formData.append('patient_email', email);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Paciente agregado exitosamente', 'success');
                this.closeModal('add-patient-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al agregar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Agregar';
        }
    },

    // === CREAR ACTIVIDAD ===
    showCreateActivityModal() {
        const modal = this.createModal('create-activity-modal', 'Crear Actividad', `
            <form id="create-activity-form">
                <div class="form-group">
                    <label>Título</label>
                    <input 
                        type="text" 
                        name="title" 
                        class="form-control" 
                        placeholder="Nombre de la actividad"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea 
                        name="content" 
                        class="form-control" 
                        rows="5"
                        placeholder="Describe la actividad..."
                        required
                    ></textarea>
                </div>
                <div class="form-group">
                    <label>Fecha límite (opcional)</label>
                    <input 
                        type="date" 
                        name="due_date" 
                        class="form-control"
                    >
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="OpenmindApp.closeModal('create-activity-modal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-check"></i>
                        Crear
                    </button>
                </div>
            </form>
        `);

        document.getElementById('create-activity-form').addEventListener('submit', (e) => this.createActivity(e));
    },

    async createActivity(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'openmind_create_activity');
        formData.append('nonce', openmindData.nonce);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creando...';

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Actividad creada exitosamente', 'success');
                this.closeModal('create-activity-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al crear');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Crear';
        }
    },

    // === ASIGNAR ACTIVIDAD ===
    async showAssignActivityModal(e) {
        const activityId = e.currentTarget.dataset.id;

        // Obtener lista de pacientes
        const formData = new FormData();
        formData.append('action', 'openmind_get_patients');
        formData.append('nonce', openmindData.nonce);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const patients = data.data.patients;
                const patientsHtml = patients.map(p => `
                    <label class="patient-option">
                        <input type="radio" name="patient_id" value="${p.ID}" required>
                        <div class="patient-info">
                            <img src="${p.avatar}" alt="${p.display_name}">
                            <div>
                                <strong>${p.display_name}</strong>
                                <span>${p.user_email}</span>
                            </div>
                        </div>
                    </label>
                `).join('');

                const modal = this.createModal('assign-activity-modal', 'Asignar Actividad', `
                    <form id="assign-activity-form">
                        <input type="hidden" name="activity_id" value="${activityId}">
                        <div class="form-group">
                            <label>Selecciona un paciente</label>
                            <div class="patients-list">
                                ${patientsHtml}
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="OpenmindApp.closeModal('assign-activity-modal')">
                                Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fa-solid fa-check"></i>
                                Asignar
                            </button>
                        </div>
                    </form>
                `);

                document.getElementById('assign-activity-form').addEventListener('submit', (e) => this.assignActivity(e));
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cargar pacientes', 'error');
        }
    },

    async assignActivity(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'openmind_assign_activity');
        formData.append('nonce', openmindData.nonce);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Asignando...';

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Actividad asignada exitosamente', 'success');
                this.closeModal('assign-activity-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al asignar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Asignar';
        }
    },

    // === BITÁCORA / DIARIO ===
    showDiaryModal() {
        const modal = this.createModal('diary-modal', 'Nueva Entrada', `
            <form id="diary-form">
                <div class="form-group">
                    <label>¿Cómo te sientes?</label>
                    <div class="mood-selector">
                        <label><input type="radio" name="mood" value="feliz"> 😊 Feliz</label>
                        <label><input type="radio" name="mood" value="triste"> 😢 Triste</label>
                        <label><input type="radio" name="mood" value="ansioso"> 😰 Ansioso</label>
                        <label><input type="radio" name="mood" value="neutral"> 😐 Neutral</label>
                        <label><input type="radio" name="mood" value="enojado"> 😠 Enojado</label>
                        <label><input type="radio" name="mood" value="calmado"> 😌 Calmado</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Contenido</label>
                    <textarea 
                        name="content" 
                        class="form-control" 
                        rows="8"
                        placeholder="Escribe tus pensamientos..."
                        required
                    ></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="OpenmindApp.closeModal('diary-modal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-save"></i>
                        Guardar
                    </button>
                </div>
            </form>
        `);

        document.getElementById('diary-form').addEventListener('submit', (e) => this.saveDiaryEntry(e));
    },

    async saveDiaryEntry(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'openmind_save_diary');
        formData.append('nonce', openmindData.nonce);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Entrada guardada exitosamente', 'success');
                this.closeModal('diary-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Guardar';
        }
    },

    async deleteDiaryEntry(e) {
        if (!confirm('¿Estás seguro de eliminar esta entrada?')) return;

        const btn = e.currentTarget;
        const entryId = btn.dataset.entryId;

        try {
            const formData = new FormData();
            formData.append('action', 'openmind_delete_diary');
            formData.append('nonce', openmindData.nonce);
            formData.append('entry_id', entryId);

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const entry = btn.closest('.diary-entry, .diary-entry-card');
                entry.style.opacity = '0';
                setTimeout(() => entry.remove(), 300);
                this.showNotification('Entrada eliminada', 'success');
            } else {
                throw new Error(data.data?.message || 'Error al eliminar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al eliminar la entrada', 'error');
        }
    },

    // === MENSAJERÍA ===
    async loadConversation(e) {
        const item = e.currentTarget;
        const otherUserId = item.dataset.userId;

        // Marcar como activo
        document.querySelectorAll('.conversation-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        // Cargar mensajes
        const formData = new FormData();
        formData.append('action', 'openmind_get_messages');
        formData.append('nonce', openmindData.nonce);
        formData.append('other_user_id', otherUserId);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.renderMessages(data.data.messages, otherUserId);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cargar mensajes', 'error');
        }
    },

    renderMessages(messages, otherUserId) {
        const container = document.getElementById('message-thread');
        const currentUserId = openmindData.userId;

        if (messages.length === 0) {
            container.innerHTML = '<div class="empty-thread"><p>No hay mensajes aún. ¡Inicia la conversación!</p></div>';
            return;
        }

        const messagesHtml = messages.map(msg => `
            <div class="message ${msg.sender_id == currentUserId ? 'sent' : 'received'}">
                <div class="message-content">${msg.message}</div>
                <div class="message-time">${this.formatDate(msg.created_at)}</div>
            </div>
        `).join('');

        container.innerHTML = `
            <div class="messages-container">
                ${messagesHtml}
            </div>
            <div class="message-input">
                <form id="send-message-form">
                    <input type="hidden" name="receiver_id" value="${otherUserId}">
                    <textarea 
                        name="message" 
                        placeholder="Escribe un mensaje..." 
                        rows="2"
                        required
                    ></textarea>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        `;

        // Scroll to bottom
        const messagesContainer = container.querySelector('.messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Bind send message
        document.getElementById('send-message-form').addEventListener('submit', (e) => this.sendMessage(e));
    },

    async sendMessage(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'openmind_send_message');
        formData.append('nonce', openmindData.nonce);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                form.reset();
                // Recargar conversación
                const receiverId = form.receiver_id.value;
                const activeConv = document.querySelector(`.conversation-item[data-user-id="${receiverId}"]`);
                if (activeConv) {
                    this.loadConversation({ currentTarget: activeConv });
                }
            } else {
                throw new Error(data.data?.message || 'Error al enviar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al enviar mensaje', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    },

    messagePatient(e) {
        const patientId = e.currentTarget.dataset.patientId;
        window.location.href = `?view=mensajeria&patient=${patientId}`;
    },

    // === PERFIL ===
    showEditProfileModal() {
        this.showNotification('Función en desarrollo', 'info');
    },

    showChangePasswordModal() {
        const modal = this.createModal('change-password-modal', 'Cambiar Contraseña', `
            <form id="change-password-form">
                <div class="form-group">
                    <label>Contraseña actual</label>
                    <input 
                        type="password" 
                        name="current_password" 
                        class="form-control"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <input 
                        type="password" 
                        name="new_password" 
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>
                <div class="form-group">
                    <label>Confirmar nueva contraseña</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        class="form-control"
                        minlength="8"
                        required
                    >
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="OpenmindApp.closeModal('change-password-modal')">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-key"></i>
                        Cambiar
                    </button>
                </div>
            </form>
        `);

        document.getElementById('change-password-form').addEventListener('submit', (e) => this.changePassword(e));
    },

    async changePassword(e) {
        e.preventDefault();
        const form = e.target;

        if (form.new_password.value !== form.confirm_password.value) {
            this.showNotification('Las contraseñas no coinciden', 'error');
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'openmind_change_password');
        formData.append('nonce', openmindData.nonce);

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cambiando...';

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Contraseña cambiada exitosamente', 'success');
                this.closeModal('change-password-modal');
                form.reset();
            } else {
                throw new Error(data.data?.message || 'Error al cambiar contraseña');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-key"></i> Cambiar';
        }
    },

    viewPatient(e) {
        const patientId = e.currentTarget.dataset.patientId;
        window.location.href = `?view=pacientes&patient_id=${patientId}`;
    },

    editActivity(e) {
        const activityId = e.currentTarget.dataset.id;
        this.showNotification('Función en desarrollo', 'info');
    },

    // === UTILIDADES ===
    createModal(id, title, content) {
        // Remover modal existente si hay
        const existing = document.getElementById(id);
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'openmind-modal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-header">
                    <h2>${title}</h2>
                    <button type="button" class="modal-close" onclick="OpenmindApp.closeModal('${id}')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        setTimeout(() => modal.style.display = 'flex', 10);

        return modal;
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            setTimeout(() => modal.remove(), 300);
        }
    },

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `openmind-notification ${type}`;

        const icons = {
            success: 'fa-circle-check',
            error: 'fa-circle-xmark',
            info: 'fa-circle-info',
            warning: 'fa-triangle-exclamation'
        };

        notification.innerHTML = `
            <i class="fa-solid ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }, 100);
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (hours < 1) return 'Hace un momento';
        if (hours < 24) return `Hace ${hours}h`;
        if (days < 7) return `Hace ${days}d`;

        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

document.addEventListener('DOMContentLoaded', () => OpenmindApp.init());