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
                <flux:button href="{{ roleRoute('visual') }}" variant="outline" icon="map">
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
            wireMethod="switchTab"
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

                    @if($plot->soilType)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Tipo de Suelo</label>
                            <p class="text-zinc-900">{{ $plot->soilType->name }}</p>
                        </div>
                    @endif

                    @if($plot->topography)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Topografía</label>
                            <p class="text-zinc-900">{{ $plot->topography->name }}</p>
                        </div>
                    @endif

                    @if($plot->orientation)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Orientación</label>
                            <p class="text-zinc-900">{{ $plot->orientation->name }} ({{ $plot->orientation->abbreviation }})</p>
                        </div>
                    @endif

                    @if($plot->slope)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Pendiente</label>
                            <p class="text-zinc-900">{{ $plot->slope }}%</p>
                        </div>
                    @endif

                    @if($plot->code_parcel)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Código Catastral</label>
                            <p class="text-zinc-900 font-mono text-sm">{{ $plot->code_parcel }}</p>
                        </div>
                    @endif

                    @if($plot->total_vines)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Total Cepas</label>
                            <p class="text-zinc-900">{{ number_format($plot->total_vines, 0, ',', '.') }}</p>
                        </div>
                    @endif

                    @if($plot->oldest_planting_year)
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Plantación más antigua</label>
                            <p class="text-zinc-900">{{ $plot->oldest_planting_year }}</p>
                        </div>
                    @endif

                    @if($plot->description)
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-zinc-500">Descripción</label>
                            <p class="text-zinc-900">{{ $plot->description }}</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            <!-- Plantaciones -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="book-open" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">Plantaciones</span>
                            @if($plot->plantings->where('status', 'active')->count() > 0)
                                <span class="text-xs text-zinc-400">({{ $plot->plantings->where('status', 'active')->count() }} activas)</span>
                            @endif
                        </div>
                        @can('update', $plot)
                            <a href="{{ route('plots.plantings.create', $plot) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-agro-600 hover:text-agro-700">
                                <flux:icon icon="plus" class="size-3.5" />
                                Nueva
                            </a>
                        @endcan
                    </div>
                </x-slot:header>

                @if($plot->plantings->isNotEmpty())
                    <div class="divide-y divide-zinc-100">
                        @foreach($plot->plantings->sortByDesc('status') as $planting)
                            <div class="flex items-center justify-between py-2.5 {{ $planting->status !== 'active' ? 'opacity-50' : '' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-2 h-2 rounded-full shrink-0 {{ $planting->status === 'active' ? 'bg-agro-500' : ($planting->status === 'removed' ? 'bg-red-400' : 'bg-amber-400') }}"></div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-zinc-800 truncate">
                                            {{ $planting->grapeVariety?->name ?? $planting->name ?? 'Sin variedad' }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ number_format($planting->area_planted, 3) }} ha
                                            @if($planting->planting_year) · {{ $planting->planting_year }} @endif
                                            @if($planting->vine_count) · {{ number_format($planting->vine_count, 0, ',', '.') }} cepas @endif
                                            @if($planting->harvest_limit_kg) · Límite: {{ number_format($planting->harvest_limit_kg, 0, ',', '.') }} kg @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-medium uppercase tracking-wide px-2 py-0.5 rounded-full shrink-0
                                    {{ match($planting->status) {
                                        'active' => 'bg-agro-50 text-agro-700',
                                        'removed' => 'bg-red-50 text-red-600',
                                        'experimental' => 'bg-amber-50 text-amber-700',
                                        'replanting' => 'bg-blue-50 text-blue-600',
                                        default => 'bg-zinc-100 text-zinc-500',
                                    } }}">
                                    {{ match($planting->status) {
                                        'active' => 'Activa',
                                        'removed' => 'Arrancada',
                                        'experimental' => 'Experimental',
                                        'replanting' => 'Replantación',
                                        default => $planting->status,
                                    } }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <flux:icon icon="book-open" class="size-8 text-zinc-300 mx-auto mb-2" />
                        <p class="text-sm text-zinc-500">No hay plantaciones en esta parcela.</p>
                        @can('update', $plot)
                            <a href="{{ route('plots.plantings.create', $plot) }}"
                               class="inline-flex items-center gap-1 text-sm font-medium text-agro-600 hover:text-agro-700 mt-2">
                                <flux:icon icon="plus" class="size-4" />
                                Crear primera plantación
                            </a>
                        @endcan
                    </div>
                @endif
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
                    @if($plot->viticulturist && $plot->viticulturist->wineries->isNotEmpty())
                        <div>
                            <label class="text-sm font-semibold text-zinc-500">Bodega</label>
                            <p class="text-zinc-900">{{ $plot->viticulturist->wineries->first()->name }}</p>
                        </div>
                    @endif

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

@if($hasGeometry && count($plotGeometries) > 0)
    @script
    <script>
        (function() {
            const plotGeometries = @js($plotGeometries);
            const plotName = @js($plot->name);

            if (!plotGeometries || plotGeometries.length === 0) return;

            const mapContainer = document.getElementById('plot-map');
            if (!mapContainer) return;

            function loadLeaflet(cb) {
                if (window.L) { cb(); return; }
                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }
                const s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.onload = cb;
                document.head.appendChild(s);
            }

            function parseCoordinateString(coordString) {
                if (!coordString || typeof coordString !== 'string') return [];
                return coordString.split(",").reduce((acc, coord) => {
                    const parts = coord.trim().split(/\s+/);
                    if (parts.length >= 2) {
                        const lon = parseFloat(parts[0]), lat = parseFloat(parts[1]);
                        if (!isNaN(lat) && !isNaN(lon) && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                            acc.push([lat, lon]);
                        }
                    }
                    return acc;
                }, []);
            }

            function parsePolygon(wkt) {
                const ringMatches = wkt.match(/\(([^)]+)\)/g);
                if (!ringMatches) return [];
                const rings = ringMatches.map(r => parseCoordinateString(r.slice(1, -1))).filter(r => r.length >= 3);
                if (rings.length === 0) return [];
                return rings.length === 1 ? rings[0] : { isComplex: true, outerRing: rings[0], holes: rings.slice(1) };
            }

            function parseMultiPolygon(wkt) {
                let inner = wkt.replace(/^MULTIPOLYGON\s*\(\s*/i, '').replace(/\s*\)$/i, '');
                return inner.split(/\)\s*,\s*\(/).map(polyStr => {
                    let rings = polyStr.replace(/^\(\s*/, '').replace(/\s*\)$/i, '').match(/\(([^)]+)\)/g);
                    if (!rings) return null;
                    const parsed = rings.map(r => parseCoordinateString(r.slice(1, -1))).filter(r => r.length >= 3);
                    if (parsed.length === 1) return parsed[0];
                    if (parsed.length > 1) return { isComplex: true, outerRing: parsed[0], holes: parsed.slice(1) };
                    return null;
                }).filter(Boolean);
            }

            function parseWKT(wkt) {
                if (!wkt || typeof wkt !== 'string') return [];
                const t = wkt.trim();
                if (t.startsWith("POLYGON")) return parsePolygon(t);
                if (t.startsWith("MULTIPOLYGON")) return parseMultiPolygon(t);
                return [];
            }

            loadLeaflet(function() {
                const map = L.map('plot-map', { zoomControl: true });
                const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' });
                const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '&copy; Esri' });
                street.addTo(map);
                L.control.layers({ "Mapa": street, "Satelite": satellite }).addTo(map);

                const bounds = [];

                plotGeometries.forEach(plot => {
                    let geometries = parseWKT(plot.wkt);
                    if (!geometries || geometries.length === 0) return;

                    let polygons = Array.isArray(geometries[0]) && Array.isArray(geometries[0][0]) ? geometries : [geometries];

                    polygons.forEach(coords => {
                        let polygonCoords;
                        if (coords.isComplex) {
                            polygonCoords = [coords.outerRing, ...coords.holes];
                            bounds.push(...coords.outerRing);
                        } else if (Array.isArray(coords[0])) {
                            polygonCoords = [coords];
                            bounds.push(...coords);
                        } else {
                            polygonCoords = coords;
                            bounds.push(...coords);
                        }

                        const poly = L.polygon(polygonCoords, { color: '#10b981', fillColor: '#86efac', fillOpacity: 0.5, weight: 2 }).addTo(map);
                        poly.bindPopup(`<b>Parcela:</b> ${plotName}<br><b>SIGPAC:</b> ${plot.sigpac_code || '-'}`);
                    });
                });

                if (bounds.length > 0) {
                    setTimeout(() => { map.fitBounds(bounds); map.invalidateSize(); }, 200);
                } else {
                    map.setView([40.4168, -3.7038], 13);
                }
                setTimeout(() => map.invalidateSize(), 100);
            });
        })();
    </script>
    @endscript
@endif
</div>
