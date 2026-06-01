<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Trasvases y Coupage') }}"
        :description="__('Registro de trasiegos, mezclas y movimientos de vino entre depósitos')"
        icon="arrows-right-left"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus" href="{{ roleRoute('wine-transfers.create') }}" wire:navigate>
                Nuevo trasvase
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="wine-transfers">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                :label="__('Total trasvases')"
                :value="$stats['total']"
                icon="arrows-right-left"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Este año')"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Trasiegos')"
                :value="$stats['rackings']"
                icon="arrow-right"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Coupages')"
                :value="$stats['blendings']"
                icon="beaker"
                color="amber"
            />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por vino...')" />

        <flux:select wire:model.live="wineFilter" class="w-44">
            <flux:select.option value="">{{ __('Todos los vinos') }}</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($types as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        @if($search || $wineFilter || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Loading skeleton --}}
    <x-agro.loading-grid target="search, wineFilter, typeFilter, clearFilters, nextPage, previousPage" />

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="search, wineFilter, typeFilter, clearFilters, nextPage, previousPage">
        @if($transfers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($transfers as $transfer)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $typeConfig = match($transfer->transfer_type) {
                            'racking'  => ['color' => 'blue',   'badge' => 'blue',   'icon' => 'text-blue-600',   'bg' => 'bg-blue-100'],
                            'blending' => ['color' => 'violet', 'badge' => 'violet', 'icon' => 'text-violet-600', 'bg' => 'bg-violet-100'],
                            'top_up'   => ['color' => 'green',  'badge' => 'green',  'icon' => 'text-agro-600',   'bg' => 'bg-agro-100'],
                            default    => ['color' => 'zinc',   'badge' => 'zinc',   'icon' => 'text-zinc-500',   'bg' => 'bg-zinc-100'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="transfer-{{ $transfer->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $typeConfig['bg'] }}">
                                    <flux:icon icon="arrows-right-left" class="size-5 {{ $typeConfig['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">
                                        @if($transfer->wine)
                                            {{ $transfer->wine->name }}
                                            @if($transfer->wine->vintage)
                                                <span class="text-zinc-400 font-normal text-sm">{{ $transfer->wine->vintage }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </h3>
                                    <p class="text-xs text-zinc-400">{{ $transfer->transfer_date->format('d/m/Y') }}</p>
                                </div>
                                <flux:badge color="{{ $typeConfig['badge'] }}" size="sm" class="shrink-0">
                                    {{ $transfer->transfer_type_label }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Cantidad --}}
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad transferida') }}</p>
                                <p class="text-2xl font-bold text-agro-700 leading-none">
                                    {{ number_format($transfer->quantity, 2) }}
                                    <span class="text-sm font-normal text-agro-500">{{ $transfer->unitOfMeasurement?->symbol ?? '' }}</span>
                                </p>
                            </div>

                            {{-- Origen → Destino --}}
                            <div class="flex items-center gap-2 text-sm">
                                <div class="flex-1 min-w-0 bg-zinc-50 rounded-lg px-3 py-2">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Origen') }}</p>
                                    <p class="font-medium text-zinc-700 truncate">{{ $transfer->fromContainer?->name ?? '—' }}</p>
                                </div>
                                <flux:icon icon="arrow-right" class="size-4 text-zinc-300 shrink-0" />
                                <div class="flex-1 min-w-0 bg-zinc-50 rounded-lg px-3 py-2">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Destino') }}</p>
                                    <p class="font-medium text-zinc-700 truncate">{{ $transfer->toContainer?->name ?? '—' }}</p>
                                </div>
                            </div>

                            {{-- Notas --}}
                            @if($transfer->notes ?? false)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $transfer->notes }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('wine-transfers.edit', $transfer) }}" wire:navigate title="{{ __('Editar trasvase') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $transfer->id }})" wire:confirm="{{ __('¿Eliminar este trasvase?') }}" wire:loading.attr="disabled" title="{{ __('Eliminar trasvase') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$transfers" />
        @else
            <x-agro.empty-state
                icon="arrows-right-left"
                title="{{ $search || $wineFilter || $typeFilter ? 'Ningún trasvase coincide con los filtros' : 'Sin trasvases registrados' }}"
                description="{{ $search || $wineFilter || $typeFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra los trasiegos, mezclas y movimientos de vino entre depósitos.' }}"
            >
                @if($search || $wineFilter || $typeFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('wine-transfers.create') }}" wire:navigate variant="primary" icon="plus">
                            Nuevo trasvase
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

