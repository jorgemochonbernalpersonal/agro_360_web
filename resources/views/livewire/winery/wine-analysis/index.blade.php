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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
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

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-48 relative">
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

        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="resultFilter" class="w-40">
            <flux:select.option value="">Todos los resultados</flux:select.option>
            @foreach($results as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="containerFilter" class="w-40">
            <flux:select.option value="">Todos los depósitos</flux:select.option>
            @foreach($containers as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $typeFilter || $resultFilter || $containerFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </div>

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
                            {{-- Tipo + Depósito --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <flux:badge color="blue" size="sm">{{ $analysis->type_label }}</flux:badge>
                                @if($analysis->container)
                                    <a href="{{ roleRoute('containers.show', $analysis->container) }}" wire:navigate
                                       class="text-xs text-zinc-500 hover:text-agro-600 hover:underline truncate">
                                        {{ $analysis->container->name }}
                                    </a>
                                @endif
                            </div>

                            {{-- Parámetros clave --}}
                            <div class="grid grid-cols-2 gap-2">
                                @if($analysis->alcoholic_strength !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Graduación</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($analysis->alcoholic_strength, 2) }}<span class="text-xs font-normal"> %vol</span>
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

                            {{-- Laboratorio --}}
                            @if($analysis->laboratory)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="building-office" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs truncate">{{ $analysis->laboratory }}</span>
                                    @if($analysis->sample_reference)
                                        <span class="text-xs text-zinc-400">· Ref: {{ $analysis->sample_reference }}</span>
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

</div>
