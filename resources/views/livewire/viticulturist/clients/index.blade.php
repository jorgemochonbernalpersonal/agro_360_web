<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Clientes"
        description="Gestiona tus clientes y analiza tu cartera"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.clients.create') }}" class="group">
                <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Cliente
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activos</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>
                
                <button 
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Inactivos</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button 
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadísticas</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
                <div class="space-y-6">
                    {{-- Estadísticas rápidas --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Total</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Activos</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['active'] }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Particulares</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['individual'] }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                            <p class="text-sm font-medium text-indigo-700">Empresas</p>
                            <p class="text-3xl font-bold text-indigo-900 mt-1">{{ $stats['company'] }}</p>
                        </div>
                    </div>

                    {{-- Filtros --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input wire:model.live="search" type="text" placeholder="Buscar clientes..." />
                            <x-select wire:model.live="filterType">
                                <option value="">Todos los tipos</option>
                                <option value="individual">Particular</option>
                                <option value="company">Empresa</option>
                            </x-select>
                        </div>
                    </div>

                    {{-- Tabla --}}
                    @php
                        $headers = [
                            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
                            ['label' => 'Contacto', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'],
                            ['label' => 'Dirección', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                            'Acciones',
                        ];
                    @endphp

                    <x-data-table :headers="$headers" empty-message="No se encontraron clientes" empty-description="Comienza agregando tu primer cliente al sistema">
                        @if($clients->count() > 0)
                            @foreach($clients as $client)
                                <x-table-row>
                                    <x-table-cell>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $client->full_name }}</div>
                                                @if($client->company_name && $client->client_type === 'company')
                                                    <div class="text-xs text-gray-500 mt-1">{{ $client->company_name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </x-table-cell>
                                    <x-table-cell>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $client->client_type === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                                        </span>
                                    </x-table-cell>
                                    <x-table-cell>
                                        <div class="text-sm text-gray-700">
                                            @if($client->email)
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span>{{ $client->email }}</span>
                                                </div>
                                            @endif
                                            @if($client->phone)
                                                <div class="flex items-center gap-1 mt-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    <span>{{ $client->phone }}</span>
                                                </div>
                                            @endif
                                            @if(!$client->email && !$client->phone)
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </div>
                                    </x-table-cell>
                                    <x-table-cell>
                                        @if($client->addresses && $client->addresses->count() > 0)
                                            @php
                                                // Obtener la dirección por defecto o la primera disponible
                                                $defaultAddress = $client->addresses->where('is_default', true)->first() 
                                                    ?? $client->addresses->first();
                                            @endphp
                                            <div class="space-y-1">
                                                <div class="text-sm text-gray-700 font-medium">
                                                    {{ $defaultAddress->address }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    @if($defaultAddress->municipality)
                                                        {{ $defaultAddress->municipality->name }}
                                                        @if($defaultAddress->province)
                                                            , {{ $defaultAddress->province->name }}
                                                        @endif
                                                    @endif
                                                    @if($defaultAddress->postal_code)
                                                        - {{ $defaultAddress->postal_code }}
                                                    @endif
                                                </div>
                                                @if($client->addresses->count() > 1)
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        +{{ $client->addresses->count() - 1 }} {{ $client->addresses->count() - 1 === 1 ? 'dirección más' : 'direcciones más' }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <span class="text-sm text-gray-400">Sin dirección</span>
                                            </div>
                                        @endif
                                    </x-table-cell>
                                    <x-table-cell>
                                        <x-status-badge :active="$client->active" />
                                    </x-table-cell>
                                    <x-table-actions align="right">
                                        <x-action-button variant="view" href="{{ route('viticulturist.clients.show', $client->id) }}" />
                                        <x-action-button variant="edit" href="{{ route('viticulturist.clients.edit', $client->id) }}" />
                                        <button 
                                            wire:click="toggleActive({{ $client->id }})"
                                            class="p-2 rounded-lg transition-all duration-200 group/btn {{ $client->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }}"
                                            title="{{ $client->active ? 'Desactivar cliente' : 'Activar cliente' }}"
                                        >
                                            @if($client->active)
                                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </x-table-actions>
                                </x-table-row>
                            @endforeach
                            <x-slot name="pagination">
                                {{ $clients->links() }}
                            </x-slot>
                        @else
                            <x-slot name="emptyAction">
                                <x-button href="{{ route('viticulturist.clients.create') }}" variant="primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Crear mi primer cliente
                                </x-button>
                            </x-slot>
                        @endif
                    </x-data-table>
                </div>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Año --}}
                    <div class="flex justify-end">
                        <select wire:model.live="yearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Activos {{ $yearFilter }}</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ $advancedStats['activeThisYear'] ?? 0 }}</p>
                            <p class="text-xs text-green-600 mt-2">Con facturas este año</p>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                            <p class="text-sm font-medium text-gray-700">Inactivos {{ $yearFilter }}</p>
                            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $advancedStats['inactiveThisYear'] ?? 0 }}</p>
                            <p class="text-xs text-gray-600 mt-2">Sin facturas este año</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Facturación Media</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ number_format($advancedStats['avgInvoicePerClient'] ?? 0, 0) }} €</p>
                            <p class="text-xs text-blue-600 mt-2">Por cliente activo</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Total Clientes</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">En base de datos</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Top 10 Clientes --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🏆 Top 10 Clientes por Facturación</h3>
                            <div class="space-y-3">
                                @forelse(($advancedStats['topClients'] ?? []) as $index => $client)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $client['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $client['type'] === 'company' ? 'Empresa' : 'Particular' }}</p>
                                            </div>
                                        </div>
                                        <span class="font-bold text-[var(--color-agro-green-dark)]">{{ number_format($client['total'], 0) }} €</span>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de facturación</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Distribución por Tipo --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Tipo</h3>
                            <div class="space-y-4">
                                @php
                                    $total = ($advancedStats['distributionByType']['individual'] ?? 0) + ($advancedStats['distributionByType']['company'] ?? 0);
                                    $individualPct = $total > 0 ? (($advancedStats['distributionByType']['individual'] ?? 0) / $total) * 100 : 0;
                                    $companyPct = $total > 0 ? (($advancedStats['distributionByType']['company'] ?? 0) / $total) * 100 : 0;
                                @endphp
                                
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Particulares</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['distributionByType']['individual'] ?? 0 }} ({{ number_format($individualPct, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $individualPct }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Empresas</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['distributionByType']['company'] ?? 0 }} ({{ number_format($companyPct, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $companyPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Nuevos Clientes --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Nuevos Clientes (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newClientsByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newClientsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} clientes"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
