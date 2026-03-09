/**
 * Agro365 Toast Notification System
 * Used by x-agro-toast component via Alpine.js x-data
 */
window.toastNotifications = function () {
    return {
        notifications: [],

        init() {
            // Flash messages from session
            const flashes = window.__agro_flashes || {};
            if (flashes.message) this.add(flashes.message, 'success');
            if (flashes.error)   this.add(flashes.error,   'error');
            if (flashes.info)    this.add(flashes.info,    'info');
            if (flashes.warning) this.add(flashes.warning, 'warning');

            // Livewire toast events
            if (typeof Livewire !== 'undefined') {
                Livewire.on('toast', (data) => {
                    let d = null;
                    if (Array.isArray(data) && data.length > 0) {
                        d = data[0];
                    } else if (data && typeof data === 'object') {
                        d = data;
                    } else if (typeof data === 'string') {
                        this.add(data, 'success');
                        return;
                    }
                    if (d) this.add(d.message, d.type || 'success');
                });
            }
        },

        add(message, type = 'success') {
            if (!message) return;
            const now = Date.now();

            // Deduplicate within 1s
            if (this.notifications.some(n => n.message === message && n.type === type && (now - n.id) < 1000)) return;

            const id = now + Math.random();
            this.notifications.push({ id, message, type, show: false, paused: false, timeout: null });

            this.$nextTick(() => {
                const idx = this.notifications.findIndex(n => n.id === id);
                if (idx !== -1) {
                    this.notifications[idx].show = true;
                    this._startTimer(id);
                }
            });
        },

        pause(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n) { n.paused = true; clearTimeout(n.timeout); }
        },

        resume(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n) { n.paused = false; this._startTimer(id); }
        },

        remove(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx !== -1) {
                this.notifications[idx].show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        _startTimer(id) {
            const n = this.notifications.find(n => n.id === id);
            if (!n) return;
            n.timeout = setTimeout(() => this.remove(id), 5000);
        },
    };
};
