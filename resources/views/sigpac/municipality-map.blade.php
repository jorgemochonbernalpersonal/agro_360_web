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

        // Inicializar mapa con lazy loading de Leaflet
        async function initMap() {
            console.log('🗺️ Inicializando mapa del municipio');
            console.log('Geometrías cargadas:', plotGeometries.length);
            console.log('Datos completos:', plotGeometries);

            // Verificar que existen las funciones de parsing WKT
            if (typeof window.parseWKT === 'undefined') {
                console.error('❌ window.parseWKT no está definido');
                alert('Error: El parser WKT no está cargado. Recarga la página.');
                return;
            }

            if (typeof window.isValidWKT === 'undefined') {
                console.error('❌ window.isValidWKT no está definido');
                alert('Error: El validador WKT no está cargado. Recarga la página.');
                return;
            }

            // Cargar Leaflet de forma lazy
            const L = await window.loadLeaflet();
            console.log('✅ Leaflet cargado');

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

            // Procesar cada geometría
            plotGeometries.forEach((geometry, index) => {
                console.log(`Procesando geometría ${index}:`, geometry);
                
                if (!geometry.wkt) {
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
                    console.log(`✅ Coordenadas parseadas para ${geometry.sigpac_formatted}:`, coordinates);
                    
                    if (!coordinates || coordinates.length === 0) {
                        console.warn(`❌ No se pudieron parsear coordenadas para ${geometry.sigpac_formatted}`);
                        errorsCount++;
                        return;
                    }

                    // Crear polígono
                    const polygon = L.polygon(coordinates, {
                        color: geometry.color.line,
                        fillColor: geometry.color.fill,
                        fillOpacity: 0.3,
                        weight: 2
                    });

                    // Añadir popup
                    polygon.bindPopup(`
                        <div class="p-2">
                            <p class="font-bold text-gray-900">${geometry.plot_name}</p>
                            <p class="text-sm text-gray-600 font-mono">${geometry.sigpac_formatted}</p>
                        </div>
                    `);

                    // Añadir al mapa
                    polygon.addTo(map);
                    polygonLayers.push(polygon);
                    console.log(`✅ Polígono ${index} añadido al mapa`);

                    // Actualizar bounds
                    if (!bounds) {
                        bounds = polygon.getBounds();
                    } else {
                        bounds.extend(polygon.getBounds());
                    }
                } catch (error) {
                    console.error(`❌ Error procesando geometría ${index}:`, error);
                    errorsCount++;
                }
            });

            // Ajustar vista a todos los polígonos
            if (bounds && polygonLayers.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
                console.log(`✅ ${polygonLayers.length} polígonos renderizados correctamente`);
            } else {
                // Vista por defecto de España si no hay geometrías
                map.setView([40.4168, -3.7038], 6);
                console.warn(`⚠️ No se renderizaron polígonos. Errores: ${errorsCount}`);
            }

            console.log(`📊 Resumen: ${polygonLayers.length} éxitos, ${errorsCount} errores`);
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    </script>
    @endpush
</x-app-layout>
