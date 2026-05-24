{{-- Tab Riego: balance hídrico semanal y recomendación de riego --}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Balance Hídrico --}}
    <div class="bg-gradient-to-br from-cyan-50 to-blue-50 rounded-lg p-5 border border-cyan-200">
        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            💧 {{ __('Balance Hídrico Semanal') }}
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('ET0 (evapotranspiración)') }}</span>
                <span class="font-bold text-blue-600">{{ $irrigationNeeds['et0'] }} mm/{{ __('día') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('Kc (coef. cultivo vid)') }}</span>
                <span class="font-bold">{{ $irrigationNeeds['kc'] }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('ETc (necesidad cultivo)') }}</span>
                <span class="font-bold text-cyan-600">{{ $irrigationNeeds['etc'] }} mm/{{ __('día') }}</span>
            </div>
            <hr class="border-zinc-200">
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('Necesidad semanal') }}</span>
                <span class="font-bold">{{ $irrigationNeeds['weekly_need_mm'] }} mm</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('Lluvia prevista') }}</span>
                <span class="font-bold text-indigo-600">-{{ $irrigationNeeds['expected_rain_mm'] }} mm</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-zinc-600">{{ __('Reserva suelo') }}</span>
                <span class="font-bold text-amber-600">-{{ $irrigationNeeds['soil_reserve_mm'] }} mm</span>
            </div>
        </div>
    </div>

    {{-- Recomendación --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            🚿 {{ __('Recomendación de Riego') }}
        </h3>
        <div class="text-center py-4">
            <div class="text-5xl mb-3">💧</div>
            <div class="text-3xl font-bold {{ $irrigationNeeds['recommendation']['color'] }}">
                {{ $irrigationNeeds['irrigation_need_mm'] }} mm
            </div>
            <div class="text-sm text-zinc-600 mt-1">{{ __('Déficit hídrico esta semana') }}</div>
            <div class="mt-4 px-4 py-2 rounded-full font-bold {{ $irrigationNeeds['recommendation']['bg'] }} {{ $irrigationNeeds['recommendation']['color'] }}">
                {{ $irrigationNeeds['recommendation']['text'] }}
            </div>
            @if($irrigationNeeds['liters_per_ha'] > 0)
                <div class="mt-4 text-sm text-zinc-600">
                    ≈ <strong>{{ number_format($irrigationNeeds['liters_per_ha']) }}</strong> {{ __('litros/ha') }}
                </div>
            @endif
        </div>
    </div>
</div>
