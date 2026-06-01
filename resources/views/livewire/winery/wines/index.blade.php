<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Vinos en elaboración') }}"
        :description="__('Pipeline de vinificación — seguimiento de vinos desde recepción hasta embotellado.')"
    />

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre, variedad, código...')" />

        @php $filterCount = ($typeFilter ? 1 : 0) + ($statusFilter ? 1 : 0) + ($vintageFilter ? 1 : 0); @endphp
        <x-agro.filter-button modal="wine-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('wines.create') }}" wire:navigate variant="primary" icon="plus">
            Nuevo
        </flux:button>
    </div>

    {{-- Filter chips --}}
    @if($typeFilter || $statusFilter || $vintageFilter)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

            @if($typeFilter)
                <x-agro.filter-chip :label="$types[$typeFilter] ?? $typeFilter" wireRemove="$set('typeFilter', '')" />
            @endif

            @if($statusFilter)
                <x-agro.filter-chip :label="$statuses[$statusFilter] ?? $statusFilter" wireRemove="$set('statusFilter', '')" />
            @endif

            @if($vintageFilter)
                <x-agro.filter-chip :label="'Añada ' . $vintageFilter" wireRemove="$set('vintageFilter', '')" />
            @endif

            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">{{ __('Limpiar todo') }}</button>
        </div>
    @endif

    {{-- Skeleton durante carga --}}
    <x-agro.loading-grid target="search, typeFilter, statusFilter, vintageFilter, nextPage, previousPage, gotoPage" />

    {{-- Grid real --}}
    <div wire:loading.remove wire:target="search, typeFilter, statusFilter, vintageFilter, nextPage, previousPage, gotoPage">
        @if($wines->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wines as $wine)
                    @php
                        $delay = min($loop->index * 50, 300);

                        $typeColors = [
                            'red'        => ['bg' => 'bg-red-100',    'icon' => 'text-red-600'],
                            'white'      => ['bg' => 'bg-amber-100',  'icon' => 'text-amber-600'],
                            'rose'       => ['bg' => 'bg-pink-100',   'icon' => 'text-pink-600'],
                            'sparkling'  => ['bg' => 'bg-blue-100',   'icon' => 'text-blue-600'],
                            'fortified'  => ['bg' => 'bg-orange-100', 'icon' => 'text-orange-600'],
                            'sweet'      => ['bg' => 'bg-purple-100', 'icon' => 'text-purple-600'],
                            'semi_sweet' => ['bg' => 'bg-violet-100', 'icon' => 'text-violet-600'],
                            'other'      => ['bg' => 'bg-zinc-100',   'icon' => 'text-zinc-500'],
                        ];
                        $tc = $typeColors[$wine->wine_type] ?? $typeColors['other'];

                        $statusColor = match($wine->status) {
                            'in_progress' => 'blue',
                            'aged'        => 'yellow',
                            'bottled'     => 'green',
                            'sold'        => 'zinc',
                            default       => 'red',
                        };

                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="wine-card-{{ $wine->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="beaker"
                                :title="$wine->name"
                                :subtitle="$wine->type_label . ($wine->vintage ? ' · ' . $wine->vintage : '') . ($wine->aging_type_label ? ' · ' . $wine->aging_type_label : '')"
                                :iconBg="$tc['bg']"
                                :iconColor="$tc['icon']"
                                size="md"
                                radius="xl"
                            >
                                <x-agro.status-badge :label="$wine->status_label" :color="$statusColor" />
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-purple-50 rounded-xl p-2">
                                    <p class="text-[10px] text-purple-400 uppercase tracking-wide mb-0.5">{{ __('Volumen') }}</p>
                                    <p class="text-sm font-bold text-purple-700">
                                        {{ $wine->volume_liters ? number_format($wine->volume_liters, 0) . ' L' : '—' }}
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-2">
                                    <p class="text-[10px] text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Operaciones') }}</p>
                                    <p class="text-sm font-bold text-zinc-700">{{ $wine->process_details_count ?: '—' }}</p>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-sm">
                                @if($wine->variety)
                                    <div class="flex items-center gap-2 text-zinc-600">
                                        <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs">{{ $wine->variety }}</span>
                                    </div>
                                @endif
                                @if($wine->internal_code)
                                    <div class="flex items-center gap-2">
                                        <flux:icon icon="hashtag" class="size-3.5 text-zinc-400 shrink-0" />
                                        <span class="text-xs font-mono text-zinc-500">{{ $wine->internal_code }}</span>
                                    </div>
                                @endif
                                @if($wine->is_organic || $wine->is_must)
                                    <div class="flex flex-wrap gap-1.5 mt-1">
                                        @if($wine->is_organic)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-agro-50 text-agro-700 border border-agro-200">{{ __('Ecológico') }}</span>
                                        @endif
                                        @if($wine->is_must)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">{{ __('Mosto') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="view" href="{{ roleRoute('wines.show', $wine) }}" wire:navigate title="{{ __('Ver detalle') }}" />
                                    <x-agro.action-button variant="edit" href="{{ roleRoute('wines.edit', $wine) }}" wire:navigate title="{{ __('Editar') }}" />
                                </div>

                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="delete" wire:click="delete({{ $wine->id }})" wire:loading.attr="disabled" wire:confirm="{{ __('¿Eliminar este vino y todas sus operaciones?') }}" title="{{ __('Eliminar') }}" />
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$wines" />
        @else
            <x-agro.empty-state
                icon="beaker"
                title="{{ __('Sin vinos registrados') }}"
                :description="$search || $typeFilter || $statusFilter || $vintageFilter
                    ? 'Ningún vino coincide con los filtros aplicados.'
                    : 'Crea un vino para comenzar el seguimiento de su proceso de elaboración.'"
            >
                @if(!$search && !$typeFilter && !$statusFilter && !$vintageFilter)
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('wines.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo vino
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal Filtros --}}
    <x-agro.modal name="wine-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-purple-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'wine-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Tipo de vino') }}</label>
                <flux:select wire:model.live="typeFilter">
                    <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
                    @foreach($types as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Estado') }}</label>
                <flux:select wire:model.live="statusFilter">
                    <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
                    @foreach($statuses as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">{{ __('Añada') }}</label>
                <flux:select wire:model.live="vintageFilter">
                    <flux:select.option value="">{{ __('Todas las añadas') }}</flux:select.option>
                    @foreach($vintages as $v)
                        <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($typeFilter || $statusFilter || $vintageFilter)
                <flux:button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'wine-filters')" variant="ghost" size="sm">{{ __('Limpiar filtros') }}</flux:button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'wine-filters')" variant="primary" size="sm">{{ __('Aplicar') }}</flux:button>
        </div>
    </x-agro.modal>

</div>
