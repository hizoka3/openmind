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

                Toast.show(data.data.message || '¡Actividad completada!', 'success');
            } else {
                throw new Error(data.data?.message || 'Error al completar');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message || 'Error al completar la actividad', 'error');
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
                Toast.show(data.data.message || 'Paciente agregado exitosamente', 'success');
                this.closeModal('add-patient-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al agregar');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message, 'error');
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
                Toast.show(data.data.message || 'Actividad creada exitosamente', 'success');
                this.closeModal('create-activity-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al crear');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message, 'error');
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
            Toast.show('Error al cargar pacientes', 'error');
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
                Toast.show(data.data.message || 'Actividad asignada exitosamente', 'success');
                this.closeModal('assign-activity-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al asignar');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message, 'error');
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
                Toast.show(data.data.message || 'Entrada guardada exitosamente', 'success');
                this.closeModal('diary-modal');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message, 'error');
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
                Toast.show(data.data.message || 'Entrada eliminada', 'success');
            } else {
                throw new Error(data.data?.message || 'Error al eliminar');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show('Error al eliminar la entrada', 'error');
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
            Toast.show('Error al cargar mensajes', 'error');
        }
    },

    renderMessages(messages, otherUserId) {
        console.log('Mensaje completo:', messages[0]);
        const container = document.getElementById('message-thread');
        const currentUserId = openmindData.userId;

        if (messages.length === 0) {
            container.innerHTML = '<div class="empty-thread"><p>No hay mensajes. ¡Inicia la conversación!</p></div>';
            return;
        }

        const messagesHtml = messages.map(msg => `
            <div class="message ${msg.sender_id == currentUserId ? 'sent' : 'received'}">
                <div class="message-content">${msg.message}</div>
                <div class="message-time">${formatTimeAgo(msg.timestamp)}</div>
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
            Toast.show('Error al enviar mensaje', 'error');
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
        Toast.show('Función en desarrollo', 'info');
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
            Toast.show('Las contraseñas no coinciden', 'error');
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
                Toast.show(data.data.message || 'Contraseña cambiada exitosamente', 'success');
                this.closeModal('change-password-modal');
                form.reset();
            } else {
                throw new Error(data.data?.message || 'Error al cambiar contraseña');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show(error.message, 'error');
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
        Toast.show('Función en desarrollo', 'info');
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
        setTimeout(() => {
            modal.style.display = 'flex';
            if (typeof ModalUtils !== 'undefined') {
                ModalUtils.open();
            }
        }, 10);

        return modal;
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (typeof ModalUtils !== 'undefined') {
                ModalUtils.close();
            }
            modal.style.display = 'none';
            setTimeout(() => modal.remove(), 300);
        }
    },

    showNotification(message, type = 'info') {
        Toast.show(message, type);
    }
};

document.addEventListener('DOMContentLoaded', () => OpenmindApp.init());


// ==========================================
// MÓDULO DE MENSAJERÍA
// ==========================================

const OpenmindMessages = {
    currentConversation: null,
    pollInterval: null,

    init(initialUserId = 0) {
        this.loadConversations();
        this.startPolling();

        if (initialUserId) {
            setTimeout(() => this.openConversation(initialUserId), 500);
        }
    },

    async loadConversations() {
        const formData = new FormData();
        formData.append('action', 'openmind_get_conversations');
        formData.append('nonce', openmindData.nonce);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.renderConversations(data.data.conversations);
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            document.getElementById('conversations-list').innerHTML =
                '<div class="p-4 text-center text-red-500">Error al cargar conversaciones</div>';
        }
    },

    renderConversations(conversations) {
        console.log('Mensaje completo:', conversations);
        const container = document.getElementById('conversations-list');

        if (!conversations || conversations.length === 0) {
            container.innerHTML = `
            <div class="p-8 text-center text-gray-400">
                <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i>
                <p class="text-sm not-italic">No tienes conversaciones aún</p>
            </div>
        `;
            return;
        }

        const html = conversations.map(conv => {
            const userId = conv.patient_id || conv.psychologist_id;
            const isActive = this.currentConversation == userId;
            const isCurrent = conv.is_current === true || conv.is_current === '1';

            return `
            <div class="conversation-item ${isActive ? 'active' : ''} ${conv.unread_count > 0 ? 'has-unread' : ''}" 
                 data-user-id="${userId}"
                 data-is-current="${isCurrent}"
                 onclick="OpenmindMessages.openConversation(${userId})">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="text-sm font-semibold text-gray-900 m-0">
                            ${conv.display_name}
                        </h4>
                        ${isCurrent ? '<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Actual</span>' : ''}
                    </div>
                    <p class="text-xs text-gray-500 m-0">
                        <i class="fa-solid fa-clock mr-1"></i>
                        ${formatTimeAgo(conv.timestamp)}
                    </p>
                </div>
                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
            </div>
        `;
        }).join('');

        container.innerHTML = html;
    },

    async openConversation(otherUserId) {
        this.currentConversation = otherUserId;

        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.toggle('active', item.dataset.userId == otherUserId);
        });

        await this.loadMessages(otherUserId);
        this.markConversationRead(otherUserId);
    },

    async loadMessages(otherUserId, page = 1) {
        const container = document.getElementById('message-thread');
        container.innerHTML = `
            <div class="flex items-center justify-center py-8 text-gray-400">
                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                Cargando mensajes...
            </div>
        `;

        const formData = new FormData();
        formData.append('action', 'openmind_get_messages');
        formData.append('nonce', openmindData.nonce);
        formData.append('other_user_id', otherUserId);
        formData.append('page', page);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.renderMessages(data.data.messages, otherUserId);
            } else {
                container.innerHTML = `
                    <div class="p-8 text-center text-red-500">
                        <i class="fa-solid fa-exclamation-triangle text-4xl mb-3"></i>
                        <p>${data.data?.message || 'Error al cargar mensajes'}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            container.innerHTML = `
                <div class="p-8 text-center text-red-500">
                    <i class="fa-solid fa-exclamation-triangle text-4xl mb-3"></i>
                    <p>Error al cargar mensajes</p>
                </div>
            `;
        }
    },

    renderMessages(messages, otherUserId) {
        const container = document.getElementById('message-thread');
        const currentUserId = openmindData.userId;

        if (!messages || messages.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <i class="fa-solid fa-comment-dots text-6xl mb-4 text-gray-300"></i>
                    <p class="text-lg not-italic text-gray-600 mb-6">
                        No hay mensajes aún. ¡Inicia la conversación!
                    </p>
                </div>
                ${this.renderMessageForm(otherUserId)}
            `;
            this.bindMessageForm();
            return;
        }

        const messagesHtml = messages.map(msg => `
            <div class="message ${msg.sender_id == currentUserId ? 'sent' : 'received'}">
                <div class="message-content">${this.escapeHtml(msg.message)}</div>
                <div class="message-time">
                    <i class="fa-solid fa-clock mr-1"></i>
                    ${formatTimeAgo(msg.timestamp)}
                </div>
            </div>
        `).join('');

        container.innerHTML = `
            <div class="messages-container" id="messages-container">
                ${messagesHtml}
            </div>
            ${this.renderMessageForm(otherUserId)}
        `;

        setTimeout(() => {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }, 100);

        this.bindMessageForm();
    },

    renderMessageForm(otherUserId) {
        const canSendMessages = this.canSendMessagesTo(otherUserId);

        if (!canSendMessages) {
            return `
            <div class="message-input">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <i class="fa-solid fa-info-circle text-yellow-600 mr-2"></i>
                    <span class="text-sm text-yellow-700">
                        Este psicólogo ya no está asignado a ti. Solo puedes leer el historial de mensajes.
                    </span>
                </div>
            </div>
        `;
        }

        return `
        <div class="message-input">
            <form id="send-message-form">
                <input type="hidden" name="receiver_id" value="${otherUserId}">
                <div class="flex gap-3">
                    <textarea 
                        name="message" 
                        placeholder="Escribe un mensaje..." 
                        rows="2"
                        class="flex-1 border border-gray-300 rounded-lg p-3 resize-none focus:outline-none focus:ring-2 focus:ring-primary-500"
                        required
                    ></textarea>
                    <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-primary-600 self-end">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span class="ml-2 hidden md:inline">Enviar</span>
                    </button>
                </div>
            </form>
        </div>
        `;
    },

    canSendMessagesTo(otherUserId) {
        if (window.location.href.includes('dashboard-psicologo')) {
            return true;
        }
        return this.isCurrentPsychologist(otherUserId);
    },

    isCurrentPsychologist(psychologistId) {
        const conversations = document.querySelectorAll('.conversation-item');

        for (let conv of conversations) {
            if (conv.dataset.userId == psychologistId) {
                return conv.dataset.isCurrent === 'true';
            }
        }

        return true;
    },

    bindMessageForm() {
        const form = document.getElementById('send-message-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const textarea = form.querySelector('textarea[name="message"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const message = textarea.value.trim();

            if (!message) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            const formData = new FormData(form);
            formData.append('action', 'openmind_send_message');
            formData.append('nonce', openmindData.nonce);

            try {
                const response = await fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    form.reset();
                    this.loadMessages(this.currentConversation);
                    Toast.show(data.data.message || 'Mensaje enviado', 'success');
                } else {
                    Toast.show(data.data?.message || 'Error al enviar', 'error');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                Toast.show('Error al enviar mensaje', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span class="ml-2 hidden md:inline">Enviar</span>';
            }
        });

        const textarea = form.querySelector('textarea[name="message"]');
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.requestSubmit();
            }
        });
    },

    async markConversationRead(otherUserId) {
        const formData = new FormData();
        formData.append('action', 'openmind_mark_conversation_read');
        formData.append('nonce', openmindData.nonce);
        formData.append('other_user_id', otherUserId);

        try {
            await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            this.updateGlobalBadge();
            this.loadConversations();
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    },

    async updateGlobalBadge() {
        const formData = new FormData();
        formData.append('action', 'openmind_get_unread_count');
        formData.append('nonce', openmindData.nonce);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                const count = data.data.count;

                const sidebarBadge = document.getElementById('messages-badge');
                if (sidebarBadge) {
                    if (count > 0) {
                        sidebarBadge.textContent = count;
                        sidebarBadge.style.display = 'inline-flex';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                }

                const headerBadge = document.getElementById('header-messages-badge');
                if (headerBadge) {
                    if (count > 0) {
                        headerBadge.textContent = count;
                        headerBadge.style.display = 'flex';
                    } else {
                        headerBadge.style.display = 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Error updating badge:', error);
        }
    },

    startPolling() {
        this.pollInterval = setInterval(() => {
            this.updateGlobalBadge();

            if (document.getElementById('conversations-list')) {
                this.loadConversations();
            }
        }, 15000);

        this.updateGlobalBadge();
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

function formatTimeAgo(timestamp) {
    const now = Math.floor(Date.now() / 1000);
    const diff = now - timestamp;

    if (diff < 60) return 'hace un momento';
    if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `hace ${Math.floor(diff / 86400)} días`;

    // Fecha completa si > 7 días
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString('es-CL');
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof OpenmindApp !== 'undefined' && typeof openmindData !== 'undefined') {
        if (document.querySelector('.openmind-dashboard')) {
            OpenmindApp.updateGlobalBadge();
            OpenmindApp.startPolling();
        }
    }
});

// ==========================================
// BADGE DE DIARIOS COMPARTIDOS (PSICÓLOGO)
// ==========================================

async function updateDiaryBadge() {
    const badge = document.getElementById('diary-badge');
    if (!badge) return;

    const formData = new FormData();
    formData.append('action', 'openmind_get_shared_count');
    formData.append('nonce', openmindData.nonce);

    try {
        const response = await fetch(openmindData.ajaxUrl, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            if (data.data.count > 0) {
                badge.textContent = data.data.count;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating diary badge:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.openmind-sidebar')) {
        updateDiaryBadge();
        setInterval(updateDiaryBadge, 30000);
    }
});