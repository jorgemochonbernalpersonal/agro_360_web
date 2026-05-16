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
    <x-agro.stats-section key="container-returns">
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
    </x-agro.stats-section>

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
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar por producto, punto de recogida..." />
        <x-agro.filter-button modal="container-returns-filters" :count="$filterCount" />
    </div>

    {{-- Chips filtros activos --}}
    @if($filterCampaign || $filterCollectionSystem)
        <div class="flex flex-wrap items-center gap-2">
            @if($filterCampaign)
                @php $camp = $campaigns->firstWhere('id', $filterCampaign); @endphp
                <x-agro.filter-chip icon="calendar-days" :label="$camp?->name ?? $filterCampaign" wireRemove="$set('filterCampaign', '')" />
            @endif
            @if($filterCollectionSystem)
                <x-agro.filter-chip icon="archive-box-x-mark" :label="$collectionSystems[$filterCollectionSystem] ?? $filterCollectionSystem" wireRemove="$set('filterCollectionSystem', '')" />
            @endif
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Limpiar todo</button>
        </div>
    @endif

    {{-- Skeleton carga --}}
    <x-agro.loading-grid target="switchTab, search, filterCampaign, filterCollectionSystem, nextPage, previousPage, gotoPage" />

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
                            <x-agro.card-item-header
                                icon="archive-box-x-mark"
                                :title="$entry->product_name"
                                :subtitle="$entry->date->format('d/m/Y')"
                                :iconBg="$systemColor['bg']"
                                :iconColor="$systemColor['icon']"
                                size="md"
                                radius="xl"
                            >
                                <x-agro.status-badge :color="$entry->collection_system === 'sigfito' ? 'green' : ($entry->collection_system === 'field' ? 'blue' : 'zinc')">
                                    {{ $entry->collection_system_label }}
                                </x-agro.status-badge>
                            </x-agro.card-item-header>
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
