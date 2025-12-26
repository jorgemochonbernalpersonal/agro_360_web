import './bootstrap';
import './csrf-refresh'; // Auto-refresh CSRF tokens

// ✅ Lazy loading de Leaflet - se carga solo cuando se necesita
// Esto mejora el tiempo de carga inicial de la página
import { loadLeaflet, isLeafletLoaded } from './utils/leaflet-loader';

// Importar parser WKT
import { parseWKT, isValidWKT } from './utils/wkt-parser';

// Exponer funciones globalmente para uso en vistas Blade
window.loadLeaflet = loadLeaflet;
window.isLeafletLoaded = isLeafletLoaded;
window.parseWKT = parseWKT;
window.isValidWKT = isValidWKT;
