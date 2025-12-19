<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    <!-- Header -->
    @php
        $icon =
            '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    @endphp
    <x-page-header :icon="$icon" title="Gestión de Viticultores"
        description="Administra los viticultores que has creado para tus cuadrillas y parcelas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]">
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.viticulturists.create') }}" class="group">
                <button
                    class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Viticultor
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o email..." />
        <x-slot:actions>
            @if ($search)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    <!-- Tabla de Viticultores -->
    @php
        $headers = [
            ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            ['label' => 'Email', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m0 0l4-4m-4 4l4 4"/></svg>'],
            ['label' => 'Cuadrilla', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0\"/></svg>'],
            ['label' => 'Acceso', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay viticultores registrados"
        empty-description="Comienza creando tu primer viticultor para gestionarlo en el sistema" color="green">
        @if ($viticulturists->count() > 0)
            @foreach ($viticulturists as $v)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $v->name }}</div>
                            </div>
                        </div>
                    </x-table-cell>

                    <x-table-cell>
                        <span class="text-sm text-gray-700">{{ $v->email }}</span>
                    </x-table-cell>

                    {{-- Cuadrilla actual --}}
                    <x-table-cell>
                        @php
                            $member = $membersByViticulturist->get($v->id);
                        @endphp
                        @if ($member && $member->crew)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-xs font-semibold">
                                {{ $member->crew->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500">Sin cuadrilla</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell>
                        @php
                            $hasAccess = $v->can_login;
                        @endphp
                        <span
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $hasAccess ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $hasAccess ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}" />
                            </svg>
                            {{ $hasAccess ? 'Con acceso' : 'Sin acceso' }}
                        </span>
                    </x-table-cell>

                    <x-table-cell>
                        <x-table-actions align="right">
                            {{-- Asignar a cuadrilla --}}
                            @if ($crews->count() > 0)
                                <div class="relative" x-data="{ open: false }">
                                    <button
                                        @click="open = !open"
                                        class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                                        title="Asignar a Cuadrilla"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <div
                                        x-show="open"
                                        @click.away="open = false"
                                        x-transition
                                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-10 border border-gray-200 p-4"
                                    >
                                        <p class="text-sm font-semibold text-gray-700 mb-3">Asignar a Cuadrilla</p>
                                        <select
                                            wire:model="assignToCrewId"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
                                        >
                                            <option value="">Selecciona una cuadrilla</option>
                                            @foreach ($crews as $crew)
                                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                            @endforeach
                                        </select>
                                        <button
                                            wire:click="assignToCrew({{ $v->id }})"
                                            wire:target="assignToCrew"
                                            x-on:click="open = false"
                                            class="w-full px-4 py-2 bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white rounded-lg hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            Asignar
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <x-action-button variant="delete"
                                wire:click="delete({{ $v->id }})"
                                wire:confirm="¿Estás seguro de eliminar este viticultor? Esta acción no se puede deshacer." />
                        </x-table-actions>
                    </x-table-cell>
                </x-table-row>
            @endforeach

            <x-slot name="pagination">
                {{ $viticulturists->links() }}
            </x-slot>
        @endif
    </x-data-table>
</div>
