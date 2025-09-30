// assets/js/main.js
const OpenmindApp = {
    init() {
        this.bindEvents();
        this.filterActivities();
    },

    bindEvents() {
        // Filtro de actividades
        document.querySelectorAll('.activities-tabs .tab').forEach(tab => {
            tab.addEventListener('click', (e) => this.handleTabClick(e));
        });

        // Completar actividad
        document.querySelectorAll('[data-action="complete-activity"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.completeActivity(e));
        });

        // Abrir mensajes
        document.querySelectorAll('[data-action="open-messages"]').forEach(btn => {
            btn.addEventListener('click', () => this.openMessages());
        });

        // Ver paciente
        document.querySelectorAll('[data-action="view-patient"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewPatient(e));
        });

        // Nueva entrada de bitácora
        const diaryBtn = document.getElementById('new-diary-entry');
        if (diaryBtn) diaryBtn.addEventListener('click', () => this.newDiaryEntry());

        // Agregar paciente
        const addPatientBtn = document.getElementById('add-patient');
        if (addPatientBtn) addPatientBtn.addEventListener('click', () => this.addPatient());

        // Eliminar entrada de bitácora
        document.querySelectorAll('[data-action="delete-diary"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.deleteDiaryEntry(e));
        });
    },

    handleTabClick(e) {
        const tab = e.currentTarget;
        const filter = tab.dataset.filter;

        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        this.filterActivities(filter);
    },

    filterActivities(filter = 'pending') {
        const activities = document.querySelectorAll('.activity-card');
        activities.forEach(card => {
            const status = card.dataset.status;
            card.style.display = status === filter ? 'block' : 'none';
        });
    },

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

    openMessages() {
        // TODO: Implementar modal de mensajes
        console.log('Abrir mensajes');
    },

    viewPatient(e) {
        const patientId = e.currentTarget.dataset.patientId;
        // TODO: Redirigir o abrir modal con info del paciente
        console.log('Ver paciente:', patientId);
    },

    newDiaryEntry() {
        const content = prompt('Escribe tu entrada de bitácora:');
        if (!content) return;

        const mood = prompt('¿Cómo te sientes? (feliz, triste, ansioso, neutral)');

        this.saveDiaryEntry(content, mood);
    },

    async saveDiaryEntry(content, mood) {
        try {
            const formData = new FormData();
            formData.append('action', 'openmind_save_diary');
            formData.append('nonce', openmindData.nonce);
            formData.append('content', content);
            formData.append('mood', mood || '');

            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Entrada guardada exitosamente', 'success');
                location.reload();
            } else {
                throw new Error(data.data?.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al guardar la entrada', 'error');
        }
    },

    addPatient() {
        const email = prompt('Ingresa el email del paciente:');
        if (!email) return;

        this.savePatient(email);
    },

    async savePatient(email) {
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
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data.data?.message || 'Error al agregar');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification(error.message, 'error');
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
                const entry = btn.closest('.diary-entry');
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

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `openmind-notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }, 100);
    }
};

document.addEventListener('DOMContentLoaded', () => OpenmindApp.init());