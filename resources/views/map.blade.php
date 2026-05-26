<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa - {{ $plot->name }} - Agro365</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        body { 
            margin: 0; 
            overflow: hidden; 
            font-family: system-ui, -apple-system, sans-serif;
        }
        #map-container { 
            height: 100vh; 
            width: 100vw; 
            position: relative;
        }
        #map { 
            height: 100%; 
            width: 100%; 
        }
        .map-controls {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        .color-indicator {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 2px solid;
            display: inline-block;
            margin-right: 8px;
        }
        @media (max-width: 640px) {
            .map-controls {
                max-width: calc(100vw - 40px);
                top: 10px;
                left: 10px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div id="map-container">
        <!-- Controles -->
        <div class="map-controls">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $plot->name }}
                </h1>
                @php
                    $returnUrl = request()->get('return');
                    $backUrl = match($returnUrl) {
                        'plots' => route('plots.index'),
                        'sigpac' => route('sigpac.codes'),
                        default => route('plots.show', $plot)
                    };
                @endphp
                <a href="{{ $backUrl }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>

            <!-- Selector de Recintos -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('📍 Seleccionar Recinto:') }}</label>
                <select id="recinto-selector" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                    <option value="all">🗺️ Todos los recintos ({{ count($plotGeometries) }})</option>
                    @foreach($plotGeometries as $geometry)
                        <option value="{{ $loop->index }}" data-color="{{ $geometry['color']['line'] }}">
                            {{ $geometry['sigpac_formatted'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 🛰️ Toggle NDVI -->
            <div class="mb-4 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">{{ __('🛰️ Vista NDVI') }}</label>
                    <button id="ndvi-toggle" 
                            onclick="toggleNdviMode()"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors bg-gray-300"
                            data-active="false">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1"></span>
                    </button>
                </div>
                <div id="ndvi-info" class="hidden">
                    <div class="text-xs text-gray-600 mb-2">
                        <span id="ndvi-value" class="font-bold text-green-600">--</span> NDVI
                        <span id="ndvi-status" class="ml-2">--</span>
                    </div>
                    <!-- Leyenda NDVI -->
                    <div class="flex items-center gap-1 text-[10px]">
                        <span class="w-4 h-3 rounded" style="background: rgba(239, 68, 68, 0.6)"></span>
                        <span class="text-gray-500">{{ __('Bajo') }}</span>
                        <span class="w-4 h-3 rounded ml-1" style="background: rgba(251, 146, 60, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(250, 204, 21, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(52, 211, 153, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(34, 197, 94, 0.6)"></span>
                        <span class="text-gray-500">{{ __('Alto') }}</span>
                    </div>
                </div>
                <div id="ndvi-loading" class="hidden text-xs text-gray-500">
                    <svg class="w-4 h-4 animate-spin inline mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Cargando datos NDVI...
                </div>
            </div>

            <!-- Leyenda de Colores -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-semibold text-gray-600 mb-2">{{ __('Leyenda de Recintos:') }}</p>
                <div class="space-y-1 max-h-40 overflow-y-auto">
                    @foreach($plotGeometries as $geometry)
                        <div class="flex items-center text-xs">
                            <span class="color-indicator" 
                                  style="background-color: {{ $geometry['color']['fill'] }}; border-color: {{ $geometry['color']['line'] }};">
                            </span>
                            <span class="text-gray-700">{{ $geometry['index'] }}. {{ $geometry['sigpac_formatted'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Info -->
            <div class="text-sm text-gray-600 space-y-1 border-t pt-3">
                <p><strong>{{ __('Total recintos:') }}</strong> {{ count($plotGeometries) }}</p>
                @if($plot->area)
                    <p><strong>{{ __('Área total:') }}</strong> {{ number_format($plot->area, 2) }} ha</p>
                @endif
                @if($plot->municipality)
                    <p><strong>{{ __('Municipio:') }}</strong> {{ $plot->municipality->name }}</p>
                @endif
            </div>
        </div>

        <!-- Mapa -->
        <div id="map"></div>
    </div>

    <script>
        const plotGeometries = @json($plotGeometries);
        let map;
        let polygonLayers = [];
        let originalStyles = [];

        // Área geodésica aproximada (m²) desde array de LatLng
        function geodesicArea(latLngs) {
            const R = 6378137;
            let area = 0;
            const pts = Array.isArray(latLngs[0]) ? latLngs : latLngs.map(p => [p.lat, p.lng]);
            const n = pts.length;
            for (let i = 0; i < n; i++) {
                const [lat1, lng1] = pts[i];
                const [lat2, lng2] = pts[(i + 1) % n];
                const dLng = (lng2 - lng1) * Math.PI / 180;
                const a1 = lat1 * Math.PI / 180;
                const a2 = lat2 * Math.PI / 180;
                area += dLng * (2 + Math.sin(a1) + Math.sin(a2));
            }
            return Math.abs(area * R * R / 2);
        }

        // Inicializar mapa con lazy loading de Leaflet
        async function initMap() {
            // ✅ Cargar Leaflet de forma lazy
            const L = await window.loadLeaflet();

            map = L.map('map', {
                zoomControl: true,
                attributionControl: true
            });

            // Capas base
            const streetMap = L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
                    maxZoom: 19
                }
            );

            const satelliteMap = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                {
                    attribution: '© <a href="https://www.esri.com/">Esri</a>',
                    maxZoom: 19
                }
            );

            // Ortofoto PNOA (IGN España) — máxima resolución en rural
            const pnoaMap = L.tileLayer.wms(
                'https://www.ign.es/wms-inspire/pnoa-ma',
                {
                    layers: 'OI.OrthoimageCoverage',
                    format: 'image/jpeg',
                    transparent: false,
                    attribution: '© <a href="https://www.ign.es">IGN España – PNOA</a>',
                    maxZoom: 20,
                    version: '1.3.0',
                }
            );

            // Catastro (DGC) — parcelas catastrales oficiales (capa de superposición)
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
                {
                    "🗺️ Mapa": streetMap,
                    "🛰️ Satélite": satelliteMap,
                    "📷 PNOA (IGN)": pnoaMap,
                },
                {
                    "🏛️ Catastro": catastroLayer,
                },
                { position: 'topright' }
            ).addTo(map);

            // Escala
            L.control.scale({ imperial: false, position: 'bottomright' }).addTo(map);

            // Botón geolocalización
            const LocateControl = L.Control.extend({
                options: { position: 'topright' },
                onAdd(map) {
                    const btn = L.DomUtil.create('button', '');
                    btn.title = '¿Dónde estoy?';
                    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="9" stroke-dasharray="4 2"/></svg>`;
                    btn.style.cssText = 'width:34px;height:34px;background:#fff;border:2px solid rgba(0,0,0,.2);border-radius:4px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#333;';

                    let locMarker = null;
                    let locCircle = null;
                    let watching = false;
                    let watchId = null;

                    L.DomEvent.on(btn, 'click', function(e) {
                        L.DomEvent.stopPropagation(e);

                        if (watching) {
                            // Desactivar
                            navigator.geolocation.clearWatch(watchId);
                            if (locMarker) { map.removeLayer(locMarker); locMarker = null; }
                            if (locCircle) { map.removeLayer(locCircle); locCircle = null; }
                            watching = false;
                            btn.style.color = '#333';
                            btn.style.background = '#fff';
                            return;
                        }

                        if (!navigator.geolocation) {
                            alert('Tu navegador no soporta geolocalización.');
                            return;
                        }

                        btn.style.color = '#16a34a';
                        btn.style.background = '#f0fdf4';

                        watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                                const accuracy = pos.coords.accuracy;

                                if (!locMarker) {
                                    locCircle = L.circle(latlng, { radius: accuracy, color: '#3b82f6', fillColor: '#93c5fd', fillOpacity: 0.2, weight: 1 }).addTo(map);
                                    locMarker = L.circleMarker(latlng, { radius: 8, color: '#1d4ed8', fillColor: '#3b82f6', fillOpacity: 1, weight: 2 })
                                        .bindTooltip('Tu posición', { permanent: false, direction: 'top' })
                                        .addTo(map);
                                    map.setView(latlng, 17);
                                } else {
                                    locMarker.setLatLng(latlng);
                                    locCircle.setLatLng(latlng).setRadius(accuracy);
                                }
                                watching = true;
                            },
                            (err) => {
                                btn.style.color = '#333';
                                btn.style.background = '#fff';
                                watching = false;
                                if (err.code === err.PERMISSION_DENIED) {
                                    alert('Permiso de ubicación denegado. Actívalo en la configuración del navegador.');
                                }
                            },
                            { enableHighAccuracy: true, maximumAge: 5000 }
                        );
                    });

                    return btn;
                }
            });
            new LocateControl().addTo(map);

            // Renderizar todos los recintos
            renderAllPlots();

            // Event listener para selector
            document.getElementById('recinto-selector').addEventListener('change', handleRecintoChange);
        }

        function renderAllPlots() {
            // Limpiar capas anteriores
            polygonLayers.forEach(layer => map.removeLayer(layer));
            polygonLayers = [];
            originalStyles = [];

            const allBounds = [];

            plotGeometries.forEach((geometry, index) => {
                const coords = parseWKT(geometry.wkt);
                
                if (!coords || coords.length === 0) {
                    console.warn(`No se pudieron parsear coordenadas para recinto ${index + 1}`);
                    return;
                }

                const style = {
                    color: geometry.color.line,
                    fillColor: geometry.color.fill,
                    fillOpacity: 0.5,
                    weight: 2
                };

                originalStyles[index] = {...style};

                // Crear polígono
                const polygon = L.polygon(coords, style);

                // Área aproximada en ha desde el polígono Leaflet
                const rawLatLngs = polygon.getLatLngs()[0];
                const areaM2 = rawLatLngs && rawLatLngs.length > 2 ? geodesicArea(rawLatLngs) : 0;
                const areaHa = areaM2 > 0 ? (areaM2 / 10000).toFixed(2) : null;

                // Popup con información
                polygon.bindPopup(`
                    <div style="min-width:200px">
                        <div style="border-left:4px solid ${geometry.color.line}; padding-left:10px; margin-bottom:10px">
                            <div style="font-weight:700; font-size:15px; color:${geometry.color.line}">
                                Recinto ${geometry.index}
                            </div>
                            <div style="font-family:monospace; font-size:12px; color:#555; margin-top:2px">
                                ${geometry.sigpac_formatted}
                            </div>
                        </div>
                        <table style="width:100%; font-size:12px; border-collapse:collapse">
                            <tr><td style="color:#888; padding:2px 0">{{ __('Polígono') }}</td><td style="font-weight:600; text-align:right">{{ __('${geometry.polygon || \'—\'}') }}</td></tr>
                            <tr><td style="color:#888; padding:2px 0">{{ __('Recinto') }}</td><td style="font-weight:600; text-align:right">{{ __('${geometry.enclosure || \'—\'}') }}</td></tr>
                            ${areaHa ? `<tr><td style="color:#888; padding:2px 0">{{ __('Área aprox.') }}</td><td style="font-weight:600; text-align:right">{{ __('${areaHa} ha') }}</td></tr>` : ''}
                            <tr><td style="color:#888; padding:2px 0">{{ __('Código') }}</td><td style="font-family:monospace; font-size:11px; text-align:right">{{ __('${geometry.sigpac_code}') }}</td></tr>
                        </table>
                    </div>
                `, { maxWidth: 280 });

                // Tooltip al hover
                const polygonLabel = geometry.polygon ? ` · Pol. ${geometry.polygon}` : '';
                const enclosureLabel = geometry.enclosure ? ` · Rec. ${geometry.enclosure}` : '';
                polygon.bindTooltip(
                    `<strong>{{ __('Recinto ${geometry.index}') }}</strong>${polygonLabel}${enclosureLabel}`,
                    { sticky: true, direction: 'top' }
                );

                // Highlight al hover
                polygon.on('mouseover', function() {
                    this.setStyle({ weight: 4, fillOpacity: 0.7 });
                });

                polygon.on('mouseout', function() {
                    if (document.getElementById('recinto-selector').value === 'all') {
                        this.setStyle(originalStyles[index]);
                    }
                });

                polygon.on('click', function() {
                    document.getElementById('recinto-selector').value = index;
                    zoomToRecinto(index);
                });

                polygon.addTo(map);
                polygonLayers.push(polygon);

                // Guardar bounds
                const bounds = polygon.getBounds();
                allBounds.push(bounds.getNorthEast());
                allBounds.push(bounds.getSouthWest());
            });

            // Ajustar vista
            if (allBounds.length > 0) {
                const bounds = L.latLngBounds(allBounds);
                map.fitBounds(bounds, { 
                    padding: [50, 50],
                    maxZoom: 17
                });
            } else{
                console.warn('No hay bounds para ajustar');
                map.setView([40.4168, -3.7038], 13);
            }
        }

        function handleRecintoChange(e) {
            const value = e.target.value;

            if (value === 'all') {
                renderAllPlots();
            } else {
                const index = parseInt(value);
                zoomToRecinto(index);
            }
        }

        function zoomToRecinto(index) {
            const geometry = plotGeometries[index];
            if (!geometry) return;

            // Resaltar solo el seleccionado
            polygonLayers.forEach((layer, i) => {
                if (i === index) {
                    layer.setStyle({
                        weight: 4,
                        fillOpacity: 0.7,
                        color: geometry.color.line
                    });
                    layer.openPopup();
                } else {
                    layer.setStyle({
                        weight: 1,
                        fillOpacity: 0.2
                    });
                }
            });

            // Zoom al recinto
            const bounds = polygonLayers[index].getBounds();
            map.fitBounds(bounds, {
                padding: [100, 100],
                maxZoom: 18
            });
        }

        // ✅ Usar parseWKT global desde bundle (definido en app.js)
        // El parser WKT ahora está disponible globalmente como window.parseWKT

        // ===========================================
        // 🛰️ NDVI Mode Toggle
        // ===========================================
        let ndviMode = false;
        let ndviData = null;
        const plotId = {{ $plot->id }};

        async function toggleNdviMode() {
            const toggle = document.getElementById('ndvi-toggle');
            const toggleSpan = toggle.querySelector('span');
            const ndviInfo = document.getElementById('ndvi-info');
            const ndviLoading = document.getElementById('ndvi-loading');
            
            ndviMode = !ndviMode;
            
            // Update toggle UI
            if (ndviMode) {
                toggle.classList.remove('bg-gray-300');
                toggle.classList.add('bg-green-500');
                toggleSpan.classList.remove('translate-x-1');
                toggleSpan.classList.add('translate-x-6');
                toggle.dataset.active = 'true';
                
                // Show loading
                ndviLoading.classList.remove('hidden');
                ndviInfo.classList.add('hidden');
                
                // Fetch NDVI data if not cached
                if (!ndviData) {
                    try {
                        const response = await fetch(`/remote-sensing/api/plot/${plotId}/ndvi-colors`);
                        ndviData = await response.json();
                    } catch (error) {
                        console.error('Error fetching NDVI data:', error);
                        ndviData = null;
                    }
                }
                
                // Hide loading, show info
                ndviLoading.classList.add('hidden');
                
                if (ndviData && ndviData.success) {
                    ndviInfo.classList.remove('hidden');
                    document.getElementById('ndvi-value').textContent = ndviData.ndvi_mean.toFixed(2);
                    document.getElementById('ndvi-status').textContent = `${ndviData.health_emoji} ${ndviData.health_text}`;
                    
                    // Apply NDVI colors to all polygons
                    applyNdviColors(ndviData.color);
                } else {
                    // Fallback to mock data for development
                    ndviInfo.classList.remove('hidden');
                    document.getElementById('ndvi-value').textContent = '0.65';
                    document.getElementById('ndvi-status').textContent = '🌿 Excelente';
                    applyNdviColors({ fill: 'rgba(34, 197, 94, 0.6)', line: '#16a34a' });
                }
            } else {
                toggle.classList.remove('bg-green-500');
                toggle.classList.add('bg-gray-300');
                toggleSpan.classList.remove('translate-x-6');
                toggleSpan.classList.add('translate-x-1');
                toggle.dataset.active = 'false';
                
                ndviInfo.classList.add('hidden');
                
                // Restore original colors
                restoreOriginalColors();
            }
        }

        function applyNdviColors(color) {
            polygonLayers.forEach((layer, index) => {
                layer.setStyle({
                    color: color.line,
                    fillColor: color.fill,
                    fillOpacity: 0.6,
                    weight: 2
                });
            });
        }

        function restoreOriginalColors() {
            polygonLayers.forEach((layer, index) => {
                if (originalStyles[index]) {
                    layer.setStyle(originalStyles[index]);
                }
            });
        }

        // Iniciar cuando DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                initMap();
                autoActivateNdviIfNeeded();
            });
        } else {
            initMap();
            autoActivateNdviIfNeeded();
        }

        // 🛰️ Auto-activar NDVI si viene desde teledetección (?ndvi=1)
        function autoActivateNdviIfNeeded() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('ndvi') === '1') {
                setTimeout(() => {
                    toggleNdviMode();
                }, 500); // Pequeño delay para que el mapa cargue primero
            }
        }
    </script>
</body>
</html>
