<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Almacén de Insumos')"
        :description="__('Gestiona fitosanitarios, otros insumos y tus almacenes físicos')"
    />

    {{-- Pestañas principales --}}
    <div class="flex gap-1 border-b border-zinc-200">
        @foreach(['fitosanitarios' => ['label' => __('Stock Fitosanitario'), 'icon' => 'beaker'], 'insumos' => ['label' => __('Otros Insumos'), 'icon' => 'archive-box'], 'almacenes' => ['label' => __('Almacenes'), 'icon' => 'building-office']] as $key => $tabDef)
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
                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-1">{{ __('Productos') }}</p>
                <p class="text-xl font-bold text-zinc-900">{{ $stats['total_products'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-1">{{ __('Valor total') }}</p>
                <p class="text-xl font-bold text-zinc-900">{{ number_format($stats['total_value'], 2) }} €</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-amber-600 font-medium uppercase tracking-wide mb-1">{{ __('Stock bajo') }}</p>
                <p class="text-xl font-bold text-amber-700">{{ $stats['low_stock_count'] }}</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 shadow-sm">
                <p class="text-[10px] text-orange-600 font-medium uppercase tracking-wide mb-1">{{ __('Próx. caducar') }}</p>
                <p class="text-xl font-bold text-orange-700">{{ $stats['expiring_soon_count'] }}</p>
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <x-agro.search-input wire:model.live.debounce.300ms="inv_search" :placeholder="__('Buscar producto...')" />

                @php $filterCount = ($inv_product ? 1 : 0) + ($inv_warehouse ? 1 : 0) + ($inv_status !== 'all' ? 1 : 0); @endphp
                <x-agro.filter-button modal="almacen-inv-filters" :count="$filterCount" />

                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.analytics') }}" variant="outline" icon="chart-bar">
                    {{ __('Analíticas') }}
                </flux:button>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.create') }}" variant="primary" icon="plus">
                    {{ __('Registrar') }}
                </flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">{{ __('Filtros activos') }}:</span>

                    @if($inv_search)
                        <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $inv_search . '&quot;'" wireRemove="$set('inv_search', '')" />
                    @endif

                    @if($inv_product)
                        <x-agro.filter-chip :label="__('Producto') . ': ' . ($inv_products->firstWhere('id', $inv_product)?->name ?? $inv_product)" wireRemove="$set('inv_product', '')" />
                    @endif

                    @if($inv_warehouse)
                        <x-agro.filter-chip :label="__('Almacén') . ': ' . ($inv_warehouses->firstWhere('id', $inv_warehouse)?->name ?? $inv_warehouse)" wireRemove="$set('inv_warehouse', '')" />
                    @endif

                    @if($inv_status !== 'all')
                        @php $statusLabels = ['low_stock' => __('Stock bajo'), 'expiring' => __('Próx. caducar'), 'expired' => __('Caducados')]; @endphp
                        <x-agro.filter-chip :label="$statusLabels[$inv_status] ?? $inv_status" wireRemove="$set('inv_status', 'all')" />
                    @endif

                    <button wire:click="clearInventoryFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">{{ __('Limpiar todo') }}</button>
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
                        [$statusLabel, $statusActive, $statusType] = match(true) {
                            $stock->isExpired()                                                       => [__('Caducado'),      false, 'danger'],
                            $stock->isExpiringSoon()                                                  => [__('Próx. caducar'), true,  'warning'],
                            $stock->getAvailableQuantity() < ($stock->minimum_stock ?? 5)             => [__('Stock bajo'),    true,  'info'],
                            default                                                                   => ['OK',               true,  'default'],
                        };
                    @endphp

                    <x-agro.card
                        wire:key="stock-{{ $stock->id }}"
                        class="animate-fade-in-up hover:-translate-y-1"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="archive-box"
                                :title="$stock->product->name"
                                :subtitle="$stock->product->active_ingredient ?? null"
                            >
                                @if($statusType === 'default')
                                    <x-agro.status-badge :status="true" :label="__('OK')" />
                                @else
                                    <x-agro.status-badge :status="$statusActive" :label="$statusLabel" type="{{ $statusType }}" />
                                @endif
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $stock->warehouse?->name ?? __('Sin almacén') }}</span>
                            @if($stock->batch_number)
                                <span class="text-xs text-zinc-400 shrink-0">· {{ __('Lote') }}: {{ $stock->batch_number }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <x-agro.metric-cell :label="__('Cantidad')" :value="number_format($stock->getAvailableQuantity(), 3) . ' ' . $stock->unit" color="agro" />
                            <x-agro.metric-cell :label="__('Caducidad')" :value="$stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : '—'" color="zinc" />
                        </div>

                        @if($stock->unit_price)
                            <div class="flex items-center gap-2">
                                <flux:icon icon="banknotes" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="text-xs text-zinc-600">{{ number_format($stock->unit_price, 2) }} €/{{ $stock->unit }}</span>
                            </div>
                        @endif

                        <x-slot:footer>
                            <div class="flex items-center justify-between gap-1">
                                <x-agro.action-button
                                    variant="danger"
                                    icon="archive-box-arrow-down"
                                    wire:click="deactivateStock({{ $stock->id }})"
                                    wire:confirm="{{ __('¿Archivar este lote? Dejará de aparecer en el inventario activo.') }}"
                                    :title="__('Archivar lote')"
                                />
                                <div class="flex items-center gap-1">
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.warehouse.stock.edit', $stock->id) }}"
                                        :title="__('Editar')"
                                    />
                                    <x-agro.action-button
                                        variant="default"
                                        icon="minus-circle"
                                        href="{{ roleRoute('viticulturist.warehouse.stock.consume', $stock->id) }}"
                                        :title="__('Consumir')"
                                    />
                                    <x-agro.action-button
                                        variant="history"
                                        href="{{ roleRoute('viticulturist.warehouse.stock.movements', $stock->id) }}"
                                        :title="__('Movimientos')"
                                    />
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$stocks" />

        @else
            <x-agro.empty-state
                icon="archive-box"
                :message="__('No hay stock registrado')"
                :description="$inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all' ? __('Ningún producto coincide con los filtros aplicados.') : __('Registra tu primer producto fitosanitario para empezar.')"
            >
                @if($inv_search || $inv_product || $inv_warehouse || $inv_status !== 'all')
                    <x-slot:action>
                        <flux:button wire:click="clearInventoryFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.stock.create') }}" variant="primary" icon="plus">{{ __('Registrar Stock') }}</flux:button>
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
                        <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                    </div>
                    <flux:button x-on:click="$dispatch('close-modal', 'almacen-inv-filters')" variant="ghost" size="sm" icon="x-mark" />
                </div>
            </div>
            <div class="px-6 py-5 space-y-5">
                <x-agro.filter-select :label="__('Producto')" wire:model.live="inv_product" :placeholder="__('Todos los productos')">
                    @foreach($inv_products as $product)
                        <flux:select.option value="{{ $product->id }}">{{ $product->name }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
                <x-agro.filter-select :label="__('Almacén')" wire:model.live="inv_warehouse" :placeholder="__('Todos los almacenes')">
                    @foreach($inv_warehouses as $wh)
                        <flux:select.option value="{{ $wh->id }}">{{ $wh->name }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
                <x-agro.filter-select :label="__('Estado')" wire:model.live="inv_status">
                    <flux:select.option value="all">{{ __('Todos') }}</flux:select.option>
                    <flux:select.option value="low_stock">{{ __('Stock bajo') }}</flux:select.option>
                    <flux:select.option value="expiring">{{ __('Próximos a caducar') }}</flux:select.option>
                    <flux:select.option value="expired">{{ __('Caducados') }}</flux:select.option>
                </x-agro.filter-select>
            </div>
            <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
                <button wire:click="clearInventoryFilters" x-on:click="$dispatch('close-modal', 'almacen-inv-filters')"
                        class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                    {{ __('Limpiar filtros') }}
                </button>
                <flux:button x-on:click="$dispatch('close-modal', 'almacen-inv-filters')" variant="primary" size="sm">
                    {{ __('Aplicar') }}
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
                <x-agro.search-input wire:model.live.debounce.300ms="sup_search" :placeholder="__('Buscar insumo...')" />

                @php $supFilterCount = ($sup_type ? 1 : 0) + ($sup_low ? 1 : 0); @endphp
                <x-agro.filter-button modal="almacen-sup-filters" :count="$supFilterCount" />

                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

                <flux:button href="{{ roleRoute('viticulturist.warehouse.supplies.create') }}" variant="primary" icon="plus">{{ __('Nuevo Insumo') }}</flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($sup_search || $sup_type || $sup_low)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">{{ __('Filtros activos') }}:</span>

                    @if($sup_search)
                        <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $sup_search . '&quot;'" wireRemove="$set('sup_search', '')" />
                    @endif

                    @if($sup_type)
                        <x-agro.filter-chip :label="$supplyTypes[$sup_type] ?? $sup_type" wireRemove="$set('sup_type', '')" />
                    @endif

                    @if($sup_low)
                        <x-agro.filter-chip :label="__('Stock bajo')" wireRemove="$set('sup_low', false)" />
                    @endif

                    <button wire:click="clearSupplyFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">{{ __('Limpiar todo') }}</button>
                </div>
            @endif
        </div>

        {{-- Card grid --}}
        @if($supplies->isEmpty())
            <x-agro.empty-state
                icon="archive-box"
                :message="__('Almacén vacío')"
                :description="$sup_search || $sup_type || $sup_low ? __('Ningún insumo coincide con los filtros aplicados.') : __('Registra los insumos de tu almacén para controlar el stock de abonos y otros productos.')"
            >
                @if($sup_search || $sup_type || $sup_low)
                    <x-slot:action>
                        <flux:button wire:click="clearSupplyFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.supplies.create') }}" variant="primary" icon="plus">{{ __('Nuevo Insumo') }}</flux:button>
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
                        [$statusLabel, $statusActive, $statusType] = match(true) {
                            $supply->isExpired()      => [__('Caducado'),      false, 'danger'],
                            $supply->isExpiringSoon() => [__('Próx. caducar'), true,  'warning'],
                            $supply->isLowStock()     => [__('Stock bajo'),    true,  'info'],
                            default                   => ['OK',               true,  'default'],
                        };
                    @endphp

                    <x-agro.card
                        wire:key="supply-{{ $supply->id }}"
                        class="animate-fade-in-up hover:-translate-y-1"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="archive-box"
                                :title="$supply->name"
                                :subtitle="$supply->commercial_name ?? null"
                            >
                                @if($statusType === 'default')
                                    <x-agro.status-badge :status="true" :label="__('OK')" />
                                @else
                                    <x-agro.status-badge :status="$statusActive" :label="$statusLabel" type="{{ $statusType }}" />
                                @endif
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600">{{ $supply->supply_type_label }}</span>
                            @if($supply->registration_number)
                                <span class="text-xs text-zinc-400 shrink-0 font-mono">· {{ __('Reg') }}: {{ $supply->registration_number }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $supply->warehouse?->name ?? __('Sin almacén') }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Stock actual') }}</p>
                                <p class="text-sm font-bold {{ $supply->isLowStock() ? 'text-red-600' : 'text-agro-700' }}">
                                    {{ number_format($supply->current_stock, 3, ',', '.') }} {{ $supply->unit_of_measurement }}
                                </p>
                                @if($supply->min_stock_alert)
                                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ __('Mín') }}: {{ $supply->min_stock_alert }} {{ $supply->unit_of_measurement }}</p>
                                @endif
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">{{ __('Caducidad') }}</p>
                                <p class="text-sm font-bold {{ $supply->isExpiringSoon() ? 'text-amber-600' : 'text-zinc-700' }}">
                                    {{ $supply->expiry_date ? $supply->expiry_date->format('d/m/Y') : '—' }}
                                </p>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-1">
                                <x-agro.action-button
                                    variant="default"
                                    icon="shopping-cart"
                                    wire:click="openPurchase({{ $supply->id }})"
                                    :title="__('Registrar compra')"
                                />
                                <x-agro.action-button
                                    variant="edit"
                                    href="{{ roleRoute('viticulturist.warehouse.supplies.edit', $supply) }}"
                                    :title="__('Editar')"
                                />
                                <x-agro.action-button
                                    variant="archive"
                                    icon="archive-box-arrow-down"
                                    wire:click="deactivateSupply({{ $supply->id }})"
                                    wire:confirm="{{ __('¿Archivar este insumo?') }}"
                                    :title="__('Archivar')"
                                />
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$supplies" />
        @endif

    {{-- =====================================================
         TAB: ALMACENES
    ====================================================== --}}
    @elseif($tab === 'almacenes')

        {{-- Sub-tabs activos/inactivos --}}
        <x-agro.tabs
            :tabs="[
                'active'   => ['label' => __('Activos'),   'count' => $wh_stats['active']],
                'inactive' => ['label' => __('Inactivos'),  'count' => $wh_stats['inactive']],
            ]"
            :active="$wh_tab"
            wireMethod="switchWhTab"
        />

        {{-- Toolbar --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <x-agro.search-input wire:model.live.debounce.300ms="wh_search" :placeholder="__('Buscar por nombre o ubicación...')" />
                <div class="w-px h-8 bg-zinc-200 shrink-0"></div>
                <flux:button href="{{ roleRoute('viticulturist.warehouse.warehouses.create') }}" variant="primary" icon="plus">
                    {{ __('Nuevo') }}
                </flux:button>
            </div>

            {{-- Active filter chips --}}
            @if($wh_search)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-400">{{ __('Filtros activos') }}:</span>
                    <x-agro.filter-chip icon="magnifying-glass" :label="'&quot;' . $wh_search . '&quot;'" wireRemove="$set('wh_search', '')" />
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

                    <x-agro.card
                        wire:key="warehouse-{{ $warehouse->id }}"
                        class="animate-fade-in-up hover:-translate-y-1 {{ !$warehouse->active ? 'opacity-60' : '' }}"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="building-office"
                                :title="$warehouse->name"
                                :subtitle="$warehouse->location ?? null"
                            >
                                <x-agro.status-badge :status="$warehouse->active" />
                            </x-agro.card-item-header>
                        </x-slot:header>

                        @if($warehouse->description)
                            <div class="flex items-start gap-2 mb-3">
                                <flux:icon icon="information-circle" class="size-3.5 text-zinc-400 shrink-0 mt-0.5" />
                                <span class="text-xs text-zinc-500 line-clamp-2">{{ $warehouse->description }}</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <x-agro.metric-cell :label="__('Fitosanitarios')" :value="$warehouse->stocks_count" color="agro" />
                            <x-agro.metric-cell :label="__('Insumos')" :value="$warehouse->supplies_count" color="zinc" />
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.warehouse.warehouses.edit', $warehouse->id) }}"
                                        :title="__('Editar')"
                                    />
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        wire:click="toggleActive({{ $warehouse->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleActive({{ $warehouse->id }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 transition-colors {{ $warehouse->active ? 'hover:text-red-500 hover:bg-red-50' : 'hover:text-agro-600 hover:bg-agro-50' }}"
                                        title="{{ $warehouse->active ? __('Desactivar') : __('Activar') }}"
                                    >
                                        <span wire:loading.remove wire:target="toggleActive({{ $warehouse->id }})">
                                            <flux:icon icon="{{ $warehouse->active ? 'no-symbol' : 'check-circle' }}" class="size-4" />
                                        </span>
                                        <span wire:loading wire:target="toggleActive({{ $warehouse->id }})">
                                            <flux:icon icon="arrow-path" class="animate-spin size-4" />
                                        </span>
                                    </button>
                                    @if($warehouse->stocks_count === 0)
                                        <x-agro.action-button
                                            variant="delete"
                                            wire:click="deleteWarehouse({{ $warehouse->id }})"
                                            wire:confirm="{{ __('¿Seguro que deseas eliminar este almacén?') }}"
                                            wire:loading.attr="disabled"
                                            :title="__('Eliminar')"
                                        />
                                    @endif
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$warehouses" />

        @else
            <x-agro.empty-state
                icon="building-office"
                :message="$wh_tab === 'active' ? __('No hay almacenes activos') : __('No hay almacenes inactivos')"
                :description="$wh_search ? __('Ningún almacén coincide con la búsqueda.') : __('Crea tu primer almacén para organizar el stock de productos.')"
            >
                @if($wh_search)
                    <x-slot:action>
                        <flux:button wire:click="$set('wh_search', '')" variant="outline" icon="x-mark">{{ __('Limpiar búsqueda') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.warehouse.warehouses.create') }}" variant="primary" icon="plus">
                            {{ __('Nuevo Almacén') }}
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
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Filtros') }}</h3>
                </div>
                <flux:button x-on:click="$dispatch('close-modal', 'almacen-sup-filters')" variant="ghost" size="sm" icon="x-mark" />
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            <x-agro.filter-select :label="__('Tipo de insumo')" wire:model.live="sup_type" :placeholder="__('Todos los tipos')">
                @foreach($supplyTypes as $key => $label)
                    <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                @endforeach
            </x-agro.filter-select>
            <flux:checkbox wire:model.live="sup_low" :label="__('Solo stock bajo')" :description="__('Muestra solo insumos por debajo del mínimo')" />
        </div>
        <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between rounded-b-2xl">
            <button wire:click="clearSupplyFilters" x-on:click="$dispatch('close-modal', 'almacen-sup-filters')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 transition-colors">
                {{ __('Limpiar filtros') }}
            </button>
            <flux:button x-on:click="$dispatch('close-modal', 'almacen-sup-filters')" variant="primary" size="sm">
                {{ __('Aplicar') }}
            </flux:button>
        </div>
    </x-agro.modal>

    {{-- Modal Compra --}}
    <x-agro.modal name="supply-purchase" maxWidth="xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">{{ __('Registrar Compra') }} — {{ $purchaseSupplyName }}</h2>
            <flux:callout variant="info" icon="information-circle">
                {{ __('Al guardar, el stock del insumo se incrementará automáticamente con la cantidad comprada.') }}
            </flux:callout>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>{{ __('Fecha de factura') }}</flux:label>
                    <flux:input wire:model="p_invoice_date" type="date" />
                    <flux:error name="p_invoice_date" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Nº Factura') }}</flux:label>
                    <flux:input wire:model="p_invoice_number" type="text" :placeholder="__('FAC-2026-001')" />
                    <flux:error name="p_invoice_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Cantidad comprada') }}</flux:label>
                    <flux:input wire:model.live="p_quantity" type="number" step="0.001" min="0.001" placeholder="0.000" />
                    <flux:error name="p_quantity" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Unidad') }}</flux:label>
                    <flux:select wire:model="p_unit_of_measurement">
                        @foreach($units as $u)
                            <option value="{{ $u->symbol }}">{{ $u->name }} ({{ $u->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="p_unit_of_measurement" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Precio/unidad (€)') }}</flux:label>
                    <flux:input wire:model.live="p_price_per_unit" type="number" step="0.0001" min="0" placeholder="0.0000" />
                    <flux:error name="p_price_per_unit" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Coste total (€)') }}</flux:label>
                    <flux:input wire:model="p_total_cost" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="p_total_cost" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Proveedor') }}</flux:label>
                    <flux:input wire:model="p_supplier_name" type="text" :placeholder="__('Nombre del proveedor')" />
                    <flux:error name="p_supplier_name" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="p_campaign_id">
                        <option value="">{{ __('Sin campaña') }}</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="p_campaign_id" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" x-on:click="$dispatch('close-modal', 'supply-purchase')">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" wire:click="savePurchase">{{ __('Registrar Compra') }}</flux:button>
            </div>
        </div>
    </x-agro.modal>

</div>
