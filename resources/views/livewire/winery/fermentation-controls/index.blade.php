<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Controles de Fermentación"
        description="Seguimiento periódico de parámetros de fermentación por vino y depósito"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate>
                Nuevo control
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total controles"
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
            label="En fermentación"
            :value="$stats['fermenting']"
            icon="fire"
            color="amber"
        />
        <x-agro.stat-card
            label="Completados"
            :value="$stats['done']"
            icon="check-circle"
            color="zinc"
        />
    </div>

    @php
        $filterCount = (int) !empty($wineFilter) + (int) !empty($containerFilter) + (int) !empty($statusFilter);
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
                placeholder="Buscar por vino..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        <button x-on:click="$dispatch('open-modal', 'fermentation-filters')"
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
            @if($wineFilter)
                @php $wineLabel = $wines->firstWhere('id', $wineFilter)?->name ?? $wineFilter; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="beaker" class="size-3" />
                    {{ $wineLabel }}
                    <button wire:click="$set('wineFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
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
            @if($statusFilter)
                @php $statusLabel = $statusFilter === 'fermenting' ? 'En fermentación' : 'Completada'; @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="tag" class="size-3" />
                    {{ $statusLabel }}
                    <button wire:click="$set('statusFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
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
    <div wire:loading wire:target="search, wineFilter, statusFilter, containerFilter, clearFilters, nextPage, previousPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, wineFilter, statusFilter, containerFilter, clearFilters, nextPage, previousPage">
        @if($controls->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($controls as $control)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $isFermenting = $control->isFermenting();
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="control-{{ $control->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                    {{ $isFermenting ? 'bg-amber-100' : 'bg-agro-100' }}">
                                    <flux:icon icon="beaker" class="size-5 {{ $isFermenting ? 'text-amber-600' : 'text-agro-600' }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        @if($control->wine)
                                            <a href="{{ roleRoute('wines.show', $control->wine) }}" wire:navigate class="hover:text-agro-700">
                                                {{ $control->wine->name }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </h3>
                                    <p class="text-xs text-zinc-400">
                                        {{ \Carbon\Carbon::parse($control->control_date)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                @if($isFermenting)
                                    <flux:badge color="amber" size="sm" class="shrink-0">Fermentando</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm" class="shrink-0">Completada</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            @if($control->container)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="cube" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-sm truncate">{{ $control->container->name }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                @if($control->temperature !== null)
                                    <div class="bg-orange-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-orange-400 uppercase tracking-widest mb-0.5">Temperatura</p>
                                        <p class="text-xl font-bold text-orange-700 leading-none">
                                            {{ number_format($control->temperature, 1) }}<span class="text-sm font-normal"> °C</span>
                                        </p>
                                    </div>
                                @endif
                                @if($control->density !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Densidad</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->density, 4) }}
                                        </p>
                                    </div>
                                @endif
                                @if($control->brix_degree !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Brix</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->brix_degree, 1) }}<span class="text-sm font-normal"> °Bx</span>
                                        </p>
                                    </div>
                                @endif
                                @if($control->ph !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">pH</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->ph, 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($control->volatile_acidity !== null)
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <span class="font-semibold text-zinc-600">Ac. Volátil:</span>
                                    {{ number_format($control->volatile_acidity, 2) }} g/L
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('fermentation-controls.edit', $control) }}"
                                   wire:navigate
                                   title="Editar control"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="delete({{ $control->id }})"
                                    wire:confirm="¿Eliminar este control de fermentación?"
                                    wire:loading.attr="disabled"
                                    title="Eliminar control"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $controls->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Ningún control coincide con los filtros' : 'Sin controles registrados' }}"
                description="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra los controles periódicos de fermentación de tus vinos.' }}"
            >
                @if($search || $wineFilter || $statusFilter || $containerFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">
                            Limpiar filtros
                        </flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo control
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="fermentation-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'fermentation-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Vino</label>
                <flux:select wire:model.live="wineFilter">
                    <option value="">Todos los vinos</option>
                    @foreach($wines as $wine)
                        <option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</option>
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
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Estado</label>
                <flux:select wire:model.live="statusFilter">
                    <option value="">Todos los estados</option>
                    <option value="fermenting">En fermentación</option>
                    <option value="done">Fermentación completada</option>
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
            <flux:button x-on:click="$dispatch('close-modal', 'fermentation-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
