/**
 * Auto-refresh CSRF tokens para prevenir "Page Expired"
 * Refresca el token cada 30 minutos para mantener la sesión activa
 */

// Refrescar token CSRF automáticamente
function refreshCsrfToken() {
    fetch('/sanctum/csrf-cookie', {
        credentials: 'same-origin'
    })
    .then(response => {
        // Silencioso en producción - los console.log serán eliminados por terser
        if (!response.ok && import.meta.env.DEV) {
            console.warn('⚠️ Failed to refresh CSRF token');
        }
    })
    .catch(error => {
        // Solo log en desarrollo
        if (import.meta.env.DEV) {
            console.error('❌ Error refreshing CSRF token:', error);
        }
    });
}

// Refrescar cada 30 minutos (antes de que expire la sesión)
const REFRESH_INTERVAL = 30 * 60 * 1000; // 30 minutos
setInterval(refreshCsrfToken, REFRESH_INTERVAL);

