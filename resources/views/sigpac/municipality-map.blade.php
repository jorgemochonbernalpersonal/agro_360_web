<x-app-layout>
    <x-slot name="title">Mapas de {{ $municipality->name }} - SIGPAC</x-slot>
    <x-slot name="description">Visualiza todos los mapas SIGPAC de {{ $municipality->name }}</x-slot>

    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        @php
            $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
        @endphp
        <x-page-header
            :icon="$icon"
            title="Mapas de {{ $municipality->name }}"
            description="Visualización de todos los recintos SIGPAC del municipio"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        >
            <x-slot:actionButton>
                <a 
                    href="{{ route('sigpac.codes') }}"
                    class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all font-semibold"
                >
                    ← Volver a Códigos SIGPAC
                </a>
            </x-slot:actionButton>
        </x-page-header>

        <!-- Información del Municipio -->
        <div class="glass-card rounded-xl p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $municipality->name }}</h2>
                        <p class="text-sm text-gray-600">
                            {{ $municipality->province->name ?? '' }}, {{ $municipality->province->autonomousCommunity->name ?? '' }}
                        </p>
                        <p class="text-sm text-green-600 font-semibold mt-1">
                            {{ count($plotGeometries) }} recintos visualizados
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa -->
        <div class="glass-card rounded-xl overflow-hidden shadow-xl">
            <div id="map" class="w-full h-[600px]"></div>
        </div>

        <!-- Leyenda de Colores -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📍 Recintos del Municipio</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto">
                @foreach($plotGeometries as $index => $geometry)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div 
                            class="w-6 h-6 rounded border-2 flex-shrink-0"
                            style="background-color: {{ $geometry['color']['fill'] }}; border-color: {{ $geometry['color']['line'] }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $geometry['plot_name'] }}</p>
                            <p class="text-xs text-gray-600 font-mono">{{ $geometry['sigpac_formatted'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Datos de geometrías desde el servidor
        const plotGeometries = @json($plotGeometries);

        // Función para esperar a que app.js esté cargado
        function waitForAppJS() {
            return new Promise((resolve) => {
                // Si ya están disponibles, resolver inmediatamente
                if (typeof window.parseWKT !== 'undefined' && typeof window.loadLeaflet !== 'undefined') {
                    resolve();
                    return;
                }

                // Esperar hasta que estén disponibles (máximo 10 segundos)
                let attempts = 0;
                const maxAttempts = 100;
                const interval = setInterval(() => {
                    attempts++;
                    if (typeof window.parseWKT !== 'undefined' && typeof window.loadLeaflet !== 'undefined') {
                        clearInterval(interval);
                        resolve();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(interval);
                        console.error('Timeout esperando app.js');
                        resolve(); // Resolver de todas formas para no bloquear
                    }
                }, 100);
            });
        }

        // Inicializar mapa con lazy loading de Leaflet
        async function initMap() {
            console.log('🗺️ Inicializando mapa del municipio');
            console.log('Geometrías cargadas:', plotGeometries.length);

            // Esperar a que app.js esté cargado
            await waitForAppJS();

            // Verificar que existen las funciones de parsing WKT
            if (typeof window.parseWKT === 'undefined') {
                console.error('❌ window.parseWKT no está definido');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded';
                errorMsg.innerHTML = '<p class="font-bold">Error: El parser WKT no está cargado.</p><p>Por favor, recarga la página.</p>';
                document.getElementById('map').parentElement.insertBefore(errorMsg, document.getElementById('map'));
                return;
            }

            if (typeof window.isValidWKT === 'undefined') {
                console.error('❌ window.isValidWKT no está definido');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded';
                errorMsg.innerHTML = '<p class="font-bold">Error: El validador WKT no está cargado.</p><p>Por favor, recarga la página.</p>';
                document.getElementById('map').parentElement.insertBefore(errorMsg, document.getElementById('map'));
                return;
            }

            // Cargar Leaflet de forma lazy
            let L;
            try {
                L = await window.loadLeaflet();
                console.log('✅ Leaflet cargado');
            } catch (error) {
                console.error('❌ Error cargando Leaflet:', error);
                const errorMsg = document.createElement('div');
                errorMsg.className = 'bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded';
                errorMsg.innerHTML = '<p class="font-bold">Error cargando el mapa.</p><p>Por favor, recarga la página.</p>';
                document.getElementById('map').parentElement.insertBefore(errorMsg, document.getElementById('map'));
                return;
            }

            // Verificar que el contenedor del mapa existe
            const mapContainer = document.getElementById('map');
            if (!mapContainer) {
                console.error('❌ No se encontró el contenedor #map');
                return;
            }

            const map = L.map('map', {
                zoomControl: true,
                attributionControl: true
            });

            const streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                maxZoom: 19
            });

            const satelliteMap = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: '© <a href="https://www.esri.com/">Esri</a>', maxZoom: 19 }
            );

            const pnoaMap = L.tileLayer.wms('https://www.ign.es/wms-inspire/pnoa-ma', {
                layers: 'OI.OrthoimageCoverage',
                format: 'image/jpeg',
                transparent: false,
                attribution: '© <a href="https://www.ign.es">IGN España – PNOA</a>',
                maxZoom: 20,
                version: '1.3.0',
            });

            const catastroLayer = L.tileLayer.wms(
                'https://ovc.catastro.meh.es/Cartografia/WMS/ServidorWMS.aspx',
                {
                    layers: 'Catastro',
                    format: 'image/png',
                    transparent: true,
                    attribution: '© <a href="https://www.catastro.meh.es">Dirección General del Catastro</a>',
                    maxZoom: 20,
                    opacity: 0.7,
                }
            );

            satelliteMap.addTo(map);

            L.control.layers(
                { "🗺️ Mapa": streetMap, "🛰️ Satélite": satelliteMap, "📷 PNOA (IGN)": pnoaMap },
                { "🏛️ Catastro": catastroLayer },
                { position: 'topright' }
            ).addTo(map);

            L.control.scale({ imperial: false, position: 'bottomright' }).addTo(map);

            // Geolocalización
            const LocateControl = L.Control.extend({
                options: { position: 'topright' },
                onAdd(map) {
                    const btn = L.DomUtil.create('button', '');
                    btn.title = '¿Dónde estoy?';
                    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="9" stroke-dasharray="4 2"/></svg>`;
                    btn.style.cssText = 'width:34px;height:34px;background:#fff;border:2px solid rgba(0,0,0,.2);border-radius:4px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#333;';
                    let locMarker = null, locCircle = null, watchId = null, watching = false;
                    L.DomEvent.on(btn, 'click', function(e) {
                        L.DomEvent.stopPropagation(e);
                        if (watching) {
                            navigator.geolocation.clearWatch(watchId);
                            if (locMarker) { map.removeLayer(locMarker); locMarker = null; }
                            if (locCircle) { map.removeLayer(locCircle); locCircle = null; }
                            watching = false; btn.style.color = '#333'; btn.style.background = '#fff';
                            return;
                        }
                        if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
                        btn.style.color = '#16a34a'; btn.style.background = '#f0fdf4';
                        watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                                const acc = pos.coords.accuracy;
                                if (!locMarker) {
                                    locCircle = L.circle(latlng, { radius: acc, color: '#3b82f6', fillColor: '#93c5fd', fillOpacity: 0.2, weight: 1 }).addTo(map);
                                    locMarker = L.circleMarker(latlng, { radius: 8, color: '#1d4ed8', fillColor: '#3b82f6', fillOpacity: 1, weight: 2 })
                                        .bindTooltip('Tu posición', { permanent: false, direction: 'top' }).addTo(map);
                                    map.setView(latlng, 17);
                                } else { locMarker.setLatLng(latlng); locCircle.setLatLng(latlng).setRadius(acc); }
                                watching = true;
                            },
                            (err) => {
                                btn.style.color = '#333'; btn.style.background = '#fff'; watching = false;
                                if (err.code === err.PERMISSION_DENIED) alert('Permiso de ubicación denegado.');
                            },
                            { enableHighAccuracy: true, maximumAge: 5000 }
                        );
                    });
                    return btn;
                }
            });
            new LocateControl().addTo(map);

            // Área geodésica (m²)
            function geodesicArea(latLngs) {
                const R = 6378137;
                let area = 0;
                const pts = latLngs.map(p => [p.lat, p.lng]);
                const n = pts.length;
                for (let i = 0; i < n; i++) {
                    const [lat1, lng1] = pts[i], [lat2, lng2] = pts[(i + 1) % n];
                    const dLng = (lng2 - lng1) * Math.PI / 180;
                    area += dLng * (2 + Math.sin(lat1 * Math.PI / 180) + Math.sin(lat2 * Math.PI / 180));
                }
                return Math.abs(area * R * R / 2);
            }

            const polygonLayers = [];
            let bounds = null;
            let errorsCount = 0;
            let successCount = 0;

            // Procesar cada geometría
            plotGeometries.forEach((geometry, index) => {
                if (!geometry || !geometry.wkt) {
                    console.warn(`❌ Geometría ${index} no tiene WKT`);
                    errorsCount++;
                    return;
                }

                if (!window.isValidWKT(geometry.wkt)) {
                    console.warn(`❌ Geometría ${index} tiene WKT inválido:`, geometry.wkt.substring(0, 100));
                    errorsCount++;
                    return;
                }

                try {
                    const coordinates = window.parseWKT(geometry.wkt);
                    
                    if (!coordinates || coordinates.length === 0) {
                        console.warn(`❌ No se pudieron parsear coordenadas para ${geometry.sigpac_formatted || 'geometría ' + index}`);
                        errorsCount++;
                        return;
                    }

                    // Verificar que las coordenadas son válidas
                    const validCoords = coordinates.filter(coord => 
                        Array.isArray(coord) && 
                        coord.length === 2 && 
                        !isNaN(coord[0]) && 
                        !isNaN(coord[1]) &&
                        coord[0] >= -90 && coord[0] <= 90 &&
                        coord[1] >= -180 && coord[1] <= 180
                    );

                    if (validCoords.length < 3) {
                        console.warn(`❌ Geometría ${index} no tiene suficientes coordenadas válidas (${validCoords.length})`);
                        errorsCount++;
                        return;
                    }

                    const style = {
                        color: geometry.color?.line || '#3388ff',
                        fillColor: geometry.color?.fill || '#3388ff',
                        fillOpacity: 0.3,
                        weight: 2
                    };

                    // Crear polígono
                    const polygon = L.polygon(validCoords, style);

                    // Área aproximada
                    const rawLatLngs = polygon.getLatLngs()[0];
                    const areaM2 = rawLatLngs && rawLatLngs.length > 2 ? geodesicArea(rawLatLngs) : 0;
                    const areaHa = areaM2 > 0 ? (areaM2 / 10000).toFixed(2) : null;

                    // Popup
                    polygon.bindPopup(`
                        <div style="min-width:200px">
                            <div style="border-left:4px solid ${geometry.color?.line || '#3388ff'}; padding-left:10px; margin-bottom:10px">
                                <div style="font-weight:700; font-size:14px">${geometry.plot_name || 'Sin nombre'}</div>
                                <div style="font-family:monospace; font-size:11px; color:#555; margin-top:2px">${geometry.sigpac_formatted || geometry.sigpac_code || ''}</div>
                            </div>
                            <table style="width:100%; font-size:12px; border-collapse:collapse">
                                ${geometry.polygon ? `<tr><td style="color:#888;padding:2px 0">Polígono</td><td style="font-weight:600;text-align:right">${geometry.polygon}</td></tr>` : ''}
                                ${geometry.enclosure ? `<tr><td style="color:#888;padding:2px 0">Recinto</td><td style="font-weight:600;text-align:right">${geometry.enclosure}</td></tr>` : ''}
                                ${areaHa ? `<tr><td style="color:#888;padding:2px 0">Área aprox.</td><td style="font-weight:600;text-align:right">${areaHa} ha</td></tr>` : ''}
                            </table>
                        </div>
                    `, { maxWidth: 260 });

                    // Tooltip hover
                    polygon.bindTooltip(
                        `<strong>${geometry.plot_name || 'Sin nombre'}</strong>${geometry.polygon ? ' · Pol. ' + geometry.polygon : ''}`,
                        { sticky: true, direction: 'top' }
                    );

                    // Highlight hover
                    polygon.on('mouseover', function() { this.setStyle({ weight: 4, fillOpacity: 0.6 }); });
                    polygon.on('mouseout', function() { this.setStyle(style); });

                    // Añadir al mapa
                    polygon.addTo(map);
                    polygonLayers.push(polygon);
                    successCount++;

                    // Actualizar bounds
                    try {
                        const polygonBounds = polygon.getBounds();
                        if (!bounds) {
                            bounds = polygonBounds;
                        } else {
                            bounds.extend(polygonBounds);
                        }
                    } catch (boundsError) {
                        console.warn(`⚠️ Error obteniendo bounds del polígono ${index}:`, boundsError);
                    }
                } catch (error) {
                    console.error(`❌ Error procesando geometría ${index}:`, error);
                    errorsCount++;
                }
            });

            // Ajustar vista a todos los polígonos
            if (bounds && polygonLayers.length > 0) {
                try {
                    map.fitBounds(bounds, { padding: [50, 50] });
                    console.log(`✅ ${successCount} polígonos renderizados correctamente`);
                } catch (fitError) {
                    console.warn('⚠️ Error ajustando bounds, usando vista por defecto:', fitError);
                    map.setView([40.4168, -3.7038], 6);
                }
            } else {
                // Vista por defecto de España si no hay geometrías
                map.setView([40.4168, -3.7038], 6);
                if (errorsCount > 0) {
                    console.warn(`⚠️ No se renderizaron polígonos. Errores: ${errorsCount}`);
                }
            }

            console.log(`📊 Resumen: ${successCount} éxitos, ${errorsCount} errores de ${plotGeometries.length} geometrías`);
        }

        // Inicializar cuando el DOM esté listo y app.js esté cargado
        function startInit() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMap);
            } else {
                // Esperar un poco más para asegurar que app.js esté cargado
                setTimeout(initMap, 100);
            }
        }

        startInit();
    </script>
    @endpush
</x-app-layout>
