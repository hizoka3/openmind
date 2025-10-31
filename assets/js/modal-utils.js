const ModalUtils = {
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Bloquear body
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.touchAction = 'none';

        // Mostrar modal
        modal.classList.remove('hidden');
        modal.style.display = 'flex';

        // Forzar propiedades de scroll en contenedor interno
        const scrollContainer = modal.querySelector('[data-modal-scroll]');
        if (scrollContainer) {
            scrollContainer.style.overflowY = 'auto';
            scrollContainer.style.overscrollBehavior = 'contain';
            scrollContainer.style.webkitOverflowScrolling = 'touch';
            scrollContainer.style.touchAction = 'pan-y';
            scrollContainer.style.pointerEvents = 'auto';
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('hidden');
        modal.style.display = 'none';

        // Restaurar body
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.touchAction = '';
    }
};

window.ModalUtils = ModalUtils;