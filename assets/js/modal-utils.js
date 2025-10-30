// assets/js/modal-utils.js
const ModalUtils = {
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Mostrar modal
        modal.classList.remove('hidden');
        modal.style.display = 'flex';

        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = this.getScrollbarWidth() + 'px';
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Ocultar modal
        modal.classList.add('hidden');
        modal.style.display = 'none';

        // Desbloquear scroll del body
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    },

    getScrollbarWidth() {
        // Calcular ancho del scrollbar para evitar "salto" al bloquear
        const outer = document.createElement('div');
        outer.style.visibility = 'hidden';
        outer.style.overflow = 'scroll';
        document.body.appendChild(outer);

        const inner = document.createElement('div');
        outer.appendChild(inner);

        const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
        outer.parentNode.removeChild(outer);

        return scrollbarWidth;
    }
};

// Hacer disponible globalmente
window.ModalUtils = ModalUtils;