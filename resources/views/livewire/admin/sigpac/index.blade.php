@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
@endphp

<div class="space-y-6 animate-fade-in">
    <x-page-header
        :icon="$icon"
        title="Códigos SIGPAC del Sistema"
        description="Visualiza todos los códigos SIGPAC registrados por todos los usuarios"
        icon-color="from-blue-500 to-blue-700"
    />

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <p class="text-sm font-medium text-blue-700">Total Códigos</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <p class="text-sm font-medium text-green-700">Por Viticultores</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['by_role']['viticulturist'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
            <p class="text-sm font-medium text-indigo-700">Por Bodegas</p>
            <p class="text-3xl font-bold text-indigo-900 mt-1">{{ $stats['by_role']['winery'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <p class="text-sm font-medium text-purple-700">Por Supervisores</p>
            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['by_role']['supervisor'] }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-filter-section title="Filtros de Búsqueda" color="blue">
        <x-filter-input 
            wire:model.live="search" 
            placeholder="Buscar por código o usuario..."
        />
        <x-filter-select wire:model.live="roleFilter">
            <option value="all">Todos los roles</option>
            <option value="viticulturist">Viticultores</option>
            <option value="winery">Bodegas</option>
            <option value="supervisor">Supervisores</option>
        </x-filter-select>
    </x-filter-section>

    {{-- Tabla --}}
    @php
        $headers = [
            ['label' => 'Código SIGPAC', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>'],
            ['label' => 'Parcelas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>'],
            ['label' => 'Usuario', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            ['label' => 'Registro', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No se encontraron códigos SIGPAC" empty-description="No hay códigos que coincidan con los filtros seleccionados">
        @if($sigpacs->count() > 0)
            @foreach($sigpacs as $sigpac)
                @php
                    $firstPlot = $sigpac->plots->first();
                    $user = $firstPlot ? $firstPlot->viticulturist : null;
                @endphp
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900 font-mono">{{ $sigpac->code }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $sigpac->full_code }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            {{ $sigpac->plots_count }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        @if($user)
                            <div class="text-sm">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full mt-1 inline-block
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                       ($user->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 
                                       ($user->role === 'winery' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800')) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">Sin usuario</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-700">
                            <div>{{ $sigpac->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $sigpac->created_at->diffForHumans() }}</div>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $sigpacs->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <p class="text-sm text-gray-500">Intenta ajustar los filtros de búsqueda</p>
            </x-slot>
        @endif
    </x-data-table>
</div>

