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

    {{-- Nuevas Métricas: Productos Fitosanitarios --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">📦 Productos Fitosanitarios</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <div class="text-sm text-blue-600 font-medium">Total Productos</div>
                <div class="text-2xl font-bold text-blue-900">{{ $totalProducts }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                <div class="text-sm text-green-600 font-medium">Registro Válido</div>
                <div class="text-2xl font-bold text-green-900">{{ $productsWithValidRegistration }}</div>
                <div class="text-xs text-green-600 mt-1">{{ $productRegistrationPercentage }}%</div>
            </div>
            <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                <div class="text-sm text-amber-600 font-medium">Próximos a Caducar</div>
                <div class="text-2xl font-bold text-amber-900">{{ $productsExpiringSoon }}</div>
                <div class="text-xs text-amber-600 mt-1">Próximos 30 días</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                <div class="text-sm text-red-600 font-medium">Caducados/Revocados</div>
                <div class="text-2xl font-bold text-red-900">{{ $productsExpiredOrRevoked }}</div>
            </div>
        </div>

        {{-- Barra de progreso de productos válidos --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Estado de Registros</span>
                <span class="text-sm font-bold {{ $productRegistrationPercentage >= 95 ? 'text-green-600' : ($productRegistrationPercentage >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $productRegistrationPercentage }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full transition-all duration-500 {{ $productRegistrationPercentage >= 95 ? 'bg-green-500' : ($productRegistrationPercentage >= 80 ? 'bg-amber-500' : 'bg-red-500') }}" 
                     style="width: {{ $productRegistrationPercentage }}%"></div>
            </div>
        </div>

        {{-- Alertas de productos próximos a caducar --}}
        @if($productsExpiringSoon > 0)
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg mt-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-amber-800">Productos próximos a caducar</p>
                        <div class="mt-2 text-sm text-amber-700 space-y-1">
                            @foreach($expiringProducts as $product)
                                <div class="flex items-center justify-between">
                                    <span>{{ $product->name }} ({{ $product->registration_number }})</span>
                                    <span class="font-medium">{{ $product->registration_expiry_date->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-amber-600">
                            <a href="{{ route('viticulturist.phytosanitary-products.index') }}" class="underline hover:text-amber-800">
                                Ver todos los productos →
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Nuevas Métricas: Actividades Bloqueadas --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">🔒 Actividades Bloqueadas</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="text-sm text-gray-600 font-medium">Total Bloqueadas</div>
                <div class="text-2xl font-bold text-gray-900">{{ $totalLockedActivities }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                <div class="text-sm text-blue-600 font-medium">% Bloqueadas</div>
                <div class="text-2xl font-bold text-blue-900">{{ $lockedActivitiesPercentage }}%</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                <div class="text-sm text-purple-600 font-medium">Bloqueadas Recientemente</div>
                <div class="text-2xl font-bold text-purple-900">{{ $recentlyLockedActivities->count() }}</div>
                <div class="text-xs text-purple-600 mt-1">Últimos 7 días</div>
            </div>
        </div>

        {{-- Lista de actividades bloqueadas recientemente --}}
        @if($recentlyLockedActivities->count() > 0)
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700">Actividades bloqueadas recientemente</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($recentlyLockedActivities as $activity)
                        <div class="px-4 py-3 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ ucfirst($activity->activity_type) }}
                                        </span>
                                        <span class="text-xs text-gray-500">•</span>
                                        <span class="text-xs text-gray-600">
                                            {{ $activity->plot->name ?? 'Sin parcela' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Fecha: {{ $activity->activity_date->format('d/m/Y') }} • 
                                        Bloqueada: {{ $activity->locked_at->diffForHumans() }}
                                    </div>
                                </div>
                                <x-activity-locked-badge :activity="$activity" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-4">No hay actividades bloqueadas recientemente</p>
        @endif

        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
            <p class="text-xs text-blue-700">
                <strong>ℹ️ Información:</strong> Las actividades se bloquean automáticamente después de 7 días para garantizar el cumplimiento PAC y prevenir modificaciones retroactivas.
            </p>
        </div>
    </div>

    {{-- Nuevas Métricas: Validación de Cosechas --}}
    @if($totalHarvests > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🌾 Validación de Plazos de Seguridad en Cosechas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <div class="text-sm text-blue-600 font-medium">Total Cosechas</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $totalHarvests }}</div>
                    <div class="text-xs text-blue-600 mt-1">En el período seleccionado</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                    <div class="text-sm text-red-600 font-medium">Con Errores de Plazo</div>
                    <div class="text-2xl font-bold text-red-900">{{ $harvestsWithWithdrawalIssues }}</div>
                    @if($harvestsWithWithdrawalIssues > 0)
                        <div class="text-xs text-red-600 mt-1">⚠️ Requieren atención</div>
                    @endif
                </div>
                <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                    <div class="text-sm text-amber-600 font-medium">Con Advertencias</div>
                    <div class="text-2xl font-bold text-amber-900">{{ $harvestsWithWarnings }}</div>
                    <div class="text-xs text-amber-600 mt-1">Cerca del límite</div>
                </div>
            </div>

            @if($harvestsWithWithdrawalIssues > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                Se detectaron {{ $harvestsWithWithdrawalIssues }} cosecha(s) que no cumplen el plazo de seguridad
                            </p>
                            <p class="text-xs text-red-700 mt-1">
                                Estas cosechas se realizaron antes de cumplirse el plazo de seguridad del último tratamiento fitosanitario. 
                                Revisa el cuaderno digital para más detalles.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                ✅ Todas las cosechas cumplen con los plazos de seguridad
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

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
