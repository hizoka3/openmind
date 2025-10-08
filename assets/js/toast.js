// assets/js/toast.js
const Toast = {
    container: null,
    queue: [],
    maxToasts: 3,

    init() {
        this.container = document.getElementById('openmind-toast-container');
        if (!this.container) {
            console.warn('Toast container not found');
        }
    },

    show(message, type = 'success', duration = 5000) {
        if (!this.container) this.init();

        // Limitar a 3 toasts
        if (this.queue.length >= this.maxToasts) {
            this.queue[0].remove();
            this.queue.shift();
        }

        const config = {
            success: { color: 'green', icon: 'fa-circle-check' },
            error: { color: 'red', icon: 'fa-circle-xmark' },
            info: { color: 'blue', icon: 'fa-circle-info' }
        };

        const { color, icon } = config[type] || config.info;

        // Crear toast element
        const toast = document.createElement('div');
        toast.className = `transform translate-x-full transition-transform duration-300 pointer-events-auto`;
        toast.innerHTML = `
            <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-${color}-500 flex items-start gap-3">
                <i class="fa-solid ${icon} text-${color}-600 text-lg mt-0.5"></i>
                <p class="flex-1 text-sm font-medium text-gray-900">${message}</p>
                <button onclick="Toast.hide(this.closest('div[data-toast]'))" 
                        class="text-gray-400 hover:text-gray-600 transition-colors border-0 bg-transparent cursor-pointer p-0">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        `;
        toast.setAttribute('data-toast', '');

        this.container?.appendChild(toast);
        this.queue.push(toast);

        // Animar entrada
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
        });

        // Auto-hide
        if (duration > 0) {
            setTimeout(() => this.hide(toast), duration);
        }
    },

    hide(toast) {
        if (!toast) return;

        toast.classList.add('translate-x-full');

        setTimeout(() => {
            toast.remove();
            const index = this.queue.indexOf(toast);
            if (index > -1) this.queue.splice(index, 1);
        }, 300);
    }
};

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Toast.init());
} else {
    Toast.init();
}

// Exponer globalmente
window.Toast = Toast;