<div class="space-y-8 animate-fade-in">

    <x-agro.page-header
        title="Estadísticas"
        description="Métricas agregadas de producción, superficie y actividad en la denominación de origen."
    />

    {{-- Top-level stat cards --}}
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

    {{-- Pool composition + notebook access --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Pool composition --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="users" class="size-4 text-agro-500" />
                    <span>Composición del pool</span>
                </div>
            </x-slot:header>

            @if($totalViticulturists > 0)
                <div class="space-y-3">
                    @foreach([
                        'active'  => ['label' => 'Activos',   'color' => 'bg-emerald-500'],
                        'invited' => ['label' => 'Invitados', 'color' => 'bg-amber-400'],
                        'ghost'   => ['label' => 'Ghost',     'color' => 'bg-zinc-300'],
                    ] as $key => $meta)
                        @php
                            $count = $poolComposition[$key];
                            $pct   = $totalViticulturists > 0 ? round(($count / $totalViticulturists) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-zinc-600">{{ $meta['label'] }}</span>
                                <span class="font-semibold text-zinc-800">{{ $count }} <span class="text-xs font-normal text-zinc-400">({{ $pct }}%)</span></span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-2">
                                <div class="{{ $meta['color'] }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-sm">
                        <span class="text-zinc-500 flex items-center gap-1">
                            <flux:icon icon="book-open" class="size-3.5 text-agro-500" />
                            Acceso cuaderno concedido
                        </span>
                        <span class="font-semibold text-agro-700">
                            {{ $notebookAccessCount }}
                            <span class="text-xs font-normal text-zinc-400">
                                / {{ $totalViticulturists }}
                            </span>
                        </span>
                    </div>
                </div>
            @else
                <p class="text-sm text-zinc-400 py-4 text-center">Sin viticultores en el pool.</p>
            @endif
        </x-agro.card>

        {{-- Organic plots --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon icon="sparkles" class="size-4 text-emerald-500" />
                    <span>Agricultura ecológica</span>
                </div>
            </x-slot:header>

            @if(($plotStats->total_plots ?? 0) > 0)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="text-center flex-1">
                            <p class="text-3xl font-bold text-emerald-600">{{ $organicPct }}%</p>
                            <p class="text-xs text-zinc-400 mt-0.5">parcelas ecológicas</p>
                        </div>
                        <div class="w-px h-12 bg-zinc-100"></div>
                        <div class="text-center flex-1">
                            <p class="text-3xl font-bold text-zinc-700">{{ $plotStats->organic_plots }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">de {{ $plotStats->total_plots }} parcelas</p>
                        </div>
                        <div class="w-px h-12 bg-zinc-100"></div>
                        <div class="text-center flex-1">
                            <p class="text-3xl font-bold text-zinc-700">{{ number_format($plotStats->organic_area, 1) }}</p>
                            <p class="text-xs text-zinc-400 mt-0.5">ha ecológicas</p>
                        </div>
                    </div>
                    <div class="w-full bg-zinc-100 rounded-full h-3">
                        <div class="bg-emerald-400 h-3 rounded-full transition-all" style="width: {{ $organicPct }}%"></div>
                    </div>
                </div>
            @else
                <p class="text-sm text-zinc-400 py-4 text-center">Sin datos de parcelas.</p>
            @endif
        </x-agro.card>

    </div>

    {{-- Top grape varieties --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-700 mb-3">Variedades principales</h2>

        @if($topVarieties->count() > 0)
            @php
                $maxArea = $topVarieties->max('total_area') ?: 1;
                $colorMap = [
                    'red'     => ['bar' => 'bg-red-400',    'badge' => 'bg-red-50 text-red-700',    'label' => 'Tinto'],
                    'white'   => ['bar' => 'bg-amber-300',  'badge' => 'bg-amber-50 text-amber-700','label' => 'Blanco'],
                    'rose'    => ['bar' => 'bg-pink-300',   'badge' => 'bg-pink-50 text-pink-700',  'label' => 'Rosado'],
                    'default' => ['bar' => 'bg-zinc-400',   'badge' => 'bg-zinc-100 text-zinc-600', 'label' => 'Otro'],
                ];
            @endphp
            <x-agro.card>
                <div class="divide-y divide-zinc-100">
                    @foreach($topVarieties as $i => $variety)
                        @php
                            $pct    = $maxArea > 0 ? round(($variety->total_area / $maxArea) * 100) : 0;
                            $colors = $colorMap[$variety->variety_color] ?? $colorMap['default'];
                        @endphp
                        <div class="py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="w-5 text-xs font-bold text-zinc-400 text-right shrink-0">{{ $i + 1 }}</span>
                                <div class="flex-1 flex items-center gap-2 min-w-0">
                                    <span class="text-sm font-medium text-zinc-800 truncate">{{ $variety->variety_name }}</span>
                                    <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $colors['badge'] }}">
                                        {{ $colors['label'] }}
                                    </span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-semibold text-zinc-700">{{ number_format($variety->total_area, 2) }} ha</span>
                                    <span class="text-xs text-zinc-400 ml-1">({{ $variety->planting_count }} plant.)</span>
                                </div>
                            </div>
                            <div class="ml-8 w-full bg-zinc-100 rounded-full h-1.5">
                                <div class="{{ $colors['bar'] }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-agro.card>
        @else
            <x-agro.empty-state icon="sparkles" title="Sin variedades" description="No hay plantaciones activas con variedad asignada." />
        @endif
    </div>

    {{-- Activity breakdown + Harvest by vintage --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Activity type breakdown --}}
        <div>
            <h2 class="text-sm font-semibold text-zinc-700 mb-3">Actividades {{ $currentYear }}</h2>

            @php
                $activityLabels = [
                    'phytosanitary' => ['label' => 'Fitosanitario',   'icon' => 'beaker',          'color' => 'text-red-500',    'bg' => 'bg-red-50'],
                    'fertilization' => ['label' => 'Fertilización',   'icon' => 'arrow-trending-up','color' => 'text-amber-500',  'bg' => 'bg-amber-50'],
                    'irrigation'    => ['label' => 'Riego',           'icon' => 'fire',             'color' => 'text-blue-500',   'bg' => 'bg-blue-50'],
                    'cultural'      => ['label' => 'Labor cultural',  'icon' => 'wrench-screwdriver','color' => 'text-zinc-500',   'bg' => 'bg-zinc-100'],
                    'pruning'       => ['label' => 'Poda',            'icon' => 'scissors',         'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
                    'harvest'       => ['label' => 'Cosecha',         'icon' => 'archive-box',      'color' => 'text-emerald-500','bg' => 'bg-emerald-50'],
                    'observation'   => ['label' => 'Observación',     'icon' => 'eye',              'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
                    'phenology'     => ['label' => 'Fenología',       'icon' => 'chart-bar',        'color' => 'text-teal-500',   'bg' => 'bg-teal-50'],
                    'post_harvest'  => ['label' => 'Post-vendimia',   'icon' => 'clock',            'color' => 'text-orange-500', 'bg' => 'bg-orange-50'],
                ];
                $totalActivities = $activityBreakdown->sum('total');
            @endphp

            @if($activityBreakdown->count() > 0)
                <x-agro.card>
                    <div class="space-y-2">
                        @foreach($activityBreakdown as $row)
                            @php
                                $meta  = $activityLabels[$row->activity_type] ?? ['label' => ucfirst($row->activity_type), 'icon' => 'ellipsis-horizontal', 'color' => 'text-zinc-400', 'bg' => 'bg-zinc-50'];
                                $pct   = $totalActivities > 0 ? round(($row->total / $totalActivities) * 100) : 0;
                            @endphp
                            <div class="flex items-center gap-3 py-2">
                                <div class="w-7 h-7 rounded-lg {{ $meta['bg'] }} flex items-center justify-center shrink-0">
                                    <flux:icon icon="{{ $meta['icon'] }}" class="size-3.5 {{ $meta['color'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-zinc-700">{{ $meta['label'] }}</span>
                                        <span class="text-sm font-semibold text-zinc-800">{{ $row->total }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-zinc-400 transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @else
                <x-agro.empty-state icon="chart-bar" title="Sin actividades" description="No se han registrado actividades este año." />
            @endif
        </div>

        {{-- Harvest by vintage --}}
        <div>
            <h2 class="text-sm font-semibold text-zinc-700 mb-3">Histórico de vendimias</h2>

            @if($harvestByVintage->count() > 0)
                <div class="space-y-4">
                    @foreach($harvestByVintage as $row)
                        <x-agro.card wire:key="vintage-{{ $row->vintage }}" class="hover:-translate-y-0.5 transition-transform">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="calendar" class="size-5 text-amber-600" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-zinc-900">{{ $row->vintage }}</span>
                                        @if($row->vintage === $currentYear)
                                            <flux:badge color="green" size="sm">Actual</flux:badge>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div>
                                            <p class="text-[10px] text-zinc-400 uppercase tracking-wide">Recepciones</p>
                                            <p class="text-sm font-bold text-zinc-700">{{ $row->reception_count }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-zinc-400 uppercase tracking-wide">Uva (kg)</p>
                                            <p class="text-sm font-bold text-emerald-600">{{ number_format($row->total_kg, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-zinc-400 uppercase tracking-wide">Brix</p>
                                            <p class="text-sm font-bold text-blue-600">{{ $row->avg_brix ? $row->avg_brix . '°' : '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="calendar" title="Sin datos" description="No hay datos de vendimias aún." />
            @endif
        </div>

    </div>

</div>
