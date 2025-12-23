<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Gestión de Campañas"
        description="Administra y visualiza todas tus campañas vitícolas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            @can('create', \App\Models\Campaign::class)
                <a href="{{ route('viticulturist.campaign.create') }}" class="group" data-cy="create-campaign-button">
                    <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Campaña
                    </button>
                </a>
            @endcan
        </x-slot:actionButton>
    </x-page-header>

    <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por nombre o descripción..."
            data-cy="campaign-search-input"
        />
        <x-filter-select wire:model.live="yearFilter" icon="calendar" data-cy="campaign-year-filter">
            <option value="">Todos los años</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </x-filter-select>
        <x-slot:actions>
            @if($search || $yearFilter)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    <!-- Tabla de Campañas -->
    @php
        $headers = [
            ['label' => 'Campaña', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Año', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Período', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Actividades', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table 
        :headers="$headers" 
        empty-message="No hay campañas registradas" 
        empty-description="{{ ($search || $yearFilter) ? 'No se encontraron campañas con los filtros seleccionados' : 'Comienza creando tu primera campaña vitícola' }}"
        color="green"
    >
        @if($campaigns->count() > 0)
            @foreach($campaigns as $campaign)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $campaign->name }}</div>
                                @if($campaign->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($campaign->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm font-medium text-gray-900">{{ $campaign->year }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        @if($campaign->start_date && $campaign->end_date)
                            <span class="text-sm text-gray-700">
                                {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <x-status-badge :active="$campaign->active" />
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $campaign->activities_count }}
                        </span>
                    </x-table-cell>
                    <x-table-actions align="right">
                        @can('view', $campaign)
                            <x-action-button variant="view" href="{{ route('viticulturist.campaign.show', $campaign) }}" data-cy="view-campaign-button" />
                        @endcan
                        @can('update', $campaign)
                            <x-action-button variant="edit" href="{{ route('viticulturist.campaign.edit', $campaign) }}" data-cy="edit-campaign-button" />
                        @endcan
                        @if(!$campaign->active)
                            @can('activate', $campaign)
                                <x-action-button 
                                    variant="activate"
                                    wireClick="activate({{ $campaign->id }})"
                                />
                            @endcan
                        @endif
                        @can('delete', $campaign)
                            @if($campaign->activities_count === 0)
                                <x-action-button 
                                    variant="delete" 
                                    wireClick="delete({{ $campaign->id }})"
                                    wireConfirm="¿Estás seguro de eliminar esta campaña?"
                                />
                            @else
                                <x-action-button variant="delete" disabled title="No se puede eliminar: tiene actividades" />
                            @endif
                        @endcan
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $campaigns->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Campaign::class)
                    <x-button href="{{ route('viticulturist.campaign.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Primera Campaña
                    </x-button>
                @endcan
            </x-slot>
        @endif
    </x-data-table>
</div>
