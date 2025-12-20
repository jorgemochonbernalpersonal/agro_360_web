<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Clientes"
        description="Gestiona tus clientes"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('viticulturist.clients.create') }}" variant="primary">
                Nuevo Cliente
            </x-button>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Activos</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Particulares</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['individual'] }}</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <p class="text-sm font-medium text-gray-600">Empresas</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['company'] }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
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

    <!-- Tabla -->
    <div class="glass-card rounded-xl overflow-hidden">
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
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $client->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $client->client_type === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
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
                                <a href="{{ route('viticulturist.clients.show', $client->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                <a href="{{ route('viticulturist.clients.edit', $client->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No se encontraron clientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $clients->links() }}
        </div>
    </div>
</div>
