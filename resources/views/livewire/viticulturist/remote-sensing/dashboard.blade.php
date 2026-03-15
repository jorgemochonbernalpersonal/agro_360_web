<div class="container mx-auto px-4 py-6">
    {{-- Banner de datos simulados --}}
    @if(config('services.nasa_earthdata.mock'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-300 rounded-xl text-amber-800 text-sm">
            <flux:icon icon="beaker" class="size-5 shrink-0 text-amber-600" />
            <div>
                <span class="font-semibold">Modo demo activo</span>
                — Los datos mostrados son simulados, no proceden de la API de NASA.
                Configura <code class="bg-amber-100 px-1 rounded text-xs">NASA_EARTHDATA_MOCK=false</code> en <code class="bg-amber-100 px-1 rounded text-xs">.env</code> para usar datos reales.
            </div>
        </div>
    @endif

    {{-- Header --}}
    <x-agro.page-header title="Análisis Avanzado" description="Datos satelitales y meteorológicos detallados">
        <x-slot:actions>
            <a href="{{ route('remote-sensing.dashboard') }}"
               class="px-3 py-2 text-sm text-zinc-600 hover:text-zinc-900 border border-zinc-300 rounded-lg hover:bg-zinc-50 transition-colors inline-flex items-center gap-2">
                <flux:icon icon="arrow-left" variant="micro" />
                Resumen
            </a>

            <flux:select wire:model.live="selectedPlotId">
                @if(count($plots) > 0)
                    @foreach($plots as $plot)
                        <option value="{{ $plot->id }}" wire:key="plot-{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                @else
                    <option value="">Sin parcelas con geometrías</option>
                @endif
            </flux:select>

            <button wire:click="refreshData" wire:loading.attr="disabled"
                    class="px-3 py-2 bg-agro-600 hover:bg-agro-700 disabled:bg-zinc-400 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg wire:loading.remove wire:target="refreshData" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="refreshData" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Actualizar
            </button>

            <button wire:click="downloadReport" wire:loading.attr="disabled"
                    class="px-3 py-2 bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg wire:loading.remove wire:target="downloadReport" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg wire:loading wire:target="downloadReport" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Informe PDF
            </button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-zinc-200 p-3 text-center">
            <div class="text-2xl font-bold text-zinc-700">{{ $stats['total_plots'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">Parcelas</div>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-200 p-3 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['average_ndvi'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">NDVI Medio</div>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-200 p-3 text-center">
            <div class="text-2xl font-bold text-green-600">{{ ($stats['excellent'] ?? 0) + ($stats['good'] ?? 0) }}</div>
            <div class="text-xs text-zinc-500">Saludables</div>
        </div>
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-3 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['moderate'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">Moderadas</div>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-3 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $stats['alerts'] ?? 0 }}</div>
            <div class="text-xs text-zinc-500">Alertas</div>
        </div>
    </div>

    <div wire:key="dashboard-body-{{ $isLoading ? 'loading' : ($selectedPlotId ?? 'none') }}">
    @if($isLoading)
        <div class="flex items-center justify-center py-16">
            <svg class="w-12 h-12 animate-spin text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="ml-3 text-zinc-600 text-lg">Cargando datos...</span>
        </div>

    @elseif($selectedPlot)
        {{-- Recomendaciones --}}
        @if(count($recommendations) > 0)
            <div class="mb-6 flex flex-wrap gap-3" wire:key="recommendations-{{ $selectedPlotId ?? 'none' }}">
                @foreach($recommendations as $index => $rec)
                    <div wire:key="rec-{{ $selectedPlotId ?? 'none' }}-{{ $index }}"
                         class="flex-1 min-w-[200px] p-3 rounded-lg border-l-4
                            @if($rec['type'] === 'danger')  bg-red-50 border-red-500
                            @elseif($rec['type'] === 'warning') bg-amber-50 border-amber-500
                            @elseif($rec['type'] === 'success') bg-green-50 border-green-500
                            @else bg-blue-50 border-blue-500
                            @endif">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $rec['icon'] }}</span>
                            <span class="font-semibold text-sm">{{ $rec['title'] }}</span>
                        </div>
                        <p class="text-xs text-zinc-600 mt-1">{{ $rec['text'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Tabs --}}
        <x-agro.tabs
            :tabs="[
                'satellite'  => 'Vegetación',
                'thermal'    => 'Térmico',
                'soil'       => 'Suelo',
                'weather'    => 'Clima',
                'irrigation' => 'Riego',
                'harvest'    => 'Cosecha',
                'history'    => 'Historial',
                'compare'    => 'Comparar',
            ]"
            :active="$activeTab"
            wireMethod="setTab"
        />

        {{-- Tab Content --}}
        <x-agro.card wire:key="tab-content-{{ $selectedPlotId ?? 'none' }}-{{ $activeTab }}">

            {{-- Cabecera de parcela (compartida en todos los tabs) --}}
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-zinc-100">
                <div class="size-10 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                    <flux:icon icon="map-pin" class="size-5 text-agro-500" />
                </div>
                <div>
                    <h2 class="font-bold text-base text-zinc-900">{{ $selectedPlot->name }}</h2>
                    <p class="text-xs text-zinc-500">{{ $selectedPlot->municipality?->name ?? 'Sin municipio' }}</p>
                </div>
            </div>

            {{-- Selector de recinto (compartido en todos los tabs) --}}
            @if(!empty($availableSigpacs))
                @if(count($availableSigpacs) === 1)
                    <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-blue-900">
                                    📍 {{ $availableSigpacs[0]['display_name'] }}
                                </div>
                                <div class="text-xs text-blue-700 mt-1">
                                    ✓ Analizando {{ number_format($availableSigpacs[0]['area_ha'], 2) }} hectáreas
                                    @if($availableSigpacs[0]['centroid'])
                                        • Lat: {{ number_format($availableSigpacs[0]['centroid']['lat'], 4) }}°,
                                        Lon: {{ number_format($availableSigpacs[0]['centroid']['lng'], 4) }}°
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif(count($availableSigpacs) <= 4)
                    <div class="mb-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-zinc-800 mb-3">📍 Selecciona recinto a analizar:</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($availableSigpacs as $sigpac)
                                <button wire:click="$set('selectedSigpacId', {{ $sigpac['id'] }})"
                                        wire:key="sigpac-btn-{{ $sigpac['id'] }}"
                                        class="text-left p-3 rounded-lg border-2 transition-all
                                            {{ $selectedSigpacId == $sigpac['id']
                                                ? 'border-green-500 bg-green-50 shadow-md'
                                                : 'border-zinc-300 bg-white hover:border-blue-400' }}">
                                    <div class="flex items-start gap-2">
                                        <div class="mt-0.5">
                                            @if($selectedSigpacId == $sigpac['id'])
                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                                    <circle cx="10" cy="10" r="8" stroke-width="2"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold {{ $selectedSigpacId == $sigpac['id'] ? 'text-green-900' : 'text-zinc-900' }}">
                                                {{ $sigpac['display_name'] }}
                                            </div>
                                            <div class="text-xs {{ $selectedSigpacId == $sigpac['id'] ? 'text-green-700' : 'text-zinc-600' }} mt-1">
                                                📏 {{ number_format($sigpac['area_ha'], 2) }} ha
                                                @if($sigpac['centroid'])
                                                    <br>📍 {{ number_format($sigpac['centroid']['lat'], 4) }}°, {{ number_format($sigpac['centroid']['lng'], 4) }}°
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                @else
                    <div class="mb-4 bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-zinc-800 mb-2">
                                    📍 Recinto a analizar
                                    <span class="text-xs font-normal text-zinc-600">(parcela con {{ count($availableSigpacs) }} recintos)</span>
                                </label>
                                <flux:select wire:model.live="selectedSigpacId">
                                    @foreach($availableSigpacs as $sigpac)
                                        <option value="{{ $sigpac['id'] }}">
                                            {{ $sigpac['display_name'] }} ({{ number_format($sigpac['area_ha'], 2) }} ha)
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>

                            @php $selectedSigpac = collect($availableSigpacs)->firstWhere('id', $selectedSigpacId); @endphp
                            @if($selectedSigpac && $selectedSigpac['centroid'])
                                <div class="bg-white rounded-lg px-4 py-3 border border-blue-200 text-xs">
                                    <div class="font-semibold text-zinc-700 mb-1">Coordenadas:</div>
                                    <div class="text-zinc-600">
                                        📍 Lat: {{ number_format($selectedSigpac['centroid']['lat'], 6) }}°<br>
                                        📍 Lon: {{ number_format($selectedSigpac['centroid']['lng'], 6) }}°
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            {{-- Contenido del tab activo --}}
            @if($activeTab === 'satellite')  @include('livewire.viticulturist.remote-sensing.tabs.satellite')  @endif
            @if($activeTab === 'thermal')    @include('livewire.viticulturist.remote-sensing.tabs.thermal')    @endif
            @if($activeTab === 'soil')       @include('livewire.viticulturist.remote-sensing.tabs.soil')       @endif
            @if($activeTab === 'weather')    @include('livewire.viticulturist.remote-sensing.tabs.weather')    @endif
            @if($activeTab === 'irrigation') @include('livewire.viticulturist.remote-sensing.tabs.irrigation') @endif
            @if($activeTab === 'harvest')    @include('livewire.viticulturist.remote-sensing.tabs.harvest')    @endif
            @if($activeTab === 'history')    @include('livewire.viticulturist.remote-sensing.tabs.history')    @endif
            @if($activeTab === 'compare')    @include('livewire.viticulturist.remote-sensing.tabs.compare')    @endif

        </x-agro.card>

        {{-- Footer NASA sources --}}
        <p class="mt-4 text-xs text-zinc-400 text-center">
            VIIRS NDVI · Bandas Espectrales · LAI MODIS · LST · SMAP Suelo · ET NASA · Open-Meteo
            &nbsp;·&nbsp; <strong>100% Gratuito</strong>
        </p>

    @else
        <x-agro.card>
            <x-agro.empty-state
                icon="signal"
                title="Selecciona una parcela"
                description="Elige una parcela para ver sus datos de análisis avanzado" />
        </x-agro.card>
    @endif
    </div>{{-- cierre wire:key dashboard-body --}}
</div>
