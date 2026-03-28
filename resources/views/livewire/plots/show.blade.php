<div class="space-y-6 animate-fade-in">
    <x-agro.page-header :title="$plot->name" description="Detalles de la parcela">
        <x-slot:actions>
            @can('update', $plot)
                <flux:button
                    href="{{ roleRoute('plots.edit', $plot) }}"
                    variant="primary"
                    icon="pencil-square"
                >
                    Editar
                </flux:button>
            @endcan
            @if($fromVisual)
                <flux:button href="{{ route('winery.visual') }}" variant="outline" icon="map">
                    Volver al Mapa
                </flux:button>
            @else
                <flux:button href="{{ roleRoute('plots.index') }}" variant="outline" icon="arrow-left">
                    Volver
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    @if($isViticulturist)
        <x-agro.tabs
            :tabs="['info' => 'Información', 'entorno' => 'Entorno (RD 1311/2012)']"
            :active="$currentTab"
        />
    @endif

    @if($currentTab === 'info')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informacion Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informacion General -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-agro-50">
                            <flux:icon icon="information-circle" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Información General</span>
                    </div>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-500">Nombre</label>
                        <p class="text-zinc-900 text-lg">{{ $plot->name }}</p>
                    </div>

                    @if($plot->area)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Area</label>
                            <p class="text-zinc-900">{{ number_format($plot->area, 3) }} hectareas</p>
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-semibold text-zinc-500">Estado</label>
                        <div class="mt-1">
                            <x-agro.status-badge :active="$plot->active" />
                        </div>
                    </div>

                    @if($plot->tenure_regime)
                        @php
                            $tenureLabels = [
                                'propiedad'    => 'Propiedad',
                                'arrendamiento'=> 'Arrendamiento',
                                'aparceria'    => 'Aparcería',
                                'cesion_uso'   => 'Cesión de uso',
                                'otros'        => 'Otros',
                            ];
                        @endphp
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Régimen de Tenencia</label>
                            <p class="text-zinc-900">{{ $tenureLabels[$plot->tenure_regime] ?? $plot->tenure_regime }}</p>
                        </div>
                    @endif

                    @if($plot->soil_type)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Tipo de Suelo</label>
                            <p class="text-zinc-900 capitalize">{{ str_replace('-', ' ', $plot->soil_type) }}</p>
                        </div>
                    @endif

                    @if($plot->orientation)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Orientación</label>
                            <p class="text-zinc-900">{{ $plot->orientation }}</p>
                        </div>
                    @endif

                    @if($plot->description)
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-zinc-500">Descripcion</label>
                            <p class="text-zinc-900">{{ $plot->description }}</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            <!-- Asignaciones -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="building-office" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Asignaciones</span>
                    </div>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-500">Bodega</label>
                        @php
                            $wineryName = '-';
                            if ($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty()) {
                                $wineryName = $plot->viticulturist->wineries->first()->name;
                            }
                        @endphp
                        <p class="text-zinc-900">{{ $wineryName }}</p>
                    </div>

                    @if($plot->viticulturist)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Viticultor Asignado</label>
                            <p class="text-zinc-900">{{ $plot->viticulturist->name }}</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            <!-- Ubicacion -->
            @if($plot->autonomousCommunity)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="map-pin" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">Ubicación</span>
                        </div>
                    </x-slot:header>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Comunidad Autonoma</label>
                            <p class="text-zinc-900">{{ $plot->autonomousCommunity->name }}</p>
                        </div>

                        @if($plot->province)
                            <div>
                                <label class="text-sm font-semibold text-zinc-500">Provincia</label>
                                <p class="text-zinc-900">{{ $plot->province->name }}</p>
                            </div>
                        @endif

                        @if($plot->municipality)
                            <div>
                                <label class="text-sm font-semibold text-zinc-500">Municipio</label>
                                <p class="text-zinc-900">{{ $plot->municipality->name }}</p>
                            </div>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            <!-- Configuracion de Alertas -->
            @if($plot->ndvi_alert_threshold)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-yellow-50">
                            <flux:icon icon="bell-alert" class="size-4 text-yellow-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Configuración de Alertas</span>
                    </div>
                </x-slot:header>
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-semibold text-zinc-500">Umbral NDVI (Vigor)</label>
                        <p class="text-zinc-900 font-bold text-lg">{{ $plot->ndvi_alert_threshold }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-500">Alertas por Email</label>
                        <div class="mt-1">
                            @if($plot->alert_email_enabled)
                                <flux:badge color="green" icon="check-circle" size="sm">Activado</flux:badge>
                            @else
                                <flux:badge size="sm">Desactivado</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>
            </x-agro.card>
            @endif

            <!-- SIGPAC -->
            @if($plot->sigpacUses->count() > 0 || $plot->sigpacCodes->count() > 0 || auth()->user()->can('update', $plot))
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-blue-50">
                                    <flux:icon icon="hashtag" class="size-4 text-blue-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Datos SIGPAC</span>
                            </div>
                        @if($plot->sigpacCodes->count() > 0)
                            @php
                                $hasGeometryForButton = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                                    ->whereNotNull('plot_geometry_id')
                                    ->exists();
                            @endphp
                            @can('update', $plot)
                                @if(!$hasGeometryForButton)
                                    <flux:button
                                        wire:click="generateMap({{ $plot->id }})"
                                        wire:loading.attr="disabled"
                                        variant="outline"
                                        size="sm"
                                        icon="map"
                                    >
                                        <span wire:loading.remove wire:target="generateMap({{ $plot->id }})">
                                            Generar Mapa
                                        </span>
                                        <span wire:loading wire:target="generateMap({{ $plot->id }})">
                                            Generando...
                                        </span>
                                    </flux:button>
                                @endif
                            @endcan
                        @endif
                    </div>
                    </x-slot:header>

                    @if($plot->sigpacUses->count() > 0)
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-zinc-500 block mb-2">Usos SIGPAC</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($plot->sigpacUses as $use)
                                    <flux:badge color="green" size="sm">
                                        {{ $use->code }} - {{ $use->description }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-zinc-500">Codigos SIGPAC</label>
                            <flux:button href="{{ route('sigpac.codes.create', ['plot_id' => $plot->id]) }}" variant="ghost" size="xs" icon="plus">
                                Añadir código SIGPAC
                            </flux:button>
                        </div>
                        @if($plot->sigpacCodes->count() > 0)
                            <div class="space-y-2">
                                @foreach($plot->sigpacCodes as $code)
                                    <div class="flex items-center justify-between p-2 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                        <flux:badge color="blue" size="sm">
                                            {{ $code->formatted_code ?? $code->code }}
                                        </flux:badge>
                                        @php
                                            $codeHasGeometry = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                                                ->where('sigpac_code_id', $code->id)
                                                ->whereNotNull('plot_geometry_id')
                                                ->exists();
                                        @endphp
                                        @can('update', $plot)
                                            @if(!$codeHasGeometry)
                                                <flux:button
                                                    wire:click="generateMap({{ $plot->id }}, {{ $code->id }})"
                                                    wire:loading.attr="disabled"
                                                    variant="ghost"
                                                    size="xs"
                                                    icon="map"
                                                >
                                                    <span wire:loading.remove wire:target="generateMap({{ $plot->id }}, {{ $code->id }})">
                                                        Generar Mapa
                                                    </span>
                                                    <span wire:loading wire:target="generateMap({{ $plot->id }}, {{ $code->id }})">
                                                        Generando...
                                                    </span>
                                                </flux:button>
                                            @endif
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-400 italic">No hay codigos SIGPAC asociados</p>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            <!-- Mapa SIGPAC -->
            @php
                $hasGeometry = \App\Models\MultipartPlotSigpac::where('plot_id', $plot->id)
                    ->whereNotNull('plot_geometry_id')
                    ->exists();

                $plotGeometries = [];
                if ($hasGeometry) {
                    $relations = \App\Models\MultipartPlotSigpac::with(['plotGeometry', 'sigpacCode'])
                        ->where('plot_id', $plot->id)
                        ->whereNotNull('plot_geometry_id')
                        ->get();

                    foreach ($relations as $relation) {
                        if ($relation->plotGeometry) {
                            $wkt = $relation->plotGeometry->getWktCoordinates();
                            if ($wkt) {
                                $plotGeometries[] = [
                                    'wkt' => $wkt,
                                    'sigpac_code' => $relation->sigpacCode?->code ?? null,
                                ];
                            }
                        }
                    }
                }
            @endphp

            @if($hasGeometry)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-agro-50">
                                    <flux:icon icon="map" class="size-4 text-agro-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Mapa de la Parcela</span>
                            </div>
                            <flux:button href="/map/{{ $plot->id }}" variant="primary" size="sm" icon="map">
                                Ver Mapa Completo
                            </flux:button>
                        </div>
                    </x-slot:header>

                    <div class="flex items-start gap-4 p-1">
                        <div class="w-12 h-12 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="map" class="size-6 text-agro-600" />
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-zinc-900 mb-1">Mapa Interactivo Disponible</p>
                            <p class="text-zinc-500 text-sm mb-3">
                                Esta parcela tiene recintos SIGPAC con geometrías generadas. Visualiza el mapa a pantalla completa con selector de recintos.
                            </p>
                            <ul class="text-sm text-zinc-600 space-y-1">
                                <li class="flex items-center gap-2">
                                    <flux:icon icon="check-circle" class="size-4 text-agro-500 flex-shrink-0" />
                                    Vista a pantalla completa
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon icon="check-circle" class="size-4 text-agro-500 flex-shrink-0" />
                                    Selector de recintos individuales
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon icon="check-circle" class="size-4 text-agro-500 flex-shrink-0" />
                                    Zoom y navegación interactiva
                                </li>
                                <li class="flex items-center gap-2">
                                    <flux:icon icon="check-circle" class="size-4 text-agro-500 flex-shrink-0" />
                                    Colores diferenciados por recinto
                                </li>
                            </ul>
                        </div>
                    </div>
                </x-agro.card>
            @endif

            <!-- Teledeteccion Sentinel-2 -->
            <livewire:viticulturist.remote-sensing.plot-ndvi-card :plot="$plot" />

            <!-- Datos Meteorologicos -->
            <livewire:viticulturist.remote-sensing.plot-weather-card :plot="$plot" />

            <!-- Coordenadas Multiparte -->
            @if($plot->multiplePlotSigpacs->count() > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="map-pin" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">Coordenadas Multiparte</span>
                        </div>
                    </x-slot:header>

                    <div class="space-y-4">
                        @foreach($plot->multiplePlotSigpacs as $coord)
                            <div class="p-4 border border-zinc-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-sm font-semibold text-zinc-700">Coordenadas #{{ $loop->iteration }}</span>
                                    @if($coord->sigpacCode)
                                        <flux:badge color="blue" size="sm">
                                            {{ $coord->sigpacCode->code }}
                                        </flux:badge>
                                    @endif
                                </div>
                                <p class="text-zinc-900 font-mono text-sm whitespace-pre-wrap">{{ $coord->plotGeometry?->getWktCoordinates() ?? '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Fechas -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="calendar" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Fechas</span>
                    </div>
                </x-slot:header>
                <div class="space-y-2 text-sm">
                    <div>
                        <label class="text-zinc-500">Creada</label>
                        <p class="text-zinc-900">{{ $plot->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-zinc-500">Actualizada</label>
                        <p class="text-zinc-900">{{ $plot->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </x-agro.card>
        </div>
    </div>
    @elseif($currentTab === 'entorno')

    {{-- TAB: ENTORNO RD 1311/2012 --}}
    @if(!$activeCampaign)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Sin campaña activa</flux:callout.heading>
            <flux:callout.text>Necesitas tener una campaña activa para registrar el entorno de la parcela.</flux:callout.text>
        </flux:callout>
    @else
        <div class="max-w-3xl space-y-6">

            {{-- Cabecera campaña --}}
            <div class="flex items-center gap-2">
                <flux:badge color="green" icon="calendar">
                    Campaña {{ $activeCampaign->year }} — {{ $activeCampaign->name }}
                </flux:badge>
                @if($env_id)
                    <flux:badge color="blue" icon="check-circle" size="sm">Datos guardados</flux:badge>
                @endif
            </div>

            {{-- Captaciones de Agua --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="beaker" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Captaciones de Agua</span>
                    </div>
                </x-slot:header>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>¿Existe una captación de agua cercana?</flux:label>
                        <div class="flex items-center gap-3 mt-1">
                            <flux:switch wire:model.live="env_water_intake_nearby" />
                            <span class="text-sm text-zinc-600">
                                {{ $env_water_intake_nearby ? 'Sí, existe captación de agua cercana' : 'No' }}
                            </span>
                        </div>
                    </flux:field>

                    @if($env_water_intake_nearby)
                        <flux:field>
                            <flux:label>Distancia a la captación (metros)</flux:label>
                            <flux:input wire:model="env_water_intake_distance_m" type="number" min="0" step="0.01" placeholder="Ej: 50" />
                            <flux:error name="env_water_intake_distance_m" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.card>

            {{-- Zonas Protegidas --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-yellow-50">
                            <flux:icon icon="shield-exclamation" class="size-4 text-yellow-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Zonas Protegidas</span>
                    </div>
                </x-slot:header>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Zona protegida total (ZEP, LIC, ZEC, ZEPA…)</flux:label>
                        <div class="flex items-center gap-3 mt-1">
                            <flux:switch wire:model.live="env_protected_zone_total" />
                            <span class="text-sm text-zinc-600">
                                {{ $env_protected_zone_total ? 'Sí, zona protegida total' : 'No' }}
                            </span>
                        </div>
                    </flux:field>

                    <flux:field>
                        <flux:label>Zona protegida parcial / tampón</flux:label>
                        <div class="flex items-center gap-3 mt-1">
                            <flux:switch wire:model.live="env_protected_zone_partial" />
                            <span class="text-sm text-zinc-600">
                                {{ $env_protected_zone_partial ? 'Sí, zona protegida parcial' : 'No' }}
                            </span>
                        </div>
                    </flux:field>

                    @if($env_protected_zone_total || $env_protected_zone_partial)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Tipo de zona de protección</flux:label>
                                <flux:input wire:model="env_protection_zone_type" placeholder="Ej: ZEC, LIC, ZEPA..." maxlength="100" />
                                <flux:error name="env_protection_zone_type" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Zona tampón (metros)</flux:label>
                                <flux:input wire:model="env_buffer_zone_m" type="number" min="0" step="0.01" placeholder="Ej: 10" />
                                <flux:error name="env_buffer_zone_m" />
                            </flux:field>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            {{-- Características del Terreno --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-agro-50">
                            <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Características del Terreno</span>
                    </div>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Pendiente (%)</flux:label>
                        <flux:input wire:model="env_slope_pct" type="number" min="0" max="100" step="0.01" placeholder="Ej: 15" />
                        <flux:error name="env_slope_pct" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Riesgo de erosión</flux:label>
                        <div class="flex items-center gap-3 mt-2">
                            <flux:switch wire:model="env_erosion_risk" />
                            <span class="text-sm text-zinc-600">
                                {{ $env_erosion_risk ? 'Sí, riesgo de erosión' : 'No' }}
                            </span>
                        </div>
                    </flux:field>
                </div>
            </x-agro.card>

            {{-- Notas --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-zinc-50">
                            <flux:icon icon="document-text" class="size-4 text-zinc-500" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Notas</span>
                    </div>
                </x-slot:header>

                <flux:field>
                    <flux:textarea wire:model="env_notes" rows="3" placeholder="Observaciones sobre el entorno de la parcela..." />
                    <flux:error name="env_notes" />
                </flux:field>
            </x-agro.card>

            {{-- Guardar --}}
            <div class="flex justify-end">
                <flux:button wire:click="saveEnvironment" variant="primary" icon="check">
                    Guardar entorno
                </flux:button>
            </div>

        </div>
    @endif
    @endif
</div>

@push('scripts')
@if($hasGeometry && count($plotGeometries) > 0)

    <script>
        function initMap() {
            console.log('=== Iniciando mapa ===');
            let plotGeometries = @json($plotGeometries);
            console.log('plotGeometries:', plotGeometries);

            if (plotGeometries.length === 0) {
                console.warn('No hay geometrias para mostrar');
                return;
            }

            // Verificar que el contenedor existe
            const mapContainer = document.getElementById('plot-map');
            if (!mapContainer) {
                console.error('No se encontro el contenedor #plot-map');
                return;
            }

            // Inicializar mapa
            let map = L.map('plot-map', {
                zoomControl: true
            });

            // Capas base
            let streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            });

            let satelliteMap = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '&copy; Esri'
                }
            );

            streetMap.addTo(map);

            // Control de capas
            let baseMaps = {
                "Mapa": streetMap,
                "Satelite": satelliteMap
            };
            L.control.layers(baseMaps).addTo(map);

            let bounds = [];

            // Funcion para parsear WKT
            function parseWKT(wkt) {
                if (!wkt || typeof wkt !== 'string') {
                    return [];
                }

                const trimmedWkt = wkt.trim();
                if (trimmedWkt.length === 0) {
                    return [];
                }

                if (trimmedWkt.startsWith("POLYGON")) {
                    return parsePolygon(trimmedWkt);
                } else if (trimmedWkt.startsWith("MULTIPOLYGON")) {
                    return parseMultiPolygon(trimmedWkt);
                } else {
                    return [];
                }
            }

            function parsePolygon(wkt) {
                try {
                    const ringMatches = wkt.match(/\(([^)]+)\)/g);
                    if (!ringMatches || ringMatches.length === 0) {
                        return [];
                    }

                    const rings = [];
                    ringMatches.forEach((ringMatch) => {
                        const coordString = ringMatch.slice(1, -1);
                        const coordinates = parseCoordinateString(coordString);
                        if (coordinates.length >= 3) {
                            rings.push(coordinates);
                        }
                    });

                    if (rings.length === 0) {
                        return [];
                    }

                    if (rings.length === 1) {
                        return rings[0];
                    }

                    return {
                        isComplex: true,
                        outerRing: rings[0],
                        holes: rings.slice(1)
                    };
                } catch (error) {
                    console.error('parsePolygon error:', error);
                    return [];
                }
            }

            function parseMultiPolygon(wkt) {
                try {
                    const polygons = [];
                    let inner = wkt.replace(/^MULTIPOLYGON\s*\(\s*/i, '').replace(/\s*\)$/i, '');
                    let polyStrings = inner.split(/\)\s*,\s*\(/);

                    polyStrings.forEach(polyStr => {
                        let cleanStr = polyStr.replace(/^\(\s*/, '').replace(/\s*\)$/i, '');
                        let rings = cleanStr.match(/\(([^)]+)\)/g);
                        if (rings) {
                            const parsedRings = [];
                            rings.forEach(ringMatch => {
                                const coordString = ringMatch.slice(1, -1);
                                const coordinates = parseCoordinateString(coordString);
                                if (coordinates.length >= 3) {
                                    parsedRings.push(coordinates);
                                }
                            });
                            if (parsedRings.length === 1) {
                                polygons.push(parsedRings[0]);
                            } else if (parsedRings.length > 1) {
                                polygons.push({
                                    isComplex: true,
                                    outerRing: parsedRings[0],
                                    holes: parsedRings.slice(1)
                                });
                            }
                        }
                    });
                    return polygons;
                } catch (error) {
                    console.error('parseMultiPolygon error:', error);
                    return [];
                }
            }

            function parseCoordinateString(coordString) {
                if (!coordString || typeof coordString !== 'string') {
                    return [];
                }

                const coords = coordString.split(",");
                const validCoords = [];

                coords.forEach((coord) => {
                    try {
                        const trimmedCoord = coord.trim();
                        if (trimmedCoord.length === 0) return;

                        const parts = trimmedCoord.split(/\s+/);
                        if (parts.length >= 2) {
                            const lon = parseFloat(parts[0]);
                            const lat = parseFloat(parts[1]);

                            if (isNaN(lat) || isNaN(lon)) {
                                return;
                            }

                            if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                                return;
                            }

                            validCoords.push([lat, lon]);
                        }
                    } catch (error) {
                        console.error('Error parsing coordinate:', error);
                    }
                });
                return validCoords;
            }

            // Renderizar geometrias
            let polygonsAdded = 0;
            plotGeometries.forEach((plot, index) => {
                console.log(`Procesando geometria ${index + 1}:`, plot.wkt.substring(0, 100));
                let geometries = parseWKT(plot.wkt);
                console.log('Geometrias parseadas:', geometries);

                if (!geometries || geometries.length === 0) {
                    console.warn(`No se pudieron parsear las geometrias para el indice ${index}`);
                    return;
                }

                let polygons = Array.isArray(geometries[0]) && Array.isArray(geometries[0][0]) ?
                    geometries : [geometries];

                console.log('Poligonos a renderizar:', polygons.length);

                polygons.forEach((coords, polyIndex) => {
                    let polygonCoords;
                    if (coords.isComplex) {
                        polygonCoords = [coords.outerRing, ...coords.holes];
                        bounds.push(...coords.outerRing);
                        console.log(`Poligono complejo ${polyIndex}:`, coords.outerRing.length, 'puntos');
                    } else if (Array.isArray(coords[0])) {
                        polygonCoords = [coords];
                        bounds.push(...coords);
                        console.log(`Poligono simple ${polyIndex}:`, coords.length, 'puntos');
                    } else {
                        polygonCoords = coords;
                        bounds.push(...coords);
                        console.log(`Poligono directo ${polyIndex}:`, coords.length, 'puntos');
                    }

                    console.log('Coordenadas del poligono:', polygonCoords[0]?.slice(0, 3));

                    try {
                        let plotPolygon = L.polygon(polygonCoords, {
                            color: '#10b981',
                            fillColor: '#86efac',
                            fillOpacity: 0.5,
                            weight: 2
                        }).addTo(map);

                        polygonsAdded++;
                        console.log(`Poligono ${polygonsAdded} agregado al mapa`);

                        let tooltipContent = `
                            <b>Parcela:</b> {{ $plot->name }}<br>
                            <b>Codigo SIGPAC:</b> ${plot.sigpac_code || '-'}
                        `;

                        plotPolygon.bindPopup(tooltipContent);
                        plotPolygon.on('mouseover', function() {
                            this.bindTooltip(tooltipContent, { sticky: true }).openTooltip();
                        });
                    } catch (error) {
                        console.error('Error al agregar poligono:', error, polygonCoords);
                    }
                });
            });

            console.log(`Total poligonos agregados: ${polygonsAdded}`);
            console.log('Bounds:', bounds.length, 'puntos');

            // Ajustar vista al contenido
            if (bounds.length > 0) {
                try {
                    // Esperar un momento para que el mapa se renderice
                    setTimeout(function() {
                        map.fitBounds(bounds);
                        map.invalidateSize();
                        console.log('Vista ajustada a bounds');
                    }, 200);
                } catch (error) {
                    console.error('Error al ajustar bounds:', error);
                }
            } else {
                console.warn('No hay bounds para ajustar la vista');
                map.setView([40.4168, -3.7038], 13);
            }

            // Asegurar que el mapa se renderice correctamente
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }

        // Inicializar cuando el DOM este listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    </script>
@endif
@endpush
