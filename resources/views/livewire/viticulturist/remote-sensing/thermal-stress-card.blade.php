@php
    $lstData        = $lstData ?? null;
    $cwsiData       = $cwsiData ?? null;
    $heatStress     = $heatStress ?? null;
    $frostRisk      = $frostRisk ?? null;
    $loading        = $loading ?? false;
    $error          = $error ?? null;
    $availableDates = $availableDates ?? [];

    // Static CWSI classes — Tailwind purge safe
    $cwsiColor = $cwsiData ? match($cwsiData['color'] ?? '') {
        'green'  => ['text' => 'text-green-700',  'bar' => 'bg-green-500'],
        'yellow' => ['text' => 'text-yellow-600', 'bar' => 'bg-yellow-400'],
        'orange' => ['text' => 'text-orange-600', 'bar' => 'bg-orange-400'],
        'red'    => ['text' => 'text-red-600',    'bar' => 'bg-red-500'],
        default  => ['text' => 'text-zinc-600',   'bar' => 'bg-zinc-400'],
    } : [];

    $fmt = fn($v) => $v !== null ? number_format((float) $v, 1).'°C' : '—';
@endphp

<div class="bg-white rounded-lg shadow-md p-6">

    {{-- Header: título + selector de fecha en una sola línea --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-bold text-zinc-900">{{ __('Análisis Térmico') }}</h3>
            <p class="text-xs text-zinc-400 mt-0.5">{{ __('Temperatura de superficie · MODIS LST') }}</p>
        </div>

        <div class="flex items-center gap-2">
            @if(count($availableDates) > 1)
                <flux:select wire:model.live="selectedDate" class="text-sm">
                    @foreach($availableDates as $date)
                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                    @endforeach
                </flux:select>
            @elseif($lstData)
                <span class="text-sm text-zinc-500">{{ $lstData['date'] }}</span>
            @endif

            <flux:button wire:click="reloadData" variant="ghost" size="sm"
                wire:loading.attr="disabled" wire:target="reloadData"
                :title="__('Actualizar datos térmicos desde NASA')">
                <svg wire:loading.remove wire:target="reloadData" class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="reloadData" class="animate-spin w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </flux:button>
        </div>
    </div>

    {{-- Loading --}}
    @if($loading && !$lstData)
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

    @if($lstData)
        {{-- Temperatura: 3 tarjetas compactas --}}
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Día') }}</div>
                <div class="text-2xl font-bold text-orange-600">{{ $fmt($lstData['day']) }}</div>
                <div class="text-xs text-zinc-400 mt-0.5">{{ __('superficie') }}</div>
            </div>
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Noche') }}</div>
                <div class="text-2xl font-bold text-blue-600">{{ $fmt($lstData['night']) }}</div>
                <div class="text-xs text-zinc-400 mt-0.5">{{ __('mínima') }}</div>
            </div>
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100">
                <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1">{{ __('Amplitud') }}</div>
                <div class="text-2xl font-bold text-zinc-700">{{ $fmt($lstData['diff']) }}</div>
                <div class="text-xs text-zinc-400 mt-0.5">{{ __('día − noche') }}</div>
            </div>
        </div>

        {{-- CWSI --}}
        @if($cwsiData)
            <div class="bg-zinc-50 rounded-xl p-4 border border-zinc-100 mb-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">{{ __('CWSI · Estrés Hídrico') }}</div>
                        <div class="text-xs text-zinc-400 mt-0.5">{{ $cwsiData['description'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold {{ $cwsiColor['text'] }}">{{ number_format($cwsiData['value'], 2) }}</div>
                        <div class="text-xs {{ $cwsiColor['text'] }} font-medium">{{ $cwsiData['label'] }}</div>
                    </div>
                </div>
                <div class="h-1.5 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="{{ $cwsiColor['bar'] }} h-full rounded-full transition-all"
                         style="width: {{ min(100, $cwsiData['value'] * 100) }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-zinc-400 mt-1">
                    <span>{{ __('Sin estrés') }}</span>
                    <span>{{ __('Máximo') }}</span>
                </div>
            </div>
        @endif

        {{-- Alerta estrés térmico --}}
        @if($heatStress && $heatStress['detected'])
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 mb-3">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-red-900">{{ __('Estrés Térmico') }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-red-200 text-red-900 font-medium">{{ strtoupper($heatStress['severity']) }}</span>
                        </div>
                        <p class="text-xs text-red-800">
                            {{ number_format($heatStress['lst_day'], 1) }}°C · +{{ number_format($heatStress['excess'], 1) }}°C {{ __('sobre umbral de') }} {{ $heatStress['threshold'] }}°C
                        </p>
                        <p class="text-xs text-red-700 mt-1">{{ $heatStress['recommendation'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Alerta helada --}}
        @if($frostRisk && $frostRisk['detected'])
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M3 12h18M5.636 5.636l12.728 12.728M18.364 5.636L5.636 18.364"/>
                    </svg>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-blue-900">{{ __('Riesgo de Helada') }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-blue-200 text-blue-900 font-medium">{{ strtoupper($frostRisk['severity']) }}</span>
                        </div>
                        <p class="text-xs text-blue-800">
                            {{ $fmt($frostRisk['lst_night']) }} {{ __('nocturna') }} · {{ $frostRisk['risk_level'] }}
                        </p>
                        <p class="text-xs text-blue-700 mt-1">{{ $frostRisk['recommendation'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
