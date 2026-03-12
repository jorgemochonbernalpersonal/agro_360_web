{{-- Tab Cosecha: estado fenológico y Grados-Día acumulados (GDD) --}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Estado Fenológico --}}
    <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-lg p-5 border border-pink-200">
        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            🍇 Estado Fenológico
        </h3>
        <div class="text-center py-4">
            <div class="text-6xl mb-3">
                @switch($gdd['stage']['icon'])
                    @case('sprout') 🌱 @break
                    @case('flower') 🌸 @break
                    @case('grape')  🍇 @break
                    @case('green')  🟢 @break
                    @case('purple') 🟣 @break
                    @case('wine')   🍷 @break
                    @default {{ $gdd['stage']['icon'] }}
                @endswitch
            </div>
            <div class="text-2xl font-bold text-purple-700">{{ $gdd['stage']['name'] }}</div>
            <div class="w-full bg-zinc-200 rounded-full h-4 mt-4">
                <div class="h-4 rounded-full bg-gradient-to-r from-green-400 to-purple-500"
                     style="width: {{ $gdd['stage']['progress'] }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-zinc-500 mt-1">
                <span>Brotación</span>
                <span>Vendimia</span>
            </div>
        </div>
    </div>

    {{-- GDD Stats --}}
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-5 border border-amber-200">
        <h3 class="font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            🌡️ Grados-Día Acumulados (GDD)
        </h3>
        <div class="space-y-4">
            <div class="bg-white rounded-lg p-3 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">GDD Hoy</span>
                    <span class="font-bold text-orange-600">+{{ $gdd['gdd_today'] }}°</span>
                </div>
            </div>
            <div class="bg-white rounded-lg p-3 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">GDD Próximos 7 días</span>
                    <span class="font-bold text-amber-600">+{{ $gdd['gdd_week_forecast'] }}°</span>
                </div>
            </div>
            <div class="bg-white rounded-lg p-3 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">GDD Acumulado (desde 1 abril)</span>
                    <span class="font-bold text-red-600">{{ $gdd['gdd_accumulated'] }}°</span>
                </div>
                <div class="w-full bg-zinc-200 rounded-full h-2 mt-2">
                    <div class="h-2 rounded-full bg-gradient-to-r from-amber-400 to-red-500"
                         style="width: {{ min(100, ($gdd['gdd_accumulated'] / $gdd['gdd_target']) * 100) }}%"></div>
                </div>
                <div class="text-xs text-zinc-500 mt-1 text-right">{{ $gdd['gdd_accumulated'] }} / {{ $gdd['gdd_target'] }}° objetivo</div>
            </div>
            @if($gdd['estimated_harvest_date'])
                <div class="bg-purple-100 rounded-lg p-4 text-center">
                    <div class="text-sm text-purple-600">Fecha estimada de vendimia</div>
                    <div class="text-xl font-bold text-purple-800">📅 {{ $gdd['estimated_harvest_date'] }}</div>
                    <div class="text-xs text-purple-600">(en ~{{ $gdd['days_to_harvest'] }} días)</div>
                </div>
            @endif
        </div>
    </div>
</div>
