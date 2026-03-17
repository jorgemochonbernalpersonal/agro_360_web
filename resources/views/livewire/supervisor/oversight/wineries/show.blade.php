<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mb-1">
                <a href="{{ route('supervisor.oversight.wineries.index') }}" wire:navigate
                   class="hover:text-zinc-600 transition">Supervisión — Bodegas</a>
                <flux:icon icon="chevron-right" class="size-3" />
                <span class="text-zinc-600">{{ $winery->name }}</span>
            </div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ $winery->name }}</h1>
            @if($winery->email)
                <p class="text-sm text-zinc-400 mt-0.5">{{ $winery->email }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($winery->can_login)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    <span class="size-1.5 rounded-full bg-green-500"></span>
                    Activa
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                    <span class="size-1.5 rounded-full bg-zinc-400"></span>
                    Sin acceso
                </span>
            @endif
        </div>
    </div>

    {{-- Stats de la vendimia actual --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$viticulturistRelations->count()"
            icon="users"
            color="blue"
        />
        <x-agro.stat-card
            :label="'Uva recibida ' . $currentVintage"
            :value="number_format($vintageStats->total_kg ?? 0, 0, ',', '.') . ' kg'"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            :label="'Recepciones ' . $currentVintage"
            :value="$vintageStats->reception_count ?? 0"
            icon="inbox"
            color="yellow"
        />
        <x-agro.stat-card
            label="Contenedores"
            :value="$containerCount"
            icon="beaker"
            color="purple"
        />
    </div>

    {{-- Viticultores asignados por la DO --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="users" class="size-4 text-blue-500" />
                <span>Viticultores asignados por esta DO</span>
            </div>
        </x-slot:header>

        @if($viticulturistRelations->isEmpty())
            <x-agro.empty-state
                icon="users"
                title="Sin viticultores asignados"
                description="Esta bodega no tiene viticultores aportados por tu denominación."
            />
        @else
            <x-agro.data-table
                :headers="['Viticultor', 'Parcelas activas', 'Superficie (ha)', 'Última actividad']"
            >
                @foreach($viticulturistRelations as $rel)
                    @php
                        $vit   = $rel->viticulturist;
                        $stats = $plotStatsByVit[$vit->id] ?? null;
                        $last  = $lastActivityByVit[$vit->id] ?? null;
                    @endphp
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <div class="flex items-center gap-2">
                                <div class="size-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="user" class="size-3.5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-800 text-sm">{{ $vit->name }}</p>
                                    @if($vit->email && !str_starts_with($vit->email, 'viticultores.'))
                                        <p class="text-xs text-zinc-400">{{ $vit->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $stats?->plot_count ?? '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($stats?->total_area)
                                {{ number_format($stats->total_area, 2, ',', '.') }} ha
                            @else
                                —
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($last)
                                <span class="text-zinc-500">
                                    {{ \Carbon\Carbon::parse($last)->translatedFormat('d M Y') }}
                                </span>
                            @else
                                <span class="text-zinc-300">Sin actividad</span>
                            @endif
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        @endif
    </x-agro.card>

    {{-- Recepciones de vendimia --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Últimas recepciones --}}
        <div class="lg:col-span-2">
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="inbox-arrow-down" class="size-4 text-agro-500" />
                            <span>Últimas recepciones</span>
                        </div>
                        @if($vintageStats?->avg_baume > 0)
                            <span class="text-xs text-zinc-400">
                                Grado medio: {{ number_format($vintageStats->avg_baume, 1, ',', '.') }} °Bé
                            </span>
                        @endif
                    </div>
                </x-slot:header>

                @if($recentReceptions->isEmpty())
                    <x-agro.empty-state
                        icon="inbox"
                        title="Sin recepciones registradas"
                        description="Esta bodega no tiene recepciones de vendimia en el sistema."
                    />
                @else
                    <div class="divide-y divide-zinc-100">
                        @foreach($recentReceptions as $reception)
                            <div class="flex items-center justify-between px-4 py-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <div class="text-zinc-400 text-xs w-20 shrink-0">
                                        {{ \Carbon\Carbon::parse($reception->harvest_start_date)->format('d/m/Y') }}
                                    </div>
                                    <div>
                                        <span class="font-medium text-zinc-800">
                                            {{ number_format($reception->total_weight, 0, ',', '.') }} kg
                                        </span>
                                        @if($reception->vintage)
                                            <span class="text-xs text-zinc-400 ml-1">Vend. {{ $reception->vintage }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($reception->baume_degree)
                                        <span class="text-xs text-zinc-500">
                                            {{ number_format($reception->baume_degree, 1, ',', '.') }} °Bé
                                        </span>
                                    @endif
                                    @if($reception->status === 'cancelled')
                                        <x-agro.status-badge status="cancelled" label="Cancelada" color="red" />
                                    @elseif($reception->status === 'disputed')
                                        <x-agro.status-badge status="disputed" label="En disputa" color="yellow" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.card>
        </div>

        {{-- Desglose por variedad --}}
        <div>
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="chart-pie" class="size-4 text-purple-500" />
                        <span>Por variedad ({{ $currentVintage }})</span>
                    </div>
                </x-slot:header>

                @if($varietyBreakdown->isEmpty())
                    <p class="text-sm text-zinc-400 px-4 py-6 text-center">Sin datos de variedad</p>
                @else
                    @php $totalKg = $varietyBreakdown->sum('total_kg'); @endphp
                    <div class="divide-y divide-zinc-100">
                        @foreach($varietyBreakdown as $row)
                            @php $pct = $totalKg > 0 ? round(($row->total_kg / $totalKg) * 100) : 0; @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-zinc-700">{{ $row->variety }}</span>
                                    <span class="text-xs text-zinc-400">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                    <div class="bg-agro-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-xs text-zinc-400 mt-1">
                                    {{ number_format($row->total_kg, 0, ',', '.') }} kg · {{ $row->receptions }} recep.
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-agro.card>
        </div>

    </div>

</div>
