<div class="space-y-6">
    {{-- Métricas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Total Plantaciones --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Plantaciones</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPlantings }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $varieties }} variedades</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Superficie Plantada --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Superficie Plantada</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalSurface }}</p>
                    <p class="text-xs text-gray-500 mt-1">hectáreas</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Cumplimiento PAC --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Cumplimiento PAC</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $authorizationPercentage }}%</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $withAuthorization }}/{{ $needsAuthorization }} autorizadas</p>
                </div>
                <div class="p-3 {{ $authorizationPercentage >= 90 ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                    <svg class="w-8 h-8 {{ $authorizationPercentage >= 90 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Sin Autorización --}}
        @if($missingAuthorization > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Sin Autorización</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ $missingAuthorization }}</p>
                        <p class="text-xs text-gray-500 mt-1">requieren atención</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Distribución por Estado --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribución por Estado</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach(['active' => 'Activa', 'removed' => 'Arrancada', 'experimental' => 'Experimental', 'replanting' => 'Replantación'] as $key => $label)
                @php
                    $stats = $statusStats->get($key, ['count' => 0, 'surface' => 0]);
                @endphp
                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['count'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ round($stats['surface'], 2) }} ha</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Distribución por Edad y Ciclo de Vida --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Distribución por Edad</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Jóvenes (< 3 años)</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $ageStats['joven'] }}</p>
                <p class="text-xs text-gray-500 mt-1">20-40% productividad</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Desarrollo (3-8)</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ $ageStats['desarrollo'] }}</p>
                <p class="text-xs text-gray-500 mt-1">60-80% productividad</p>
            </div>
            <div class="border rounded-lg p-4 bg-green-50">
                <p class="text-sm font-medium text-gray-600">Productivas (8-25)</p>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ $ageStats['productiva'] }}</p>
                <p class="text-xs text-green-600 mt-1">⭐ 100% productividad</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Maduras (25-40)</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $ageStats['madura'] }}</p>
                <p class="text-xs text-gray-500 mt-1">80-90% productividad</p>
            </div>
            <div class="border rounded-lg p-4 {{ $ageStats['vieja'] > 0 ? 'bg-yellow-50' : '' }}">
                <p class="text-sm font-medium text-gray-600">Viejas (> 40)</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $ageStats['vieja'] }}</p>
                <p class="text-xs text-red-600 mt-1">⚠️ Replantación</p>
            </div>
        </div>
        @if($needsReplanting > 0)
            <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r">
                <p class="text-sm text-yellow-800">
                    ⚠️ <strong>{{ $needsReplanting }}</strong> plantación(es) necesitan replantación (> 35 años o en declive)
                </p>
            </div>
        @endif
    </div>

    {{-- Certificaciones --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Certificaciones</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Plantaciones Certificadas</p>
                <p class="text-3xl font-bold text-purple-600 mt-1">{{ $certifiedPlantings }}</p>
                <p class="text-xs text-gray-500 mt-1">de {{ $totalPlantings }} totales</p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600">Total Certificaciones</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $totalCertifications }}</p>
                <p class="text-xs text-gray-500 mt-1">activas</p>
            </div>
            <div class="border rounded-lg p-4 {{ $expiringCertifications > 0 ? 'bg-yellow-50' : '' }}">
                <p class="text-sm font-medium text-gray-600">Próximas a Vencer</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $expiringCertifications }}</p>
                <p class="text-xs text-gray-500 mt-1">en 30 días</p>
            </div>
        </div>
    </div>

    {{-- Tratamientos Fitosanitarios Recientes --}}
    @if($activeTreatments > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🌿 Tratamientos Fitosanitarios (últimos 30 días)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 bg-green-50">
                    <p class="text-sm font-medium text-gray-600">Tratamientos Aplicados</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $activeTreatments }}</p>
                    <p class="text-xs text-gray-500 mt-1">en el último mes</p>
                </div>
                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">Plagas/Enfermedades Tratadas</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $uniquePests }}</p>
                    <p class="text-xs text-gray-500 mt-1">diferentes</p>
                </div>
            </div>
            <div class="mt-3 bg-blue-50 border-l-4 border-blue-500 p-3 rounded-r">
                <p class="text-sm text-blue-800">
                    ℹ️ Los tratamientos están vinculados a actividades agrícolas y parcelas específicas
                </p>
            </div>
        </div>
    @endif

    {{-- Alertas de Cumplimiento --}}
    @if($totalAlerts > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🚨 Alertas de Cumplimiento</h3>
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
                                {{ $alert['planting'] }} - {{ $alert['plot'] }}
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
                <p class="text-sm font-medium text-green-900">✅ Todas las plantaciones cumplen con los requisitos PAC</p>
            </div>
        </div>
    @endif
</div>
