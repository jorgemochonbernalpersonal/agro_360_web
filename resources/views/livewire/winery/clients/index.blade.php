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

    @if ($clients->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($clients as $client)
                @php
                    $delay  = min($loop->index * 50, 300);
                    $initials = collect(explode(' ', $client->full_name))
                        ->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col {{ !$client->active ? 'opacity-60' : '' }}"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="client-card-{{ $client->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-agro-700">{{ $initials }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $client->full_name }}</h3>
                                @if ($client->particular_document || $client->company_document)
                                    <p class="text-xs text-zinc-400">{{ $client->particular_document ?? $client->company_document }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <flux:badge :color="$client->active ? 'green' : 'zinc'" size="sm">
                                    {{ $client->active ? 'Activo' : 'Inactivo' }}
                                </flux:badge>
                                <flux:badge variant="outline" size="sm">
                                    {{ $client->client_type === 'company' ? 'Empresa' : 'Particular' }}
                                </flux:badge>
                            </div>
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3 text-sm">
                        @if ($client->email)
                            <div class="flex items-center gap-2 text-zinc-600">
                                <flux:icon icon="envelope" class="size-4 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $client->email }}</span>
                            </div>
                        @endif

                        @if ($client->phone)
                            <div class="flex items-center gap-2 text-zinc-600">
                                <flux:icon icon="phone" class="size-4 text-zinc-400 shrink-0" />
                                <span>{{ $client->phone }}</span>
                            </div>
                        @endif

                        @if ($client->payment_method)
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Pago</span>
                                <span class="text-zinc-700">
                                    @switch($client->payment_method)
                                        @case('cash')     Efectivo @break
                                        @case('transfer') Transferencia @break
                                        @case('check')    Cheque @break
                                        @default          Otro
                                    @endswitch
                                </span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
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
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $clients->links() }}
        </div>
    @else
        <x-agro.empty-state
            icon="users"
            title="No hay clientes registrados"
            description="Añade el primer cliente para gestionar ventas de vino"
        >
            <x-slot:action>
                <flux:button href="{{ route('winery.clients.create') }}" wire:navigate variant="primary" icon="plus">
                    Nuevo Cliente
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif
</div>
