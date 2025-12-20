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
        if (response.ok) {
            console.log('✅ CSRF token refreshed');
        } else {
            console.warn('⚠️ Failed to refresh CSRF token');
        }
    })
    .catch(error => {
        console.error('❌ Error refreshing CSRF token:', error);
    });
}

// Refrescar cada 30 minutos (antes de que expire la sesión)
const REFRESH_INTERVAL = 30 * 60 * 1000; // 30 minutos
setInterval(refreshCsrfToken, REFRESH_INTERVAL);

// Log inicial
console.log('🔒 CSRF auto-refresh initialized (every 30 minutes)');
