<div class="space-y-6 animate-fade-in">
    <x-agro.page-header title="Clientes" description="Gestiona los compradores de tu vino">
        <x-slot:actions>
            <flux:button href="{{ route('winery.clients.create') }}" wire:navigate variant="primary" icon="plus">
                Nuevo Cliente
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o email..."
        />
        <flux:select wire:model.live="typeFilter" size="sm" class="w-40">
            <flux:select.option value="">Todos</flux:select.option>
            <flux:select.option value="individual">Particular</flux:select.option>
            <flux:select.option value="company">Empresa</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="statusFilter" size="sm" class="w-40">
            <flux:select.option value="active">Activos</flux:select.option>
            <flux:select.option value="inactive">Inactivos</flux:select.option>
            <flux:select.option value="all">Todos</flux:select.option>
        </flux:select>
        @if ($search || $typeFilter || $statusFilter !== 'active')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                Limpiar
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.data-table
        :headers="['Nombre', 'Tipo', 'Email', 'Teléfono', 'Pago', 'Estado', 'Acciones']"
        empty-message="No hay clientes registrados"
        empty-description="Añade el primer cliente para gestionar ventas de vino"
    >
        @if ($clients->count() > 0)
            @foreach ($clients as $client)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <p class="text-sm font-semibold text-zinc-900">{{ $client->full_name }}</p>
                        @if ($client->particular_document || $client->company_document)
                            <p class="text-xs text-zinc-500">{{ $client->particular_document ?? $client->company_document }}</p>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <flux:badge variant="outline" size="sm">
                            {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $client->email ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">{{ $client->phone ?? '—' }}</span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-600">
                            @switch($client->payment_method)
                                @case('cash')     Efectivo @break
                                @case('transfer') Transferencia @break
                                @case('check')    Cheque @break
                                @case('other')    Otro @break
                                @default          —
                            @endswitch
                        </span>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @if ($client->active)
                            <flux:badge color="green" size="sm">Activo</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">Inactivo</flux:badge>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('winery.clients.edit', $client) }}" wire:navigate title="Editar">
                                <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                    <flux:icon icon="pencil" class="size-4" />
                                </button>
                            </a>
                            <button
                                wire:click="toggleActive({{ $client->id }})"
                                title="{{ $client->active ? 'Desactivar' : 'Activar' }}"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                            >
                                <flux:icon icon="{{ $client->active ? 'eye-slash' : 'eye' }}" class="size-4" />
                            </button>
                            <button
                                wire:click="delete({{ $client->id }})"
                                wire:confirm="¿Eliminar este cliente? Esta acción no se puede deshacer."
                                title="Eliminar"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $clients->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
