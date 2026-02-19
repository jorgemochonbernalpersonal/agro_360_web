<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Gestión de Campañas"
        description="Administra y visualiza todas tus campañas vitícolas"
    >
        <x-slot:actions>
            @can('create', \App\Models\Campaign::class)
                <flux:button href="{{ route('viticulturist.campaign.create') }}" variant="primary" icon="plus" data-cy="create-campaign-button">
                    Nueva Campaña
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por nombre o descripción..."
            data-cy="campaign-search-input"
        />
        <x-agro.filter-select wire:model.live="yearFilter" data-cy="campaign-year-filter">
            <option value="">Todos los años</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </x-agro.filter-select>
        @if($search || $yearFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                Limpiar Filtros
            </flux:button>
        @endif
    </x-agro.filter-bar>

    <!-- Tabla de Campañas -->
    @php
        $headers = ['Campaña', 'Año', 'Período', 'Estado', 'Actividades', 'Acciones'];
    @endphp

    <x-agro.data-table
        :headers="$headers"
        empty-message="No hay campañas registradas"
        empty-description="{{ ($search || $yearFilter) ? 'No se encontraron campañas con los filtros seleccionados' : 'Comienza creando tu primera campaña vitícola' }}"
    >
        @if($campaigns->count() > 0)
            @foreach($campaigns as $campaign)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="calendar" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $campaign->name }}</div>
                                @if($campaign->description)
                                    <div class="text-xs text-zinc-500 mt-1">{{ Str::limit($campaign->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">{{ $campaign->year }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($campaign->start_date && $campaign->end_date)
                            <span class="text-sm text-zinc-700">
                                {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                            </span>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <x-agro.status-badge :active="$campaign->active" />
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge color="blue" size="sm">{{ $campaign->activities_count }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell align="right">
                        <div class="flex items-center justify-end gap-2">
                            @can('view', $campaign)
                                <x-agro.action-button variant="view" href="{{ route('viticulturist.campaign.show', $campaign) }}" data-cy="view-campaign-button" />
                            @endcan
                            @can('update', $campaign)
                                <x-agro.action-button variant="edit" href="{{ route('viticulturist.campaign.edit', $campaign) }}" data-cy="edit-campaign-button" />
                            @endcan
                            @if(!$campaign->active)
                                @can('activate', $campaign)
                                    <x-agro.action-button
                                        variant="activate"
                                        wireClick="activate({{ $campaign->id }})"
                                    />
                                @endcan
                            @endif
                            @can('delete', $campaign)
                                @if($campaign->activities_count === 0)
                                    <x-agro.action-button
                                        variant="delete"
                                        wireClick="delete({{ $campaign->id }})"
                                        wireConfirm="¿Estás seguro de eliminar esta campaña?"
                                    />
                                @else
                                    <x-agro.action-button variant="delete" disabled title="No se puede eliminar: tiene actividades" />
                                @endif
                            @endcan
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $campaigns->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Campaign::class)
                    <flux:button href="{{ route('viticulturist.campaign.create') }}" variant="primary" icon="plus">
                        Crear Primera Campaña
                    </flux:button>
                @endcan
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
