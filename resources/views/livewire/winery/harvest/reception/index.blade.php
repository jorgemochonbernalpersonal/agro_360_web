<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Recepción de Uva') }}"
        :description="__('Registro de entradas de uva por viticultor, parcela y variedad')"
    />

    {{-- Nav vendimia --}}
    <x-agro.harvest-nav />

    {{-- Stat-bar --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            :label="__('Total recibido')"
            :value="number_format($stats['total_kg'], 0) . ' kg'"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Entradas')"
            :value="$stats['total_count']"
            :description="$stats['viticulturists'] . ' viticultores'"
            color="zinc"
        />
        <div class="rounded-2xl p-4 shadow-sm border {{ $stats['disqualified_kg'] > 0 ? 'bg-red-50 border-red-200' : 'bg-white border-zinc-200' }}">
            <p class="text-[10px] font-semibold uppercase tracking-widest mb-1 {{ $stats['disqualified_kg'] > 0 ? 'text-red-400' : 'text-zinc-400' }}">{{ __('Descartados') }}</p>
            <p class="text-2xl font-bold leading-none {{ $stats['disqualified_kg'] > 0 ? 'text-red-600' : 'text-zinc-300' }}">
                {{ number_format($stats['disqualified_kg'], 0) }}
                <span class="text-sm font-medium {{ $stats['disqualified_kg'] > 0 ? 'text-red-400' : 'text-zinc-300' }}">kg</span>
            </p>
        </div>
        <x-agro.stat-card
            :label="__('Neto válido')"
            :value="number_format(max(0, $stats['total_kg'] - $stats['disqualified_kg']), 0) . ' kg'"
            color="agro"
        />
    </div>

    @php
        $filterCount = (int) !empty($campaignFilter) + (int) !empty($viticulturistFilter) + (int) !empty($disqualifiedFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar viticultor, variedad, parcela o ticket...')" />

        {{-- Filtros --}}
        <x-agro.filter-button modal="reception-filters" :count="$filterCount" />

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('grape-reception.export-pdf', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}" target="_blank" icon="document-arrow-down">PDF</flux:button>
        <flux:button href="{{ roleRoute('grape-reception.export-excel', array_filter(['campaign' => $campaignFilter, 'viticulturist' => $viticulturistFilter, 'disqualified' => $disqualifiedFilter])) }}" icon="table-cells">Excel</flux:button>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Nueva Recepción --}}
        <flux:button href="{{ roleRoute('grape-reception.create') }}" wire:navigate variant="primary" icon="plus">
            Nueva
        </flux:button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($campaignFilter)
                @php $campLabel = $campaigns->firstWhere('id', $campaignFilter)?->year ?? $campaignFilter; @endphp
                <x-agro.filter-chip icon="calendar" :label="'Añada: ' . $campLabel" wireRemove="$set('campaignFilter', '')" />
            @endif
            @if($viticulturistFilter)
                @php $viticLabel = $linkedViticulturists->firstWhere('id', $viticulturistFilter)?->name ?? $viticulturistFilter; @endphp
                <x-agro.filter-chip icon="user" :label="$viticLabel" wireRemove="$set('viticulturistFilter', '')" />
            @endif
            @if($disqualifiedFilter !== '')
                @php $disqLabel = $disqualifiedFilter === '1' ? 'Solo descartadas' : 'Solo válidas'; @endphp
                <x-agro.filter-chip icon="funnel" :label="$disqLabel" wireRemove="$set('disqualifiedFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, campaignFilter, viticulturistFilter, disqualifiedFilter, clearFilters, gotoPage, previousPage, nextPage" :count="6" />

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
                                        <flux:badge color="zinc" size="sm">{{ __('Anulada') }}</flux:badge>
                                    @elseif($reception->disqualified)
                                        <flux:badge color="red" size="sm">{{ __('Descartada') }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">

                            {{-- Stats principales --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-1">{{ __('Kg recibidos') }}</p>
                                    <p class="text-2xl font-bold text-agro-700 leading-none">
                                        {{ number_format($reception->total_weight, 0) }}
                                        <span class="text-xs font-medium text-zinc-400 ml-0.5">kg</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">{{ __('Fecha') }}</p>
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
                                        <span class="text-zinc-400">{{ __('Rendimiento') }}</span>
                                        <span class="text-zinc-600">{{ number_format($reception->yield_per_hectare, 0) }} kg/ha</span>
                                    </div>
                                @endif

                                @if($reception->baume_degree || $reception->potential_alcohol)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-400">{{ __('Calidad') }}</span>
                                        <span class="text-zinc-600">
                                            @if($reception->potential_alcohol) {{ $reception->potential_alcohol }}% alc. @endif
                                            @if($reception->baume_degree) {{ $reception->baume_degree }}°Bé @endif
                                        </span>
                                    </div>
                                @endif

                                @if($reception->harvest_ticket_number)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-zinc-400">{{ __('Ticket') }}</span>
                                        <span class="font-mono text-zinc-600">{{ $reception->harvest_ticket_number }}</span>
                                    </div>
                                @endif

                                {{-- Estado declaración viticultor --}}
                                @php $delivery = $reception->delivery; @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">{{ __('Declaración vitic.') }}</span>
                                    @if(!$delivery)
                                        <flux:badge color="zinc" size="sm">{{ __('Sin declarar') }}</flux:badge>
                                    @elseif($delivery->status === 'matched')
                                        <flux:badge color="green" size="sm">{{ __('Coincide') }}</flux:badge>
                                    @elseif($delivery->status === 'resolved')
                                        <flux:badge color="blue" size="sm">{{ __('Resuelta') }}</flux:badge>
                                    @elseif($delivery->status === 'disputed')
                                        <flux:badge color="amber" size="sm">
                                            Dif. {{ number_format($delivery->discrepancy_kg, 0) }} kg
                                        </flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Pendiente') }}</flux:badge>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-zinc-400">{{ __('Contenedor') }}</span>
                                    @if($reception->container_id)
                                        <flux:badge color="green" size="sm">{{ $reception->container?->name ?? '—' }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Sin asignar') }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                {{-- Grupo izquierdo: anular --}}
                                <div class="flex items-center gap-0.5">
                                    @if(!$isCancelled)
                                        <x-agro.action-button icon="x-circle" variant="danger" wire:click="cancelReception({{ $reception->id }})" wire:confirm="{{ __('¿Anular esta recepción? Esta acción no se puede deshacer.') }}" title="{{ __('Anular recepción') }}" />
                                    @endif
                                </div>

                                {{-- Separador --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: ver + editar --}}
                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="view" href="{{ roleRoute('grape-reception.show', $reception) }}" wire:navigate title="{{ __('Ver detalle') }}" />
                                    @if(!$isCancelled)
                                        <x-agro.action-button variant="edit" href="{{ roleRoute('grape-reception.edit', $reception) }}" wire:navigate title="{{ __('Editar recepción') }}" />
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$receptions" />
        @else
            <x-agro.empty-state
                icon="scale"
                title="{{ __('No hay recepciones registradas') }}"
                :description="__('Registra la primera entrada de uva para esta campaña')"
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('grape-reception.create') }}" wire:navigate variant="primary" icon="plus">
                        Nueva Recepción
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.filter-modal name="reception-filters" :hasActiveFilters="$filterCount > 0" clearAction="clearFilters">
        <div>
            <x-agro.field-label>{{ __('Añada') }}</x-agro.field-label>
            <flux:select wire:model.live="campaignFilter">
                <option value="">{{ __('Todas las añadas') }}</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->year }}{{ $c->active ? ' (activa)' : '' }}</option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <x-agro.field-label>{{ __('Viticultor') }}</x-agro.field-label>
            <flux:select wire:model.live="viticulturistFilter">
                <option value="">{{ __('Todos los viticultores') }}</option>
                @foreach($linkedViticulturists as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div>
            <x-agro.field-label>{{ __('Estado') }}</x-agro.field-label>
            <flux:select wire:model.live="disqualifiedFilter">
                <option value="">{{ __('Todas') }}</option>
                <option value="0">{{ __('Solo válidas') }}</option>
                <option value="1">{{ __('Solo descartadas') }}</option>
            </flux:select>
        </div>
    </x-agro.filter-modal>

</div>
