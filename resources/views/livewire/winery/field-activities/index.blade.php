<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Actividades de Campo"
        description="Registro de actividades agrícolas de tus viticultores vinculados (solo lectura)."
    >
        <x-slot:actions>
            <flux:button variant="ghost" icon="chart-bar" href="{{ route('winery.harvest-summary.index') }}" wire:navigate>
                Cuadro de mando
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Total actividades"
            :value="$stats['total']"
            icon="clipboard-document-list"
            color="zinc"
        />
        <x-agro.stat-card
            label="Vendimias"
            :value="$stats['harvest']"
            icon="archive-box-arrow-down"
            color="agro"
        />
        <x-agro.stat-card
            label="Tratamientos fitosanitarios"
            :value="$stats['phyto']"
            icon="beaker"
            color="amber"
        />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar :active-count="collect([$viticulturistFilter, $activityTypeFilter, $campaignFilter, $plotFilter])->filter()->count()">
        <x-agro.filter-select wire:model.live="viticulturistFilter" label="Viticultor">
            <option value="">Todos</option>
            @foreach($linkedViticulturists as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="activityTypeFilter" label="Tipo">
            <option value="">Todos los tipos</option>
            @foreach($activityTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="campaignFilter" label="Campaña">
            <option value="">Todas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->year }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="plotFilter" label="Parcela">
            <option value="">Todas</option>
            @foreach($plots as $plot)
                <option value="{{ $plot->id }}">{{ $plot->name }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    {{-- Tabla --}}
    @if($activities->isEmpty())
        <x-agro.empty-state
            icon="clipboard-document-list"
            title="Sin actividades registradas"
            description="Tus viticultores vinculados aún no han registrado actividades que coincidan con los filtros aplicados."
        />
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Viticultor', 'Tipo', 'Parcela / Plantación', 'Campaña', 'Fecha', 'Condiciones', 'Notas']">
                @foreach($activities as $act)
                    <x-agro.table-row wire:key="act-{{ $act->id }}">

                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $act->viticulturist?->name ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php
                                $typeColors = [
                                    'harvest'       => 'agro',
                                    'phytosanitary' => 'amber',
                                    'fertilization' => 'blue',
                                    'irrigation'    => 'blue',
                                    'pruning'       => 'violet',
                                    'cultural'      => 'zinc',
                                    'observation'   => 'zinc',
                                    'phenology'     => 'violet',
                                    'post_harvest'  => 'zinc',
                                ];
                                $tc = $typeColors[$act->activity_type] ?? 'zinc';
                                $tl = $activityTypes[$act->activity_type] ?? $act->activity_type;
                            @endphp
                            <x-agro.status-badge :color="$tc" :label="$tl" />
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <div class="font-medium text-zinc-900">{{ $act->plot?->name ?? '—' }}</div>
                            @if($act->plotPlanting?->grapeVariety)
                                <div class="text-xs text-zinc-400">{{ $act->plotPlanting->grapeVariety->name }}</div>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-600">{{ $act->campaign?->year ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-zinc-700">{{ $act->activity_date?->format('d/m/Y') ?? '—' }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($act->weather_conditions || $act->temperature)
                                <div class="text-sm text-zinc-600">
                                    @if($act->weather_conditions) {{ $act->weather_conditions }} @endif
                                    @if($act->temperature) · {{ $act->temperature }}°C @endif
                                </div>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($act->notes)
                                <span class="text-xs text-zinc-500 line-clamp-2">{{ $act->notes }}</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </x-agro.table-cell>

                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$activities" />
    @endif

</div>
