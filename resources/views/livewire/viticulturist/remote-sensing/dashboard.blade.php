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

            <flux:select wire:model.live="selectedSigpacId">
                @if(count($allSigpacs) > 0)
                    @foreach($allSigpacs as $sigpac)
                        <option value="{{ $sigpac['id'] }}" wire:key="sigpac-{{ $sigpac['id'] }}">
                            {{ $sigpac['display_name'] }} ({{ $sigpac['area_ha'] }} ha)
                        </option>
                    @endforeach
                @else
                    <option value="">Sin recintos con geometrías</option>
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

    {{-- Data source health badges (improvement #6) --}}
    @if(!empty($dataSourceStatus))
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
            @foreach($dataSourceStatus as $key => $source)
                @php
                    $limitWarning = $source['limit_warning'] ?? false;
                    $limitOk      = $source['limit_ok'] ?? true;
                    $isMock       = $source['mock'] ?? false;
                    $isOk         = !$isMock && $limitOk;
                    $badge        = $isOk
                        ? ($limitWarning ? ['icon' => '⚠️', 'cls' => 'bg-yellow-50 border-yellow-300 text-yellow-800']
                                         : ['icon' => '✅', 'cls' => 'bg-green-50 border-green-300 text-green-800'])
                        : ['icon' => '🔴', 'cls' => 'bg-red-50 border-red-300 text-red-800'];
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border {{ $badge['cls'] }} font-medium">
                    {{ $badge['icon'] }} {{ $source['label'] }}
                    @if($isMock) (demo) @endif
                    @if(isset($source['monthly_usage']) && $source['monthly_limit'])
                        · {{ number_format($source['monthly_usage']) }}/{{ number_format($source['monthly_limit']) }} UPs
                    @endif
                    @if(isset($source['last_fetch']) && $source['last_fetch'])
                        · {{ \Carbon\Carbon::parse($source['last_fetch'])->diffForHumans() }}
                    @endif
                </span>
            @endforeach
        </div>
    @endif

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

    <div wire:key="dashboard-body-{{ $isLoading ? 'loading' : ($selectedSigpacId ?? 'none') }}">
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
            <div class="mb-6 flex flex-wrap gap-3" wire:key="recommendations-{{ $selectedSigpacId ?? 'none' }}">
                @foreach($recommendations as $index => $rec)
                    <div wire:key="rec-{{ $selectedSigpacId ?? 'none' }}-{{ $index }}"
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
        <x-agro.card wire:key="tab-content-{{ $selectedSigpacId ?? 'none' }}-{{ $activeTab }}">

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

            {{-- Info del recinto seleccionado --}}
            @php $currentSigpac = collect($allSigpacs)->firstWhere('id', $selectedSigpacId); @endphp
            @if($currentSigpac)
                <div class="mb-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3 text-sm text-blue-800 flex items-center gap-3">
                    <flux:icon icon="map-pin" class="size-4 shrink-0 text-blue-600" />
                    <span>
                        Analizando <strong>{{ $currentSigpac['area_ha'] }} ha</strong>
                        @if($currentSigpac['centroid'])
                            · Lat: {{ number_format($currentSigpac['centroid']['lat'], 4) }}°,
                            Lon: {{ number_format($currentSigpac['centroid']['lng'], 4) }}°
                        @endif
                    </span>
                </div>
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
                title="Selecciona un recinto"
                description="Elige un recinto SIGPAC para ver sus datos de análisis avanzado" />
        </x-agro.card>
    @endif
    </div>{{-- cierre wire:key dashboard-body --}}
</div>
