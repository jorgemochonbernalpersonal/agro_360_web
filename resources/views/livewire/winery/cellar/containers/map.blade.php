<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Mapa de Bodega') }}"
        :description="__('Vista general de todos los depósitos y su estado actual.')"
    >
        <flux:button icon="list-bullet" href="{{ roleRoute('containers.index') }}" wire:navigate variant="ghost" size="sm">
            Ver lista
        </flux:button>
        <flux:button icon="plus" href="{{ roleRoute('containers.create') }}" wire:navigate variant="primary" size="sm">
            Nuevo depósito
        </flux:button>
    </x-agro.page-header>

    {{-- Stats globales --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-agro.stat-card :label="__('Total')" :value="$stats['total']" icon="archive-box" color="blue" />
        <x-agro.stat-card :label="__('En uso')" :value="$stats['in_use']" icon="beaker" color="agro" />
        <x-agro.stat-card :label="__('Vacíos')" :value="$stats['empty']" icon="archive-box-x-mark" color="blue" />
        <x-agro.stat-card :label="__('Llenos (>90%)')" :value="$stats['full']" icon="exclamation-triangle" color="red" />
        <div class="md:col-span-1 col-span-2">
            <x-agro.card>
                <div class="text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Ocupación global') }}</p>
                    <p class="text-2xl font-bold {{ $stats['global_pct'] >= 90 ? 'text-red-600' : ($stats['global_pct'] >= 70 ? 'text-amber-600' : 'text-agro-600') }}">
                        {{ $stats['global_pct'] }}%
                    </p>
                    <p class="text-xs text-zinc-400 mt-1">
                        {{ number_format($stats['total_used'], 0) }} / {{ number_format($stats['total_capacity'], 0) }} L
                    </p>
                </div>
            </x-agro.card>
        </div>
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar depósito...')" />

        <flux:select wire:model.live="roomFilter" size="sm" class="min-w-40">
            <option value="">{{ __('Todas las salas') }}</option>
            @foreach($rooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" size="sm" class="min-w-40">
            <option value="">{{ __('Todos los estados') }}</option>
            <option value="in_use">{{ __('En uso') }}</option>
            <option value="empty">{{ __('Vacíos') }}</option>
            <option value="full">{{ __('Llenos (≥90%)') }}</option>
        </flux:select>
    </x-agro.filter-bar>

    {{-- Contenedores agrupados por sala --}}
    @forelse($grouped as $roomName => $containers)
        <div>
            <div class="flex items-center gap-2 mb-3">
                <flux:icon icon="home" class="w-4 h-4 text-zinc-400" />
                <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wide">
                    {{ $roomName }}
                </h3>
                <span class="text-xs text-zinc-400">({{ $containers->count() }})</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($containers as $container)
                    @php
                        $pct       = $container->getOccupancyPercentage();
                        $isEmpty   = $container->isEmpty();
                        $isFull    = $pct >= 90;
                        $state     = $container->currentStates->first();
                        $wine      = $state?->wine;

                        $borderColor = match(true) {
                            $isFull            => 'border-red-300 dark:border-red-700',
                            $pct >= 70         => 'border-amber-300 dark:border-amber-700',
                            $pct >= 30         => 'border-agro-300 dark:border-agro-700',
                            default            => 'border-zinc-200 dark:border-zinc-700',
                        };
                        $dotColor = match(true) {
                            $isFull  => 'bg-red-500',
                            $isEmpty => 'bg-zinc-300',
                            default  => 'bg-agro-500',
                        };
                    @endphp

                    <div class="rounded-xl border-2 {{ $borderColor }} bg-white dark:bg-zinc-900 p-4 flex flex-col gap-3 hover:shadow-md transition-shadow">
                        {{-- Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $dotColor }} flex-shrink-0"></span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-sm">
                                        {{ $container->name }}
                                    </p>
                                </div>
                                @if($container->containerType)
                                    <p class="text-xs text-zinc-400 mt-0.5 pl-4">{{ $container->containerType->name }}</p>
                                @endif
                            </div>
                            <flux:button size="xs" variant="ghost" icon="pencil-square"
                                href="{{ roleRoute('containers.edit', $container) }}" wire:navigate />
                        </div>

                        {{-- Vino actual --}}
                        <div class="min-h-[2.5rem]">
                            @if($wine)
                                <div class="flex items-center gap-1.5 bg-agro-50 dark:bg-agro-900/20 rounded-lg px-2 py-1.5">
                                    <flux:icon icon="beaker" class="w-3.5 h-3.5 text-agro-600 flex-shrink-0" />
                                    <p class="text-xs font-medium text-agro-800 dark:text-agro-300 truncate">
                                        {{ $wine->name }} {{ $wine->vintage ? '('.$wine->vintage.')' : '' }}
                                    </p>
                                </div>
                            @elseif($isEmpty)
                                <div class="flex items-center gap-1.5 bg-zinc-50 dark:bg-zinc-800 rounded-lg px-2 py-1.5">
                                    <flux:icon icon="archive-box-x-mark" class="w-3.5 h-3.5 text-zinc-400 flex-shrink-0" />
                                    <p class="text-xs text-zinc-400">{{ __('Vacío') }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Barra de ocupación --}}
                        <div>
                            <div class="flex justify-between text-xs text-zinc-500 mb-1">
                                <span>{{ number_format($container->used_capacity ?? 0, 0) }} L en uso</span>
                                <span class="font-medium {{ $isFull ? 'text-red-600' : '' }}">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $isFull ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : ($pct >= 30 ? 'bg-agro-500' : 'bg-zinc-300')) }}"
                                     style="width: {{ min($pct, 100) }}%">
                                </div>
                            </div>
                            <p class="text-xs text-zinc-400 mt-1 text-right">
                                Cap. {{ number_format($container->capacity, 0) }} L
                            </p>
                        </div>

                        {{-- Próximo mantenimiento --}}
                        @if($container->next_maintenance_date && $container->next_maintenance_date->isPast())
                            <div class="flex items-center gap-1.5 text-xs text-red-600 dark:text-red-400">
                                <flux:icon icon="wrench-screwdriver" class="w-3.5 h-3.5" />
                                Mantenimiento pendiente
                            </div>
                        @elseif($container->next_maintenance_date && $container->next_maintenance_date->diffInDays(now()) <= 7)
                            <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                                <flux:icon icon="wrench-screwdriver" class="w-3.5 h-3.5" />
                                Mant. el {{ $container->next_maintenance_date->format('d/m') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <x-agro.empty-state
            icon="archive-box"
            title="{{ __('No hay depósitos') }}"
            :description="__('No se encontraron depósitos con los filtros actuales.')"
        >
            <flux:button icon="plus" href="{{ roleRoute('containers.create') }}" wire:navigate variant="primary" size="sm">
                Crear depósito
            </flux:button>
        </x-agro.empty-state>
    @endforelse
</div>
