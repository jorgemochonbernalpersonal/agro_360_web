<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Inventario de Productos Fitosanitarios"
        description="Gestiona el stock de tus productos fitosanitarios y controla las existencias"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar producto..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php $filterCount = ($productFilter ? 1 : 0) + ($warehouseFilter ? 1 : 0) + ($statusFilter !== 'all' ? 1 : 0); @endphp
            <button
                x-on:click="$dispatch('open-modal', 'inventory-filters')"
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

            <flux:button href="{{ route('viticulturist.inventory.analytics') }}" variant="outline" icon="chart-bar">
                Analíticas
            </flux:button>

            <flux:button href="{{ route('viticulturist.inventory.create') }}" variant="primary" icon="plus">
                Registrar
            </flux:button>

        </div>

        {{-- Active filter chips --}}
        @if($search || $productFilter || $warehouseFilter || $statusFilter !== 'all')
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">Filtros activos:</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($productFilter)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Producto: {{ $products->firstWhere('id', $productFilter)?->name ?? $productFilter }}
                        <button wire:click="$set('productFilter', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($warehouseFilter)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        Almacén: {{ $warehouses->firstWhere('id', $warehouseFilter)?->name ?? $warehouseFilter }}
                        <button wire:click="$set('warehouseFilter', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($statusFilter !== 'all')
                    @php $statusLabels = ['low_stock' => 'Stock bajo', 'expiring' => 'Próx. caducar', 'expired' => 'Caducados']; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ $statusLabels[$statusFilter] ?? $statusFilter }}
                        <button wire:click="$set('statusFilter', 'all')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    Limpiar todo
                </button>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($stocks->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, productFilter, warehouseFilter, statusFilter, clearFilters"
        >
            @foreach($stocks as $i => $stock)
                @php
                    $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';

                    [$statusLabel, $statusActive, $statusType] = match(true) {
                        $stock->isExpired()                         => ['Caducado',      false, 'danger'],
                        $stock->isExpiringSoon()                    => ['Próx. caducar', true,  'warning'],
                        $stock->getAvailableQuantity() < ($stock->minimum_stock ?? 5) => ['Stock bajo', true, 'info'],
                        default                                     => ['OK',            true,  'default'],
                    };
                @endphp

                <x-agro.card
                    wire:key="stock-{{ $stock->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <flux:icon icon="archive-box" class="size-4 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $stock->product->name }}</p>
                                @if($stock->product->active_ingredient)
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $stock->product->active_ingredient }}</p>
                                @endif
                            </div>
                            @if($statusType === 'default')
                                <x-agro.status-badge :status="true" label="OK" />
                            @else
                                <x-agro.status-badge :status="$statusActive" :label="$statusLabel" type="{{ $statusType }}" />
                            @endif
                        </div>
                    </x-slot:header>

                    {{-- Almacén + Lote --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                        <span class="text-xs text-zinc-600 truncate">{{ $stock->warehouse?->name ?? 'Sin almacén' }}</span>
                        @if($stock->batch_number)
                            <span class="text-xs text-zinc-400 shrink-0">· Lote: {{ $stock->batch_number }}</span>
                        @endif
                    </div>

                    {{-- Métricas --}}
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-agro-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Cantidad</p>
                            <p class="text-sm font-bold text-agro-700">{{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Caducidad</p>
                            <p class="text-sm font-bold text-zinc-700">
                                {{ $stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Precio unitario --}}
                    @if($stock->unit_price)
                        <div class="flex items-center gap-2">
                            <flux:icon icon="banknotes" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">{{ number_format($stock->unit_price, 2) }} €/{{ $stock->unit }}</span>
                        </div>
                    @endif

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('viticulturist.inventory.edit', $stock->id) }}" class="{{ $btnBase }}" title="Editar">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <a href="{{ route('viticulturist.inventory.consume', $stock->id) }}" class="{{ $btnBase }}" title="Consumir">
                                <flux:icon icon="minus-circle" class="size-4" />
                            </a>
                            <a href="{{ route('viticulturist.inventory.movements', $stock->id) }}" class="{{ $btnBase }}" title="Movimientos">
                                <flux:icon icon="document-text" class="size-4" />
                            </a>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($stocks->hasPages())
            <div class="flex justify-center">{{ $stocks->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="archive-box"
            message="No hay stock registrado"
            description="{{ $search || $productFilter || $warehouseFilter || $statusFilter !== 'all' ? 'Ningún producto coincide con los filtros aplicados.' : 'Registra tu primer producto fitosanitario para empezar.' }}"
        >
            @if($search || $productFilter || $warehouseFilter || $statusFilter !== 'all')
                <x-slot:action>
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                </x-slot:action>
            @else
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.inventory.create') }}" variant="primary" icon="plus">
                        Registrar Stock
                    </flux:button>
                </x-slot:action>
            @endif
        </x-agro.empty-state>
    @endif

    {{-- Modal Filtros --}}
    <x-agro.modal name="inventory-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'inventory-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>

        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Producto</label>
                <select wire:model.live="productFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los productos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Almacén</label>
                <select wire:model.live="warehouseFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado</label>
                <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="all">Todos</option>
                    <option value="low_stock">Stock bajo</option>
                    <option value="expiring">Próximos a caducar</option>
                    <option value="expired">Caducados</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearFilters" x-on:click="$dispatch('close-modal', 'inventory-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'inventory-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

</div>
