<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-page-header
        title="Editar Geometría SIGPAC"
        :description="'Código: ' . $sigpac->full_code"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    <!-- Selección de Parcela -->
    @if(!$plotId)
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Seleccionar Parcela</h3>
            <p class="text-sm text-gray-600 mb-4">Selecciona una parcela para crear o editar su geometría:</p>
            
            @if($availablePlots->count() > 0)
                <div class="space-y-2">
                    @foreach($availablePlots as $availablePlot)
                        <a 
                            href="{{ route('sigpac.geometry.edit-plot', ['sigpacId' => $sigpacId, 'plotId' => $availablePlot->id]) }}"
                            class="block p-4 border border-gray-200 rounded-lg hover:border-[var(--color-agro-green)] hover:bg-[var(--color-agro-green-bg)] transition-colors"
                        >
                            <div class="font-semibold text-gray-900">{{ $availablePlot->name }}</div>
                            <div class="text-sm text-gray-600">{{ $availablePlot->municipality->name ?? 'Sin municipio' }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No hay parcelas disponibles con este código SIGPAC.</p>
            @endif
        </div>
    @else
        <!-- Mapa -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ $viewOnly ? 'Visualización de Mapa' : 'Geometría de la Parcela' }}
                </h3>
                @if(!$viewOnly)
                    <div class="flex gap-2">
                        @if($geometryId)
                            <button
                                wire:click="delete"
                                wire:confirm="¿Estás seguro de eliminar esta geometría?"
                                class="px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                            >
                                Eliminar
                            </button>
                        @endif
                        @if($plotId)
                            <button
                                wire:click="generateMapFromSigpac"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="generateMapFromSigpac">
                                    Generar desde SIGPAC
                                </span>
                                <span wire:loading wire:target="generateMapFromSigpac" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Generando...
                                </span>
                            </button>
                        @endif
                        <button
                            wire:click="$set('showMap', true)"
                            class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] rounded-lg hover:opacity-90 transition-opacity"
                        >
                            {{ $geometryId ? 'Editar Mapa' : 'Crear Mapa' }}
                        </button>
                    </div>
                @else
                    <a
                        href="{{ route('sigpac.geometry.edit-plot', ['sigpacId' => $sigpacId, 'plotId' => $plotId]) }}"
                        class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] rounded-lg hover:opacity-90 transition-opacity"
                    >
                        Editar Mapa
                    </a>
                @endif
            </div>

            @if($showMap && !$viewOnly)
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800 mb-2">
                        <strong>Instrucciones:</strong> Haz clic en el mapa para añadir puntos. Haz clic en "Guardar" cuando termines.
                    </p>
                </div>

                <!-- Mapa con Leaflet -->
                <div id="map" style="height: 500px; width: 100%;" class="rounded-lg border border-gray-300"></div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        wire:click="$set('showMap', false)"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] rounded-lg hover:opacity-90 transition-opacity"
                    >
                        Guardar Geometría
                    </button>
                </div>
            @elseif($geometryId && !empty($coordinates))
                <!-- Mostrar mapa existente (solo lectura o modo edición) -->
                <div class="mb-4 p-4 {{ $viewOnly ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200' }} border rounded-lg">
                    <p class="text-sm {{ $viewOnly ? 'text-blue-800' : 'text-green-800' }}">
                        <strong>{{ $viewOnly ? 'Vista del mapa:' : 'Mapa de geometría:' }}</strong> 
                        {{ $viewOnly ? 'Visualización de la geometría guardada para este código SIGPAC.' : 'Esta es la geometría guardada para este código SIGPAC.' }}
                    </p>
                </div>
                <div id="map-view" style="height: 500px; width: 100%;" class="rounded-lg border border-gray-300"></div>
                @if(!$viewOnly)
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            wire:click="$set('showMap', true)"
                            class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] rounded-lg hover:opacity-90 transition-opacity"
                        >
                            Editar Geometría
                        </button>
                    </div>
                @endif
            @else
                <p class="text-sm text-gray-500 text-center py-8">No hay geometría guardada. Haz clic en "Crear Mapa" para añadir una.</p>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    let map = null;
    let mapView = null;
    let markers = [];
    let polygon = null;

    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement || map) return;

        // Limpiar mapa anterior si existe
        if (map) {
            map.remove();
            map = null;
        }
        markers = [];
        if (polygon) polygon.remove();

        // Inicializar mapa centrado en España
        map = L.map('map').setView([40.4168, -3.7038], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Cargar coordenadas existentes si hay
        @if(!empty($coordinates))
            const existingCoords = @json($coordinates);
            if (existingCoords.length > 0) {
                existingCoords.forEach(coord => {
                    const marker = L.marker([coord.lat, coord.lng]).addTo(map);
                    markers.push(marker);
                });
                
                polygon = L.polygon(
                    existingCoords.map(c => [c.lat, c.lng]),
                    {color: '#10b981', fillColor: '#10b981', fillOpacity: 0.3, weight: 2}
                ).addTo(map);
                map.fitBounds(polygon.getBounds());
            }
        @endif

        // Manejar clics en el mapa
        map.on('click', function(e) {
            const marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
            markers.push(marker);
            
            // Actualizar polígono
            updatePolygon();
            
            // Actualizar coordenadas en Livewire
            const coords = markers.map(m => ({
                lat: m.getLatLng().lat,
                lng: m.getLatLng().lng
            }));
            
            @this.set('coordinates', coords);
        });

        // Permitir eliminar marcadores con doble clic
        markers.forEach(marker => {
            marker.on('dblclick', function() {
                map.removeLayer(marker);
                markers = markers.filter(m => m !== marker);
                updatePolygon();
                const coords = markers.map(m => ({
                    lat: m.getLatLng().lat,
                    lng: m.getLatLng().lng
                }));
                @this.set('coordinates', coords);
            });
        });
    }

    function updatePolygon() {
        if (polygon) {
            map.removeLayer(polygon);
        }
        
        if (markers.length >= 3) {
            const coords = markers.map(m => [m.getLatLng().lat, m.getLatLng().lng]);
            polygon = L.polygon(coords, {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.3,
                weight: 2
            }).addTo(map);
        }
    }

    function initMapView() {
        const mapViewElement = document.getElementById('map-view');
        if (!mapViewElement || mapView) return;

        mapView = L.map('map-view').setView([40.4168, -3.7038], 6);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapView);

        @if(!empty($coordinates))
            const coords = @json($coordinates);
            if (coords.length > 0) {
                const viewPolygon = L.polygon(
                    coords.map(c => [c.lat, c.lng]),
                    {color: '#10b981', fillColor: '#10b981', fillOpacity: 0.3, weight: 2}
                ).addTo(mapView);
                mapView.fitBounds(viewPolygon.getBounds());
            }
        @endif
    }

    // Inicializar cuando cambia showMap
    Livewire.hook('morph.updated', ({ el, component }) => {
        setTimeout(() => {
            if (document.getElementById('map') && @this.showMap) {
                initMap();
            }
            if (document.getElementById('map-view') && !@this.showMap && @this.geometryId) {
                initMapView();
            }
        }, 100);
    });

    // Inicializar al cargar
    setTimeout(() => {
        if (document.getElementById('map') && @this.showMap) {
            initMap();
        }
        if (document.getElementById('map-view') && !@this.showMap && @this.geometryId) {
            initMapView();
        }
    }, 300);
});
</script>
@endpush

