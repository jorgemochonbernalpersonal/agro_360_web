<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Contenedores de Bodega"
        description="Gestiona tus depósitos, barricas y otros contenedores"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('containers.analytics') }}" wire:navigate variant="ghost" icon="chart-bar">
                Analítica
            </flux:button>
            <flux:button href="{{ roleRoute('containers.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo contenedor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs Activos / Inactivos --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',   'count' => $stats['active']],
            'archived' => ['label' => 'Inactivos', 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount =
            (int) !empty($typeFilter) +
            (int) !empty($occupancyFilter) +
            (int) !empty($roomFilter) +
            (int) !empty($materialFilter);

        $occupancyLabels = ['empty' => 'Vacíos', 'in_use' => 'En uso', 'full' => 'Llenos'];
    @endphp

    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar contenedor..." />

        {{-- Botón Filtros --}}
        <x-agro.filter-button modal="container-filters" :count="$filterCount" />

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('containers.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">

            @if($search)
                <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $search . '&quot;'" wireRemove="$set('search', '')" />
            @endif

            @if($typeFilter)
                <x-agro.filter-chip icon="cube" :label="$types->firstWhere('id', $typeFilter)?->name ?? ''" wireRemove="$set('typeFilter', '')" />
            @endif

            @if($occupancyFilter)
                <x-agro.filter-chip icon="chart-bar" :label="$occupancyLabels[$occupancyFilter] ?? ''" wireRemove="$set('occupancyFilter', '')" />
            @endif

            @if($roomFilter)
                <x-agro.filter-chip icon="building-office" :label="$rooms->firstWhere('id', $roomFilter)?->name ?? ''" wireRemove="$set('roomFilter', '')" />
            @endif

            @if($materialFilter)
                <x-agro.filter-chip icon="beaker" :label="$materials->firstWhere('id', $materialFilter)?->name ?? ''" wireRemove="$set('materialFilter', '')" />
            @endif

            <button
                wire:click="clearFilters"
                class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            >
                Limpiar todo
            </button>
        </div>
    @endif

    {{-- Grid --}}
    @if($containers->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="switchTab, search, typeFilter, occupancyFilter, roomFilter, materialFilter, clearFilters"
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
                        <x-agro.progress-bar
                            :percentage="$pct"
                            label="Ocupación"
                            :showValues="false"
                        />

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
                            <x-agro.action-button icon="eye" variant="primary" href="{{ roleRoute('containers.show', $container) }}" title="Ver detalle" />
                            @if(!$container->archived && $container->wine_volume_liters > 0)
                                <x-agro.action-button icon="arrow-path" variant="warning" wire:click="emptyWine({{ $container->id }})" wire:confirm="¿Vaciar el vino elaborado de «{{ $container->name }}»?" wire:loading.attr="disabled" title="Vaciar vino" />
                            @endif
                            <x-agro.action-button icon="pencil" variant="default" href="{{ roleRoute('containers.edit', $container) }}" title="Editar" />
                            @if($container->archived)
                                <x-agro.action-button variant="activate" wire:click="unarchive({{ $container->id }})" title="Activar" />
                            @else
                                <x-agro.action-button variant="deactivate" wire:click="archive({{ $container->id }})" wire:loading.attr="disabled" wire:confirm="¿Desactivar este contenedor?" title="Desactivar" />
                            @endif
                            @if($container->harvests_count === 0)
                                <x-agro.action-button variant="delete" wire:click="delete({{ $container->id }})" wire:loading.attr="disabled" wire:confirm="¿Eliminar este contenedor permanentemente?" title="Eliminar" />
                            @endif
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$containers" />
    @else
        <x-agro.empty-state
            icon="cube"
            title="{{ $currentTab === 'active' ? 'No hay contenedores activos' : 'No hay contenedores inactivos' }}"
            description="{{ ($search || $filterCount > 0) ? 'Ningún contenedor coincide con los filtros aplicados.' : ($currentTab === 'active' ? 'Crea tu primer contenedor para empezar a asignar recepciones de uva.' : '') }}"
        >
            @if($search || $filterCount > 0)
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

    {{-- Modal: Filtros --}}
    <x-agro.modal name="container-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'container-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Tipo de contenedor</label>
                <flux:select wire:model.live="typeFilter">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Ocupación</label>
                <flux:select wire:model.live="occupancyFilter">
                    <option value="">Cualquier estado</option>
                    <option value="empty">Vacíos</option>
                    <option value="in_use">En uso</option>
                    <option value="full">Llenos</option>
                </flux:select>
            </div>

            @if($rooms->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Sala de bodega</label>
                    <flux:select wire:model.live="roomFilter">
                        <option value="">Todas las salas</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            @if($materials->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Material</label>
                    <flux:select wire:model.live="materialFilter">
                        <option value="">Todos los materiales</option>
                        @foreach($materials as $material)
                            <option value="{{ $material->id }}">{{ $material->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

        </div>

        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCount > 0)
                <button
                    wire:click="clearFilters"
                    class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors"
                >
                    Limpiar filtros
                </button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'container-filters')" variant="primary">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
