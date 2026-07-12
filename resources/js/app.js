import './echo';

// ============================================
// Toast notification system
// Registered via alpine:init so it uses Livewire's own bundled Alpine
// instance (with the navigate/wire plugins) instead of booting a second,
// conflicting Alpine instance.
// ============================================
document.addEventListener('alpine:init', () => {
    window.Alpine.data('toastSystem', () => ({
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
});

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
