import './echo';

// ============================================
// Toast notification system
// Registered via alpine:init so it uses Livewire's own bundled Alpine
// instance (with the navigate/wire plugins) instead of booting a second,
// conflicting Alpine instance.
// ============================================
document.addEventListener('alpine:init', () => {
    // ============================================
    // Login Modal (standalone, not affected by Livewire events)
    // ============================================
    Alpine.data('loginModal', () => ({
        show: false,
        open() {
            @if(Auth::check())
                return;
            @endif
            this.show = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.primaryAction?.focus());
        },
        close() {
            this.show = false;
            document.body.style.overflow = '';
        },
        init() {
            // Listen only for custom event from Livewire components (not window events)
            // Use a unique nonce to prevent replay
            this._lastNonce = null;
            window.addEventListener('show-login-modal', (e) => {
                const nonce = e.detail?.nonce;
                // Prevent duplicate/replayed events
                if (nonce && nonce === this._lastNonce) return;
                this._lastNonce = nonce;
                this.open();
            });

            // Close on escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.show) this.close();
            });

            // Close on resize
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if (this.show) this.close();
                }, 150);
            });
        }
    }));

    window.Alpine.data('toastSystem', () => ({
        toasts: [],
        toastSequence: 0,
        toastTimers: {},
        toastListener: null,
        addToast(message, type = 'success', duration = 3000) {
            const normalizedMessage = String(message ?? '').trim();

            if (!normalizedMessage) {
                return;
            }

            const allowedTypes = ['success', 'error', 'info', 'warning'];
            const normalizedType = allowedTypes.includes(type) ? type : 'info';
            const normalizedDuration = Math.min(Math.max(Number(duration) || 3000, 1000), 15000);
            const id = `toast-${Date.now()}-${++this.toastSequence}`;

            this.toasts.push({ id, message: normalizedMessage, type: normalizedType });

            while (this.toasts.length > 4) {
                this.removeToast(this.toasts[0].id);
            }

            this.toastTimers[id] = window.setTimeout(() => {
                this.removeToast(id);
            }, normalizedDuration);
        },
        removeToast(id) {
            if (this.toastTimers[id]) {
                window.clearTimeout(this.toastTimers[id]);
                delete this.toastTimers[id];
            }

            this.toasts = this.toasts.filter(toast => toast.id !== id);
        },
        init() {
            this.toastListener = (e) => {
                const detail = e.detail ?? {};
                this.addToast(detail.message, detail.type || 'success');
            };

            window.addEventListener('toast', this.toastListener);
        },
        destroy() {
            if (this.toastListener) {
                window.removeEventListener('toast', this.toastListener);
            }

            Object.values(this.toastTimers).forEach(timer => window.clearTimeout(timer));
            this.toastTimers = {};
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
