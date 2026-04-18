<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Análisis de Laboratorio"
        description="Registro y seguimiento de análisis fisicoquímicos de vinos"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('wine-analysis.create') }}" wire:navigate>
                Nuevo análisis
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="wine-analysis">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                label="Total análisis"
                :value="$stats['total']"
                icon="beaker"
                color="zinc"
            />
            <x-agro.stat-card
                label="Este año"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                label="Conformes"
                :value="$stats['passed']"
                icon="check-circle"
                color="agro"
            />
            <x-agro.stat-card
                label="No conformes"
                :value="$stats['failed']"
                icon="x-circle"
                color="amber"
            />
        </div>
    </x-agro.stats-section>

    @php
        $filterCount = (int) !empty($typeFilter) + (int) !empty($resultFilter) + (int) !empty($containerFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por laboratorio o referencia..." />

        <x-agro.filter-button modal="analysis-filters" :count="$filterCount" />
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($typeFilter)
                @php $typeLabel = $types[$typeFilter] ?? $typeFilter; @endphp
                <x-agro.filter-chip icon="tag" :label="$typeLabel" wireRemove="$set('typeFilter', '')" />
            @endif
            @if($resultFilter)
                @php $resultLabel = $results[$resultFilter] ?? $resultFilter; @endphp
                <x-agro.filter-chip icon="check-badge" :label="$resultLabel" wireRemove="$set('resultFilter', '')" />
            @endif
            @if($containerFilter)
                @php $containerLabel = $containers->firstWhere('id', $containerFilter)?->name ?? $containerFilter; @endphp
                <x-agro.filter-chip icon="cube" :label="$containerLabel" wireRemove="$set('containerFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, typeFilter, resultFilter, containerFilter, clearFilters, nextPage, previousPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, typeFilter, resultFilter, containerFilter, clearFilters, nextPage, previousPage">
        @if($analyses->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($analyses as $analysis)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $resultConfig = match($analysis->result ?? 'pending') {
                            'passed'  => ['badge' => 'green',  'icon' => 'text-agro-600',  'bg' => 'bg-agro-100'],
                            'failed'  => ['badge' => 'red',    'icon' => 'text-red-600',    'bg' => 'bg-red-100'],
                            default   => ['badge' => 'yellow', 'icon' => 'text-amber-600',  'bg' => 'bg-amber-100'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="analysis-{{ $analysis->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $resultConfig['bg'] }}">
                                    <flux:icon icon="beaker" class="size-5 {{ $resultConfig['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        @if($analysis->wine)
                                            <a href="{{ roleRoute('wines.show', $analysis->wine) }}" wire:navigate class="hover:text-agro-700">
                                                {{ $analysis->wine->name }}
                                            </a>
                                            @if($analysis->wine->vintage)
                                                <span class="text-zinc-400 font-normal text-sm ml-1">{{ $analysis->wine->vintage }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </h3>
                                    <p class="text-xs text-zinc-400">{{ $analysis->analysis_date->format('d/m/Y') }}</p>
                                </div>
                                <flux:badge color="{{ $resultConfig['badge'] }}" size="sm" class="shrink-0">
                                    {{ $analysis->result_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <flux:badge color="blue" size="sm">{{ $analysis->type_label }}</flux:badge>
                                @if($analysis->container)
                                    <a href="{{ roleRoute('containers.show', $analysis->container) }}" wire:navigate
                                       class="text-xs text-zinc-500 hover:text-agro-600 hover:underline truncate">
                                        {{ $analysis->container->name }}
                                    </a>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                @if($analysis->alcoholic_strength !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Graduación</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($analysis->alcoholic_strength, 2) }}<span class="text-xs font-normal text-zinc-400 ml-0.5">%vol</span>
                                        </p>
                                    </div>
                                @endif
                                @if($analysis->ph !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">pH</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($analysis->ph, 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($analysis->laboratory)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="building-office" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs truncate">{{ $analysis->laboratory }}</span>
                                    @if($analysis->sample_reference)
                                        <span class="text-xs text-zinc-400 shrink-0">· Ref: {{ $analysis->sample_reference }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('wine-analysis.edit', $analysis) }}"
                                   wire:navigate
                                   title="Editar análisis"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $analysis->id }})"
                                    wire:confirm="¿Eliminar este análisis de laboratorio?"
                                    wire:loading.attr="disabled"
                                    title="Eliminar análisis"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $analyses->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ $search || $typeFilter || $resultFilter || $containerFilter ? 'Ningún análisis coincide con los filtros' : 'Sin análisis registrados' }}"
                description="{{ $search || $typeFilter || $resultFilter || $containerFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra los análisis fisicoquímicos de tus vinos para controlar su calidad.' }}"
            >
                @if($search || $typeFilter || $resultFilter || $containerFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('wine-analysis.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo análisis
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="analysis-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'analysis-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de análisis</label>
                <flux:select wire:model.live="typeFilter">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Resultado</label>
                <flux:select wire:model.live="resultFilter">
                    <option value="">Todos los resultados</option>
                    @foreach($results as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Depósito</label>
                <flux:select wire:model.live="containerFilter">
                    <option value="">Todos los depósitos</option>
                    @foreach($containers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
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
            <flux:button x-on:click="$dispatch('close-modal', 'analysis-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>

