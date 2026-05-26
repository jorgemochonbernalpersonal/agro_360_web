@php
    $spectralData = $spectralData ?? null;
    $indices      = $indices ?? null;
    $loading      = $loading ?? false;
    $error        = $error ?? null;
    $availableDates = $availableDates ?? [];

    // Rangos agronómicos por índice para colorear el valor
    $ranges = [
        'ndvi'  => ['bad' => 0.2,  'ok' => 0.4,  'good' => 0.6,  'max' => 1.0],
        'gndvi' => ['bad' => 0.2,  'ok' => 0.4,  'good' => 0.6,  'max' => 1.0],
        'ndwi'  => ['bad' => -0.3, 'ok' => 0.0,  'good' => 0.2,  'max' => 1.0],
        'ndre'  => ['bad' => 0.1,  'ok' => 0.3,  'good' => 0.5,  'max' => 1.0],
    ];
@endphp

<div class="bg-white rounded-lg shadow-md p-6">

    {{-- Header: título + selector de fecha en una sola línea --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-bold text-zinc-900">{{ __('Índices espectrales') }}</h3>
            <p class="text-xs text-zinc-400 mt-0.5">Sentinel-2 · {{ __('resolución 10 m') }}</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Cobertura útil --}}
            @if($spectralData && isset($spectralData['valid_pixel_ratio']) && $spectralData['valid_pixel_ratio'] !== null)
                @php $ratio = $spectralData['valid_pixel_ratio']; @endphp
                <span class="text-xs px-2 py-1 rounded-full font-medium
                    {{ $ratio >= 70 ? 'bg-green-100 text-green-700' : ($ratio >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ $ratio }}% {{ __('sin nubes') }}
                </span>
            @endif

            {{-- Selector de fecha — el cambio recarga automáticamente (wire:model.live) --}}
            @if(count($availableDates) > 1)
                <flux:select wire:model.live="selectedDate" class="text-sm">
                    @foreach($availableDates as $date)
                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                    @endforeach
                </flux:select>
            @elseif($spectralData)
                <span class="text-sm text-zinc-500">{{ $spectralData['date'] }}</span>
            @endif
        </div>
    </div>

    {{-- Loading --}}
    @if($loading && !$spectralData)
        <div class="flex items-center justify-center py-10 text-zinc-400">
            <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ __('Cargando...') }}
        </div>
    @endif

    {{-- Error --}}
    @if($error && !$loading)
        <flux:callout variant="warning" icon="exclamation-triangle">
            {{ $error }}
        </flux:callout>
    @endif

    {{-- Índices en grid compacto --}}
    @if($spectralData && $indices)
        <div class="grid grid-cols-2 gap-3">
            @foreach($indices as $key => $index)
                @if($index['value'] !== null)
                    @php
                        $v   = (float) $index['value'];
                        $r   = $ranges[$key] ?? null;
                        $pct = $r ? min(100, max(0, ($v - ($r['bad'] - 0.2)) / ($r['max'] - ($r['bad'] - 0.2)) * 100)) : 50;
                        $barColor = !$r ? 'bg-zinc-400'
                            : ($v >= $r['good'] ? 'bg-green-500'
                            : ($v >= $r['ok']   ? 'bg-yellow-400'
                            :                     'bg-red-400'));
                        $textColor = !$r ? 'text-zinc-700'
                            : ($v >= $r['good'] ? 'text-green-700'
                            : ($v >= $r['ok']   ? 'text-yellow-700'
                            :                     'text-red-600'));
                    @endphp
                    <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ $index['label'] }}</div>
                                <div class="text-xs text-zinc-400 mt-0.5">{{ $index['description'] }}</div>
                            </div>
                            <div class="text-2xl font-bold {{ $textColor }}">
                                {{ number_format($v, 3) }}
                            </div>
                        </div>
                        {{-- Barra en escala agronómica del índice --}}
                        <div class="h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Bandas crudas (si disponibles desde Sentinel-2) --}}
        @if(isset($spectralData['red_band']) || isset($spectralData['nir_band']) || isset($spectralData['green_band']))
            <div class="mt-4 pt-4 border-t border-zinc-100">
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wide mb-2">{{ __('Reflectancias brutas') }}</p>
                <div class="flex gap-4 text-xs text-zinc-600">
                    @if(isset($spectralData['red_band']))
                        <span><span class="font-semibold text-red-500">{{ __('Rojo') }}</span> {{ number_format($spectralData['red_band'], 4) }}</span>
                    @endif
                    @if(isset($spectralData['nir_band']))
                        <span><span class="font-semibold text-purple-500">{{ __('NIR') }}</span> {{ number_format($spectralData['nir_band'], 4) }}</span>
                    @endif
                    @if(isset($spectralData['green_band']))
                        <span><span class="font-semibold text-green-500">{{ __('Verde') }}</span> {{ number_format($spectralData['green_band'], 4) }}</span>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
