<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="Equipos y Personal"
        description="Administra tus equipos de trabajo y viticultores"
    >
        <x-slot:actions>
            @can('create', \App\Models\Crew::class)
                <flux:button href="{{ route('viticulturist.personal.create') }}" variant="primary" icon="plus" data-cy="create-crew-button">
                    Nuevo Equipo
                </flux:button>
            @endcan
            <flux:button href="{{ route('viticulturist.viticulturists.create') }}" variant="primary" icon="user-plus" data-cy="create-viticulturist-button">
                Nuevo Viticultor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Tabs -->
    <x-agro.card :padding="false">
        <div class="p-2" data-cy="view-tabs">
            <div class="flex gap-2">
                <button
                    wire:click="switchView('personal')"
                    data-cy="personal-tab"
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition-colors {{ $viewMode === 'personal' ? 'bg-agro-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' }}"
                >
                    Personal
                </button>
                <button
                    wire:click="switchView('crews')"
                    data-cy="crews-tab"
                    class="flex-1 px-4 py-2 rounded-lg font-semibold transition-colors {{ $viewMode === 'crews' ? 'bg-agro-600 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' }}"
                >
                    Equipos
                </button>
            </div>
        </div>
    </x-agro.card>

    @if($viewMode === 'personal')
        <!-- Panel de Estadisticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-agro.stat-card label="Total Viticultores" :value="$viticulturistsCount ?? 0" icon="user-group" color="agro" />
            <x-agro.stat-card label="En Equipos" :value="$inCrewCount ?? 0" icon="users" color="blue" />
            <x-agro.stat-card label="Sin Equipo" :value="($individualCount ?? 0) + ($unassignedCount ?? 0)" icon="user" color="red" />
            <x-agro.stat-card label="Total Equipos" :value="$crewsCount ?? 0" icon="user-group" color="agro" />
        </div>
    @endif

    <!-- Filtros -->
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live.debounce.300ms="search" placeholder="{{ $viewMode === 'personal' ? 'Buscar por nombre o email...' : 'Buscar por nombre o descripcion...' }}" data-cy="personal-search-input" />
        @if(isset($wineries) && $wineries->count() > 1)
            <x-agro.filter-select wire:model.live="wineryFilter">
                <option value="">Todas las bodegas</option>
                @foreach($wineries as $winery)
                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif
        @if($viewMode === 'personal' && isset($crews) && $crews->count() > 0)
            <x-agro.filter-select wire:model.live="crewFilter" data-cy="crew-filter">
                <option value="">Todas las cuadrillas</option>
                @foreach($crews as $crew)
                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                @endforeach
            </x-agro.filter-select>
        @endif
        @if($viewMode === 'personal')
            <x-agro.filter-select wire:model.live="statusFilter" data-cy="status-filter">
                <option value="">Todos los estados</option>
                <option value="in_crew">En equipo</option>
                <option value="individual">Sin equipo</option>
                <option value="unassigned">Sin asignar</option>
            </x-agro.filter-select>
        @endif
        @if($search || $wineryFilter || $statusFilter || $crewFilter)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </x-agro.filter-bar>

    @if($viewMode === 'personal')
        <!-- Vista Personal -->
        <x-agro.data-table :headers="['Viticultor', 'Email', 'Bodega', 'Equipo', 'Actividad', 'Acceso', 'Acciones']" empty-message="No hay viticultores registrados" empty-description="Comienza creando tu primer viticultor para gestionarlo en el sistema">
            @if(isset($viticulturists) && $viticulturists->count() > 0)
                @foreach($viticulturists as $v)
                    @php
                        $member = $membersByViticulturist->get($v->id) ?? null;
                    @endphp
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                    <flux:icon icon="user" class="size-5 text-agro-600" />
                                </div>
                                <div>
                                    <div class="font-semibold text-zinc-900">{{ $v->name }}@if($v->id === auth()->id()) <span class="text-agro-700">(Yo)</span>@endif</div>
                                </div>
                            </div>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-sm text-zinc-700">{{ $v->email }}</span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php
                                $vWineries = $wineriesByViticulturist->get($v->id) ?? collect();
                            @endphp
                            @if($vWineries->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($vWineries->take(2) as $winery)
                                        <flux:badge color="blue" size="sm">{{ $winery->name }}</flux:badge>
                                    @endforeach
                                    @if($vWineries->count() > 2)
                                        <span class="text-xs text-zinc-500">+{{ $vWineries->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-zinc-400">Sin bodega</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @if($member && $member->crew)
                                <div class="flex flex-col gap-1">
                                    <flux:badge color="green" icon="user-group" size="sm">{{ $member->crew->name }}</flux:badge>
                                    @if($member->crew->members_count ?? 0)
                                        <span class="text-xs text-zinc-500">{{ $member->crew->members_count }} miembros</span>
                                    @endif
                                </div>
                            @elseif($member)
                                <flux:badge size="sm">Sin equipo</flux:badge>
                            @else
                                <span class="text-sm text-zinc-400">Sin asignar</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            @php
                                $recentActivities = \App\Models\AgriculturalActivity::where('viticulturist_id', $v->id)
                                    ->where('activity_date', '>=', now()->subDays(30))
                                    ->count();
                            @endphp
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-zinc-900">{{ $recentActivities }}</span>
                                    <span class="text-xs text-zinc-500">ultimos 30 dias</span>
                                </div>
                                @if($recentActivities === 0)
                                    <span class="text-xs text-zinc-400 mt-1">Sin actividad reciente</span>
                                @endif
                            </div>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <flux:badge :color="$v->can_login ? 'green' : null" :icon="$v->can_login ? 'check' : 'x-mark'" size="sm">
                                {{ $v->can_login ? 'Con acceso' : 'Sin acceso' }}
                            </flux:badge>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <div class="flex items-center justify-end gap-2">
                                @if(isset($crews) && $crews->count() > 0)
                                    <div class="relative" x-data="{ open: false }">
                                        <button
                                            @click="open = !open"
                                            class="p-2 text-agro-700 hover:bg-agro-50 rounded-lg transition-colors"
                                            title="Asignar a Equipo"
                                        >
                                            <flux:icon icon="plus" class="size-5" />
                                        </button>
                                        <div
                                            x-show="open"
                                            @click.away="open = false"
                                            x-transition
                                            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-10 border border-zinc-200 p-4"
                                        >
                                            <p class="text-sm font-semibold text-zinc-700 mb-3">Asignar a Equipo</p>
                                            <select
                                                wire:model="assignToCrewId"
                                                class="w-full px-3 py-2 border border-zinc-300 rounded-lg mb-3"
                                            >
                                                <option value="">Selecciona un equipo</option>
                                                @foreach($crews as $crew)
                                                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                                @endforeach
                                            </select>
                                            <button
                                                wire:click="assignToCrew({{ $v->id }})"
                                                wire:target="assignToCrew"
                                                x-on:click="open = false"
                                                class="w-full px-4 py-2 bg-agro-600 text-white rounded-lg hover:bg-agro-700 transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {{ $member && $member->crew ? 'Cambiar Equipo' : 'Asignar' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($member && $member->crew)
                                    <flux:button wire:click="makeIndividual({{ $v->id }})" variant="ghost" size="sm" icon="user" title="Quitar del equipo" />
                                @elseif(!$member)
                                    <flux:button wire:click="makeIndividual({{ $v->id }})" variant="ghost" size="sm" icon="user" title="Marcar como sin equipo" />
                                @endif

                                {{-- Boton para enviar invitacion --}}
                                @if(!$v->can_login && $v->invitation_sent_at === null)
                                    <flux:button wire:click="sendInvitation({{ $v->id }})" wire:target="sendInvitation" variant="ghost" size="sm" icon="envelope" title="Enviar invitacion por email" />
                                @elseif($v->invitation_sent_at !== null)
                                    <span class="p-2 text-green-500" title="Invitacion enviada el {{ $v->invitation_sent_at->format('d/m/Y H:i') }}">
                                        <flux:icon icon="check" class="size-5" />
                                    </span>
                                @endif

                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="deleteViticulturist({{ $v->id }})"
                                    wire:confirm="Estas seguro de eliminar este viticultor? Esta accion no se puede deshacer."
                                />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $viticulturists->links() }}
                </x-slot>
            @endif
        </x-agro.data-table>
    @else
        <!-- Vista Equipos -->
        <x-agro.data-table :headers="['Nombre', 'Bodega', 'Miembros', 'Actividades', 'Creada', 'Acciones']" empty-message="No hay equipos" empty-description="Crea tu primer equipo para comenzar a gestionar tu equipo de trabajo">
            @if(isset($crewsPaginated) && $crewsPaginated->count() > 0)
                @foreach($crewsPaginated as $crew)
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
                                    data-cy="view-crew-button"
                                />
                                @can('update', $crew)
                                    <x-agro.action-button variant="edit" href="{{ route('viticulturist.personal.edit', $crew) }}" data-cy="edit-crew-button" />
                                @endcan
                                @can('delete', $crew)
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="deleteCrew({{ $crew->id }})"
                                        wire:confirm="Estas seguro de eliminar este equipo?"
                                    />
                                @endcan
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
                <x-slot name="pagination">
                    {{ $crewsPaginated->links() }}
                </x-slot>
            @else
                <x-slot name="emptyAction">
                    @can('create', \App\Models\Crew::class)
                        <flux:button href="{{ route('viticulturist.personal.create') }}" variant="primary">
                            Crear Primer Equipo
                        </flux:button>
                    @endcan
                </x-slot>
            @endif
        </x-agro.data-table>
    @endif
</div>
