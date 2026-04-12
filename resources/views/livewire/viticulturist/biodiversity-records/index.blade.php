<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Biodiversidad y Cubiertas"
        description="Registro de elementos de biodiversidad, cubiertas vegetales y estructuras ecol&oacute;gicas para eco-esquemas PAC"
        icon="sparkles"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
                Nuevo
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('biodiversity-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('biodiversity-stats-open', String(this.open));
        }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>Estad&iacute;sticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-agro.stat-card
                    label="Registros totales"
                    :value="$stats['total']"
                    :description="$stats['total'] . ' registros'"
                    icon="sparkles"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Superficie total"
                    :value="number_format($stats['total_area_m2'], 2, ',', '.') . ' m&sup2;'"
                    description="Superficie documentada"
                    icon="map"
                    color="green"
                />
                <x-agro.stat-card
                    label="Tipos utilizados"
                    :value="$stats['types_count']"
                    :description="$stats['types_count'] . ' tipos distintos'"
                    icon="tag"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Parcelas cubiertas"
                    :value="$stats['plots_count']"
                    :description="$stats['plots_count'] . ' parcelas'"
                    icon="map-pin"
                    color="amber"
                />
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterRecordType) + (int) !empty($filterPlot) + (int) !empty($filterCampaign);
    @endphp
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por descripci&oacute;n, especies, notas..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>
        <button
            x-on:click="$dispatch('open-modal', 'biodiversity-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
            @endif
        </button>
        <div class="w-px h-6 bg-zinc-200"></div>
        <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>
    </div>

    {{-- Chips filtros activos --}}
    @if($filterRecordType || $filterPlot || $filterCampaign)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterRecordType)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="sparkles" class="size-3" />
                    {{ $recordTypes[$filterRecordType] ?? $filterRecordType }}
                    <button wire:click="$set('filterRecordType', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterPlot)
                @php $plotObj = $plots->firstWhere('id', $filterPlot); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map-pin" class="size-3" />
                    {{ $plotObj?->name ?? $filterPlot }}
                    <button wire:click="$set('filterPlot', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar-days" class="size-3" />
                    {{ $camp?->name ?? $filterCampaign }}
                    <button wire:click="$set('filterCampaign', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <div wire:loading wire:target="search, filterRecordType, filterPlot, filterCampaign, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, filterRecordType, filterPlot, filterCampaign, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="sparkles"
                title="Sin registros de biodiversidad"
                description="{{ $search || $filterRecordType || $filterPlot || $filterCampaign ? 'Ning&uacute;n registro coincide con los filtros aplicados.' : 'Documenta cubiertas vegetales, setos, fauna auxiliar y otros elementos de biodiversidad.' }}"
            >
                @if(!$search && !$filterRecordType && !$filterPlot && !$filterCampaign)
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.biodiversity-records.create') }}" variant="primary" icon="plus">
                            Nuevo Registro
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $color = $entry->record_type_color;
                        $colorClasses = match($color) {
                            'green'   => ['bg' => 'bg-green-100',   'icon' => 'text-green-600',   'badge' => 'bg-green-100 text-green-700'],
                            'amber'   => ['bg' => 'bg-amber-100',   'icon' => 'text-amber-600',   'badge' => 'bg-amber-100 text-amber-700'],
                            'emerald' => ['bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700'],
                            'blue'    => ['bg' => 'bg-blue-100',    'icon' => 'text-blue-600',    'badge' => 'bg-blue-100 text-blue-700'],
                            'violet'  => ['bg' => 'bg-violet-100',  'icon' => 'text-violet-600',  'badge' => 'bg-violet-100 text-violet-700'],
                            'orange'  => ['bg' => 'bg-orange-100',  'icon' => 'text-orange-600',  'badge' => 'bg-orange-100 text-orange-700'],
                            'cyan'    => ['bg' => 'bg-cyan-100',    'icon' => 'text-cyan-600',    'badge' => 'bg-cyan-100 text-cyan-700'],
                            default   => ['bg' => 'bg-zinc-100',    'icon' => 'text-zinc-400',    'badge' => 'bg-zinc-100 text-zinc-500'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="bio-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 {{ $colorClasses['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon icon="{{ $entry->record_type_icon }}" class="size-5 {{ $colorClasses['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $colorClasses['badge'] }}">
                                        {{ $entry->record_type_label }}
                                    </span>
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            {{-- Parcela + Fecha --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center gap-2 text-sm text-zinc-700">
                                    <flux:icon icon="map-pin" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate font-medium">{{ $entry->plot->name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                                    <span class="text-xs">{{ $entry->record_date->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            {{-- Superficie --}}
                            @if($entry->area_m2)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                    <span>{{ number_format($entry->area_m2, 2, ',', '.') }} m&sup2;</span>
                                </div>
                            @endif

                            {{-- Especies --}}
                            @if($entry->species)
                                <div class="flex items-start gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="leaf" class="size-4 text-zinc-400 shrink-0 mt-0.5" />
                                    <span class="line-clamp-2">{{ $entry->species }}</span>
                                </div>
                            @endif

                            {{-- Descripcion --}}
                            @if($entry->description)
                                <p class="text-xs text-zinc-500 line-clamp-2">{{ $entry->description }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('viticulturist.biodiversity-records.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button wire:click="delete({{ $entry->id }})"
                                        wire:confirm="&iquest;Eliminar este registro permanentemente?"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="biodiversity-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'biodiversity-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de registro</label>
                <flux:select wire:model.live="filterRecordType">
                    <flux:select.option value="">Todos los tipos</flux:select.option>
                    @foreach($recordTypes as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="filterPlot">
                    <flux:select.option value="">Todas las parcelas</flux:select.option>
                    @foreach($plots as $plot)
                        <flux:select.option value="{{ $plot->id }}">{{ $plot->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campa&ntilde;a</label>
                <flux:select wire:model.live="filterCampaign">
                    <flux:select.option value="">Todas las campa&ntilde;as</flux:select.option>
                    @foreach($campaigns as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterRecordType || $filterPlot || $filterCampaign)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar filtros</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'biodiversity-filters')" variant="primary">Aplicar</flux:button>
        </div>
    </x-agro.modal>

</div>
