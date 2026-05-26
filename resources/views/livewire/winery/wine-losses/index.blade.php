<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Mermas y Pérdidas') }}"
        :description="__('Registro de mermas, filtraciones y pérdidas de vino elaborado')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('wine-losses.create') }}" variant="primary" icon="plus">
                Nueva Merma
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <x-agro.stats-section key="wine-losses">
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                :label="__('Total mermas')"
                :value="$stats['total']"
                icon="exclamation-triangle"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Este año')"
                :value="$stats['this_year']"
                icon="calendar-days"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Naturales')"
                :value="$stats['natural']"
                icon="beaker"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Accidentales')"
                :value="$stats['accidental']"
                icon="fire"
                color="amber"
            />
        </div>
    </x-agro.stats-section>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por vino o contenedor...')" />

        <flux:select wire:model.live="wineFilter" class="w-44">
            <flux:select.option value="">{{ __('Todos los vinos') }}</flux:select.option>
            @foreach($wines as $wine)
                <flux:select.option value="{{ $wine->id }}">{{ $wine->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" class="w-48">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($lossTypes as $key => $label)
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
        @if($losses->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($losses as $loss)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $typeColors = [
                            'evaporation' => ['icon' => 'text-yellow-600', 'bg' => 'bg-yellow-100', 'badge' => 'yellow'],
                            'filtration'  => ['icon' => 'text-blue-600',   'bg' => 'bg-blue-100',   'badge' => 'blue'],
                            'sampling'    => ['icon' => 'text-zinc-500',   'bg' => 'bg-zinc-100',   'badge' => 'zinc'],
                            'spillage'    => ['icon' => 'text-red-600',    'bg' => 'bg-red-100',    'badge' => 'red'],
                            'other'       => ['icon' => 'text-zinc-500',   'bg' => 'bg-zinc-100',   'badge' => 'zinc'],
                        ];
                        $tc = $typeColors[$loss->loss_type] ?? $typeColors['other'];
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="loss-{{ $loss->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="exclamation-triangle"
                                :title="$loss->wine?->name ?? '—'"
                                :subtitle="$loss->loss_date instanceof \Carbon\Carbon ? $loss->loss_date->format('d/m/Y') : $loss->loss_date"
                                :iconBg="$tc['bg']"
                                :iconColor="$tc['icon']"
                                size="md"
                                radius="xl"
                            >
                                <flux:badge color="{{ $tc['badge'] }}" size="sm">
                                    {{ $lossTypes[$loss->loss_type] ?? $loss->loss_type }}
                                </flux:badge>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-red-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-red-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad') }}</p>
                                    <p class="text-2xl font-bold text-red-700 leading-none">
                                        −{{ number_format($loss->quantity, 2) }}
                                    </p>
                                    <p class="text-xs text-red-400 mt-0.5">
                                        {{ $loss->unitOfMeasurement?->symbol ?? $loss->unitOfMeasurement?->name ?? '' }}
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Contenedor') }}</p>
                                    <p class="text-sm font-semibold text-zinc-700 leading-snug truncate">
                                        {{ $loss->container?->name ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Autorización --}}
                            @if($loss->loss_authorization)
                                <div class="flex items-center gap-2 text-sm text-zinc-600">
                                    <flux:icon icon="shield-check" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="text-xs">{{ $loss->loss_authorization_label }}</span>
                                </div>
                            @endif

                            {{-- Notas --}}
                            @if($loss->notes)
                                <p class="text-xs text-zinc-400 line-clamp-2">{{ $loss->notes }}</p>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button variant="edit" href="{{ roleRoute('wine-losses.edit', $loss) }}" title="{{ __('Editar merma') }}" />
                                <x-agro.action-button variant="delete" wire:click="delete({{ $loss->id }})" wire:confirm="{{ __('¿Eliminar esta merma? Se restaurarán los litros al contenedor.') }}" wire:loading.attr="disabled" title="{{ __('Eliminar merma') }}" />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$losses" />
        @else
            <x-agro.empty-state
                icon="exclamation-triangle"
                title="{{ $search || $wineFilter || $typeFilter ? 'Ninguna merma coincide con los filtros' : 'Sin mermas registradas' }}"
                description="{{ $search || $wineFilter || $typeFilter ? 'Prueba a cambiar o limpiar los filtros aplicados.' : 'Registra evaporaciones, filtraciones o pérdidas accidentales de vino.' }}"
            >
                @if($search || $wineFilter || $typeFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('wine-losses.create') }}" variant="primary" icon="plus">
                            Nueva Merma
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    </div>

</div>

