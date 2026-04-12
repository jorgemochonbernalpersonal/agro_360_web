<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Análisis de Suelo"
        description="Registro de análisis edafológicos: pH, materia orgánica, nutrientes y textura de tus parcelas"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.soil-analyses.create') }}" variant="primary" icon="plus">
                Nuevo Análisis
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('soil-analyses-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('soil-analyses-stats-open', String(this.open));
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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-agro.stat-card
                    label="Total análisis"
                    :value="$stats['total']"
                    icon="beaker"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Parcelas analizadas"
                    :value="$stats['plots_analyzed']"
                    icon="map"
                    color="blue"
                />
                <x-agro.stat-card
                    label="pH medio"
                    :value="$stats['avg_ph'] ?: '—'"
                    :description="$stats['avg_ph_label']"
                    icon="scale"
                    :color="$stats['avg_ph_color']"
                />
                <x-agro.stat-card
                    label="MO media"
                    :value="$stats['avg_organic_matter'] ? $stats['avg_organic_matter'] . '%' : '—'"
                    description="Materia orgánica"
                    icon="sparkles"
                    color="amber"
                />
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterPlot) + (int) !empty($filterCampaign) + (int) !empty($filterTexture);
    @endphp
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por laboratorio o notas..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        {{-- Filtros --}}
        <button
            x-on:click="$dispatch('open-modal', 'soil-analyses-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if ($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nuevo Análisis --}}
        <flux:button href="{{ roleRoute('viticulturist.soil-analyses.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if ($filterPlot || $filterCampaign || $filterTexture)
        <div class="flex flex-wrap items-center gap-2">
            @if ($filterPlot)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="map" class="size-3" />
                    {{ $plots->firstWhere('id', (int) $filterPlot)?->name ?? $filterPlot }}
                    <button
                        wire:click="$set('filterPlot', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
                    >
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterCampaign)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    {{ $campaigns->firstWhere('id', (int) $filterCampaign)?->name ?? $filterCampaign }}
                    <button
                        wire:click="$set('filterCampaign', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
                    >
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if ($filterTexture)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="beaker" class="size-3" />
                    {{ $textureClasses[$filterTexture] ?? $filterTexture }}
                    <button
                        wire:click="$set('filterTexture', '')"
                        class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors"
                    >
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button
                wire:click="clearFilters"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <div
        wire:loading
        wire:target="search, filterPlot, filterCampaign, filterTexture, nextPage, previousPage, gotoPage"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for ($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div
        wire:loading.remove
        wire:target="search, filterPlot, filterCampaign, filterTexture, nextPage, previousPage, gotoPage"
    >
        @if ($entries->isEmpty())
            <x-agro.empty-state
                icon="beaker"
                title="Sin análisis de suelo"
                description="Registra los resultados de tus análisis edafológicos para llevar un control de la fertilidad de tus parcelas."
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturist.soil-analyses.create') }}" variant="primary" icon="plus">
                        Nuevo Análisis
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $phColor = $entry->ph_color;
                        $phBgClasses = match($phColor) {
                            'red'    => 'bg-red-100 text-red-600',
                            'amber'  => 'bg-amber-100 text-amber-600',
                            'green'  => 'bg-green-100 text-green-600',
                            'blue'   => 'bg-blue-100 text-blue-600',
                            'violet' => 'bg-violet-100 text-violet-600',
                            default  => 'bg-zinc-100 text-zinc-400',
                        };
                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="soil-{{ $entry->id }}"
                    >
                        {{-- Header --}}
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $phBgClasses }}">
                                    <flux:icon icon="beaker" class="size-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->plot->name ?? '—' }}</h3>
                                    <p class="text-xs text-zinc-400">{{ $entry->analysis_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </x-slot:header>

                        {{-- Body --}}
                        <div class="flex-1 space-y-3">

                            {{-- pH --}}
                            <div class="bg-zinc-50 rounded-xl p-3 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">pH</span>
                                    @if ($entry->ph !== null)
                                        <span class="font-bold {{ match($phColor) {
                                            'red'    => 'text-red-600',
                                            'amber'  => 'text-amber-600',
                                            'green'  => 'text-green-600',
                                            'blue'   => 'text-blue-600',
                                            'violet' => 'text-violet-600',
                                            default  => 'text-zinc-600',
                                        } }}">
                                            {{ $entry->ph }} ({{ $entry->ph_label }})
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">Materia orgánica</span>
                                    <span class="font-medium text-zinc-600">{{ $entry->organic_matter !== null ? $entry->organic_matter . '%' : '—' }}</span>
                                </div>
                            </div>

                            {{-- N-P-K --}}
                            @if ($entry->nitrogen_total !== null || $entry->phosphorus !== null || $entry->potassium !== null)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-zinc-400">N-P-K:</span>
                                    <span class="font-medium text-zinc-600">
                                        {{ $entry->nitrogen_total ?? '—' }} /
                                        {{ $entry->phosphorus ?? '—' }} /
                                        {{ $entry->potassium ?? '—' }}
                                    </span>
                                    <span class="text-zinc-300">mg/kg</span>
                                </div>
                            @endif

                            {{-- Textura --}}
                            @if ($entry->texture_class)
                                <div class="flex items-center gap-2 text-xs text-zinc-600">
                                    <flux:icon icon="squares-2x2" class="size-3.5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-400">Textura:</span>
                                    <span class="font-medium">{{ $entry->texture_label }}</span>
                                </div>
                            @endif

                            {{-- Laboratorio --}}
                            @if ($entry->laboratory)
                                <p class="text-xs text-zinc-500 truncate" title="{{ $entry->laboratory }}">
                                    {{ Str::limit($entry->laboratory, 40) }}
                                </p>
                            @endif

                        </div>

                        {{-- Footer acciones --}}
                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('viticulturist.soil-analyses.edit', $entry) }}"
                                   title="Editar"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $entry->id }})"
                                    wire:confirm="¿Eliminar este análisis de suelo? Esta acción no se puede deshacer."
                                    title="Eliminar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if ($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="soil-analyses-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'soil-analyses-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Parcela</label>
                <flux:select wire:model.live="filterPlot">
                    <option value="">Todas las parcelas</option>
                    @foreach ($plots as $plot)
                        <option value="{{ $plot->id }}">{{ $plot->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filterCampaign">
                    <option value="">Todas las campañas</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Textura</label>
                <flux:select wire:model.live="filterTexture">
                    <option value="">Todas las texturas</option>
                    @foreach ($textureClasses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if ($filterPlot || $filterCampaign || $filterTexture)
                <button
                    wire:click="clearFilters"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'soil-analyses-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
