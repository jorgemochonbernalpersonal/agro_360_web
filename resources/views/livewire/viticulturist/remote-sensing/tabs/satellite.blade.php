{{-- Tab Vegetación: NDVI principal + Bandas Espectrales + LAI + Mapa de Vigor --}}

{{-- Date Selector --}}
<div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div class="flex flex-col md:flex-row md:items-center gap-2">
        <span class="text-sm font-medium text-zinc-700">📅 Fecha de análisis:</span>
        @if(count($satelliteAvailableDates) > 0)
            <flux:select wire:model.live="satelliteSelectedDate">
                <option value="">Último dato disponible</option>
                @foreach($satelliteAvailableDates as $date)
                    <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                @endforeach
            </flux:select>
        @else
            <span class="text-sm text-amber-600 font-medium bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                ⚠️ Sin datos históricos - Carga datos para comenzar
            </span>
        @endif
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        @if($satelliteSelectedDate && count($satelliteAvailableDates) > 0)
            <button wire:click="$set('satelliteSelectedDate', '')"
                    class="text-sm text-blue-600 hover:text-blue-800 transition font-medium">
                🔄 Ver último dato
            </button>
        @endif

        @if(!$ndviData || ($ndviData->ndvi_mean ?? 0) == 0)
            <button wire:click="requestDataForDate"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-medium rounded-lg transition-all shadow-md disabled:opacity-50">
                <svg wire:loading.remove wire:target="requestDataForDate" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <svg wire:loading wire:target="requestDataForDate" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading.remove wire:target="requestDataForDate">🛰️ Cargar Datos NASA</span>
                <span wire:loading wire:target="requestDataForDate">Cargando...</span>
            </button>
        @endif
    </div>
</div>

{{-- Sin datos --}}
@if(!$ndviData || ($ndviData->ndvi_mean ?? 0) == 0)
    <div class="mb-6 bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 rounded-r-lg p-5 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h4 class="text-base font-semibold text-yellow-900 mb-2">📡 Sin datos satelitales para esta parcela</h4>
                <div class="text-sm text-yellow-800 space-y-2 mb-3">
                    <p>Esta parcela <strong>{{ $selectedPlot->name }}</strong> no tiene datos de Remote Sensing cargados
                    @if($satelliteSelectedDate)
                        para la fecha <strong>{{ \Carbon\Carbon::parse($satelliteSelectedDate)->format('d/m/Y') }}</strong>
                    @endif.</p>
                    <p class="font-medium">🎯 ¿Qué hacer?</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li>Haz clic en <strong>"🛰️ Cargar Datos NASA"</strong> para obtener datos satelitales actuales</li>
                        <li>Los datos se cargarán directamente desde NASA Earthdata (gratuito)</li>
                        <li>Verás NDVI, humedad, temperatura y más indicadores en 30-60 segundos</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg p-3 text-xs border border-yellow-200">
                    <p class="font-semibold text-zinc-800 mb-2">📋 Verifica que:</p>
                    <div class="space-y-1 text-zinc-700">
                        <div class="flex items-center gap-2">
                            @php $recintoCoords = $this->getSelectedRecintoCoordinates(); @endphp
                            @if($recintoCoords)
                                <span class="text-green-600">✓</span>
                                <span>El recinto tiene coordenadas (Lat: {{ number_format($recintoCoords['lat'], 4) }}°, Lon: {{ number_format($recintoCoords['lng'], 4) }}°)</span>
                            @else
                                <span class="text-red-600">✗</span>
                                <span class="text-red-600 font-semibold">⚠️ El recinto seleccionado NO tiene geometría configurada</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-blue-600">ℹ️</span>
                            <span>Las credenciales NASA están configuradas en el servidor</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-blue-600">ℹ️</span>
                            <span>La cola de trabajos está procesándose</span>
                        </div>
                    </div>
                </div>

                @if($dataLoadError)
                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                        <p class="text-sm text-red-700"><strong>❌ Error:</strong> {{ $dataLoadError }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Cobertura de nubes elevada --}}
@if($ndviData && $ndviData->hasHighCloudCoverage())
    <div class="mb-4 flex items-start gap-3 px-4 py-3 bg-orange-50 border border-orange-300 rounded-xl text-orange-800 text-sm">
        <flux:icon icon="cloud" class="size-5 shrink-0 text-orange-500 mt-0.5" />
        <div>
            <span class="font-semibold">Cobertura de nubes elevada ({{ $ndviData->cloud_coverage }}%)</span>
            — Los valores NDVI de esta fecha pueden ser inexactos.
            Selecciona otra fecha con menos nubes para obtener datos fiables.
        </div>
    </div>
@endif

{{-- NDVI / NDWI / Año anterior --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-zinc-600">NDVI</span>
            <span class="text-2xl">{{ $ndviData?->health_emoji ?? '❓' }}</span>
        </div>
        <div class="text-4xl font-bold text-green-700 mb-2">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</div>
        <div class="w-full bg-zinc-200 rounded-full h-2.5 mb-2">
            <div class="h-2.5 rounded-full" style="width: {{ $ndviData?->ndvi_percentage ?? 0 }}%; background: linear-gradient(90deg, #ef4444, #eab308, #22c55e);"></div>
        </div>
        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
            @if($ndviData?->health_status === 'excellent') bg-green-100 text-green-800
            @elseif($ndviData?->health_status === 'good') bg-emerald-100 text-emerald-800
            @elseif($ndviData?->health_status === 'moderate') bg-yellow-100 text-yellow-800
            @elseif($ndviData?->health_status === 'poor') bg-orange-100 text-orange-800
            @else bg-red-100 text-red-800
            @endif">
            {{ $ndviData?->health_text ?? 'Sin datos' }}
        </span>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-5 border border-blue-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-zinc-600">NDWI (Agua)</span>
            <span class="text-2xl">💧</span>
        </div>
        <div class="text-4xl font-bold text-blue-700 mb-2">{{ number_format($ndviData?->ndwi_mean ?? 0, 2) }}</div>
        <p class="text-xs text-zinc-600">Contenido de agua en vegetación</p>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-zinc-600">vs {{ now()->year - 1 }}</span>
            <span class="text-2xl">📅</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-center">
                <div class="text-xs text-zinc-500">{{ now()->year - 1 }}</div>
                <div class="text-xl font-bold text-zinc-600">{{ number_format($lastYearNdvi ?? 0, 2) }}</div>
            </div>
            <span class="text-xl">→</span>
            <div class="text-center">
                <div class="text-xs text-zinc-500">{{ now()->year }}</div>
                <div class="text-xl font-bold text-green-600">{{ number_format($ndviData?->ndvi_mean ?? 0, 2) }}</div>
            </div>
            <div class="px-2 py-1 rounded-full text-xs font-bold
                @if($yearChange > 0) bg-green-100 text-green-700
                @elseif($yearChange < 0) bg-red-100 text-red-700
                @else bg-zinc-100 text-zinc-700
                @endif">
                {{ $yearChange > 0 ? '↑' : ($yearChange < 0 ? '↓' : '=') }}
                {{ number_format(abs(($yearChange ?? 0) * 100), 1) }}%
            </div>
        </div>
    </div>
</div>

{{-- Fuente y calidad --}}
@if($ndviData && ($ndviData->ndvi_mean ?? 0) > 0)
    <div class="mt-4 bg-zinc-50 rounded-lg p-3 text-sm text-zinc-600 flex flex-wrap gap-4 items-center">
        <span>📡 NASA MODIS</span>
        <span>📅 {{ $ndviData?->image_date?->format('d/m/Y') ?? 'N/A' }}</span>
        <span class="flex items-center gap-1 {{ $ndviData->hasHighCloudCoverage() ? 'text-orange-600 font-semibold' : '' }}">
            <flux:icon icon="cloud" variant="micro" class="{{ $ndviData->hasHighCloudCoverage() ? 'text-orange-500' : 'text-zinc-400' }}" />
            {{ $ndviData?->cloud_coverage ?? 0 }}% nubes
            &nbsp;·&nbsp; {{ $ndviData?->cloud_quality_label }}
        </span>
    </div>
@endif

{{-- Bandas Espectrales --}}
<div class="mt-6 pt-6 border-t border-zinc-100">
    <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-4">Bandas Espectrales</h3>
    @livewire('viticulturist.remote-sensing.spectral-bands-card', ['plot' => $selectedPlot], key('spectral-'.$selectedPlot->id))
</div>

{{-- LAI Oficial NASA --}}
<div class="mt-6 pt-6 border-t border-zinc-100">
    <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-4">LAI Oficial NASA</h3>
    @livewire('viticulturist.remote-sensing.official-lai-card', ['plot' => $selectedPlot], key('lai-official-'.$selectedPlot->id))
</div>

{{-- Mapa de Vigor intra-parcela --}}
<div class="mt-6 pt-6 border-t border-zinc-100">
    <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wide mb-4">Mapa de Vigor</h3>
    @livewire('viticulturist.remote-sensing.vigor-map-card', ['plot' => $selectedPlot], key('vigor-'.$selectedPlot->id))
</div>
