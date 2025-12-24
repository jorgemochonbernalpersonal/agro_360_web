@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
@endphp

<div class="space-y-6 animate-fade-in">
    <x-page-header
        :icon="$icon"
        title="Parcelas del Sistema"
        description="Visualiza todas las parcelas registradas por todos los usuarios"
        icon-color="from-green-500 to-green-700"
    />

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <p class="text-sm font-medium text-green-700">Total Parcelas</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <p class="text-sm font-medium text-blue-700">Parcelas Activas</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <p class="text-sm font-medium text-purple-700">Área Total</p>
            <p class="text-3xl font-bold text-purple-900 mt-1">{{ number_format($stats['total_area'], 2) }} ha</p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
            <p class="text-sm font-medium text-orange-700">Por Viticultores</p>
            <p class="text-3xl font-bold text-orange-900 mt-1">{{ $stats['by_role']['viticulturist'] }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live="search" 
            placeholder="Buscar por nombre, descripción o usuario..."
        />
        <x-filter-select wire:model.live="activeFilter">
            <option value="">Todas las parcelas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
        </x-filter-select>
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
            ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
            ['label' => 'Ubicación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Área', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Registro', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No se encontraron parcelas" empty-description="No hay parcelas que coincidan con los filtros seleccionados">
        @if($plots->count() > 0)
            @foreach($plots as $plot)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $plot->name }}</div>
                                @if($plot->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($plot->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ $plot->viticulturist->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $plot->viticulturist->email ?? '' }}</div>
                            @if($plot->viticulturist)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full mt-1 inline-block
                                    {{ $plot->viticulturist->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                       ($plot->viticulturist->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 
                                       ($plot->viticulturist->role === 'winery' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800')) }}">
                                    {{ ucfirst($plot->viticulturist->role) }}
                                </span>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-700">
                            @if($plot->municipality)
                                <div>{{ $plot->municipality->name }}</div>
                                @if($plot->municipality->province)
                                    <div class="text-xs text-gray-500">{{ $plot->municipality->province->name }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($plot->area, 2) }} ha</span>
                    </x-table-cell>
                    <x-table-cell>
                        <x-status-badge :active="$plot->active" />
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-700">
                            <div>{{ $plot->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $plot->created_at->diffForHumans() }}</div>
                        </div>
                    </x-table-cell>
                    <x-table-actions align="right">
                        <x-action-button 
                            variant="view" 
                            href="{{ route('plots.show', $plot->id) }}"
                            title="Ver detalles"
                        />
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $plots->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                <p class="text-sm text-gray-500">Intenta ajustar los filtros de búsqueda</p>
            </x-slot>
        @endif
    </x-data-table>
</div>

