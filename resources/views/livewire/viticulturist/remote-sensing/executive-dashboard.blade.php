<div>
    {{-- Overlay: generando datos --}}
    <div wire:loading wire:target="generateData"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <x-agro.card class="max-w-sm mx-4 text-center">
            <flux:icon icon="signal" class="size-16 text-green-600 mx-auto mb-4 animate-pulse" />
            <p class="text-lg font-bold text-zinc-900 mb-1">{{ __('Obteniendo datos del satélite') }}</p>
            <p class="text-zinc-500 text-sm">{{ __('Consultando NASA... unos segundos.') }}</p>
        </x-agro.card>
    </div>

    <div class="container mx-auto px-4 py-6">
        {{-- Banner de datos simulados --}}
        @if(config('services.nasa_earthdata.mock'))
            <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-800 text-sm">
                <flux:icon icon="beaker" class="size-5 shrink-0 text-amber-600" />
                <div>
                    <span class="font-semibold">{{ __('Modo demo activo') }}</span>
                    — Los datos mostrados son simulados, no proceden de la API de NASA.
                    Configura <code class="bg-amber-100 px-1 rounded text-xs">NASA_EARTHDATA_MOCK=false</code> en <code class="bg-amber-100 px-1 rounded text-xs">.env</code> para usar datos reales.
                </div>
            </div>
        @endif

        <x-agro.page-header title="{{ __('Teledetección') }}" :description="__('Estado satelital de tus parcelas')">
            <x-slot:actions>
                <flux:select wire:model.live="selectedSigpacId">
                    @foreach($sigpacs as $sigpac)
                        <option value="{{ $sigpac['id'] }}">{{ $sigpac['display_name'] }}</option>
                    @endforeach
                </flux:select>

                <flux:button wire:click="refreshData" wire:loading.attr="disabled" variant="primary">
                    <svg wire:loading.remove wire:target="refreshData" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg wire:loading wire:target="refreshData" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Actualizar
                </flux:button>
            </x-slot:actions>
        </x-agro.page-header>

        {{-- Metadata de última actualización --}}
        @if($selectedPlot && !empty($summary))
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500 mb-6 -mt-2">
                <span class="flex items-center gap-1.5">
                    <span class="size-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
                    Última actualización: {{ $summary['last_update'] }}
                </span>
                <span class="hidden md:inline">•</span>
                @if(!empty($summary['is_estimated']))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">{{ __('⚠️ Datos estimados — sin cobertura satelital reciente') }}</span>
                @else
                    <span>{{ $summary['satellite'] }}</span>
                @endif
                @if($selectedPlot->area)
                    <span class="hidden md:inline">•</span>
                    <span>{{ number_format($selectedPlot->area, 2) }} ha</span>
                @endif
                <a href="{{ route('remote-sensing.report.plot', $selectedPlot) }}"
                   class="ml-auto flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition-colors">
                    <flux:icon icon="document-arrow-down" variant="micro" />
                    Descargar PDF
                </a>
            </div>
        @endif

        {{-- ── Mapa de Vigor NDVI (todas las parcelas) ── --}}
        @if(!empty($mapData))
            <x-agro.card class="mb-6">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-zinc-900">{{ __('Mapa de Vigor NDVI') }}</h2>
                            <p class="text-sm text-zinc-500 mt-0.5">{{ __('Estado satelital de todas tus parcelas · haz clic para seleccionar') }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div wire:ignore>
                    <div x-data="execNdviMap()" x-init="init()" x-ref="root">
                        <div x-ref="mapEl" class="h-72 rounded-lg z-0"></div>
                    </div>
                </div>

                {{-- Leyenda --}}
                <div class="flex flex-wrap gap-x-5 gap-y-1 mt-3 text-xs text-zinc-500">
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-green-500 inline-block"></span>Excelente ≥0.7</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-emerald-400 inline-block"></span>Bueno ≥0.5</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-yellow-400 inline-block"></span>Moderado ≥0.3</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-orange-400 inline-block"></span>Bajo ≥0.15</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-red-500 inline-block"></span>Crítico</span>
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-gray-400 inline-block"></span>Sin datos</span>
                </div>
            </x-agro.card>

            <script>
                function execNdviMap() {
                    return {
                        mapData: @json($mapData),
                        map: null,
                        init() {
                            if (!window.loadLeaflet || !window.parseWKT) return;

                            window.loadLeaflet().then(() => {
                                if (this.map) {
                                    this.map.remove();
                                    this.map = null;
                                }

                                this.map = window.L.map(this.$refs.mapEl);

                                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap',
                                    maxZoom: 18,
                                }).addTo(this.map);

                                const bounds = [];
                                const wire = this.$wire;

                                this.mapData.forEach(plot => {
                                    if (!plot.wkts || !plot.wkts.length) return;

                                    plot.wkts.forEach(wkt => {
                                        const coords = window.parseWKT(wkt);
                                        if (!coords || !coords.length) return;

                                        const poly = window.L.polygon(coords, {
                                            fillColor: plot.fill,
                                            fillOpacity: 0.7,
                                            color: plot.line,
                                            weight: 2,
                                        });

                                        const ndviLabel = plot.ndvi !== null
                                            ? parseFloat(plot.ndvi).toFixed(3)
                                            : 'Sin datos';

                                        poly.bindTooltip(
                                            '<strong>{{ __('\' + plot.plot_name + \'') }}</strong><br>NDVI: ' + ndviLabel,
                                            { sticky: true }
                                        );

                                        poly.on('click', () => {
                                            wire.call('selectPlot', plot.plot_id);
                                        });

                                        poly.addTo(this.map);
                                        coords.forEach(c => bounds.push(c));
                                    });
                                });

                                if (bounds.length > 0) {
                                    this.map.fitBounds(bounds, { padding: [20, 20] });
                                } else {
                                    this.map.setView([40.4, -3.7], 6);
                                }
                            });
                        },
                        destroy() {
                            if (this.map) {
                                this.map.remove();
                                this.map = null;
                            }
                        }
                    };
                }
            </script>
        @endif

        @if($loading)
            {{-- Skeleton cargando --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 0; $i < 6; $i++)
                    <x-agro.skeleton-card />
                @endfor
            </div>

        @elseif($selectedPlot && !empty($summary) && isset($summary['vigor']['status']) && $summary['vigor']['status'] !== 'no_data')
            @php
                $colorRemap = [
                    'emerald' => 'green', 'teal' => 'green',
                    'amber' => 'yellow',
                    'sky' => 'blue', 'cyan' => 'blue', 'indigo' => 'blue',
                    'rose' => 'red', 'pink' => 'red',
                    'violet' => 'purple',
                ];
                $validColors = ['agro', 'blue', 'purple', 'orange', 'red', 'green', 'yellow'];
                $toColor = fn($c) => in_array($c ?? '', $validColors) ? ($c ?? 'agro') : ($colorRemap[$c ?? ''] ?? 'agro');
                $vigorColor     = $toColor($summary['vigor']['color'] ?? null);
                $waterColor     = $toColor($summary['water']['color'] ?? null);
                $tempColor      = $toColor($summary['temperature']['color'] ?? null);
                $harvestColor   = $toColor($summary['harvest']['color'] ?? null);
                $nutritionColor = $toColor($summary['nutrition']['color'] ?? null);
                $alertsColor    = $toColor($summary['alerts']['color'] ?? null);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

                {{-- Vigor Vegetativo --}}
                <x-agro.card :color="$vigorColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Vigor Vegetativo') }}</h3>
                            <span class="text-3xl">{{ $summary['vigor']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">{{ $summary['vigor']['label'] }}</p>
                    </x-slot:header>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-zinc-500 flex items-center gap-1">
                                NDVI
                                <span title="{{ __('Índice de Vegetación: 0-1, valores altos = mayor vigor') }}" class="cursor-help text-zinc-400 text-xs">ⓘ</span>
                            </span>
                            <span class="text-2xl font-bold">{{ number_format($summary['vigor']['ndvi'], 2) }}</span>
                        </div>
                        @if($summary['vigor']['gndvi'])
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500 flex items-center gap-1">
                                    GNDVI
                                    <span title="{{ __('Indicador de Nitrógeno foliar') }}" class="cursor-help text-zinc-400 text-xs">ⓘ</span>
                                </span>
                                <span class="text-lg font-semibold">{{ number_format($summary['vigor']['gndvi'], 2) }}</span>
                            </div>
                        @endif
                        @if($summary['vigor']['lai'])
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500 flex items-center gap-1">
                                    LAI
                                    <span title="{{ __('Índice de Área Foliar') }}" class="cursor-help text-zinc-400 text-xs">ⓘ</span>
                                </span>
                                <span class="text-lg font-semibold">{{ number_format($summary['vigor']['lai'], 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['vigor']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver análisis completo') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

                {{-- Estado Hídrico --}}
                <x-agro.card :color="$waterColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Estado Hídrico') }}</h3>
                            <span class="text-3xl">{{ $summary['water']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">{{ $summary['water']['label'] }}</p>
                    </x-slot:header>

                    <div class="space-y-3">
                        @if(isset($summary['water']['cwsi']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500 flex items-center gap-1">
                                    CWSI
                                    <span title="{{ __('Índice de Estrés Hídrico: 0-1, valores bajos son mejores') }}" class="cursor-help text-zinc-400 text-xs">ⓘ</span>
                                </span>
                                <span class="text-2xl font-bold">{{ number_format($summary['water']['cwsi'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($summary['water']['soil_moisture']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Humedad Suelo') }}</span>
                                <span class="text-lg font-semibold">{{ number_format($summary['water']['soil_moisture'], 1) }}%</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['water']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver análisis completo') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

                {{-- Temperatura --}}
                <x-agro.card :color="$tempColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Temperatura') }}</h3>
                            <span class="text-3xl">{{ $summary['temperature']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">{{ $summary['temperature']['label'] }}</p>
                    </x-slot:header>

                    <div class="space-y-3">
                        @if(isset($summary['temperature']['lst_day']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('LST Día') }}</span>
                                <span class="text-2xl font-bold">{{ number_format($summary['temperature']['lst_day'], 1) }}°C</span>
                            </div>
                        @endif
                        @if(isset($summary['temperature']['lst_night']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('LST Noche') }}</span>
                                <span class="text-lg font-semibold">{{ number_format($summary['temperature']['lst_night'], 1) }}°C</span>
                            </div>
                        @endif
                        @if(isset($summary['temperature']['lst_diff']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Amplitud térmica') }}</span>
                                <span class="text-lg font-semibold">{{ number_format($summary['temperature']['lst_diff'], 1) }}°C</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['temperature']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver análisis completo') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

                {{-- Rendimiento / Cosecha --}}
                <x-agro.card :color="$harvestColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Rendimiento') }}</h3>
                            <span class="text-3xl">{{ $summary['harvest']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">{{ __('Predicción de cosecha') }}</p>
                    </x-slot:header>

                    <div class="space-y-3">
                        @if(isset($summary['harvest']['yield_per_ha']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Por hectárea') }}</span>
                                <span class="text-2xl font-bold">{{ number_format($summary['harvest']['yield_per_ha'], 1) }} t/ha</span>
                            </div>
                        @endif
                        @if(isset($summary['harvest']['total_yield']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Total estimado') }}</span>
                                <span class="text-lg font-semibold">{{ number_format($summary['harvest']['total_yield'] * 1000, 0) }} kg</span>
                            </div>
                        @endif
                        @if(isset($summary['harvest']['confidence_label']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Confianza') }}</span>
                                <span class="text-sm font-semibold">{{ $summary['harvest']['confidence_label'] }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['harvest']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver pronóstico') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

                {{-- Nutrición --}}
                <x-agro.card :color="$nutritionColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Nutrición') }}</h3>
                            <span class="text-3xl">{{ $summary['nutrition']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">{{ $summary['nutrition']['label'] }}</p>
                    </x-slot:header>

                    <div class="space-y-3">
                        @if(isset($summary['nutrition']['gndvi']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500 flex items-center gap-1">
                                    GNDVI
                                    <span title="{{ __('Índice verde de nitrógeno foliar') }}" class="cursor-help text-zinc-400 text-xs">ⓘ</span>
                                </span>
                                <span class="text-2xl font-bold">{{ number_format($summary['nutrition']['gndvi'], 2) }}</span>
                            </div>
                        @endif
                        @if(isset($summary['nutrition']['chlorophyll']))
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-500">{{ __('Clorofila') }}</span>
                                <span class="text-lg font-semibold">{{ number_format($summary['nutrition']['chlorophyll'], 0) }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['nutrition']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver análisis') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

                {{-- Alertas --}}
                <x-agro.card :color="$alertsColor">
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-zinc-900">{{ __('Alertas') }}</h3>
                            <span class="text-3xl">{{ $summary['alerts']['icon'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-zinc-700 mt-1">
                            @if($summary['alerts']['total'] == 0)
                                Sin alertas activas
                            @else
                                {{ $summary['alerts']['total'] }} {{ $summary['alerts']['total'] == 1 ? 'alerta activa' : 'alertas activas' }}
                            @endif
                        </p>
                    </x-slot:header>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-zinc-500">{{ __('Críticas') }}</span>
                            <span class="text-2xl font-bold text-red-600">{{ $summary['alerts']['critical'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-zinc-500">{{ __('Avisos') }}</span>
                            <span class="text-lg font-semibold text-yellow-600">{{ $summary['alerts']['warnings'] }}</span>
                        </div>
                        @if(!empty($summary['alerts']['list']))
                            <div class="space-y-1 pt-1">
                                @foreach(array_slice($summary['alerts']['list'], 0, 2) as $alert)
                                    <p class="text-xs px-2 py-1.5 rounded {{ $alert['type'] === 'critical' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $alert['message'] }}
                                    </p>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <a href="{{ $summary['alerts']['detail_route'] }}" class="flex items-center justify-between text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                            <span>{{ __('Ver todas las alertas') }}</span>
                            <flux:icon icon="chevron-right" variant="micro" />
                        </a>
                    </x-slot:footer>
                </x-agro.card>

            </div>

            {{-- CTA hacia análisis avanzado --}}
            <x-agro.card>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 mb-0.5">{{ __('¿Necesitas más detalle?') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __('Historial, riego, GDD, espectral y comparación entre parcelas') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('remote-sensing.advanced') }}"
                           class="px-4 py-2 bg-agro-600 hover:bg-agro-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            Análisis avanzado
                        </a>
                        <a href="{{ route('remote-sensing.advanced', ['tab' => 'history']) }}"
                           class="px-4 py-2 bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-lg transition-colors">
                            Histórico
                        </a>
                        <a href="{{ route('remote-sensing.advanced', ['tab' => 'compare']) }}"
                           class="px-4 py-2 bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-lg transition-colors">
                            Comparar parcelas
                        </a>
                    </div>
                </div>
                <x-slot:footer>
                    <p class="text-xs text-zinc-400">{{ __('Datos NASA • VIIRS + MODIS + SMAP • 100% Gratuito') }}</p>
                </x-slot:footer>
            </x-agro.card>

            {{-- ── Configuración de alertas NDVI ── --}}
            <x-agro.card class="mt-6">
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="bell-alert" class="size-5 text-zinc-500" />
                        <div>
                            <h2 class="font-bold text-zinc-900">{{ __('Alertas NDVI') }}</h2>
                            <p class="text-sm text-zinc-500 mt-0.5">Notificaciones para {{ $selectedPlot->name }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Umbral NDVI
                            <span title="{{ __('Recibirás una alerta cuando el NDVI caiga por debajo de este valor') }}" class="cursor-help text-zinc-400 ml-1">ⓘ</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <flux:input
                                type="number"
                                wire:model="ndviThreshold"
                                step="0.05"
                                min="0"
                                max="1"
                                class="w-28"
                            />
                            <span class="text-sm text-zinc-400">{{ __('de 0 a 1') }}</span>
                        </div>
                        @error('ndviThreshold')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2.5 pb-1">
                        <flux:checkbox wire:model="alertEmailEnabled" id="exec-alert-email" />
                        <label for="exec-alert-email" class="text-sm font-medium text-zinc-700 cursor-pointer select-none">{{ __('Enviar alerta por email') }}</label>
                    </div>

                    <flux:button wire:click="saveAlertSettings" variant="primary" class="shrink-0">{{ __('Guardar configuración') }}</flux:button>
                </div>

                <p class="text-xs text-zinc-400 mt-4">{{ __('Las alertas se guardan siempre en las notificaciones del sistema. El email solo se envía si está habilitado.
                    Máximo 1 alerta cada 24 horas por parcela.') }}</p>
            </x-agro.card>

        @elseif($selectedPlot && !empty($summary))
            {{-- Parcela sin datos --}}
            <x-agro.card>
                <x-agro.empty-state
                    icon="signal"
                    title="{{ __('Sin datos de teledetección') }}"
                    description="La parcela &quot;{{ $selectedPlot->name }}&quot; aún no tiene datos satelitales. Solicita una actualización desde Sentinel-2 (10m resolución).">
                    <x-slot:action>
                        <button wire:click="generateData"
                                wire:loading.attr="disabled"
                                wire:target="generateData"
                                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-zinc-400 text-white text-sm font-semibold rounded-lg transition-colors inline-flex items-center gap-2">
                            <svg wire:loading.remove wire:target="generateData" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <svg wire:loading wire:target="generateData" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            🛰️ Solicitar datos Sentinel-2
                        </button>
                        <p class="mt-2 text-xs text-zinc-500">{{ __('Los datos estarán disponibles en 1-2 minutos. Recarga la página para verlos.') }}</p>
                        @if($generateError)
                            <p class="mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                                {{ $generateError }}
                            </p>
                        @endif
                    </x-slot:action>
                </x-agro.empty-state>
            </x-agro.card>

        @else
            {{-- Sin parcela seleccionada --}}
            <x-agro.card>
                @if(count($plots) === 0)
                    <x-agro.empty-state
                        icon="map"
                        title="{{ __('Sin parcelas') }}"
                        :description="__('Crea tu primera parcela para ver datos de teledetección')">
                        <x-slot:action>
                            <flux:button href="{{ roleRoute('plots.create') }}" variant="primary" icon="plus">
                                Crear parcela
                            </flux:button>
                        </x-slot:action>
                    </x-agro.empty-state>
                @elseif(count($sigpacs) === 0)
                    <x-agro.empty-state
                        icon="rectangle-group"
                        title="{{ __('Vincula tus parcelas con SIGPAC') }}"
                        :description="__('Para obtener datos satelitales necesitas asociar códigos SIGPAC a tus parcelas y generar sus geometrías')">
                        <x-slot:action>
                            <flux:button href="{{ route('sigpac.codes') }}" variant="primary" icon="map-pin">
                                Gestionar SIGPAC
                            </flux:button>
                        </x-slot:action>
                    </x-agro.empty-state>
                @else
                    <x-agro.empty-state
                        icon="signal"
                        title="{{ __('Selecciona una parcela') }}"
                        :description="__('Elige una parcela del selector para ver sus datos de teledetección')" />
                @endif
            </x-agro.card>
        @endif
    </div>
</div>
