<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Gestión de Envases Fitosanitarios"
        description="Registro de entregas de envases vacíos en puntos SIGFITO / FIELD (RD 1311/2012)"
        icon="archive-box-x-mark"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.container-returns.create') }}" variant="primary" icon="plus">
                Registrar Entrega
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats (colapsables) --}}
    <div x-data="{
        open: localStorage.getItem('container-returns-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('container-returns-stats-open', String(this.open));
        }
    }">
        <button @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3">
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="grid grid-cols-2 gap-4">
                <x-agro.stat-card
                    label="Total registros"
                    :value="$stats['active'] + $stats['archived']"
                    :description="$stats['active'] . ' activos · ' . $stats['archived'] . ' archivados'"
                    icon="archive-box-x-mark"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Envases entregados"
                    :value="number_format($stats['total_containers'], 0, ',', '.')"
                    description="Campaña seleccionada"
                    icon="cube"
                    color="green"
                />
                <x-agro.stat-card
                    label="Activos"
                    :value="$stats['active']"
                    description="Registros en curso"
                    icon="check-circle"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Archivados"
                    :value="$stats['archived']"
                    :description="$stats['archived'] > 0 ? 'Fuera de activo' : 'Ninguno archivado'"
                    icon="archive-box"
                    color="zinc"
                />
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'active'   => ['label' => 'Activos',    'count' => $stats['active']],
            'archived' => ['label' => 'Archivados', 'count' => $stats['archived']],
        ]"
        :active="$currentTab"
        wireMethod="switchTab"
    />

    {{-- Toolbar --}}
    @php
        $filterCount = (int) !empty($filterCampaign) + (int) !empty($filterCollectionSystem);
    @endphp
    <div class="flex items-center gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por producto, punto de recogida..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
            />
        </div>
        <button
            x-on:click="$dispatch('open-modal', 'container-returns-filters')"
            class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
        >
            <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
            Filtros
            @if($filterCount > 0)
                <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
            @endif
        </button>
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterCollectionSystem)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="calendar-days" class="size-3" />
                    {{ $camp?->name ?? $filterCampaign }}
                    <button wire:click="$set('filterCampaign', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            @if($filterCollectionSystem)
                <span class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                    <flux:icon icon="archive-box-x-mark" class="size-3" />
                    {{ $collectionSystems[$filterCollectionSystem] ?? $filterCollectionSystem }}
                    <button wire:click="$set('filterCollectionSystem', '')" class="ml-0.5 p-0.5 rounded-full hover:bg-agro-200 transition-colors">
                        <flux:icon icon="x-mark" class="size-3" />
                    </button>
                </span>
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <div wire:loading wire:target="switchTab, search, filterCampaign, filterCollectionSystem, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @for($i = 0; $i < 8; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de cards --}}
    <div wire:loading.remove wire:target="switchTab, search, filterCampaign, filterCollectionSystem, nextPage, previousPage, gotoPage">
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="archive-box-x-mark"
                title="{{ $currentTab === 'active' ? 'Sin entregas registradas' : 'Sin registros archivados' }}"
                description="{{ $search || $filterCampaign || $filterCollectionSystem ? 'Ningún registro coincide con los filtros aplicados.' : 'Registra cada entrega de envases vacíos en puntos de recogida autorizados.' }}"
            >
                @if(!$search && !$filterCampaign && !$filterCollectionSystem && $currentTab === 'active')
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.container-returns.create') }}" variant="primary" icon="plus">
                            Registrar Entrega
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($entries as $entry)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $systemColor = match($entry->collection_system) {
                            'sigfito' => ['bg' => 'bg-green-100',  'icon' => 'text-green-600'],
                            'field'   => ['bg' => 'bg-blue-100',   'icon' => 'text-blue-600'],
                            default   => ['bg' => 'bg-zinc-100',   'icon' => 'text-zinc-400'],
                        };
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1 {{ $currentTab === 'archived' ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="cr-{{ $entry->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 {{ $systemColor['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                                    <flux:icon icon="archive-box-x-mark" class="size-5 {{ $systemColor['icon'] }}" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $entry->product_name }}</h3>
                                    <p class="text-xs text-zinc-500">{{ $entry->date->format('d/m/Y') }}</p>
                                </div>
                                <x-agro.status-badge :color="$entry->collection_system === 'sigfito' ? 'green' : ($entry->collection_system === 'field' ? 'blue' : 'zinc')" class="shrink-0">
                                    {{ $entry->collection_system_label }}
                                </x-agro.status-badge>
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-green-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-green-400 uppercase tracking-widest mb-1">Envases</p>
                                    <p class="text-2xl font-bold text-green-700 leading-none">
                                        {{ $entry->containers_quantity }}
                                        <span class="text-xs font-medium text-zinc-400 ml-0.5">uds.</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-1">Tipo</p>
                                    <p class="text-sm font-semibold text-zinc-600 leading-tight">{{ $entry->container_type_label }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2 text-zinc-600">
                                    <flux:icon icon="map-pin" class="size-4 text-zinc-400 shrink-0" />
                                    <span class="truncate">{{ $entry->collection_point }}</span>
                                </div>
                                @if($entry->registration_number)
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon icon="identification" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate font-mono text-xs">{{ $entry->registration_number }}</span>
                                    </div>
                                @endif
                                @if($entry->transport_document)
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon icon="document-text" class="size-4 text-zinc-400 shrink-0" />
                                        <span class="truncate text-xs">Alb. {{ $entry->transport_document }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                @if($currentTab === 'active')
                                    <a href="{{ roleRoute('viticulturist.container-returns.edit', $entry) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                       title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <button wire:click="archive({{ $entry->id }})"
                                            wire:confirm="¿Archivar este registro?"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                            title="Archivar">
                                        <flux:icon icon="archive-box" class="size-4" />
                                    </button>
                                @else
                                    <button wire:click="unarchive({{ $entry->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors"
                                            title="Restaurar">
                                        <flux:icon icon="arrow-uturn-left" class="size-4" />
                                    </button>
                                    <button wire:click="delete({{ $entry->id }})"
                                            wire:confirm="¿Eliminar este registro permanentemente?"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Eliminar">
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($entries->hasPages())
                <div class="mt-6">{{ $entries->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Modal: Filtros --}}
    <x-agro.modal name="container-returns-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'container-returns-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Campaña</label>
                <flux:select wire:model.live="filterCampaign">
                    <flux:select.option value="">Todas las campañas</flux:select.option>
                    @foreach($campaigns as $c)
                        <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wide mb-1.5">Sistema de recogida</label>
                <flux:select wire:model.live="filterCollectionSystem">
                    <flux:select.option value="">Todos los sistemas</flux:select.option>
                    @foreach($collectionSystems as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zinc-200 flex items-center justify-between">
            @if($filterCampaign || $filterCollectionSystem)
                <button wire:click="clearFilters" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar filtros</button>
            @else
                <span></span>
            @endif
            <flux:button x-on:click="$dispatch('close-modal', 'container-returns-filters')" variant="primary">Aplicar</flux:button>
        </div>
    </x-agro.modal>

</div>
