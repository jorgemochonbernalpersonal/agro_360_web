<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Productos Fitosanitarios"
        description="Gestiona el catálogo de productos fitosanitarios que utilizas en tus tratamientos"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.phytosanitary-products.create') }}" class="group">
                <button
                    class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre, materia activa, registro o fabricante..."
        />

        <x-filter-select wire:model.live="typeFilter">
            <option value="">Todos los tipos</option>
            @foreach($types as $type)
                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
            @endforeach
        </x-filter-select>

        <x-slot:actions>
            @if($search || $typeFilter)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    @php
        $headers = [
            ['label' => 'Producto', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>'],
            ['label' => 'Materia activa', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 11-10 10A10 10 0 0112 2z"/></svg>'],
            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>'],
            ['label' => 'Registro / PS', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m1-14H7a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V6a2 2 0 00-2-2z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay productos registrados" 
        empty-description="Comienza creando tu primer producto fitosanitario para poder usarlo en los tratamientos"
        color="green"
    >
        @if($products->count() > 0)
            @foreach($products as $product)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-900">{{ $product->name }}</span>
                            @if($product->manufacturer)
                                <span class="text-xs text-gray-500">Fabricante: {{ $product->manufacturer }}</span>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $product->active_ingredient ?: '—' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm text-gray-700">
                            {{ $product->type ?: '—' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex flex-col text-sm text-gray-700">
                            <span>
                                Nº Reg.: {{ $product->registration_number ?: '—' }}
                            </span>
                            <span class="text-xs text-gray-500">
                                PS: {{ $product->withdrawal_period_days !== null ? $product->withdrawal_period_days . ' días' : '—' }}
                            </span>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <x-table-actions align="right">
                            <x-action-button 
                                variant="edit" 
                                href="{{ route('viticulturist.phytosanitary-products.edit', $product) }}"
                            />
                        </x-table-actions>
                    </x-table-cell>
                </x-table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $products->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>


