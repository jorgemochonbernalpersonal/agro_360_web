<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Gestion de Cuadrillas"
        description="Administra tus equipos de trabajo y personal"
    >
        <x-slot:actions>
            @can('create', \App\Models\Crew::class)
                <flux:button href="{{ route('viticulturist.personal.create') }}" variant="primary" icon="plus">
                    Nueva Cuadrilla
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripcion..." />
        @if($wineries->count() > 1)
            <x-agro.filter-select wire:model.live="wineryFilter">
                <option value="">Todas las bodegas</option>
                @foreach($wineries as $winery)
                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif
        @if($search || $wineryFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Nombre', 'Bodega', 'Miembros', 'Actividades', 'Creada', 'Acciones']" empty-message="No hay cuadrillas" empty-description="Crea tu primera cuadrilla para comenzar a gestionar tu equipo de trabajo">
        @if($crews->count() > 0)
            @foreach($crews as $crew)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="user-group" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $crew->name }}</div>
                                @if($crew->description)
                                    <div class="text-sm text-zinc-500 mt-1">{{ Str::limit($crew->description, 50) }}</div>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-700">{{ $crew->winery->name ?? 'Sin bodega' }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge color="blue" icon="user-group">{{ $crew->members_count }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <flux:badge color="blue" icon="clipboard-document-list">{{ $crew->activities_count }}</flux:badge>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm text-zinc-500">{{ $crew->created_at->format('d/m/Y') }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-2">
                            <x-agro.action-button
                                variant="view"
                                href="{{ route('viticulturist.personal.show', $crew) }}#miembros"
                            />
                            @can('update', $crew)
                                <x-agro.action-button variant="edit" href="{{ route('viticulturist.personal.edit', $crew) }}" />
                            @endcan
                            @can('delete', $crew)
                                <x-agro.action-button
                                    variant="delete"
                                    wireClick="delete({{ $crew->id }})"
                                    wireConfirm="Estas seguro de eliminar esta cuadrilla?"
                                />
                            @endcan
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $crews->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Crew::class)
                    <flux:button href="{{ route('viticulturist.personal.create') }}" variant="primary">
                        Crear Primera Cuadrilla
                    </flux:button>
                @endcan
            </x-slot>
        @endif
    </x-agro.data-table>
</div>
