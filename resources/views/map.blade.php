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
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    📍 Seleccionar Recinto:
                </label>
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
                    <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        🛰️ Vista NDVI
                    </label>
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
                        <span class="text-gray-500">Bajo</span>
                        <span class="w-4 h-3 rounded ml-1" style="background: rgba(251, 146, 60, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(250, 204, 21, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(52, 211, 153, 0.6)"></span>
                        <span class="w-4 h-3 rounded" style="background: rgba(34, 197, 94, 0.6)"></span>
                        <span class="text-gray-500">Alto</span>
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
                <p class="text-xs font-semibold text-gray-600 mb-2">Leyenda de Recintos:</p>
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
                <p><strong>Total recintos:</strong> {{ count($plotGeometries) }}</p>
                @if($plot->area)
                    <p><strong>Área total:</strong> {{ number_format($plot->area, 2) }} ha</p>
                @endif
                @if($plot->municipality)
                    <p><strong>Municipio:</strong> {{ $plot->municipality->name }}</p>
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

        // Inicializar mapa con lazy loading de Leaflet
        async function initMap() {
            console.log('🗺️ Inicializando mapa SIGPAC');
            console.log('Geometrías cargadas:', plotGeometries.length);

            // ✅ Cargar Leaflet de forma lazy
            const L = await window.loadLeaflet();
            console.log('✅ Leaflet cargado');

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

            satelliteMap.addTo(map);

            L.control.layers({
                "🗺️ Mapa": streetMap,
                "🛰️ Satélite": satelliteMap
            }, null, { position: 'topright' }).addTo(map);

            // Renderizar todos los recintos
            renderAllPlots();

            // Event listener para selector
            document.getElementById('recinto-selector').addEventListener('change', handleRecintoChange);
        }

        function renderAllPlots() {
            console.log('Renderizando todos los recintos...');
            
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

                // Popup con información
                polygon.bindPopup(`
                    <div class="p-3">
                        <h3 class="font-bold text-lg mb-2" style="color: ${geometry.color.line}">
                            ${geometry.sigpac_formatted}
                        </h3>
                        <div class="space-y-1 text-sm">
                            <p><strong>Recinto:</strong> ${geometry.index}</p>
                            <p><strong>Código:</strong> ${geometry.sigpac_code}</p>
                        </div>
                    </div>
                `, { maxWidth: 300 });

                // Tooltip al hover
                polygon.bindTooltip(`Recinto ${geometry.index}`, { 
                    sticky: true,
                    direction: 'top'
                });

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

            console.log(`✅ ${polygonLayers.length} polígonos renderizados`);

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

            console.log(`Zoom a recinto ${index + 1}: ${geometry.sigpac_formatted}`);

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
            console.log('🛰️ Applying NDVI colors:', color);
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
            console.log('🎨 Restoring original colors');
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
