<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Contenedores de Bodega"
        description="Gestiona tus depósitos, barricas y otros contenedores"
    />

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
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar contenedor..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>

        {{-- Botón Filtros --}}
        <button
            x-on:click="$dispatch('open-modal', 'container-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $filterCount }}
                </span>
            @endif
        </button>

        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        <flux:button href="{{ roleRoute('containers.create') }}" variant="primary" icon="plus">
            Nuevo
        </flux:button>

    </div>

    {{-- Chips de filtros activos --}}
    @if($search || $filterCount > 0)
        <div class="flex flex-wrap items-center gap-2">

            @if($search)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="magnifying-glass" class="size-3" />
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif

            @if($typeFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="cube" class="size-3" />
                    {{ $types->firstWhere('id', $typeFilter)?->name ?? '' }}
                    <button wire:click="$set('typeFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif

            @if($occupancyFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="chart-bar" class="size-3" />
                    {{ $occupancyLabels[$occupancyFilter] ?? '' }}
                    <button wire:click="$set('occupancyFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif

            @if($roomFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="building-office" class="size-3" />
                    {{ $rooms->firstWhere('id', $roomFilter)?->name ?? '' }}
                    <button wire:click="$set('roomFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif

            @if($materialFilter)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="beaker" class="size-3" />
                    {{ $materials->firstWhere('id', $materialFilter)?->name ?? '' }}
                    <button wire:click="$set('materialFilter', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
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
