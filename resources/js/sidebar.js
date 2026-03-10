/**
 * Agro365 Nav Rail Logic
 * Initializes Alpine.store('nav') for the rail + flyout sidebar
 */

// Mobile toggle (hamburger button in top-bar)
window.toggleSidebar = function () {
    const rail = document.querySelector('aside');
    if (rail) rail.classList.toggle('-translate-x-full');
};

// Init (or re-init) Alpine store for the nav rail
function initNavStore() {
    if (typeof Alpine === 'undefined') return;
    if (Alpine.store('nav')) return;
    Alpine.store('nav', {
        open: null,
        toggle(key) { this.open = (this.open === key) ? null : key; },
        close()     { this.open = null; },
    });
}

// Case 1: Alpine not started yet when this module runs → listen for alpine:init
document.addEventListener('alpine:init', initNavStore);

// Case 2: Alpine already started (e.g. after wire:navigate re-execution) → try immediately
initNavStore();

// Close flyout on every wire:navigate (sidebar re-renders)
document.addEventListener('livewire:navigate', () => {
    if (window.Alpine && Alpine.store('nav')) {
        Alpine.store('nav').close();
    }
});
