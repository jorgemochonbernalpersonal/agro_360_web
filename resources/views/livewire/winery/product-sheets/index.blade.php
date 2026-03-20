<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Fichas Técnicas"
        description="Resumen de calidad analítica y sensorial de cada vino"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('tasting-notes.create') }}" variant="ghost" icon="plus" size="sm">
                Nueva cata
            </flux:button>
            <flux:button href="{{ roleRoute('wines.create') }}" variant="primary" icon="plus">
                Nuevo vino
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div x-data="{
        open: localStorage.getItem('product-sheets-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('product-sheets-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total vinos"
                :value="$stats['total']"
                icon="beaker"
                color="zinc"
            />
            <x-agro.stat-card
                label="En elaboración"
                :value="$stats['active']"
                icon="play-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="Embotellados"
                :value="$stats['bottled']"
                icon="archive-box-arrow-down"
                color="zinc"
            />
            <x-agro.stat-card
                label="Con analítica"
                :value="$stats['with_analysis']"
                icon="chart-bar"
                color="amber"
            />
        </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar vino..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($wineTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-44">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach($statuses as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $statusFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        <div class="space-y-4">
            @for($i = 0; $i < 5; $i++)
                <div class="h-36 bg-zinc-100 rounded-2xl animate-pulse"></div>
            @endfor
        </div>
    </div>

    {{-- Lista --}}
    <div wire:loading.remove wire:target="search, typeFilter, statusFilter, clearFilters, nextPage, previousPage">
        @if($wines->count() > 0)
            <div class="space-y-4">
                @foreach($wines as $wine)
                    @php
                        $analysis = $wine->analyses->first();
                        $tasting  = $wine->tastingNotes->first();
                        $delay    = min($loop->index * 40, 300);
                        $statusColors = ['in_progress' => 'yellow', 'aged' => 'blue', 'bottled' => 'green', 'sold' => 'zinc', 'cancelled' => 'red'];
                    @endphp
                    <x-agro.card wire:key="sheet-{{ $wine->id }}" class="animate-fade-in-up" style="animation-delay: {{ $delay }}ms;">
                        <div class="flex flex-col md:flex-row md:items-start gap-6">

                            {{-- Identidad --}}
                            <div class="md:w-52 shrink-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-bold text-zinc-900 text-base">{{ $wine->name }}</h3>
                                </div>
                                <div class="space-y-1 text-sm">
                                    @if($wine->vintage)
                                        <p class="text-zinc-500">Cosecha <span class="text-zinc-700 font-medium">{{ $wine->vintage }}</span></p>
                                    @endif
                                    <p class="text-zinc-500">{{ $wine->type_label }}</p>
                                    @if($wine->aging_type_label)
                                        <p class="text-zinc-500">{{ $wine->aging_type_label }}</p>
                                    @endif
                                    @if($wine->category)
                                        <flux:badge color="zinc" size="sm">{{ $wine->category_label }}</flux:badge>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    <flux:badge color="{{ $statusColors[$wine->status] ?? 'zinc' }}" size="sm">
                                        {{ $wine->status_label }}
                                    </flux:badge>
                                </div>
                            </div>

                            {{-- Analítica --}}
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">
                                    Analítica
                                    @if($analysis)
                                        <span class="font-normal normal-case ml-1">({{ $analysis->analysis_date->format('d/m/Y') }})</span>
                                    @endif
                                </p>
                                @if($analysis)
                                    <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                                        @php
                                            $analyticsData = [
                                                ['label' => 'Alcohol',     'value' => $analysis->alcohol,           'unit' => '%vol'],
                                                ['label' => 'Ac. total',   'value' => $analysis->total_acidity,     'unit' => 'g/L'],
                                                ['label' => 'Ac. volátil', 'value' => $analysis->volatile_acidity,  'unit' => 'g/L'],
                                                ['label' => 'pH',          'value' => $analysis->ph,                'unit' => ''],
                                                ['label' => 'SO₂ libre',   'value' => $analysis->so2_free,          'unit' => 'mg/L'],
                                                ['label' => 'SO₂ total',   'value' => $analysis->so2_total,         'unit' => 'mg/L'],
                                            ];
                                        @endphp
                                        @foreach($analyticsData as $item)
                                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                                <p class="text-[10px] text-zinc-400 uppercase tracking-wide">{{ $item['label'] }}</p>
                                                <p class="text-sm font-bold text-zinc-700 mt-0.5">
                                                    @if($item['value'] !== null)
                                                        {{ $item['value'] }}
                                                        @if($item['unit'])<span class="text-[10px] font-normal text-zinc-400"> {{ $item['unit'] }}</span>@endif
                                                    @else
                                                        <span class="text-zinc-300">—</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-400 italic">Sin análisis registrado.</p>
                                @endif
                            </div>

                            {{-- Cata --}}
                            <div class="md:w-56 shrink-0">
                                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">
                                    Última cata
                                    @if($tasting)
                                        <span class="font-normal normal-case ml-1">({{ $tasting->evaluation_date->format('d/m/Y') }})</span>
                                    @endif
                                </p>
                                @if($tasting)
                                    <div class="space-y-2">
                                        @if($tasting->overall_score !== null)
                                            <div class="flex items-center gap-2">
                                                <span class="text-3xl font-black text-zinc-800">{{ number_format($tasting->overall_score, 1) }}</span>
                                                <span class="text-xs text-zinc-400">/100</span>
                                            </div>
                                        @endif
                                        @if($tasting->visual_color)
                                            <p class="text-sm text-zinc-600"><span class="text-zinc-400">Color:</span> {{ $tasting->visual_color }}</p>
                                        @endif
                                        @if($tasting->aroma_descriptors)
                                            <p class="text-sm text-zinc-600 line-clamp-2">{{ $tasting->aroma_descriptors }}</p>
                                        @endif
                                        @if($tasting->overall_conclusion)
                                            <p class="text-xs text-zinc-400 italic line-clamp-2">{{ $tasting->overall_conclusion }}</p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-400 italic">Sin cata registrada.</p>
                                @endif
                            </div>

                            {{-- Acciones --}}
                            <div class="flex md:flex-col gap-2 md:items-end shrink-0">
                                <a href="{{ roleRoute('wines.edit', $wine) }}">
                                    <flux:button variant="ghost" size="sm" icon="pencil">Editar</flux:button>
                                </a>
                                <a href="{{ roleRoute('tasting-notes.create') }}?wine_id={{ $wine->id }}">
                                    <flux:button variant="ghost" size="sm" icon="plus">Añadir cata</flux:button>
                                </a>
                            </div>

                        </div>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">{{ $wines->links() }}</div>
        @else
            <x-agro.empty-state
                icon="document-text"
                title="{{ $search || $typeFilter || $statusFilter ? 'Ningún vino coincide con los filtros' : 'No hay vinos' }}"
                description="{{ $search || $typeFilter || $statusFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Crea tu primer vino para ver su ficha técnica aquí.' }}"
            >
                @if($search || $typeFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('wines.create') }}" variant="primary" icon="plus">Nuevo vino</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

