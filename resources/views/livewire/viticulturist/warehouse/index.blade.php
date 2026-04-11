<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Almacén de Insumos"
        description="Gestiona fitosanitarios, otros insumos y tus almacenes físicos"
    />

    {{-- Pestañas principales --}}
    <div class="flex gap-1 border-b border-zinc-200">
        @foreach(['fitosanitarios' => ['label' => 'Stock Fitosanitario', 'icon' => 'beaker'], 'insumos' => ['label' => 'Otros Insumos', 'icon' => 'archive-box'], 'almacenes' => ['label' => 'Almacenes', 'icon' => 'building-office']] as $key => $tabDef)
            <button
                wire:click="$set('tab', '{{ $key }}')"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === $key
                        ? 'border-agro-500 text-agro-600'
                        : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
            >
                <flux:icon icon="{{ $tabDef['icon'] }}" class="size-4" />
                {{ $tabDef['label'] }}
            </button>
        @endforeach
    </div>

    {{-- =====================================================
         TAB: FITOSANITARIOS
    ====================================================== --}}
    @if($tab === 'fitosanitarios')

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-1">Productos</p>
                <p class="text-xl font-bold text-zinc-900">{{ $stats['total_products'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-1">Valor total</p>
                <p class="text-xl font-bold text-zinc-900">{{ number_format($stats['total_value'], 2) }} €</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-amber-600 font-medium uppercase tracking-wide mb-1">Stock bajo</p>
                <p class="text-xl font-bold text-amber-700">{{ $stats['low_stock_count'] }}</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-orange-600 font-medium uppercase tracking-wide mb-1">Próx. caducar</p>
                <p class="text-xl font-bold text-orange-700">{{ $stats['expiring_soon_count'] }}</p>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                    </div>
                    <input
                        wire:model.live.debounce.300ms="inv_search"
                        type="text"
                        placeholder="Buscar producto..."
                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                    />
                </div>

                @php $filterCount = ($inv_product ? 1 : 0) + ($inv_warehouse ? 1 : 0) + ($inv_status !== 'all' ? 1 : 0); @endphp
                <button
                    x-on:click="$dispatch('open-modal', 'almacen-inv-filters')"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
                >
                    <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                    Filtros
                    @if($filterCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
                    @endif
                </button>

                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.analytics') }}" variant="outline" icon="chart-bar">
                    Analíticas
                </flux:button>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.create') }}" variant="primary" icon="plus">
                    Registrar
                </flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">Filtros activos:</span>

                    @if($inv_search)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            <flux:icon icon="magnifying-glass" class="size-3" />
                            "{{ $inv_search }}"
                            <button wire:click="$set('inv_search', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    @if($inv_product)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            Producto: {{ $inv_products->firstWhere('id', $inv_product)?->name ?? $inv_product }}
                            <button wire:click="$set('inv_product', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    @if($inv_warehouse)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            Almacén: {{ $inv_warehouses->firstWhere('id', $inv_warehouse)?->name ?? $inv_warehouse }}
                            <button wire:click="$set('inv_warehouse', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    @if($inv_status !== 'all')
                        @php $statusLabels = ['low_stock' => 'Stock bajo', 'expiring' => 'Próx. caducar', 'expired' => 'Caducados']; @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            {{ $statusLabels[$inv_status] ?? $inv_status }}
                            <button wire:click="$set('inv_status', 'all')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    <button wire:click="clearInventoryFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">Limpiar todo</button>
                </div>
            @endif
        </div>

        {{-- Card grid --}}
        @if($stocks->count() > 0)
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="inv_search, inv_product, inv_warehouse, inv_status, clearInventoryFilters"
            >
                @foreach($stocks as $i => $stock)
                    @php
                        $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';

                        [$statusLabel, $statusActive, $statusType] = match(true) {
                            $stock->isExpired()                                                       => ['Caducado',      false, 'danger'],
                            $stock->isExpiringSoon()                                                  => ['Próx. caducar', true,  'warning'],
                            $stock->getAvailableQuantity() < ($stock->minimum_stock ?? 5)             => ['Stock bajo',    true,  'info'],
                            default                                                                   => ['OK',            true,  'default'],
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

                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $stock->warehouse?->name ?? 'Sin almacén' }}</span>
                            @if($stock->batch_number)
                                <span class="text-xs text-zinc-400 shrink-0">· Lote: {{ $stock->batch_number }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Cantidad</p>
                                <p class="text-sm font-bold text-agro-700">{{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}</p>
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Caducidad</p>
                                <p class="text-sm font-bold text-zinc-700">{{ $stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : '—' }}</p>
                            </div>
                        </div>

                        @if($stock->unit_price)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="banknotes" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ number_format($stock->unit_price, 2) }} €/{{ $stock->unit }}</span>
                            </div>
                        @endif

                        <x-slot:footer>
                            <div class="flex items-center justify-between gap-1">
                                <button
                                    wire:click="deactivateStock({{ $stock->id }})"
                                    wire:confirm="¿Archivar este lote? Dejará de aparecer en el inventario activo."
                                    class="{{ $btnBase }} hover:text-red-600 hover:bg-red-50"
                                    title="Archivar lote"
                                >
                                    <flux:icon icon="archive-box-arrow-down" class="size-4" />
                                </button>
                                <div class="flex items-center gap-1">
                                    <a href="{{ roleRoute('viticulturist.warehouse.stock.edit', $stock->id) }}" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                    <a href="{{ roleRoute('viticulturist.warehouse.stock.consume', $stock->id) }}" class="{{ $btnBase }}" title="Consumir">
                                        <flux:icon icon="minus-circle" class="size-4" />
                                    </a>
                                    <a href="{{ roleRoute('viticulturist.warehouse.stock.movements', $stock->id) }}" class="{{ $btnBase }}" title="Movimientos">
                                        <flux:icon icon="document-text" class="size-4" />
                                    </a>
                                </div>
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
                description="{{ $inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all' ? 'Ningún producto coincide con los filtros aplicados.' : 'Registra tu primer producto fitosanitario para empezar.' }}"
            >
                @if($inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all')
                    <x-slot:action>
                        <flux:button wire:click="clearInventoryFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.create') }}" variant="primary" icon="plus">Registrar Stock</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif

        {{-- Modal Filtros --}}
        <x-agro.modal name="almacen-inv-filters" maxWidth="sm">
            <div class="px-6 py-4 border-b border-zinc-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                            <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                        </div>
                        <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                    </div>
                    <flux:button x-on:click="$dispatch('close-modal', 'almacen-inv-filters')" variant="ghost" size="sm" icon="x-mark" />
                </div>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Producto</label>
                    <select wire:model.live="inv_product"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                        <option value="">Todos los productos</option>
                        @foreach($inv_products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Almacén</label>
                    <select wire:model.live="inv_warehouse"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                        <option value="">Todos los almacenes</option>
                        @foreach($inv_warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Estado</label>
                    <select wire:model.live="inv_status"
                            class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                        <option value="all">Todos</option>
                        <option value="low_stock">Stock bajo</option>
                        <option value="expiring">Próximos a caducar</option>
                        <option value="expired">Caducados</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
                <button wire:click="clearInventoryFilters" x-on:click="$dispatch('close-modal', 'almacen-inv-filters')"
                        class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                    Limpiar filtros
                </button>
                <flux:button x-on:click="$dispatch('close-modal', 'almacen-inv-filters')" variant="primary" size="sm">
                    Aplicar
                </flux:button>
            </div>
        </x-agro.modal>

    {{-- =====================================================
         TAB: INSUMOS
    ====================================================== --}}
    @elseif($tab === 'insumos')

        {{-- Toolbar --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                    </div>
                    <input
                        wire:model.live.debounce.300ms="sup_search"
                        type="text"
                        placeholder="Buscar insumo..."
                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                    />
                </div>

                @php $supFilterCount = ($sup_type ? 1 : 0) + ($sup_low ? 1 : 0); @endphp
                <button
                    x-on:click="$dispatch('open-modal', 'almacen-sup-filters')"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
                >
                    <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                    Filtros
                    @if($supFilterCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">{{ $supFilterCount }}</span>
                    @endif
                </button>

                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.supplies.create') }}" variant="primary" icon="plus">Nuevo Insumo</flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($sup_search || $sup_type || $sup_low)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">Filtros activos:</span>

                    @if($sup_search)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            <flux:icon icon="magnifying-glass" class="size-3" />
                            "{{ $sup_search }}"
                            <button wire:click="$set('sup_search', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    @if($sup_type)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            {{ $supplyTypes[$sup_type] ?? $sup_type }}
                            <button wire:click="$set('sup_type', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    @if($sup_low)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                            Stock bajo
                            <button wire:click="$set('sup_low', false)" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                        </span>
                    @endif

                    <button wire:click="clearSupplyFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">Limpiar todo</button>
                </div>
            @endif
        </div>

        {{-- Card grid --}}
        @if($supplies->isEmpty())
            <x-agro.empty-state
                icon="archive-box"
                message="Almacén vacío"
                description="{{ $sup_search || $sup_type || $sup_low ? 'Ningún insumo coincide con los filtros aplicados.' : 'Registra los insumos de tu almacén para controlar el stock de abonos y otros productos.' }}"
            >
                @if($sup_search || $sup_type || $sup_low)
                    <x-slot:action>
                        <flux:button wire:click="clearSupplyFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.supplies.create') }}" variant="primary" icon="plus">Nuevo Insumo</flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @else
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="sup_type, sup_low"
            >
                @foreach($supplies as $i => $supply)
                    @php
                        $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                        [$statusLabel, $statusActive, $statusType] = match(true) {
                            $supply->isExpired()      => ['Caducado',      false, 'danger'],
                            $supply->isExpiringSoon() => ['Próx. caducar', true,  'warning'],
                            $supply->isLowStock()     => ['Stock bajo',    true,  'info'],
                            default                   => ['OK',            true,  'default'],
                        };
                    @endphp

                    <x-agro.card
                        wire:key="supply-{{ $supply->id }}"
                        class="animate-fade-in-up hover:-translate-y-1"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                    <flux:icon icon="archive-box" class="size-4 text-zinc-500" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $supply->name }}</p>
                                    @if($supply->commercial_name)
                                        <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $supply->commercial_name }}</p>
                                    @endif
                                </div>
                                @if($statusType === 'default')
                                    <x-agro.status-badge :status="true" label="OK" />
                                @else
                                    <x-agro.status-badge :status="$statusActive" :label="$statusLabel" type="{{ $statusType }}" />
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">{{ $supply->supply_type_label }}</span>
                            @if($supply->registration_number)
                                <span class="text-xs text-zinc-400 shrink-0 font-mono">· Reg: {{ $supply->registration_number }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $supply->warehouse?->name ?? 'Sin almacén' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Stock actual</p>
                                <p class="text-sm font-bold {{ $supply->isLowStock() ? 'text-red-600' : 'text-agro-700' }}">
                                    {{ number_format($supply->current_stock, 3, ',', '.') }} {{ $supply->unit_of_measurement }}
                                </p>
                                @if($supply->min_stock_alert)
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Mín: {{ $supply->min_stock_alert }} {{ $supply->unit_of_measurement }}</p>
                                @endif
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Caducidad</p>
                                <p class="text-sm font-bold {{ $supply->isExpiringSoon() ? 'text-amber-600' : 'text-zinc-700' }}">
                                    {{ $supply->expiry_date ? $supply->expiry_date->format('d/m/Y') : '—' }}
                                </p>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openPurchase({{ $supply->id }})" class="{{ $btnBase }}" title="Registrar compra">
                                    <flux:icon icon="shopping-cart" class="size-4" />
                                </button>
                                <a href="{{ roleRoute('viticulturist.warehouse.supplies.edit', $supply) }}" class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivateSupply({{ $supply->id }})"
                                    wire:confirm="¿Archivar este insumo?"
                                    class="{{ $btnBase }}"
                                    title="Archivar"
                                >
                                    <flux:icon icon="archive-box-arrow-down" class="size-4" />
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($supplies->hasPages())
                <div class="flex justify-center">{{ $supplies->links() }}</div>
            @endif
        @endif

    {{-- =====================================================
         TAB: ALMACENES
    ====================================================== --}}
    @elseif($tab === 'almacenes')

        {{-- Sub-tabs activos/inactivos --}}
        <x-agro.tabs
            :tabs="[
                'active'   => ['label' => 'Activos',   'count' => $wh_stats['active']],
                'inactive' => ['label' => 'Inactivos',  'count' => $wh_stats['inactive']],
            ]"
            :active="$wh_tab"
            wireMethod="switchWhTab"
        />

        {{-- Toolbar --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                    </div>
                    <input
                        wire:model.live.debounce.300ms="wh_search"
                        type="text"
                        placeholder="Buscar por nombre o ubicación..."
                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                    />
                </div>
                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>
                <flux:button href="{{ roleRoute('viticulturist.warehouse.warehouses.create') }}" variant="primary" icon="plus">
                    Nuevo
                </flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($wh_search)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">Filtros activos:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $wh_search }}"
                        <button wire:click="$set('wh_search', '')" class="hover:text-agro-900 ml-0.5"><flux:icon icon="x-mark" class="size-3" /></button>
                    </span>
                </div>
            @endif
        </div>

        {{-- Card grid --}}
        @if($warehouses->count() > 0)
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="switchWhTab, wh_search"
            >
                @foreach($warehouses as $i => $warehouse)
                    @php
                        $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                        $btnDanger  = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                        $btnSuccess = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
                    @endphp

                    <x-agro.card
                        wire:key="warehouse-{{ $warehouse->id }}"
                        class="animate-fade-in-up hover:-translate-y-1 {{ !$warehouse->active ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                    <flux:icon icon="building-office" class="size-4 text-zinc-500" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $warehouse->name }}</p>
                                    @if($warehouse->location)
                                        <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $warehouse->location }}</p>
                                    @endif
                                </div>
                                <x-agro.status-badge :status="$warehouse->active" />
                            </div>
                        </x-slot:header>

                        @if($warehouse->description)
                            <div class="flex items-start gap-2 mb-3">
                                <flux:icon icon="information-circle" class="size-3.5 text-zinc-400 shrink-0 mt-0.5" />
                                <span class="text-xs text-zinc-500 line-clamp-2">{{ $warehouse->description }}</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">Fitosanitarios</p>
                                <p class="text-sm font-bold text-agro-700">{{ $warehouse->stocks_count }}</p>
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Insumos</p>
                                <p class="text-sm font-bold text-zinc-700">{{ $warehouse->supplies_count }}</p>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <a href="{{ roleRoute('viticulturist.warehouse.warehouses.edit', $warehouse->id) }}" class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil-square" class="size-4" />
                                    </a>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        wire:click="toggleActive({{ $warehouse->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleActive({{ $warehouse->id }})"
                                        class="{{ $warehouse->active ? $btnDanger : $btnSuccess }}"
                                        title="{{ $warehouse->active ? 'Desactivar' : 'Activar' }}"
                                    >
                                        <span wire:loading.remove wire:target="toggleActive({{ $warehouse->id }})">
                                            <flux:icon icon="{{ $warehouse->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                        </span>
                                        <span wire:loading wire:target="toggleActive({{ $warehouse->id }})">
                                            <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    @if($warehouse->stocks_count === 0)
                                        <button
                                            wire:click="deleteWarehouse({{ $warehouse->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar este almacén?"
                                            wire:loading.attr="disabled"
                                            class="{{ $btnDanger }}"
                                            title="Eliminar"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            @if($warehouses->hasPages())
                <div class="flex justify-center">{{ $warehouses->links() }}</div>
            @endif

        @else
            <x-agro.empty-state
                icon="building-office"
                message="{{ $wh_tab === 'active' ? 'No hay almacenes activos' : 'No hay almacenes inactivos' }}"
                description="{{ $wh_search ? 'Ningún almacén coincide con la búsqueda.' : 'Crea tu primer almacén para organizar el stock de productos.' }}"
            >
                @if($wh_search)
                    <x-slot:action>
                        <flux:button wire:click="$set('wh_search', '')" variant="outline" icon="x-mark">Limpiar búsqueda</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.warehouses.create') }}" variant="primary" icon="plus">
                            Nuevo Almacén
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif

    @endif

    {{-- Modal Filtros Insumos --}}
    <x-agro.modal name="almacen-sup-filters" maxWidth="sm">
        <div class="px-6 py-4 border-b border-zinc-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center">
                        <flux:icon icon="adjustments-horizontal" class="size-4 text-agro-600" />
                    </div>
                    <h3 class="text-base font-semibold text-zinc-900">Filtros</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'almacen-sup-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Tipo de insumo</label>
                <select wire:model.live="sup_type"
                        class="w-full px-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-400 focus:border-transparent">
                    <option value="">Todos los tipos</option>
                    @foreach($supplyTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <flux:checkbox wire:model.live="sup_low" label="Solo stock bajo" description="Muestra solo insumos por debajo del mínimo" />
        </div>
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearSupplyFilters" x-on:click="$dispatch('close-modal', 'almacen-sup-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                Limpiar filtros
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'almacen-sup-filters')" variant="primary" size="sm">
                Aplicar
            </flux:button>
        </div>
    </x-agro.modal>

    {{-- Modal Compra --}}
    <x-agro.modal name="supply-purchase" maxWidth="xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">Registrar Compra — {{ $purchaseSupplyName }}</h2>
            <flux:callout variant="info" icon="information-circle">
                Al guardar, el stock del insumo se incrementará automáticamente con la cantidad comprada.
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Fecha de factura</flux:label>
                    <flux:input wire:model="p_invoice_date" type="date" />
                    <flux:error name="p_invoice_date" />
                </flux:field>
                <flux:field>
                    <flux:label>Nº Factura</flux:label>
                    <flux:input wire:model="p_invoice_number" type="text" placeholder="FAC-2026-001" />
                    <flux:error name="p_invoice_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Cantidad comprada</flux:label>
                    <flux:input wire:model.live="p_quantity" type="number" step="0.001" min="0.001" placeholder="0.000" />
                    <flux:error name="p_quantity" />
                </flux:field>
                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:select wire:model="p_unit_of_measurement">
                        @foreach($units as $u)
                            <option value="{{ $u->symbol }}">{{ $u->name }} ({{ $u->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="p_unit_of_measurement" />
                </flux:field>

                <flux:field>
                    <flux:label>Precio/unidad (€)</flux:label>
                    <flux:input wire:model.live="p_price_per_unit" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="p_price_per_unit" />
                </flux:field>
                <flux:field>
                    <flux:label>Coste total (€)</flux:label>
                    <flux:input wire:model="p_total_cost" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="p_total_cost" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Proveedor</flux:label>
                    <flux:input wire:model="p_supplier_name" type="text" placeholder="Nombre del proveedor" />
                    <flux:error name="p_supplier_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="p_campaign_id">
                        <option value="">Sin campaña</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="p_campaign_id" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" x-on:click="$dispatch('close-modal', 'supply-purchase')">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="savePurchase">Registrar Compra</flux:button>
            </div>
        </div>
    </x-agro.modal>

</div>
