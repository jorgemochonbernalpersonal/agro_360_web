<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Estadísticas"
        description="Métricas agregadas de producción, superficie y actividad en la denominación de origen."
    />

    {{-- Stat cards --}}
    <x-agro.stats-section key="supervisor-statistics">
        <x-agro.stat-card
            label="Bodegas"
            :value="$totalWineries"
            icon="building-office-2"
            color="blue"
        />
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$totalViticulturists"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            label="Superficie (ha)"
            :value="number_format($totalPlotAreaHa, 2)"
            icon="map"
            color="yellow"
        />
        <x-agro.stat-card
            label="Uva {{ $currentYear }} (kg)"
            :value="number_format($totalKgCurrentVintage, 0, ',', '.')"
            icon="scale"
            color="orange"
            :description="'Vendimia ' . $currentYear"
        />
    </x-agro.stats-section>

    {{-- Harvest by vintage cards --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-700 mb-3">Histórico de vendimias</h2>

        @if(count($harvestByVintage) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($harvestByVintage as $row)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="vintage-{{ $row->vintage }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="calendar" class="size-5 text-amber-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900">Vendimia {{ $row->vintage }}</h3>
                                    <p class="text-xs text-zinc-400">Datos de campaña</p>
                                </div>
                                @if($row->vintage === $currentYear)
                                    <flux:badge color="green" size="sm" class="shrink-0">Actual</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-amber-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">Recepciones</p>
                                    <p class="text-sm font-bold text-amber-700">{{ $row->reception_count }}</p>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">Uva (kg)</p>
                                    <p class="text-sm font-bold text-emerald-700">
                                        {{ number_format($row->total_kg, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">Brix</p>
                                    <p class="text-sm font-bold text-blue-700">
                                        @if($row->avg_brix)
                                            {{ $row->avg_brix }}°
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </x-agro.card>
                @endforeach
            </div>
        @else
            <x-agro.empty-state
                icon="calendar"
                title="Sin datos"
                description="No hay datos de vendimias aún."
            />
        @endif
    </div>

</div>
