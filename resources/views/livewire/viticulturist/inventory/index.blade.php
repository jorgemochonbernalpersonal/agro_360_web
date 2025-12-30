<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Inventario de Productos Fitosanitarios"
        description="Gestiona el stock de tus productos fitosanitarios y controla las existencias"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                <!-- Analytics Button -->
                <a href="{{ route('viticulturist.inventory.analytics') }}">
                    <button class="flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analíticas
                    </button>
                </a>
                
                <!-- Register Stock Button -->
                <a href="{{ route('viticulturist.inventory.create') }}" class="group">
                    <button
                        class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Registrar Stock
                    </button>
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Productos en Stock</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_products'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Valor Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_value'], 2) }} €</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Stock Bajo</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['low_stock_count'] }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Próximos a Caducar</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['expiring_soon_count'] }}</p>
                </div>
                <div class="p-3 bg-amber-100 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-label for="search">Buscar producto</x-label>
                <x-input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    id="search"
                    placeholder="Nombre del producto..."
                />
            </div>
            <div>
                <x-label for="productFilter">Producto</x-label>
                <x-select wire:model.live="productFilter" id="productFilter">
                    <option value="">Todos</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label for="warehouseFilter">Almacén</x-label>
                <x-select wire:model.live="warehouseFilter" id="warehouseFilter">
                    <option value="">Todos</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label for="statusFilter">Estado</x-label>
                <x-select wire:model.live="statusFilter" id="statusFilter">
                    <option value="all">Todos</option>
                    <option value="low_stock">Stock Bajo</option>
                    <option value="expiring">Próximos a Caducar</option>
                    <option value="expired">Caducados</option>
                </x-select>
            </div>
        </div>
        @if($search || $productFilter || $warehouseFilter || $statusFilter !== 'all')
            <div class="mt-4">
                <button wire:click="clearFilters" class="text-sm text-[var(--color-agro-green)] hover:underline">
                    Limpiar filtros
                </button>
            </div>
        @endif
    </div>

    <!-- Tabla de Stock -->
    <div class="glass-card rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Almacén</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caducidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stocks as $stock)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $stock->product->name }}</div>
                                @if($stock->product->active_ingredient)
                                    <div class="text-sm text-gray-500">{{ $stock->product->active_ingredient }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $stock->batch_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($stock->getAvailableQuantity(), 3) }} {{ $stock->unit }}
                                </div>
                                @if($stock->unit_price)
                                    <div class="text-xs text-gray-500">
                                        {{ number_format($stock->unit_price, 2) }} €/{{ $stock->unit }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $stock->warehouse->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($stock->expiry_date)
                                    {{ $stock->expiry_date->format('d/m/Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stock->isExpired())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Caducado
                                    </span>
                                @elseif($stock->isExpiringSoon())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                        Próximo a caducar
                                    </span>
                                @elseif($stock->getAvailableQuantity() < 5)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Stock bajo
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        OK
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Editar -->
                                    <a href="{{ route('viticulturist.inventory.edit', $stock->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors"
                                       title="Editar stock">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <!-- Consumir -->
                                    <a href="{{ route('viticulturist.inventory.consume', $stock->id) }}" 
                                       class="text-orange-600 hover:text-orange-900 transition-colors"
                                       title="Registrar consumo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M20 12H4"/>
                                        </svg>
                                    </a>

                                    <!-- Ver movimientos -->
                                    <a href="{{ route('viticulturist.inventory.movements', $stock->id) }}" 
                                       class="text-[var(--color-agro-green)] hover:text-[var(--color-agro-green-dark)] transition-colors"
                                       title="Ver movimientos">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="mt-4 text-sm">No hay stock registrado</p>
                                <a href="{{ route('viticulturist.inventory.create') }}" class="mt-2 text-[var(--color-agro-green)] hover:underline">
                                    Registrar primer stock
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $stocks->links() }}
        </div>
    </div>
</div>
