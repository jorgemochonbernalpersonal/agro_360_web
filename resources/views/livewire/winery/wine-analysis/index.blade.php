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
    <div x-data="{
        open: localStorage.getItem('wine-analysis-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('wine-analysis-stats-open', String(this.open));
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
        </div>
    </div>

    @php
        $filterCount = (int) !empty($typeFilter) + (int) !empty($resultFilter) + (int) !empty($containerFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por laboratorio o referencia..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button x-on:click="$dispatch('open-modal', 'analysis-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors">
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($typeFilter)
                @php $typeLabel = $types[$typeFilter] ?? $typeFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="tag" class="size-3" />
                    {{ $typeLabel }}
                    <button wire:click="$set('typeFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($resultFilter)
                @php $resultLabel = $results[$resultFilter] ?? $resultFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="check-badge" class="size-3" />
                    {{ $resultLabel }}
                    <button wire:click="$set('resultFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($containerFilter)
                @php $containerLabel = $containers->firstWhere('id', $containerFilter)?->name ?? $containerFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="cube" class="size-3" />
                    {{ $containerLabel }}
                    <button wire:click="$set('containerFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <div wire:loading wire:target="search, typeFilter, resultFilter, containerFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

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

