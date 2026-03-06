<div>
    <x-agro.page-header title="Clientes" description="Gestiona los compradores de tu vino">
        <x-slot name="actions">
            <flux:button href="{{ route('winery.clients.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Cliente
            </flux:button>
        </x-slot>
    </x-agro.page-header>

    <!-- Filters -->
    <x-agro.filter-bar>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o email..." icon="magnifying-glass" clearable />

        <flux:select wire:model.live="typeFilter" class="w-40">
            <option value="">Todos</option>
            <option value="individual">Particular</option>
            <option value="company">Empresa</option>
        </flux:select>

        <flux:select wire:model.live="statusFilter" class="w-40">
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
            <option value="all">Todos</option>
        </flux:select>

        @if ($search || $typeFilter || $statusFilter !== 'active')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Table -->
    <x-agro.table>
        <x-slot name="head">
            <x-agro.th>Nombre</x-agro.th>
            <x-agro.th>Tipo</x-agro.th>
            <x-agro.th>Email</x-agro.th>
            <x-agro.th>Teléfono</x-agro.th>
            <x-agro.th>Pago</x-agro.th>
            <x-agro.th>Estado</x-agro.th>
            <x-agro.th class="text-right">Acciones</x-agro.th>
        </x-slot>

        @forelse ($clients as $client)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $client->full_name }}
                    @if ($client->particular_document || $client->company_document)
                        <div class="text-xs text-zinc-500">{{ $client->particular_document ?? $client->company_document }}</div>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <flux:badge variant="outline" size="sm">
                        {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                    </flux:badge>
                </td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm">{{ $client->email ?? '—' }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm">{{ $client->phone ?? '—' }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm">
                    @switch($client->payment_method)
                        @case('cash')     Efectivo @break
                        @case('transfer') Transferencia @break
                        @case('check')    Cheque @break
                        @case('other')    Otro @break
                        @default          —
                    @endswitch
                </td>
                <td class="px-4 py-3">
                    @if ($client->active)
                        <flux:badge color="green" size="sm">Activo</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">Inactivo</flux:badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <flux:button href="{{ route('winery.clients.edit', $client) }}" wire:navigate size="sm" variant="ghost" icon="pencil" />
                        <flux:button wire:click="toggleActive({{ $client->id }})" size="sm" variant="ghost"
                            icon="{{ $client->active ? 'eye-slash' : 'eye' }}" title="{{ $client->active ? 'Desactivar' : 'Activar' }}" />
                        <flux:button wire:click="delete({{ $client->id }})"
                            wire:confirm="¿Eliminar este cliente? Esta acción no se puede deshacer."
                            size="sm" variant="ghost" icon="trash" class="text-red-500" />
                    </div>
                </td>
            </tr>
        @empty
            <x-agro.empty-row colspan="7" message="No hay clientes registrados." />
        @endforelse
    </x-agro.table>

    <div class="mt-4">{{ $clients->links() }}</div>
</div>
