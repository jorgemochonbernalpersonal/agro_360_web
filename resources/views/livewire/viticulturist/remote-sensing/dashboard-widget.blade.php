<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-4 py-3">
        <div class="flex items-center justify-between">
            <h3 class="text-white font-bold flex items-center gap-2">
                🛰️ Teledetección
            </h3>
            <a href="{{ route('remote-sensing.dashboard') }}" 
               class="text-white/80 hover:text-white text-sm flex items-center gap-1 transition">
                Ver todo
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    @if($isLoading)
        <div class="p-4 text-center">
            <svg class="w-6 h-6 animate-spin mx-auto text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
    @else
        <div class="p-4">
            <!-- Stats Row -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats['average_ndvi'], 2) }}</div>
                    <div class="text-xs text-gray-500">NDVI Promedio</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-emerald-600">{{ $stats['healthy_percent'] }}%</div>
                    <div class="text-xs text-gray-500">Saludables</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $stats['alerts'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $stats['alerts'] }}
                    </div>
                    <div class="text-xs text-gray-500">Alertas</div>
                </div>
            </div>

            <!-- Health Bar -->
            <div class="mb-4">
                <div class="flex h-3 rounded-full overflow-hidden bg-gray-200">
                    @if($stats['total'] > 0)
                        @if($stats['excellent'] > 0)
                            <div class="bg-green-500" style="width: {{ ($stats['excellent'] / $stats['total']) * 100 }}%"></div>
                        @endif
                        @if($stats['good'] > 0)
                            <div class="bg-emerald-400" style="width: {{ ($stats['good'] / $stats['total']) * 100 }}%"></div>
                        @endif
                        @if($stats['moderate'] > 0)
                            <div class="bg-yellow-400" style="width: {{ ($stats['moderate'] / $stats['total']) * 100 }}%"></div>
                        @endif
                        @if($stats['poor'] > 0)
                            <div class="bg-orange-400" style="width: {{ ($stats['poor'] / $stats['total']) * 100 }}%"></div>
                        @endif
                        @if($stats['critical'] > 0)
                            <div class="bg-red-500" style="width: {{ ($stats['critical'] / $stats['total']) * 100 }}%"></div>
                        @endif
                    @endif
                </div>
                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                    <span>🌿 {{ $stats['excellent'] }}</span>
                    <span>🌱 {{ $stats['good'] }}</span>
                    <span>🌾 {{ $stats['moderate'] }}</span>
                    <span>🍂 {{ $stats['poor'] }}</span>
                    <span>🥀 {{ $stats['critical'] }}</span>
                </div>
            </div>

            <!-- Alerts List -->
            @if(count($alerts) > 0)
                <div class="border-t pt-3">
                    <p class="text-xs font-semibold text-red-600 mb-2">⚠️ Parcelas con alerta:</p>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        @foreach($alerts as $alert)
                            <a href="{{ route('plots.show', $alert['id']) }}" 
                               class="flex items-center justify-between p-2 bg-red-50 rounded-lg hover:bg-red-100 transition text-sm">
                                <div class="flex items-center gap-2">
                                    <span>{{ $alert['emoji'] }}</span>
                                    <span class="font-medium text-gray-900 truncate max-w-[120px]">{{ $alert['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold {{ $alert['status'] === 'critical' ? 'text-red-600' : 'text-orange-600' }}">
                                        {{ number_format($alert['ndvi'], 2) }}
                                    </span>
                                    <span class="text-gray-400">{{ $alert['trend_icon'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-2 text-sm text-gray-500">
                    ✅ Todas las parcelas en buen estado
                </div>
            @endif
        </div>
    @endif
</div>
