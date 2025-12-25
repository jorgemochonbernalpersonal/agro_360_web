<div class="space-y-6">
    {{-- Métricas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Total Parcelas --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Parcelas</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPlots }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $activePlots }} activas</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Superficie Total --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Superficie Total</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalSurface }}</p>
                    <p class="text-xs text-gray-500 mt-1">hectáreas</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Superficie Admisible PAC --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Admisible PAC</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $eligibleSurface }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $eligibilityPercentage }}% del total</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Parcelas Bloqueadas --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Parcelas Bloqueadas</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $lockedPlots }}</p>
                    <p class="text-xs text-gray-500 mt-1">protegidas</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribución por Régimen de Tenencia --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribución por Régimen de Tenencia</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach(['propiedad' => 'Propiedad', 'arrendamiento' => 'Arrendamiento', 'aparceria' => 'Aparcería', 'cesion' => 'Cesión', 'usufructo' => 'Usufructo'] as $key => $label)
                @php
                    $stats = $tenureStats->get($key, ['count' => 0, 'surface' => 0]);
                @endphp
                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['count'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ round($stats['surface'], 2) }} ha</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Alertas de Cumplimiento --}}
    @if($totalAlerts > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">⚠️ Alertas de Cumplimiento PAC</h3>
                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                    {{ $totalAlerts }} {{ $totalAlerts === 1 ? 'alerta' : 'alertas' }}
                </span>
            </div>
            <div class="space-y-3">
                @foreach($alerts as $alert)
                    <div class="flex items-start gap-3 p-3 rounded-lg {{ $alert['type'] === 'error' ? 'bg-red-50 border-l-4 border-red-500' : 'bg-yellow-50 border-l-4 border-yellow-500' }}">
                        <svg class="w-5 h-5 {{ $alert['type'] === 'error' ? 'text-red-600' : 'text-yellow-600' }} flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold {{ $alert['type'] === 'error' ? 'text-red-900' : 'text-yellow-900' }}">
                                {{ $alert['plot'] }}
                            </p>
                            <p class="text-sm {{ $alert['type'] === 'error' ? 'text-red-700' : 'text-yellow-700' }} mt-1">
                                {{ $alert['message'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-medium text-green-900">✅ Todas las parcelas cumplen con los requisitos PAC</p>
            </div>
        </div>
    @endif
</div>
