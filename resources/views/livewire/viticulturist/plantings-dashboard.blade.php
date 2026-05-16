<div>
@if($totalPlantings === 0)
    <x-agro.empty-state
        icon="leaf"
        title="Sin plantaciones registradas"
        description="Añade plantaciones a tus parcelas para el cumplimiento PAC y la trazabilidad."
    >
        <flux:button href="{{ route('plots.plantings.index') }}" wire:navigate icon="plus">
            Añadir plantaciones
        </flux:button>
    </x-agro.empty-state>
@else
    <div class="space-y-6">

        {{-- Métricas principales --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-agro.stat-card
                label="Total Plantaciones"
                :value="$totalPlantings"
                :description="$varieties . ' variedades'"
                icon="leaf"
                color="green"
            />
            <x-agro.stat-card
                label="Superficie Plantada"
                :value="$totalSurface . ' ha'"
                description="hectáreas declaradas"
                icon="map"
                color="blue"
            />
            <x-agro.stat-card
                label="Cumplimiento PAC"
                :value="$authorizationPercentage . '%'"
                :description="$withAuthorization . '/' . $needsAuthorization . ' autorizadas'"
                icon="shield-check"
                :color="$authorizationPercentage >= 90 ? 'green' : 'red'"
            />
            @if($missingAuthorization > 0)
                <x-agro.stat-card
                    label="Sin Autorización"
                    :value="$missingAuthorization"
                    description="requieren atención"
                    icon="exclamation-triangle"
                    color="red"
                />
            @endif
        </div>

        {{-- Distribución por Estado --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="squares-2x2" class="size-4 text-agro-600" />
                    <span class="font-semibold text-zinc-900">Distribución por Estado</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach(['active' => ['label' => 'Activa', 'icon' => 'check-circle', 'color' => 'green'],
                          'removed' => ['label' => 'Arrancada', 'icon' => 'x-circle', 'color' => 'zinc'],
                          'experimental' => ['label' => 'Experimental', 'icon' => 'beaker', 'color' => 'blue'],
                          'replanting' => ['label' => 'Replantación', 'icon' => 'arrow-path', 'color' => 'amber']] as $key => $cfg)
                    @php $stats = $statusStats->get($key, ['count' => 0, 'surface' => 0]); @endphp
                    <x-agro.stat-card
                        :label="$cfg['label']"
                        :value="$stats['count']"
                        :description="round($stats['surface'], 2) . ' ha'"
                        :icon="$cfg['icon']"
                        :color="$cfg['color']"
                    />
                @endforeach
            </div>
        </x-agro.card>

        {{-- Distribución por Edad --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="clock" class="size-4 text-agro-600" />
                    <span class="font-semibold text-zinc-900">Distribución por Edad</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                @foreach([
                    ['label' => 'Jóvenes (< 3 años)', 'key' => 'joven',      'color' => 'text-green-600', 'sub' => '20-40% productividad'],
                    ['label' => 'Desarrollo (3-8)',    'key' => 'desarrollo',  'color' => 'text-blue-600',  'sub' => '60-80% productividad'],
                    ['label' => 'Productivas (8-25)',  'key' => 'productiva',  'color' => 'text-green-700', 'sub' => '100% productividad',  'highlight' => true],
                    ['label' => 'Maduras (25-40)',     'key' => 'madura',      'color' => 'text-amber-600', 'sub' => '80-90% productividad'],
                    ['label' => 'Viejas (> 40)',       'key' => 'vieja',       'color' => 'text-red-600',   'sub' => 'Replantación'],
                ] as $row)
                    <div class="border rounded-xl p-4 {{ isset($row['highlight']) ? 'bg-green-50 border-green-200' : 'border-zinc-200' }}">
                        <p class="text-xs font-medium text-zinc-500">{{ $row['label'] }}</p>
                        <p class="text-2xl font-bold {{ $row['color'] }} mt-1">{{ $ageStats[$row['key']] }}</p>
                        <p class="text-xs text-zinc-400 mt-1">{{ $row['sub'] }}</p>
                    </div>
                @endforeach
            </div>

            @if($needsReplanting > 0)
                <div class="mt-4">
                    <x-agro.alert-banner
                        tone="warning"
                        icon="exclamation-circle"
                        :message="$needsReplanting . ' plantación(es) necesitan replantación (> 35 años o en declive).'"
                    />
                </div>
            @endif
        </x-agro.card>

        {{-- Certificaciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="academic-cap" class="size-4 text-agro-600" />
                    <span class="font-semibold text-zinc-900">Certificaciones</span>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-agro.stat-card
                    label="Plantaciones Certificadas"
                    :value="$certifiedPlantings"
                    :description="'de ' . $totalPlantings . ' totales'"
                    icon="academic-cap"
                    color="purple"
                />
                <x-agro.stat-card
                    label="Total Certificaciones"
                    :value="$totalCertifications"
                    description="activas"
                    icon="document-check"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Próximas a Vencer"
                    :value="$expiringCertifications"
                    description="en 30 días"
                    icon="clock"
                    :color="$expiringCertifications > 0 ? 'amber' : 'green'"
                />
            </div>
        </x-agro.card>

        {{-- Tratamientos Fitosanitarios Recientes --}}
        @if($activeTreatments > 0)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="shield-exclamation" class="size-4 text-agro-600" />
                        <span class="font-semibold text-zinc-900">Tratamientos Fitosanitarios (últimos 30 días)</span>
                    </div>
                </x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-agro.stat-card
                        label="Tratamientos Aplicados"
                        :value="$activeTreatments"
                        description="en el último mes"
                        icon="beaker"
                        color="green"
                    />
                    <x-agro.stat-card
                        label="Plagas/Enfermedades Tratadas"
                        :value="$uniquePests"
                        description="diferentes"
                        icon="bug-ant"
                        color="blue"
                    />
                </div>
            </x-agro.card>
        @endif

        {{-- Alertas de Cumplimiento --}}
        @if($totalAlerts > 0)
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="exclamation-triangle" class="size-4 text-red-600" />
                            <span class="font-semibold text-zinc-900">Alertas de Cumplimiento</span>
                        </div>
                        <flux:badge color="red" size="sm">
                            {{ $totalAlerts }} {{ $totalAlerts === 1 ? 'alerta' : 'alertas' }}
                        </flux:badge>
                    </div>
                </x-slot:header>
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <x-agro.alert-banner
                            :tone="$alert['type'] === 'error' ? 'danger' : 'warning'"
                            icon="exclamation-triangle"
                            :title="$alert['planting'] . ' - ' . $alert['plot']"
                            :message="$alert['message']"
                        />
                    @endforeach
                </div>
            </x-agro.card>
        @else
            <x-agro.alert-banner
                tone="success"
                message="Todas las plantaciones cumplen con los requisitos PAC."
            />
        @endif

    </div>
@endif
</div>
