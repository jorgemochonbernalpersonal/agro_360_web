@php
    $smapData       = $smapData ?? null;
    $error          = $error ?? null;
    $availableDates = $availableDates ?? [];

    // Static color classes — Tailwind purge safe
    $colorMap = fn(string $status) => match($status) {
        'very_dry'  => ['text' => 'text-red-600',    'bar' => 'bg-red-500'],
        'dry'       => ['text' => 'text-orange-600', 'bar' => 'bg-orange-400'],
        'optimal'   => ['text' => 'text-green-600',  'bar' => 'bg-green-500'],
        'wet'       => ['text' => 'text-blue-600',   'bar' => 'bg-blue-500'],
        'saturated' => ['text' => 'text-purple-600', 'bar' => 'bg-purple-500'],
        default     => ['text' => 'text-zinc-600',   'bar' => 'bg-zinc-400'],
    };

    $surfaceColor  = $smapData ? $colorMap($smapData['surface_status']['status'] ?? '') : [];
    $rootzoneColor = $smapData ? $colorMap($smapData['rootzone_status']['status'] ?? '') : [];
@endphp

<div class="bg-white rounded-lg shadow-md p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-bold text-zinc-900">Humedad de Suelo</h3>
            <p class="text-xs text-zinc-400 mt-0.5">Open-Meteo · modelo meteorológico</p>
        </div>

        <div class="flex items-center gap-2">
            @if(count($availableDates) > 1)
                <flux:select wire:model.live="selectedDate" class="text-sm">
                    @foreach($availableDates as $date)
                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                    @endforeach
                </flux:select>
            @elseif($smapData)
                <span class="text-sm text-zinc-500">{{ $smapData['date'] }}</span>
            @endif
        </div>
    </div>

    {{-- Loading --}}
    @if(!$smapData && !$error)
        <div class="flex items-center justify-center py-10 text-zinc-400">
            <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Cargando...
        </div>
    @endif

    {{-- Error --}}
    @if($error)
        <flux:callout variant="warning" icon="exclamation-triangle">
            {{ $error }}
        </flux:callout>
    @endif

    @if($smapData)
        {{-- Superficie + Zona Radicular --}}
        <div class="grid grid-cols-2 gap-3">
            {{-- Superficie --}}
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Superficie</div>
                        <div class="text-xs text-zinc-400 mt-0.5">0–5 cm</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold {{ $surfaceColor['text'] }}">
                            {{ number_format($smapData['surface'], 1) }}%
                        </div>
                        <div class="text-xs {{ $surfaceColor['text'] }} font-medium">
                            {{ $smapData['surface_status']['label'] }}
                        </div>
                    </div>
                </div>
                <div class="h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="{{ $surfaceColor['bar'] }} h-full rounded-full transition-all"
                         style="width: {{ min(100, $smapData['surface']) }}%"></div>
                </div>
                <p class="text-xs text-zinc-400 mt-2">{{ $smapData['surface_status']['recommendation'] }}</p>
            </div>

            {{-- Zona Radicular --}}
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Zona Radicular</div>
                        <div class="text-xs text-zinc-400 mt-0.5">9–27 cm</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold {{ $rootzoneColor['text'] }}">
                            {{ number_format($smapData['rootzone'], 1) }}%
                        </div>
                        <div class="text-xs {{ $rootzoneColor['text'] }} font-medium">
                            {{ $smapData['rootzone_status']['label'] }}
                        </div>
                    </div>
                </div>
                <div class="h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="{{ $rootzoneColor['bar'] }} h-full rounded-full transition-all"
                         style="width: {{ min(100, $smapData['rootzone']) }}%"></div>
                </div>
                <p class="text-xs text-zinc-400 mt-2">{{ $smapData['rootzone_status']['recommendation'] }}</p>
            </div>
        </div>

        {{-- Descripción del estado --}}
        <p class="text-xs text-zinc-500 mt-3">{{ $smapData['surface_status']['description'] }}</p>
    @endif
</div>
