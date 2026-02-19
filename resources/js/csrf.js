/**
 * Agro365 CSRF 419 Error Handler
 * Shows confirm dialog when CSRF token expires
 */
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                preventDefault();
                if (confirm('Tu sesión ha expirado. ¿Recargar la página para continuar?')) {
                    window.location.reload();
                }
            }
        });
    });
});
