<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Dashboard de Cumplimiento PAC"
        description="Monitoriza el estado de cumplimiento normativo de tu cuaderno digital en tiempo real"
    />

    {{-- Selector de período --}}
    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-zinc-600">Período de análisis:</span>
        <select
            wire:model.live="timeRange"
            class="px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
        >
            <option value="30">Últimos 30 días</option>
            <option value="90">Últimos 90 días</option>
            <option value="180">Últimos 6 meses</option>
            <option value="365">Último año</option>
            <option value="all">Todas las actividades</option>
        </select>
    </div>

    {{-- Cumplimiento Global --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                    <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900">Cumplimiento Global</span>
                <span class="ml-auto text-2xl font-bold {{ $compliancePercentage >= 95 ? 'text-green-600' : ($compliancePercentage >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ number_format($compliancePercentage, 1) }}%
                </span>
            </div>
        </x-slot:header>

        <div class="w-full bg-zinc-200 rounded-full h-3 mb-6">
            <div class="h-3 rounded-full transition-all duration-500 {{ $compliancePercentage >= 95 ? 'bg-green-500' : ($compliancePercentage >= 80 ? 'bg-amber-500' : 'bg-red-500') }}"
                 style="width: {{ $compliancePercentage }}%"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                <div class="text-2xl font-bold text-blue-900">{{ $totalActivities }}</div>
                <div class="text-xs text-blue-600 mt-1 font-medium">Total Actividades</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                <div class="text-2xl font-bold text-red-900">{{ $activitiesWithErrors }}</div>
                <div class="text-xs text-red-600 mt-1 font-medium">Con Errores Críticos</div>
            </div>
            <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                <div class="text-2xl font-bold text-amber-900">{{ $activitiesWithWarnings }}</div>
                <div class="text-xs text-amber-600 mt-1 font-medium">Con Advertencias</div>
            </div>
        </div>
    </x-agro.card>

    {{-- Productos Fitosanitarios --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="shield-exclamation" class="size-4 text-red-600" />
                <span class="font-semibold text-zinc-900">Productos Fitosanitarios</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                <div class="text-2xl font-bold text-blue-900">{{ $totalProducts }}</div>
                <div class="text-xs text-blue-600 mt-1 font-medium">Total Productos</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-xl border border-green-100">
                <div class="text-2xl font-bold text-green-900">{{ $productsWithValidRegistration }}</div>
                <div class="text-xs text-green-600 mt-1 font-medium">Registro Válido</div>
                <div class="text-xs text-green-500 mt-0.5">{{ $productRegistrationPercentage }}%</div>
            </div>
            <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                <div class="text-2xl font-bold text-amber-900">{{ $productsExpiringSoon }}</div>
                <div class="text-xs text-amber-600 mt-1 font-medium">Próximos a Caducar</div>
                <div class="text-xs text-amber-500 mt-0.5">Próximos 30 días</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                <div class="text-2xl font-bold text-red-900">{{ $productsExpiredOrRevoked }}</div>
                <div class="text-xs text-red-600 mt-1 font-medium">Caducados/Revocados</div>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-zinc-700">Estado de Registros</span>
                <span class="text-sm font-bold {{ $productRegistrationPercentage >= 95 ? 'text-green-600' : ($productRegistrationPercentage >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $productRegistrationPercentage }}%
                </span>
            </div>
            <div class="w-full bg-zinc-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-500 {{ $productRegistrationPercentage >= 95 ? 'bg-green-500' : ($productRegistrationPercentage >= 80 ? 'bg-amber-500' : 'bg-red-500') }}"
                     style="width: {{ $productRegistrationPercentage }}%"></div>
            </div>
        </div>

        @if($productsExpiringSoon > 0)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:heading>Productos próximos a caducar</flux:heading>
                <div class="mt-2 space-y-1">
                    @foreach($expiringProducts as $product)
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $product->name }} ({{ $product->registration_number }})</span>
                            <span class="font-medium">{{ $product->registration_expiry_date->format('d/m/Y') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2">
                    <a href="{{ roleRoute('viticulturist.phytosanitary-products.index') }}" class="text-sm text-amber-700 underline hover:text-amber-900">
                        Ver todos los productos →
                    </a>
                </div>
            </flux:callout>
        @endif
    </x-agro.card>

    {{-- Actividades Bloqueadas --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="lock-closed" class="size-4 text-zinc-600" />
                <span class="font-semibold text-zinc-900">Actividades Bloqueadas</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="text-center p-4 bg-zinc-50 rounded-xl border border-zinc-200">
                <div class="text-2xl font-bold text-zinc-900">{{ $totalLockedActivities }}</div>
                <div class="text-xs text-zinc-600 mt-1 font-medium">Total Bloqueadas</div>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                <div class="text-2xl font-bold text-blue-900">{{ $lockedActivitiesPercentage }}%</div>
                <div class="text-xs text-blue-600 mt-1 font-medium">% Bloqueadas</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-xl border border-purple-100">
                <div class="text-2xl font-bold text-purple-900">{{ $recentlyLockedActivities->count() }}</div>
                <div class="text-xs text-purple-600 mt-1 font-medium">Bloqueadas Recientemente</div>
                <div class="text-xs text-purple-500 mt-0.5">Últimos 7 días</div>
            </div>
        </div>

        @if($recentlyLockedActivities->count() > 0)
            <div class="border border-zinc-200 rounded-xl overflow-hidden mb-4">
                <div class="bg-zinc-50 px-4 py-2 border-b border-zinc-200">
                    <p class="text-xs font-semibold text-zinc-600 uppercase tracking-wide">Actividades bloqueadas recientemente</p>
                </div>
                <div class="divide-y divide-zinc-100">
                    @foreach($recentlyLockedActivities as $activity)
                        <div class="px-4 py-3 hover:bg-zinc-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-zinc-900">{{ ucfirst($activity->activity_type) }}</span>
                                        <span class="text-xs text-zinc-400">·</span>
                                        <span class="text-xs text-zinc-600">{{ $activity->plot->name ?? 'Sin parcela' }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-400 mt-0.5">
                                        Fecha: {{ $activity->activity_date->format('d/m/Y') }} · Bloqueada: {{ $activity->locked_at->diffForHumans() }}
                                    </p>
                                </div>
                                <x-activity-locked-badge :activity="$activity" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-sm text-zinc-500 text-center py-4">No hay actividades bloqueadas recientemente</p>
        @endif

        <flux:callout variant="info" icon="information-circle">
            Las actividades se bloquean automáticamente después de 7 días para garantizar el cumplimiento PAC y prevenir modificaciones retroactivas.
        </flux:callout>
    </x-agro.card>

    {{-- Validación de Cosechas --}}
    @if($totalHarvests > 0)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="scissors" class="size-4 text-purple-600" />
                    <span class="font-semibold text-zinc-900">Validación de Plazos de Seguridad en Cosechas</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="text-2xl font-bold text-blue-900">{{ $totalHarvests }}</div>
                    <div class="text-xs text-blue-600 mt-1 font-medium">Total Cosechas</div>
                    <div class="text-xs text-blue-500 mt-0.5">En el período seleccionado</div>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                    <div class="text-2xl font-bold text-red-900">{{ $harvestsWithWithdrawalIssues }}</div>
                    <div class="text-xs text-red-600 mt-1 font-medium">Con Errores de Plazo</div>
                    @if($harvestsWithWithdrawalIssues > 0)
                        <div class="text-xs text-red-500 mt-0.5">Requieren atención</div>
                    @endif
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="text-2xl font-bold text-amber-900">{{ $harvestsWithWarnings }}</div>
                    <div class="text-xs text-amber-600 mt-1 font-medium">Con Advertencias</div>
                    <div class="text-xs text-amber-500 mt-0.5">Cerca del límite</div>
                </div>
            </div>

            @if($harvestsWithWithdrawalIssues > 0)
                <flux:callout variant="danger" icon="x-circle">
                    <flux:heading>Se detectaron {{ $harvestsWithWithdrawalIssues }} cosecha(s) que no cumplen el plazo de seguridad</flux:heading>
                    Estas cosechas se realizaron antes de cumplirse el plazo de seguridad del último tratamiento fitosanitario. Revisa el cuaderno digital para más detalles.
                </flux:callout>
            @else
                <flux:callout variant="success" icon="check-circle">
                    Todas las cosechas cumplen con los plazos de seguridad.
                </flux:callout>
            @endif
        </x-agro.card>
    @endif

    {{-- Cumplimiento por Tipo de Actividad --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="squares-2x2" class="size-4 text-agro-600" />
                <span class="font-semibold text-zinc-900">Cumplimiento por Tipo de Actividad</span>
            </div>
        </x-slot:header>

        @if(empty($statsByType))
            <p class="text-zinc-500 text-center py-8">No hay actividades registradas en este período</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($statsByType as $type => $stats)
                    @php
                        $typeLabels = [
                            'phytosanitary' => 'Fitosanitarios',
                            'irrigation'    => 'Riegos',
                            'fertilization' => 'Fertilizaciones',
                            'harvest'       => 'Cosechas',
                            'cultural'      => 'Labores Culturales',
                            'observation'   => 'Observaciones',
                        ];
                    @endphp
                    <div class="border border-zinc-200 rounded-xl p-4 hover:shadow-md hover:border-agro-300 transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-zinc-700">{{ $typeLabels[$type] ?? ucfirst($type) }}</h3>
                            <span class="text-base font-bold {{ $stats['percentage'] >= 95 ? 'text-green-600' : ($stats['percentage'] >= 80 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($stats['percentage'], 1) }}%
                            </span>
                        </div>
                        <div class="w-full bg-zinc-200 rounded-full h-2 mb-3">
                            <div class="h-2 rounded-full {{ $stats['percentage'] >= 95 ? 'bg-green-500' : ($stats['percentage'] >= 80 ? 'bg-amber-500' : 'bg-red-500') }}"
                                 style="width: {{ $stats['percentage'] }}%"></div>
                        </div>
                        <div class="text-xs text-zinc-600 space-y-1">
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
    </x-agro.card>

    {{-- Errores Críticos --}}
    @if(count($criticalErrors) > 0)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="x-circle" class="size-4 text-red-600" />
                        <span class="font-semibold text-red-800">Errores Críticos PAC</span>
                    </div>
                    <flux:badge color="red" size="sm">
                        {{ count($criticalErrors) }} {{ count($criticalErrors) === 1 ? 'error' : 'errores' }}
                    </flux:badge>
                </div>
            </x-slot:header>

            <div class="space-y-3">
                @foreach($criticalErrors as $error)
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <p class="text-sm font-medium text-red-800">
                                Actividad #{{ $error['activity_id'] }} — {{ ucfirst($error['activity_type']) }}
                            </p>
                            <span class="text-xs text-red-600 shrink-0">{{ $error['activity_date'] }}</span>
                        </div>
                        <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                            @foreach($error['errors'] as $errorMsg)
                                <li>{{ $errorMsg }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @if($activitiesWithErrors > 10)
                <p class="text-sm text-zinc-500 mt-4 text-center">
                    Mostrando 10 de {{ $activitiesWithErrors }} errores.
                    <a href="{{ roleRoute('viticulturist.digital-notebook') }}" class="text-agro-600 hover:underline font-medium">Ver todas las actividades</a>
                </p>
            @endif
        </x-agro.card>
    @endif

    {{-- Advertencias --}}
    @if(count($warnings) > 0)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="exclamation-triangle" class="size-4 text-amber-600" />
                        <span class="font-semibold text-amber-800">Advertencias PAC</span>
                    </div>
                    <flux:badge color="yellow" size="sm">
                        {{ count($warnings) }} {{ count($warnings) === 1 ? 'advertencia' : 'advertencias' }}
                    </flux:badge>
                </div>
            </x-slot:header>

            <div class="space-y-3">
                @foreach($warnings as $warning)
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <p class="text-sm font-medium text-amber-800">
                                Actividad #{{ $warning['activity_id'] }} — {{ ucfirst($warning['activity_type']) }}
                            </p>
                            <span class="text-xs text-amber-600 shrink-0">{{ $warning['activity_date'] }}</span>
                        </div>
                        <ul class="text-sm text-amber-700 list-disc list-inside space-y-0.5">
                            @foreach($warning['warnings'] as $warningMsg)
                                <li>{{ $warningMsg }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            @if($activitiesWithWarnings > 10)
                <p class="text-sm text-zinc-500 mt-4 text-center">
                    Mostrando 10 de {{ $activitiesWithWarnings }} advertencias.
                </p>
            @endif
        </x-agro.card>
    @endif

    {{-- Éxito total --}}
    @if($compliancePercentage >= 95 && $activitiesWithErrors === 0)
        <flux:callout variant="success" icon="check-circle">
            <flux:heading>¡Excelente Cumplimiento PAC!</flux:heading>
            Tu cuaderno digital cumple con todos los requisitos normativos. Todas tus actividades están correctamente registradas y listas para auditorías PAC.
        </flux:callout>
    @endif

</div>
