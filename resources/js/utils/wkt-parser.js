/**
 * Parser WKT (Well-Known Text) para geometrías SIGPAC
 * Soporta POLYGON y MULTIPOLYGON
 */

/**
 * Parsear WKT a coordenadas Leaflet
 * @param {string} wkt - Geometría en formato WKT
 * @returns {Array} Array de coordenadas [lat, lng]
 */
export function parseWKT(wkt) {
    if (!wkt || typeof wkt !== 'string') {
        return [];
    }

    const trimmed = wkt.trim();

    // POLYGON simple
    if (trimmed.startsWith('POLYGON')) {
        return parsePolygon(trimmed);
    }

    // MULTIPOLYGON
    if (trimmed.startsWith('MULTIPOLYGON')) {
        return parseMultiPolygon(trimmed);
    }

    console.warn('Formato WKT no reconocido:', trimmed.substring(0, 50));
    return [];
}

/**
 * Parsear POLYGON
 * Formato: POLYGON((lon lat, lon lat, ...))
 */
function parsePolygon(wkt) {
    try {
        // Extraer coordenadas: POLYGON((lon lat, lon lat, ...))
        const match = wkt.match(/\(\(([^)]+)\)\)/);
        if (!match) return [];
        
        return parseCoordinates(match[1]);
    } catch (e) {
        console.error('Error parseando POLYGON:', e);
        return [];
    }
}

/**
 * Parsear MULTIPOLYGON
 * Formato: MULTIPOLYGON(((lon lat, ...)))
 */
function parseMultiPolygon(wkt) {
    try {
        // Tomar primer polígono: MULTIPOLYGON(((lon lat, ...)))
        const match = wkt.match(/\(\(\(([^)]+)\)\)\)/);
        if (!match) {
            // Intentar formato alternativo
            const altMatch = wkt.match(/\(\(([^)]+)\)\)/);
            if (!altMatch) return [];
            return parseCoordinates(altMatch[1]);
        }
        
        return parseCoordinates(match[1]);
    } catch (e) {
        console.error('Error parseando MULTIPOLYGON:', e);
        return [];
    }
}

/**
 * Parsear string de coordenadas a array
 * Formato: "lon lat, lon lat, ..."
 */
function parseCoordinates(coordString) {
    return coordString
        .split(',')
        .map(pair => {
            const parts = pair.trim().split(/\s+/);
            if (parts.length < 2) return null;
            
            const lon = parseFloat(parts[0]);
            const lat = parseFloat(parts[1]);
            
            if (isNaN(lat) || isNaN(lon)) return null;
            if (lat < -90 || lat > 90 || lon < -180 || lon > 180) return null;
            
            return [lat, lon]; // Leaflet usa [lat, lon]
        })
        .filter(coord => coord !== null);
}

/**
 * Validar formato WKT
 * @param {string} wkt - Geometría en formato WKT
 * @returns {boolean} true si es válido
 */
export function isValidWKT(wkt) {
    if (!wkt || typeof wkt !== 'string') return false;
    
    const trimmed = wkt.trim();
    return /^(POLYGON|MULTIPOLYGON|LINESTRING|POINT)\s*\(.+\)$/i.test(trimmed);
}
