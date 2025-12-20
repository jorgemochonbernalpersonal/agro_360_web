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
            <x-button href="{{ route('viticulturist.clients.create') }}" variant="primary">
                Nuevo Cliente
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('list')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'list' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Lista de Clientes</span>
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
            {{-- LIST TAB --}}
            @if($currentTab === 'list')
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
                            <x-select wire:model.live="filterActive">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </x-select>
                        </div>
                    </div>

                    {{-- Tabla --}}
                    <div class="bg-white rounded-xl overflow-hidden border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($clients as $client)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $client->full_name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $client->client_type === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{$client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $client->email ?? $client->phone ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($client->active)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('viticulturist.clients.show', $client->id) }}" class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] mr-3">Ver</a>
                                                <a href="{{ route('viticulturist.clients.edit', $client->id) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center">
                                                <p class="text-gray-500">No se encontraron clientes</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $clients->links() }}
                        </div>
                    </div>
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
