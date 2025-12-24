<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    @endphp
    
    <x-page-header 
        :icon="$icon"
        title="Dashboard de Cumplimiento PAC"
        description="Monitoriza el estado de cumplimiento normativo de tu cuaderno digital en tiempo real"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    {{-- Filtro de Rango Temporal --}}
    <x-filter-section title="Período de Análisis" color="green">
        <x-filter-select wire:model.live="timeRange">
            <option value="30">Últimos 30 días</option>
            <option value="90">Últimos 90 días</option>
            <option value="180">Últimos 6 meses</option>
            <option value="365">Último año</option>
            <option value="all">Todas las actividades</option>
        </x-filter-select>
    </x-filter-section>

    {{-- Medidor de Cumplimiento Global --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Cumplimiento Global</h2>
            <span class="text-3xl font-bold {{ $compliancePercentage >= 95 ? 'text-green-600' : ($compliancePercentage >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                {{ number_format($compliancePercentage, 1) }}%
            </span>
        </div>
        
        {{-- Barra de Progreso --}}
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <div class="h-4 rounded-full transition-all duration-500 {{ $compliancePercentage >= 95 ? 'bg-green-500' : ($compliancePercentage >= 80 ? 'bg-amber-500' : 'bg-red-500') }}" 
                 style="width: {{ $compliancePercentage }}%"></div>
        </div>
        
        {{-- Estadísticas Rápidas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <div class="text-sm text-blue-600 font-medium">Total Actividades</div>
                <div class="text-2xl font-bold text-blue-900">{{ $totalActivities }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                <div class="text-sm text-red-600 font-medium">Con Errores Críticos</div>
                <div class="text-2xl font-bold text-red-900">{{ $activitiesWithErrors }}</div>
            </div>
            <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                <div class="text-sm text-amber-600 font-medium">Con Advertencias</div>
                <div class="text-2xl font-bold text-amber-900">{{ $activitiesWithWarnings }}</div>
            </div>
        </div>
    </div>

    {{-- Cumplimiento por Tipo de Actividad --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Cumplimiento por Tipo de Actividad</h2>
        
        @if(empty($statsByType))
            <p class="text-gray-500 text-center py-8">No hay actividades registradas en este período</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($statsByType as $type => $stats)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-[var(--color-agro-green-light)] transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-700">
                                @switch($type)
                                    @case('phytosanitary') Fitosanitarios @break
                                    @case('irrigation') Riegos @break
                                    @case('fertilization') Fertilizaciones @break
                                    @case('harvest') Cosechas @break
                                    @case('cultural') Labores Culturales @break
                                    @case('observation') Observaciones @break
                                @endswitch
                            </h3>
                            <span class="text-lg font-bold {{ $stats['percentage'] >= 95 ? 'text-green-600' : ($stats['percentage'] >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($stats['percentage'], 1) }}%
                            </span>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                            <div class="h-2 rounded-full {{ $stats['percentage'] >= 95 ? 'bg-green-500' : ($stats['percentage'] >= 80 ? 'bg-amber-500' : 'bg-red-500') }}" 
                                 style="width: {{ $stats['percentage'] }}%"></div>
                        </div>
                        
                        <div class="text-sm text-gray-600 space-y-1">
                            <div class="flex justify-between">
                                <span>Total:</span>
                                <span class="font-medium">{{ $stats['total'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Conformes:</span>
                                <span class="font-medium text-green-600">{{ $stats['compliant'] }}</span>
                            </div>
                            @if($stats['errors'] > 0)
                                <div class="flex justify-between">
                                    <span>Errores:</span>
                                    <span class="font-medium text-red-600">{{ $stats['errors'] }}</span>
                                </div>
                            @endif
                            @if($stats['warnings'] > 0)
                                <div class="flex justify-between">
                                    <span>Advertencias:</span>
                                    <span class="font-medium text-amber-600">{{ $stats['warnings'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Errores Críticos --}}
    @if(count($criticalErrors) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-red-800">Errores Críticos PAC</h2>
                <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full">
                    {{ count($criticalErrors) }} {{ count($criticalErrors) === 1 ? 'error' : 'errores' }}
                </span>
            </div>
            
            <div class="space-y-3">
                @foreach($criticalErrors as $error)
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-red-800">
                                        Actividad #{{ $error['activity_id'] }} - {{ ucfirst($error['activity_type']) }}
                                    </p>
                                    <span class="text-xs text-red-600">{{ $error['activity_date'] }}</span>
                                </div>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach($error['errors'] as $errorMsg)
                                        <li>{{ $errorMsg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($activitiesWithErrors > 10)
                <p class="text-sm text-gray-600 mt-4 text-center">
                    Mostrando 10 de {{ $activitiesWithErrors }} errores. 
                    <a href="{{ route('viticulturist.digital-notebook') }}" class="text-[var(--color-agro-green)] hover:underline font-medium">Ver todas las actividades</a>
                </p>
            @endif
        </div>
    @endif

    {{-- Advertencias --}}
    @if(count($warnings) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-amber-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-amber-800">Advertencias PAC</h2>
                <span class="bg-amber-100 text-amber-800 text-sm font-medium px-3 py-1 rounded-full">
                    {{ count($warnings) }} {{ count($warnings) === 1 ? 'advertencia' : 'advertencias' }}
                </span>
            </div>
            
            <div class="space-y-3">
                @foreach($warnings as $warning)
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-amber-800">
                                        Actividad #{{ $warning['activity_id'] }} - {{ ucfirst($warning['activity_type']) }}
                                    </p>
                                    <span class="text-xs text-amber-600">{{ $warning['activity_date'] }}</span>
                                </div>
                                <ul class="mt-2 text-sm text-amber-700 list-disc list-inside space-y-1">
                                    @foreach($warning['warnings'] as $warningMsg)
                                        <li>{{ $warningMsg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($activitiesWithWarnings > 10)
                <p class="text-sm text-gray-600 mt-4 text-center">
                    Mostrando 10 de {{ $activitiesWithWarnings }} advertencias.
                </p>
            @endif
        </div>
    @endif

    {{-- Mensaje de Éxito --}}
    @if($compliancePercentage >= 95 && $activitiesWithErrors === 0)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xl font-semibold text-green-800 mb-2">¡Excelente Cumplimiento PAC!</h3>
            <p class="text-green-700">
                Tu cuaderno digital cumple con todos los requisitos normativos. 
                Todas tus actividades están correctamente registradas y listas para auditorías PAC.
            </p>
        </div>
    @endif
</div>
