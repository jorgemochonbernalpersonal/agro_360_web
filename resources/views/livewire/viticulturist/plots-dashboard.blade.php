<div class="space-y-6">

    {{-- Métricas principales --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total Parcelas"
            :value="$totalPlots"
            :description="$activePlots . ' activas'"
            icon="map-pin"
            color="agro"
        />
        <x-agro.stat-card
            label="Superficie Total"
            :value="$totalSurface . ' ha'"
            description="hectáreas"
            icon="map"
            color="blue"
        />
        <x-agro.stat-card
            label="Admisible PAC"
            :value="$eligibleSurface . ' ha'"
            :description="$eligibilityPercentage . '% del total'"
            icon="shield-check"
            color="purple"
        />
        <x-agro.stat-card
            label="Parcelas Bloqueadas"
            :value="$lockedPlots"
            description="protegidas"
            icon="lock-closed"
            color="amber"
        />
    </div>

    {{-- Alertas de Cumplimiento PAC --}}
    @if($totalAlerts > 0)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon icon="exclamation-triangle" class="size-4 text-red-600" />
                        <span class="font-semibold text-zinc-900">Alertas de Cumplimiento PAC</span>
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
                        :title="$alert['plot']"
                        :message="$alert['message']"
                    />
                @endforeach
            </div>
        </x-agro.card>
    @else
        <x-agro.alert-banner
            tone="success"
            message="Todas las parcelas cumplen con los requisitos PAC."
        />
    @endif

</div>
