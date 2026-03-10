<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Recepción de Uva"
        description="Registro de entradas de uva por viticultor, parcela y variedad"
    />

    {{-- Nav vendimia --}}
    <x-agro.harvest-nav />

    {{-- Stat-bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Total recibido</p>
            <p class="text-2xl font-bold text-agro-700 leading-none">
                {{ number_format($stats['total_kg'], 0) }}
                <span class="text-sm font-medium text-zinc-400">kg</span>
            </p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Entradas</p>
            <p class="text-2xl font-bold text-zinc-700 leading-none">{{ $stats['total_count'] }}</p>
            <p class="text-xs text-zinc-400 mt-0.5">{{ $stats['viticulturists'] }} viticultores</p>
        </div>
        <div class="rounded-2xl p-4 shadow-sm border {{ $stats['disqualified_kg'] > 0 ? 'bg-red-50 border-red-200' : 'bg-white border-zinc-200' }}">
            <p class="text-[10px] font-semibold uppercase tracking-widest mb-1 {{ $stats['disqualified_kg'] > 0 ? 'text-red-400' : 'text-zinc-400' }}">Descartados</p>
            <p class="text-2xl font-bold leading-none {{ $stats['disqualified_kg'] > 0 ? 'text-red-600' : 'text-zinc-300' }}">
                {{ number_format($stats['disqualified_kg'], 0) }}
                <span class="text-sm font-medium {{ $stats['disqualified_kg'] > 0 ? 'text-red-400' : 'text-zinc-300' }}">kg</span>
            </p>
        </div>
        <div class="bg-white border border-zinc-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Neto válido</p>
            <p class="text-2xl font-bold text-agro-700 leading-none">
                {{ number_format(max(0, $stats['total_kg'] - $stats['disqualified_kg']), 0) }}
                <span class="text-sm font-medium text-zinc-400">kg</span>
            </p>
        </div>
    </div>

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($disqualifiedFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar viticultor, variedad, parcela o ticket..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        {{-- Filtros --}}
        <button x-on:click="$dispatch('open-modal', 'reception-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Exportar PDF --}}
        <a href="{{ route('winery.grape-reception.export-pdf', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}"
           target="_blank"
           class="inline-flex items-center gap-2 px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
           title="Exportar PDF">
            <flux:icon icon="document-arrow-down" class="size-4 text-zinc-500" />
            PDF
        </a>

        {{-- Exportar Excel --}}
        <a href="{{ route('winery.grape-reception.export-excel', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
           title="Exportar Excel">
            <flux:icon icon="table-cells" class="size-4 text-zinc-500" />
            Excel
        </a>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nueva Recepción --}}
        <flux:button href="{{ route('winery.grape-reception.create') }}" wire:navigate variant="primary" icon="plus">
            Nueva
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar" class="size-3" />
                    Añada: {{ $campLabel }}
                    <button wire:click="$set('campaignFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="user" class="size-3" />
                    {{ $viticLabel }}
                    <button wire:click="$set('viticulturistFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($disqualifiedFilter !== '')
                @php $disqLabel = $disqualifiedFilter === '1' ? 'Solo descartadas' : 'Solo válidas'; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="funnel" class="size-3" />
                    {{ $disqLabel }}
                    <button wire:click="$set('disqualifiedFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <div wire:loading wire:target="search, campaignFilter, viticulturistFilter, disqualifiedFilter, clearFilters, gotoPage, previousPage, nextPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Contenido real --}}
    <div wire:loading.remove wire:target="search, campaignFilter, viticulturistFilter, disqualifiedFilter, clearFilters, gotoPage, previousPage, nextPage">
        @if($receptions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($receptions as $reception)
                    @php
                        $planting    = $reception->plotPlanting;
                        $variety     = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
                        $plotName    = $planting?->plot?->name ?? '—';
                        $vitic       = $reception->batch?->viticulturist;
                        $year        = $reception->batch?->vintage_year;
                        $delay       = min($loop->index * 50, 300);
                        $isCancelled = $reception->status === 'cancelled';
                        $btnBase     = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                        $btnDanger   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ $isCancelled ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="reception-card-{{ $reception->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $isCancelled ? 'bg-zinc-100' : ($reception->disqualified ? 'bg-red-50' : 'bg-agro-50') }} flex items-center justify-center shrink-0">
                                    <flux:icon icon="scale" class="size-5 {{ $isCancelled ? 'text-zinc-400' : ($reception->disqualified ? 'text-red-500' : 'text-agro-600') }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $variety }}</h3>
                                    <p class="text-xs text-zinc-500 truncate">{{ $plotName }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    @if($year)
                                        <span class="text-sm font-bold text-agro-700">{{ $year }}</span>
                                    @endif
                                    @if($isCancelled)
                                        <flux:badge color="zinc" size="sm">Anulada</flux:badge>
                                    @elseif($reception->disqualified)
                                        <flux:badge color="red" size="sm">Descartada</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">

                            {{-- Stats principales --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">Kg recibidos</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">
                                        {{ number_format($reception->total_weight, 0) }}
                                        <span class="text-xs font-medium text-zinc-400 ml-0.5">kg</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Fecha</p>
                                    @if($reception->harvest_start_date)
                                        <p class="text-base font-bold text-zinc-700 leading-none">
                                            {{ $reception->harvest_start_date->format('d/m') }}
                                            <span class="text-xs font-medium text-zinc-400">/{{ $reception->harvest_start_date->format('Y') }}</span>
                                        </p>
                                    @else
                                        <p class="text-base font-medium text-zinc-300 italic leading-none">—</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Info secundaria --}}
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2 text-zinc-600">
                                    <flux:icon icon="user" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate font-medium">{{ $vitic?->name ?? '—' }}</span>
                                </div>

                                @if($reception->yield_per_hectare)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-400">Rendimiento</span>
                                        <span class="text-zinc-600">{{ number_format($reception->yield_per_hectare, 0) }} kg/ha</span>
                                    </div>
                                @endif

                                @if($reception->baume_degree || $reception->potential_alcohol)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-400">Calidad</span>
                                        <span class="text-zinc-600">
                                            @if($reception->potential_alcohol) {{ $reception->potential_alcohol }}% alc. @endif
                                            @if($reception->baume_degree) {{ $reception->baume_degree }}°Bé @endif
                                        </span>
                                    </div>
                                @endif

                                @if($reception->harvest_ticket_number)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-400">Ticket</span>
                                        <span class="font-mono text-zinc-600">{{ $reception->harvest_ticket_number }}</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">Contenedor</span>
                                    @if($reception->container_id)
                                        <flux:badge color="green" size="sm">{{ $reception->container?->name ?? '—' }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">Sin asignar</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                {{-- Grupo izquierdo: anular --}}
                                <div class="flex items-center gap-0.5">
                                    @if(!$isCancelled)
                                        <button
                                            wire:click="cancelReception({{ $reception->id }})"
                                            wire:confirm="¿Anular esta recepción? Esta acción no se puede deshacer."
                                            class="{{ $btnDanger }}"
                                            title="Anular recepción"
                                        >
                                            <flux:icon icon="x-circle" class="size-4" />
                                        </button>
                                    @endif
                                </div>

                                {{-- Separador --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: ver + editar --}}
                                <div class="flex items-center gap-0.5">
                                    <a href="{{ route('winery.grape-reception.show', $reception) }}" wire:navigate class="{{ $btnBase }}" title="Ver detalle">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                    @if(!$isCancelled)
                                        <a href="{{ route('winery.grape-reception.edit', $reception) }}" wire:navigate class="{{ $btnBase }}" title="Editar recepción">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $receptions->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="scale"
                title="No hay recepciones registradas"
                description="Registra la primera entrada de uva para esta campaña"
            >
                <x-slot:action>
                    <flux:button href="{{ route('winery.grape-reception.create') }}" wire:navigate variant="primary" icon="plus">
                        Nueva Recepción
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="reception-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'reception-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Añada</label>
                <flux:select wire:model.live="campaignFilter">
                    <option value="">Todas las añadas</option>
                    @foreach($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->year }}{{ $c->active ? ' (activa)' : '' }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Viticultor</label>
                <flux:select wire:model.live="viticulturistFilter">
                    <option value="">Todos los viticultores</option>
                    @foreach($linkedViticulturists as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="disqualifiedFilter">
                    <option value="">Todas</option>
                    <option value="0">Solo válidas</option>
                    <option value="1">Solo descartadas</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'reception-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
