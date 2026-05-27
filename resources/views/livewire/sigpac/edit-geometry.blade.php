<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="{{ __('Editar Geometría SIGPAC') }}"
        :description="'Código: ' . $sigpac->full_code"
    />

    <!-- Selección de Parcela -->
    @if(!$plotId)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="map" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Seleccionar Parcela') }}</span>
                </div>
            </x-slot:header>
            <p class="text-sm text-zinc-600 mb-4">{{ __('Selecciona una parcela para crear o editar su geometría:') }}</p>

            @if($availablePlots->count() > 0)
                <div class="space-y-2">
                    @foreach($availablePlots as $availablePlot)
                        <a
                            href="{{ route('sigpac.geometry.edit-plot', ['sigpacId' => $sigpacId, 'plotId' => $availablePlot->id]) }}"
                            class="block p-4 border border-zinc-200 rounded-lg hover:border-agro-500 hover:bg-agro-50 transition-colors"
                        >
                            <div class="font-semibold text-zinc-900">{{ $availablePlot->name }}</div>
                            <div class="text-sm text-zinc-600">{{ $availablePlot->municipality->name ?? 'Sin municipio' }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="map" :message="__('No hay parcelas disponibles con este código SIGPAC')" />
            @endif
        </x-agro.card>
    @else
        <!-- Mapa -->
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-agro-50">
                            <flux:icon icon="map" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ $viewOnly ? 'Visualización de Mapa' : 'Geometría de la Parcela' }}</span>
                    </div>
                    @if(!$viewOnly)
                    <div class="flex gap-2">
                        @if($geometryId)
                            <flux:button
                                wire:click="delete"
                                wire:confirm="{{ __('¿Estás seguro de eliminar esta geometría?') }}"
                                variant="danger"
                                size="sm"
                            >
                                {{ __('Eliminar') }}
                            </flux:button>
                        @endif
                        @if($plotId)
                            <flux:button
                                wire:click="generateMapFromSigpac"
                                wire:loading.attr="disabled"
                                variant="primary"
                                size="sm"
                            >
                                <span wire:loading.remove wire:target="generateMapFromSigpac">{{ __('Generar desde SIGPAC') }}</span>
                                <span wire:loading wire:target="generateMapFromSigpac">{{ __('Generando...') }}</span>
                            </flux:button>
                        @endif
                        <flux:button
                            wire:click="$set('showMap', true)"
                            variant="primary"
                            size="sm"
                        >
                            {{ $geometryId ? __('Editar Mapa') : __('Crear Mapa') }}
                        </flux:button>
                    </div>
                @else
                    <flux:button
                        href="{{ route('sigpac.geometry.edit-plot', ['sigpacId' => $sigpacId, 'plotId' => $plotId]) }}"
                        variant="primary"
                        size="sm"
                    >
                        {{ __('Editar Mapa') }}
                    </flux:button>
                @endif
                </div>
            </x-slot:header>

            @if($showMap && !$viewOnly)
                <flux:callout variant="info" class="mb-4">
                    <flux:callout.text>
                        <strong>{{ __('Instrucciones:') }}</strong> Haz clic en el mapa para añadir puntos. Haz clic en "Guardar" cuando termines.
                    </flux:callout.text>
                </flux:callout>

                <!-- Mapa con Leaflet -->
                <div id="map" style="height: 500px; width: 100%;" class="rounded-lg border border-zinc-300"></div>

                <div class="mt-4 flex justify-end gap-2">
                    <flux:button
                        wire:click="$set('showMap', false)"
                        variant="outline"
                        size="sm"
                    >{{ __('Cancelar') }}</flux:button>
                    <flux:button
                        wire:click="save"
                        variant="primary"
                        size="sm"
                    >{{ __('Guardar Geometría') }}</flux:button>
                </div>
            @elseif($geometryId && !empty($coordinates))
                <!-- Mostrar mapa existente (solo lectura o modo edición) -->
                <div class="mb-4 p-4 {{ $viewOnly ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200' }} border rounded-lg">
                    <p class="text-sm {{ $viewOnly ? 'text-blue-800' : 'text-green-800' }}">
                        <strong>{{ $viewOnly ? 'Vista del mapa:' : 'Mapa de geometría:' }}</strong>
                        {{ $viewOnly ? 'Visualización de la geometría guardada para este código SIGPAC.' : 'Esta es la geometría guardada para este código SIGPAC.' }}
                    </p>
                </div>
                <div id="map-view" style="height: 500px; width: 100%;" class="rounded-lg border border-zinc-300"></div>
                @if(!$viewOnly)
                    <div class="mt-4 flex justify-end gap-2">
                        <flux:button
                            wire:click="$set('showMap', true)"
                            variant="primary"
                            size="sm"
                        >{{ __('Editar Geometría') }}</flux:button>
                    </div>
                @endif
            @else
                <p class="text-sm text-zinc-500 text-center py-8">{{ __('No hay geometría guardada. Haz clic en "Crear Mapa" para añadir una.') }}</p>
            @endif
        </x-agro.card>
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
