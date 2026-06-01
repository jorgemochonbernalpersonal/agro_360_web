<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Controles de Fermentación') }}"
        :description="__('Seguimiento periódico de parámetros de fermentación por vino y depósito')"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate>
                Nuevo control
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="fermentation-controls-stats">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                :label="__('Total controles')"
                :value="$stats['total']"
                icon="beaker"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Este año')"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('En fermentación')"
                :value="$stats['fermenting']"
                icon="fire"
                color="amber"
            />
            <x-agro.stat-card
                :label="__('Completados')"
                :value="$stats['done']"
                icon="check-circle"
                color="zinc"
            />
        </div>
    </x-agro.stats-section>

    @php
        $filterCount = (int) !empty($wineFilter) + (int) !empty($containerFilter) + (int) !empty($statusFilter);
    @endphp

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por vino...')" />

        <x-agro.filter-button modal="fermentation-filters" :count="$filterCount" />
    </div>

    {{-- Chips de filtros activos --}}
    @if($filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if($wineFilter)
                @php $wineLabel = $wines->firstWhere('id', $wineFilter)?->name ?? $wineFilter; @endphp
                <x-agro.filter-chip icon="beaker" :label="$wineLabel" wireRemove="$set('wineFilter', '')" />
            @endif
            @if($containerFilter)
                @php $containerLabel = $containers->firstWhere('id', $containerFilter)?->name ?? $containerFilter; @endphp
                <x-agro.filter-chip icon="cube" :label="$containerLabel" wireRemove="$set('containerFilter', '')" />
            @endif
            @if($statusFilter)
                @php $statusLabel = $statusFilter === 'fermenting' ? 'En fermentación' : 'Completada'; @endphp
                <x-agro.filter-chip icon="tag" :label="$statusLabel" wireRemove="$set('statusFilter', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, wineFilter, statusFilter, containerFilter, clearFilters, nextPage, previousPage" />

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
                                    <flux:badge color="amber" size="sm" class="shrink-0">{{ __('Fermentando') }}</flux:badge>
                                @else
                                    <flux:badge color="green" size="sm" class="shrink-0">{{ __('Completada') }}</flux:badge>
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
                                        <p class="text-[10px] font-semibold text-orange-400 uppercase tracking-widest mb-0.5">{{ __('Temperatura') }}</p>
                                        <p class="text-xl font-bold text-orange-700 leading-none">
                                            {{ number_format($control->temperature, 1) }}<span class="text-sm font-normal"> °C</span>
                                        </p>
                                    </div>
                                @endif
                                @if($control->density !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Densidad') }}</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->density, 4) }}
                                        </p>
                                    </div>
                                @endif
                                @if($control->brix_degree !== null)
                                    <div class="bg-zinc-50 rounded-xl p-3">
                                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Brix') }}</p>
                                        <p class="text-xl font-bold text-zinc-700 leading-none">
                                            {{ number_format($control->brix_degree, 1) }}<span class="text-sm font-normal">{{ __('°Bx') }}</span>
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
                                    <span class="font-semibold text-zinc-600">{{ __('Ac. Volátil:') }}</span>
                                    {{ number_format($control->volatile_acidity, 2) }} g/L
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('fermentation-controls.edit', $control) }}" wire:navigate title="{{ __('Editar control') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $control->id }})" wire:confirm="{{ __('¿Eliminar este control de fermentación?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar control') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$controls" />
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Ningún control coincide con los filtros' : 'Sin controles registrados' }}"
                description="{{ $search || $wineFilter || $statusFilter || $containerFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra los controles periódicos de fermentación de tus vinos.' }}"
            >
                @if($search || $wineFilter || $statusFilter || $containerFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
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
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'fermentation-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Vino') }}</label>
                <flux:select wire:model.live="wineFilter">
                    <option value="">{{ __('Todos los vinos') }}</option>
                    @foreach($wines as $wine)
                        <option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Depósito') }}</label>
                <flux:select wire:model.live="containerFilter">
                    <option value="">{{ __('Todos los depósitos') }}</option>
                    @foreach($containers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Estado') }}</label>
                <flux:select wire:model.live="statusFilter">
                    <option value="">{{ __('Todos los estados') }}</option>
                    <option value="fermenting">{{ __('En fermentación') }}</option>
                    <option value="done">{{ __('Fermentación completada') }}</option>
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar filtros') }}</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'fermentation-filters')" variant="primary">{{ __('Aplicar') }}</flux:button>
        </div>
    </x-agro.modal>

</div>

