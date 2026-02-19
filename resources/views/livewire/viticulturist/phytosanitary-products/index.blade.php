<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger" icon="exclamation-circle">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Productos Fitosanitarios"
        description="Gestiona el catálogo de productos fitosanitarios que utilizas en tus tratamientos"
    >
        <x-slot:actions>
            <a href="{{ route('viticulturist.phytosanitary-products.create') }}" data-cy="create-product-button">
                <flux:button variant="primary" icon="plus">
                    Nuevo Producto
                </flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-zinc-200 overflow-hidden">
        <div class="border-b border-zinc-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activos</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Inactivos</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadísticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
            <!-- Filtros -->
            <x-agro.filter-section title="Filtros de Búsqueda" color="green">
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre, materia activa, registro o fabricante..."
            data-cy="product-search-input"
        />

        <x-agro.filter-select wire:model.live="typeFilter" data-cy="product-type-filter">
            <option value="">Todos los tipos</option>
            @foreach($types as $type)
                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-slot:actions>
            @if($search || $typeFilter)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.filter-section>

    @php
        $headers = [
            ['label' => 'Producto', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>'],
            ['label' => 'Materia activa', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 11-10 10A10 10 0 0112 2z"/></svg>'],
            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>'],
            ['label' => 'Registro / PS', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m1-14H7a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V6a2 2 0 00-2-2z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-agro.data-table
        :headers="$headers"
        empty-message="No hay productos registrados"
        empty-description="Comienza creando tu primer producto fitosanitario para poder usarlo en los tratamientos"
        color="green"
    >
        @if($products->count() > 0)
            @foreach($products as $product)
                <x-agro.table-row wire:key="product-{{ $product->id }}">
                    <x-agro.table-cell>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-zinc-900">{{ $product->name }}</span>
                            @if($product->manufacturer)
                                <span class="text-xs text-zinc-500">Fabricante: {{ $product->manufacturer }}</span>
                            @endif
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $product->active_ingredient ?: '—' }}
                        </span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">
                            {{ $product->type ?: '—' }}
                        </span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex flex-col text-sm text-zinc-700">
                            <span>
                                Nº Reg.: {{ $product->registration_number ?: '—' }}
                            </span>
                            <span class="text-xs text-zinc-500">
                                PS: {{ $product->withdrawal_period_days !== null ? $product->withdrawal_period_days . ' días' : '—' }}
                            </span>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-actions align="right">
                        <x-agro.action-button
                            variant="edit"
                            href="{{ route('viticulturist.phytosanitary-products.edit', $product) }}"
                            data-cy="edit-product-button"
                        />
                        <button
                            wire:click="toggleActive({{ $product->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleActive({{ $product->id }})"
                            class="p-2 rounded-lg transition-all duration-200 group/btn {{ $product->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                            title="{{ $product->active ? 'Desactivar producto' : 'Activar producto' }}"
                        >
                            <span wire:loading.remove wire:target="toggleActive({{ $product->id }})">
                                @if($product->active)
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </span>
                            <span wire:loading wire:target="toggleActive({{ $product->id }})" class="inline-block">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </x-agro.table-actions>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $products->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Año --}}
                    <div class="flex justify-end">
                        <flux:select wire:model.live="yearFilter" >
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Total Productos</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-xs text-blue-600 mt-2">Todos los productos</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Con Registro Válido</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ $advancedStats['withValidRegistration'] ?? 0 }}</p>
                            <p class="text-xs text-green-600 mt-2">Registro activo</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Productos Activos</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['active'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">De {{ $stats['total'] }} totales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">PS Promedio</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ number_format($advancedStats['avgWithdrawalPeriod'] ?? 0, 0) }} días</p>
                            <p class="text-xs text-orange-600 mt-2">Plazo de seguridad</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Tipo --}}
                        <div class="bg-white rounded-xl p-6 border border-zinc-200">
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribución por Tipo</h3>
                            <div class="space-y-4">
                                @forelse(($advancedStats['typeStats'] ?? []) as $type => $data)
                                    @php
                                        $total = ($advancedStats['typeStats'] ?? [])->sum('count');
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">{{ ucfirst($type) }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="bg-agro-500 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-zinc-500">
                                            <span>Activos: {{ $data['active'] }}</span>
                                            <span>Inactivos: {{ $data['inactive'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de tipos</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Distribución por Clase de Toxicidad --}}
                        <div class="bg-white rounded-xl p-6 border border-zinc-200">
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribución por Toxicidad</h3>
                            <div class="space-y-4">
                                @forelse(($advancedStats['toxicityStats'] ?? []) as $class => $count)
                                    @php
                                        $total = ($advancedStats['toxicityStats'] ?? [])->sum();
                                        $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                        $colors = ['I' => 'bg-red-500', 'II' => 'bg-orange-500', 'III' => 'bg-yellow-500', 'IV' => 'bg-green-500'];
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">Clase {{ $class }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="{{ $colors[$class] ?? 'bg-zinc-500' }} h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de toxicidad</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Registro y Plazo de Seguridad --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl p-6 border border-zinc-200">
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Estado de Registro</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con Registro Válido</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withValidRegistration'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $validPct = $stats['total'] > 0 ? (($advancedStats['withValidRegistration'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $validPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Sin Registro Válido</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withoutValidRegistration'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $invalidPct = $stats['total'] > 0 ? (($advancedStats['withoutValidRegistration'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ $invalidPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 border border-zinc-200">
                            <h3 class="text-lg font-bold text-zinc-900 mb-4">Plazo de Seguridad</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con PS Definido</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withWithdrawalPeriod'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $withPSPct = $stats['total'] > 0 ? (($advancedStats['withWithdrawalPeriod'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $withPSPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Sin PS Definido</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withoutWithdrawalPeriod'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $withoutPSPct = $stats['total'] > 0 ? (($advancedStats['withoutWithdrawalPeriod'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-zinc-500 h-3 rounded-full" style="width: {{ $withoutPSPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Top 10 Productos Más Usados --}}
                    <div class="bg-white rounded-xl p-6 border border-zinc-200">
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Top 10 Productos Más Usados</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['mostUsed'] ?? []) as $index => $product)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-zinc-900">{{ $product['name'] }}</p>
                                            <p class="text-xs text-zinc-500">{{ ucfirst($product['type']) }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-agro-700">{{ $product['treatments_count'] }} tratamientos</span>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-center py-4">No hay datos de uso</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Nuevos Productos por Mes --}}
                    <div class="bg-white rounded-xl p-6 border border-zinc-200">
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Nuevos Productos (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newProductsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newProductsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} productos"></div>
                                    <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
