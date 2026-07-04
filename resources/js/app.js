import Alpine from 'alpinejs';
import './echo';

window.Alpine = Alpine;

// ============================================
// Toast notification system
// ============================================
Alpine.data('toastSystem', () => ({
    toasts: [],
    addToast(message, type = 'success', duration = 3000) {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }, duration);
    },
    init() {
        window.addEventListener('toast', (e) => {
            this.addToast(e.detail.message, e.detail.type || 'success');
        });
    }
}));

// ============================================
// Format currency helper
// ============================================
window.formatRupiah = function (amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

// Prevent Alpine double-boot in Livewire apps. Livewire 3/4 boots Alpine automatically.
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Livewire) {
        Alpine.start();
    }
});
