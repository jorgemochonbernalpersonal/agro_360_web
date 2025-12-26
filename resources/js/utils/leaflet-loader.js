/**
 * Lazy loader para Leaflet
 * Carga la librería solo cuando se necesita, mejorando el tiempo de carga inicial
 */

let leafletLoaded = false;
let leafletLoadingPromise = null;

/**
 * Cargar Leaflet de forma lazy
 * @returns {Promise<L>} Promesa que resuelve con el objeto Leaflet
 */
export async function loadLeaflet() {
    // Si ya está cargado, devolver inmediatamente
    if (leafletLoaded && window.L) {
        return window.L;
    }

    // Si ya hay una carga en progreso, esperar a que termine
    if (leafletLoadingPromise) {
        return leafletLoadingPromise;
    }

    // Iniciar carga
    leafletLoadingPromise = (async () => {
        try {
            // Importar Leaflet dinámicamente
            const L = (await import('leaflet')).default;
            await import('leaflet/dist/leaflet.css');

            // Fix para iconos de Leaflet
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });

            // Exponer globalmente
            window.L = L;
            leafletLoaded = true;

            return L;
        } catch (error) {
            console.error('Error cargando Leaflet:', error);
            leafletLoadingPromise = null;
            throw error;
        }
    })();

    return leafletLoadingPromise;
}

/**
 * Verificar si Leaflet está cargado
 * @returns {boolean}
 */
export function isLeafletLoaded() {
    return leafletLoaded && !!window.L;
}
