<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Inventario de Productos Fitosanitarios"
        description="Gestiona el stock de tus productos fitosanitarios y controla las existencias"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.inventory.analytics') }}" variant="ghost" icon="chart-bar">
                Analíticas
            </flux:button>
            <flux:button href="{{ route('viticulturist.inventory.create') }}" variant="primary" icon="plus">
                Registrar Stock
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-agro.stat-card label="Productos en Stock" :value="$stats['total_products']" icon="archive-box" color="blue" />
        <x-agro.stat-card label="Valor Total" :value="number_format($stats['total_value'], 2) . ' €'" icon="banknotes" color="green" />
        <x-agro.stat-card label="Stock Bajo" :value="$stats['low_stock_count']" icon="exclamation-triangle" color="red" />
        <x-agro.stat-card label="Próximos a Caducar" :value="$stats['expiring_soon_count']" icon="clock" color="yellow" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar producto..." />
        <x-agro.filter-select wire:model.live="productFilter" label="Producto">
            <option value="">Todos</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="warehouseFilter" label="Almacén">
            <option value="">Todos</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="statusFilter" label="Estado">
            <option value="all">Todos</option>
            <option value="low_stock">Stock Bajo</option>
            <option value="expiring">Próximos a Caducar</option>
            <option value="expired">Caducados</option>
        </x-agro.filter-select>
        @if($search || $productFilter || $warehouseFilter || $statusFilter !== 'all')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    {{-- Tabla de Stock --}}
    <x-agro.data-table
        :headers="['Producto', 'Lote', 'Cantidad', 'Almacén', 'Caducidad', 'Estado', 'Acciones']"
        empty-message="No hay stock registrado"
        empty-description="Registra tu primer producto fitosanitario"
    >
        @forelse($stocks as $stock)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900">{{ $stock->product->name }}</div>
                    @if($stock->product->active_ingredient)
                        <div class="text-xs text-zinc-500 mt-0.5">{{ $stock->product->active_ingredient }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-500">{{ $stock->batch_number ?? '-' }}</x-agro.table-cell>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900">{{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}</div>
                    @if($stock->unit_price)
                        <div class="text-xs text-zinc-500">{{ number_format($stock->unit_price, 2) }} €/{{ $stock->unit }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-500">{{ $stock->warehouse->name ?? '-' }}</x-agro.table-cell>
                <x-agro.table-cell class="text-zinc-500">
                    {{ $stock->expiry_date ? $stock->expiry_date->format('d/m/Y') : '-' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($stock->isExpired())
                        <x-agro.status-badge status="expired" label="Caducado" />
                    @elseif($stock->isExpiringSoon())
                        <x-agro.status-badge status="warning" label="Próx. caducar" />
                    @elseif($stock->getAvailableQuantity() < 5)
                        <x-agro.status-badge status="low" label="Stock bajo" />
                    @else
                        <x-agro.status-badge status="active" label="OK" />
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell class="text-right">
                    <div class="flex items-center justify-end gap-1">
                        <flux:button href="{{ route('viticulturist.inventory.edit', $stock->id) }}" size="sm" variant="ghost" icon="pencil-square" title="Editar" />
                        <flux:button href="{{ route('viticulturist.inventory.consume', $stock->id) }}" size="sm" variant="ghost" icon="minus-circle" title="Consumir" />
                        <flux:button href="{{ route('viticulturist.inventory.movements', $stock->id) }}" size="sm" variant="ghost" icon="document-text" title="Movimientos" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
        @endforelse

        <x-slot:pagination>
            {{ $stocks->links() }}
        </x-slot:pagination>
    </x-agro.data-table>
</div>
