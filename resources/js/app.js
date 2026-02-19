import './bootstrap';
import './csrf-refresh'; // Auto-refresh CSRF tokens
import './csrf';         // 419 handler for Livewire
import './toast';        // Toast notification system
import './sidebar';      // Sidebar collapse/toggle logic

// Lazy loading de Leaflet
import { loadLeaflet, isLeafletLoaded } from './utils/leaflet-loader';
import { parseWKT, isValidWKT } from './utils/wkt-parser';

window.loadLeaflet = loadLeaflet;
window.isLeafletLoaded = isLeafletLoaded;
window.parseWKT = parseWKT;
window.isValidWKT = isValidWKT;
