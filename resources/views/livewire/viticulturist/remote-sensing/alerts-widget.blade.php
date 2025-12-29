<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            🔔 Alertas de Teledetección
            @if($totalCount > 0)
                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">
                    {{ $totalCount }}
                </span>
            @endif
        </h3>
        <button wire:click="refresh" class="text-gray-400 hover:text-gray-600 transition-colors" title="Actualizar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>

    @if(count($alerts) > 0)
        <div class="space-y-3 max-h-80 overflow-y-auto">
            @foreach($alerts as $plotId => $plotData)
                @foreach($plotData['alerts'] as $alert)
                    <div class="rounded-lg p-4 border-l-4 
                        @if($alert['severity'] === 'critical') 
                            bg-red-50 border-red-500
                        @elseif($alert['severity'] === 'warning')
                            bg-orange-50 border-orange-500
                        @else
                            bg-blue-50 border-blue-500
                        @endif
                    ">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">
                                        {{ \App\Services\RemoteSensing\AlertService::getSeverityIcon($alert['severity']) }}
                                    </span>
                                    <span class="font-semibold text-gray-900">{{ $alert['title'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $alert['message'] }}</p>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                    <span>📍 {{ $plotData['plot']->name }}</span>
                                    <span>📅 {{ $alert['date'] }}</span>
                                </div>
                            </div>
                            <a href="{{ route('remote-sensing.plot', $plotData['plot']) }}" 
                               class="text-sm font-medium text-green-600 hover:text-green-700 whitespace-nowrap">
                                Ver parcela →
                            </a>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h4 class="text-lg font-medium text-gray-900 mb-1">Sin alertas activas</h4>
            <p class="text-sm text-gray-500">Todas tus parcelas están en buen estado</p>
        </div>
    @endif
</div>
