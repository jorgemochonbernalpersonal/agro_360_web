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

            // Añadir capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

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

                    // Crear polígono
                    const polygon = L.polygon(validCoords, {
                        color: geometry.color?.line || '#3388ff',
                        fillColor: geometry.color?.fill || '#3388ff',
                        fillOpacity: 0.3,
                        weight: 2
                    });

                    // Añadir popup
                    polygon.bindPopup(`
                        <div class="p-2">
                            <p class="font-bold text-gray-900">${geometry.plot_name || 'Sin nombre'}</p>
                            <p class="text-sm text-gray-600 font-mono">${geometry.sigpac_formatted || geometry.sigpac_code || 'Sin código'}</p>
                        </div>
                    `);

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
