<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Gestión de Viticultores"
        description="Administra los viticultores que has creado para tus cuadrillas y parcelas"
    >
        <x-slot:actions>
            <a href="{{ route('viticulturist.viticulturists.create') }}">
                <flux:button variant="primary" icon="plus">
                    Nuevo Viticultor
                </flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o email..." />
        @if ($search)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Tabla de Viticultores -->
    <x-agro.data-table :headers="['Viticultor', 'Email', 'Cuadrilla', 'Acceso', 'Acciones']" empty-message="No hay viticultores registrados"
        empty-description="Comienza creando tu primer viticultor para gestionarlo en el sistema">
        @if ($viticulturists->count() > 0)
            @foreach ($viticulturists as $v)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="user" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $v->name }}@if($v->id === auth()->id()) <span class="text-agro-700">(Yo)</span>@endif</div>
                            </div>
                        </div>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $v->email }}</span>
                    </x-agro.table-cell>

                    {{-- Cuadrilla actual --}}
                    <x-agro.table-cell>
                        @php
                            $member = $membersByViticulturist->get($v->id);
                        @endphp
                        @if ($member && $member->crew)
                            <flux:badge color="green" size="sm">{{ $member->crew->name }}</flux:badge>
                        @else
                            <span class="text-sm text-zinc-500">Sin cuadrilla</span>
                        @endif
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        @php
                            $hasAccess = $v->can_login;
                        @endphp
                        <flux:badge :color="$hasAccess ? 'green' : null" :icon="$hasAccess ? 'check' : 'x-mark'" size="sm">
                            {{ $hasAccess ? 'Con acceso' : 'Sin acceso' }}
                        </flux:badge>
                    </x-agro.table-cell>

                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-2">
                            {{-- Asignar a cuadrilla --}}
                            @if ($crews->count() > 0)
                                <div class="relative" x-data="{ open: false }">
                                    <button
                                        @click="open = !open"
                                        class="p-2 text-agro-700 hover:bg-agro-50 rounded-lg transition-colors"
                                        title="Asignar a Cuadrilla"
                                    >
                                        <flux:icon icon="plus" class="size-5" />
                                    </button>
                                    <div
                                        x-show="open"
                                        @click.away="open = false"
                                        x-transition
                                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-10 border border-zinc-200 p-4"
                                    >
                                        <p class="text-sm font-semibold text-zinc-700 mb-3">Asignar a Cuadrilla</p>
                                        <select
                                            wire:model="assignToCrewId"
                                            class="w-full px-3 py-2 border border-zinc-300 rounded-lg mb-3"
                                        >
                                            <option value="">Selecciona una cuadrilla</option>
                                            @foreach ($crews as $crew)
                                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                            @endforeach
                                        </select>
                                        <flux:button
                                            wire:click="assignToCrew({{ $v->id }})"
                                            wire:target="assignToCrew"
                                            x-on:click="open = false"
                                            variant="primary"
                                            class="w-full"
                                        >
                                            Asignar
                                        </flux:button>
                                    </div>
                                </div>
                            @endif

                            <x-agro.action-button variant="delete"
                                wire:click="delete({{ $v->id }})"
                                wire:confirm="¿Estás seguro de eliminar este viticultor? Esta acción no se puede deshacer." />
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $viticulturists->links() }}
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
