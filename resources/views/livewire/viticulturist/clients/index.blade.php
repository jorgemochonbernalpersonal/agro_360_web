<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Clientes"
        description="Gestiona tus clientes y analiza tu cartera"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.clients.create') }}" variant="primary" icon="plus">
                Nuevo Cliente
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs Navigation --}}
    <x-agro.tabs
        :tabs="[
            'active' => ['label' => 'Activos', 'count' => $stats['active'] > 0 ? $stats['active'] : null],
            'inactive' => ['label' => 'Inactivos', 'count' => $stats['inactive'] > 0 ? $stats['inactive'] : null],
            'statistics' => ['label' => 'Estadísticas'],
        ]"
        :active="$currentTab"
    />

    {{-- Tab Content --}}
    <div>
        {{-- ACTIVE/INACTIVE TABS --}}
        @if($currentTab === 'active' || $currentTab === 'inactive')
            <div class="space-y-6">
                {{-- Estadísticas rápidas --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-agro.stat-card label="Total" :value="$stats['total']" icon="users" color="blue" />
                    <x-agro.stat-card label="Activos" :value="$stats['active']" icon="check-circle" color="agro" />
                    <x-agro.stat-card label="Particulares" :value="$stats['individual']" icon="user" color="purple" />
                    <x-agro.stat-card label="Empresas" :value="$stats['company']" icon="building-office" color="blue" />
                </div>

                {{-- Filtros --}}
                <x-agro.filter-bar>
                    <x-agro.filter-input wire:model.live="search" placeholder="Buscar clientes..." />
                    <x-agro.filter-select wire:model.live="filterType">
                        <option value="">Todos los tipos</option>
                        <option value="individual">Particular</option>
                        <option value="company">Empresa</option>
                    </x-agro.filter-select>
                </x-agro.filter-bar>

                {{-- Tabla --}}
                @php
                    $headers = ['Nombre', 'Tipo', 'Contacto', 'Dirección', 'Estado', 'Acciones'];
                @endphp

                <x-agro.data-table :headers="$headers" empty-message="No se encontraron clientes" empty-description="Comienza agregando tu primer cliente al sistema">
                    @if($clients->count() > 0)
                        @foreach($clients as $client)
                            <x-agro.table-row>
                                <x-agro.table-cell>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                            <flux:icon icon="user" class="size-5 text-agro-600" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-zinc-900">{{ $client->full_name }}</div>
                                            @if($client->company_name && $client->client_type === 'company')
                                                <div class="text-xs text-zinc-500 mt-1">{{ $client->company_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <flux:badge :color="$client->client_type === 'company' ? 'blue' : null" size="sm">
                                        {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                                    </flux:badge>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <div class="text-sm text-zinc-700">
                                        @if($client->email)
                                            <div class="flex items-center gap-1">
                                                <flux:icon icon="envelope" class="size-4 text-zinc-400 flex-shrink-0" />
                                                <span>{{ $client->email }}</span>
                                            </div>
                                        @endif
                                        @if($client->phone)
                                            <div class="flex items-center gap-1 mt-1">
                                                <flux:icon icon="phone" class="size-4 text-zinc-400 flex-shrink-0" />
                                                <span>{{ $client->phone }}</span>
                                            </div>
                                        @endif
                                        @if(!$client->email && !$client->phone)
                                            <span class="text-zinc-400">N/A</span>
                                        @endif
                                    </div>
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    @if($client->addresses && $client->addresses->count() > 0)
                                        @php
                                            // Obtener la dirección por defecto o la primera disponible
                                            $defaultAddress = $client->addresses->where('is_default', true)->first()
                                                ?? $client->addresses->first();
                                        @endphp
                                        <div class="space-y-1">
                                            <div class="text-sm text-zinc-700 font-medium">
                                                {{ $defaultAddress->address }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
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
                                                <div class="text-xs text-zinc-400 mt-1">
                                                    +{{ $client->addresses->count() - 1 }} {{ $client->addresses->count() - 1 === 1 ? 'dirección más' : 'direcciones más' }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <flux:icon icon="x-mark" class="size-4 text-zinc-400" />
                                            <span class="text-sm text-zinc-400">Sin dirección</span>
                                        </div>
                                    @endif
                                </x-agro.table-cell>
                                <x-agro.table-cell>
                                    <x-agro.status-badge :active="$client->active" />
                                </x-agro.table-cell>
                                <x-agro.table-cell align="right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-agro.action-button variant="view" href="{{ route('viticulturist.clients.show', $client->id) }}" />
                                        <x-agro.action-button variant="edit" href="{{ route('viticulturist.clients.edit', $client->id) }}" />
                                        <button
                                            wire:click="toggleActive({{ $client->id }})"
                                            class="p-2 rounded-lg transition-all duration-200 group/btn {{ $client->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }}"
                                            title="{{ $client->active ? 'Desactivar cliente' : 'Activar cliente' }}"
                                        >
                                            @if($client->active)
                                                <flux:icon icon="x-circle" class="size-5 group-hover/btn:scale-110 transition-transform" />
                                            @else
                                                <flux:icon icon="check-circle" class="size-5 group-hover/btn:scale-110 transition-transform" />
                                            @endif
                                        </button>
                                    </div>
                                </x-agro.table-cell>
                            </x-agro.table-row>
                        @endforeach
                        <x-slot name="pagination">
                            {{ $clients->links() }}
                        </x-slot>
                    @else
                        <x-slot name="emptyAction">
                            <flux:button href="{{ route('viticulturist.clients.create') }}" variant="primary" icon="plus">
                                Crear mi primer cliente
                            </flux:button>
                        </x-slot>
                    @endif
                </x-agro.data-table>
            </div>
        @endif

        {{-- STATISTICS TAB --}}
        @if($currentTab === 'statistics')
            <div class="space-y-6">
                {{-- Filtro de Año --}}
                <div class="flex justify-end">
                    <flux:select wire:model.live="yearFilter" class="w-auto">
                        @for($year = now()->year; $year >= now()->year - 5; $year--)
                            <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                        @endfor
                    </flux:select>
                </div>

                {{-- KPIs --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-agro.stat-card label="Activos {{ $yearFilter }}" :value="$advancedStats['activeThisYear'] ?? 0" icon="check-circle" color="agro" />
                    <x-agro.stat-card label="Inactivos {{ $yearFilter }}" :value="$advancedStats['inactiveThisYear'] ?? 0" icon="x-circle" />
                    <x-agro.stat-card label="Facturación Media" :value="number_format($advancedStats['avgInvoicePerClient'] ?? 0, 0) . ' €'" icon="banknotes" color="blue" />
                    <x-agro.stat-card label="Total Clientes" :value="$stats['total']" icon="users" color="purple" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Top 10 Clientes --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-agro-50">
                                    <flux:icon icon="trophy" class="size-4 text-agro-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Top 10 Clientes por Facturación</span>
                            </div>
                        </x-slot:header>
                        <div class="space-y-3">
                            @forelse(($advancedStats['topClients'] ?? []) as $index => $client)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-zinc-900">{{ $client['name'] }}</p>
                                            <p class="text-xs text-zinc-500">{{ $client['type'] === 'company' ? 'Empresa' : 'Particular' }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-agro-700">{{ number_format($client['total'], 0) }} €</span>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-center py-4">No hay datos de facturación</p>
                            @endforelse
                        </div>
                    </x-agro.card>

                    {{-- Distribución por Tipo --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-blue-50">
                                    <flux:icon icon="chart-pie" class="size-4 text-blue-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Distribución por Tipo</span>
                            </div>
                        </x-slot:header>
                        <div class="space-y-4">
                            @php
                                $total = ($advancedStats['distributionByType']['individual'] ?? 0) + ($advancedStats['distributionByType']['company'] ?? 0);
                                $individualPct = $total > 0 ? (($advancedStats['distributionByType']['individual'] ?? 0) / $total) * 100 : 0;
                                $companyPct = $total > 0 ? (($advancedStats['distributionByType']['company'] ?? 0) / $total) * 100 : 0;
                            @endphp

                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-zinc-700">Particulares</span>
                                    <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['distributionByType']['individual'] ?? 0 }} ({{ number_format($individualPct, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-zinc-200 rounded-full h-3">
                                    <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $individualPct }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium text-zinc-700">Empresas</span>
                                    <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['distributionByType']['company'] ?? 0 }} ({{ number_format($companyPct, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-zinc-200 rounded-full h-3">
                                    <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $companyPct }}%"></div>
                                </div>
                            </div>
                        </div>
                    </x-agro.card>
                </div>

                {{-- Nuevos Clientes --}}
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="arrow-trending-up" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">Nuevos Clientes (Últimos 12 meses)</span>
                        </div>
                    </x-slot:header>
                    <div class="h-64 flex items-end justify-between gap-2">
                        @foreach(($advancedStats['newClientsByMonth'] ?? []) as $month)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                    style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newClientsByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                    title="{{ $month['count'] }} clientes"></div>
                                <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            </div>
        @endif
    </div>
</div>
