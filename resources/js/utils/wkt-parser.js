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
 * Formato: POLYGON((lon lat, lon lat, ...)) o POLYGON((lon lat, ...), (lon lat, ...)) con agujeros
 */
function parsePolygon(wkt) {
    try {
        // Remover el prefijo POLYGON
        const content = wkt.replace(/^POLYGON\s*\(/i, '').trim();
        if (!content.endsWith(')')) {
            console.warn('POLYGON mal formado:', wkt.substring(0, 100));
            return [];
        }
        
        // Remover el paréntesis final
        const ringsContent = content.slice(0, -1).trim();
        
        // Extraer el anillo exterior (primer conjunto de coordenadas)
        // Formato: ((lon lat, lon lat, ...))
        const outerRingMatch = ringsContent.match(/^\(\(([^)]+)\)\)/);
        if (!outerRingMatch) {
            // Intentar formato sin doble paréntesis
            const simpleMatch = ringsContent.match(/^\(([^)]+)\)/);
            if (!simpleMatch) return [];
            return parseCoordinates(simpleMatch[1]);
        }
        
        // Parsear el anillo exterior
        const outerRing = parseCoordinates(outerRingMatch[1]);
        if (outerRing.length < 3) {
            console.warn('POLYGON con menos de 3 puntos');
            return [];
        }
        
        return outerRing;
    } catch (e) {
        console.error('Error parseando POLYGON:', e);
        return [];
    }
}

/**
 * Parsear MULTIPOLYGON
 * Formato: MULTIPOLYGON(((lon lat, ...)), ((lon lat, ...)), ...)
 * Devuelve el primer polígono (el más grande típicamente)
 */
function parseMultiPolygon(wkt) {
    try {
        // Remover el prefijo MULTIPOLYGON
        const content = wkt.replace(/^MULTIPOLYGON\s*\(/i, '').trim();
        if (!content.endsWith(')')) {
            console.warn('MULTIPOLYGON mal formado:', wkt.substring(0, 100));
            return [];
        }
        
        // Remover el paréntesis final
        const polygonsContent = content.slice(0, -1).trim();
        
        // Buscar el primer polígono: ((lon lat, ...))
        // Usar una expresión regular más simple para encontrar el primer conjunto de coordenadas
        const firstPolygonMatch = polygonsContent.match(/\(\(\(([^)]+)\)\)\)/);
        if (firstPolygonMatch) {
            return parseCoordinates(firstPolygonMatch[1]);
        }
        
        // Intentar formato alternativo: ((lon lat, ...))
        const altMatch = polygonsContent.match(/\(\(([^)]+)\)\)/);
        if (altMatch) {
            return parseCoordinates(altMatch[1]);
        }
        
        // Si no se encuentra, intentar parsear como POLYGON simple
        console.warn('No se pudo parsear MULTIPOLYGON, intentando como POLYGON simple');
        return [];
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
