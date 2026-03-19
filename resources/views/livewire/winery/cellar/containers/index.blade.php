<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Contenedores de Bodega"
        description="Gestiona tus depósitos, barricas y otros contenedores"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('containers.create') }}" variant="primary" icon="plus">
                Nuevo Contenedor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs Activos / Inactivos --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',    'count' => $stats['active']],
            'archived' => ['label' => 'Inactivos',  'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar contenedor..."
        />
        <flux:select wire:model.live="typeFilter" size="sm" class="w-40">
            <flux:select.option value="">Todos los tipos</flux:select.option>
            @foreach($types as $type)
                <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($search || $typeFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    @if($containers->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, typeFilter, clearFilters"
        >
            @foreach($containers as $container)
                @php
                    $pct      = $container->getOccupancyPercentage();
                    $typeName = $typesById[$container->type_id]->name ?? '—';
                    $delay    = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="container-card-{{ $container->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="cube" class="size-5 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $container->name }}</h3>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($container->serial_number)
                                        <p class="text-xs text-zinc-400">S/N: {{ $container->serial_number }}</p>
                                    @else
                                        <p class="text-xs text-zinc-400">{{ $typeName }}</p>
                                    @endif
                                    @if(($container->quantity ?? 1) > 1)
                                        <flux:badge color="blue" size="sm">× {{ $container->quantity }}</flux:badge>
                                    @endif
                                </div>
                            </div>
                            @if($container->isFull())
                                <flux:badge color="red" size="sm" class="shrink-0">Lleno</flux:badge>
                            @elseif($container->isEmpty())
                                <flux:badge color="green" size="sm" class="shrink-0">Vacío</flux:badge>
                            @else
                                <flux:badge color="yellow" size="sm" class="shrink-0">En uso</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Barra de ocupación --}}
                        <div>
                            <div class="flex justify-between text-xs text-zinc-500 mb-1">
                                <span>Ocupación</span>
                                <span class="{{ $pct >= 100 ? 'text-red-600 font-semibold' : 'text-zinc-700' }}">
                                    {{ number_format($pct, 0) }}%
                                </span>
                            </div>
                            <x-agro.progress-bar :percent="$pct" />
                        </div>

                        {{-- Kg cosecha / vino / capacidad --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-amber-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">Uva</p>
                                <p class="text-sm font-bold text-amber-700">
                                    {{ number_format($container->used_capacity, 0) }}
                                    <span class="text-[9px] font-normal text-amber-400">kg</span>
                                </p>
                            </div>
                            <div class="bg-violet-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-violet-400 uppercase tracking-wide mb-0.5">Vino</p>
                                <p class="text-sm font-bold text-violet-700">
                                    {{ number_format($container->wine_volume_liters, 0) }}
                                    <span class="text-[9px] font-normal text-violet-400">L</span>
                                </p>
                            </div>
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">Cap.</p>
                                <p class="text-sm font-bold text-zinc-600">
                                    {{ number_format($container->capacity, 0) }}
                                    <span class="text-[9px] font-normal text-zinc-400">kg</span>
                                </p>
                            </div>
                        </div>

                        {{-- Tipo + recepciones --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">Tipo</span>
                            <flux:badge color="zinc" size="sm">{{ $typeName }}</flux:badge>
                        </div>
                        @if($container->harvests_count > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Recepciones</span>
                                <span class="text-zinc-700 font-medium">{{ $container->harvests_count }}</span>
                            </div>
                        @endif
                        @if($container->description)
                            <p class="text-xs text-zinc-400 truncate">{{ $container->description }}</p>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ roleRoute('containers.show', $container) }}" title="Ver detalle">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                    <flux:icon icon="eye" class="size-4" />
                                </button>
                            </a>
                            @if(!$container->archived && $container->wine_volume_liters > 0)
                                <button
                                    wire:click="emptyWine({{ $container->id }})"
                                    wire:confirm="¿Vaciar el vino elaborado de «{{ $container->name }}»?"
                                    wire:loading.attr="disabled"
                                    title="Vaciar vino"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-orange-500 hover:bg-orange-50 transition-colors">
                                    <flux:icon icon="arrow-path" class="size-4" />
                                </button>
                            @endif
                            <a href="{{ roleRoute('containers.edit', $container) }}" title="Editar">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil" class="size-4" />
                                </button>
                            </a>
                            @if($container->archived)
                                <button wire:click="unarchive({{ $container->id }})"
                                    title="Activar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors">
                                    <flux:icon icon="check-circle" class="size-4" />
                                </button>
                            @else
                                <button wire:click="archive({{ $container->id }})"
                                wire:loading.attr="disabled"
                                    wire:confirm="¿Desactivar este contenedor?"
                                    title="Desactivar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="no-symbol" class="size-4" />
                                </button>
                            @endif
                            @if($container->harvests_count === 0)
                                <button wire:click="delete({{ $container->id }})"
                                wire:loading.attr="disabled"
                                    wire:confirm="¿Eliminar este contenedor permanentemente?"
                                    title="Eliminar"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <flux:icon icon="trash" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $containers->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="cube"
            title="{{ $currentTab === 'active' ? 'No hay contenedores activos' : 'No hay contenedores inactivos' }}"
            description="{{ $search || $typeFilter ? 'Ningún contenedor coincide con los filtros aplicados.' : ($currentTab === 'active' ? 'Crea tu primer contenedor para empezar a asignar recepciones de uva.' : '') }}"
        >
            @if($search || $typeFilter)
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @elseif($currentTab === 'active')
                <x-slot:action>
                    <flux:button href="{{ roleRoute('containers.create') }}" variant="primary" icon="plus">
                        Nuevo Contenedor
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif
</div>
