<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Equipos y Personal"
        description="Administra tus equipos de trabajo y viticultores"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex gap-3">
                @can('create', \App\Models\Crew::class)
                    <x-button href="{{ route('viticulturist.personal.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo Equipo
                    </x-button>
                @endcan
                <x-button href="{{ route('viticulturist.viticulturists.create') }}" variant="primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Nuevo Viticultor
                </x-button>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Tabs -->
    <div class="glass-card rounded-xl p-2">
        <div class="flex gap-2">
            <button 
                wire:click="switchView('personal')"
                class="flex-1 px-4 py-2 rounded-lg font-semibold transition-colors {{ $viewMode === 'personal' ? 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                👤 Personal
            </button>
            <button 
                wire:click="switchView('crews')"
                class="flex-1 px-4 py-2 rounded-lg font-semibold transition-colors {{ $viewMode === 'crews' ? 'bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                👥 Equipos
            </button>
        </div>
    </div>

    @if($viewMode === 'personal')
        <!-- Panel de Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-card rounded-xl p-4 border-l-4 border-[var(--color-agro-green)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Viticultores</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $viticulturistsCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[var(--color-agro-green-bg)] flex items-center justify-center">
                        <svg class="w-6 h-6 text-[var(--color-agro-green)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">En Equipos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $inCrewCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-4 border-l-4 border-gray-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Sin Equipo</p>
                        <p class="text-2xl font-bold text-gray-900">{{ ($individualCount ?? 0) + ($unassignedCount ?? 0) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-xl p-4 border-l-4 border-[var(--color-agro-green)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Equipos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $crewsCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[var(--color-agro-green-bg)] flex items-center justify-center">
                        <svg class="w-6 h-6 text-[var(--color-agro-green)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="{{ $viewMode === 'personal' ? 'Buscar por nombre o email...' : 'Buscar por nombre o descripción...' }}"
        />
        @if(isset($wineries) && $wineries->count() > 1)
            <x-filter-select wire:model.live="wineryFilter">
                <option value="">Todas las bodegas</option>
                @foreach($wineries as $winery)
                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                @endforeach
            </x-filter-select>
        @endif
        @if($viewMode === 'personal' && isset($crews) && $crews->count() > 0)
            <x-filter-select wire:model.live="crewFilter">
                <option value="">Todas las cuadrillas</option>
                @foreach($crews as $crew)
                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                @endforeach
            </x-filter-select>
        @endif
        @if($viewMode === 'personal')
            <x-filter-select wire:model.live="statusFilter">
                <option value="">Todos los estados</option>
                <option value="in_crew">En equipo</option>
                <option value="individual">Sin equipo</option>
                <option value="unassigned">Sin asignar</option>
            </x-filter-select>
        @endif
        <x-slot:actions>
            @if($search || $wineryFilter || $statusFilter || $crewFilter)
                <x-button wire:click="clearFilters" variant="ghost" size="sm">
                    Limpiar Filtros
                </x-button>
            @endif
        </x-slot:actions>
    </x-filter-section>

    @if($viewMode === 'personal')
        <!-- Vista Personal -->
        @php
            $headers = [
                ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                ['label' => 'Email', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m0 0l4-4m-4 4l4 4"/></svg>'],
                ['label' => 'Bodega', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
                ['label' => 'Equipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'],
                ['label' => 'Actividad', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
                ['label' => 'Acceso', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'],
                'Acciones',
            ];
        @endphp

        <x-data-table :headers="$headers" empty-message="No hay viticultores registrados" empty-description="Comienza creando tu primer viticultor para gestionarlo en el sistema" color="green">
            @if(isset($viticulturists) && $viticulturists->count() > 0)
                @foreach($viticulturists as $v)
                    @php
                        $member = $membersByViticulturist->get($v->id) ?? null;
                    @endphp
                    <x-table-row>
                        <x-table-cell>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $v->name }}</div>
                                </div>
                            </div>
                        </x-table-cell>

                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $v->email }}</span>
                        </x-table-cell>

                        <x-table-cell>
                            @php
                                $vWineries = $wineriesByViticulturist->get($v->id) ?? collect();
                            @endphp
                            @if($vWineries->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($vWineries->take(2) as $winery)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ $winery->name }}
                                        </span>
                                    @endforeach
                                    @if($vWineries->count() > 2)
                                        <span class="text-xs text-gray-500">+{{ $vWineries->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Sin bodega</span>
                            @endif
                        </x-table-cell>

                        <x-table-cell>
                            @if($member && $member->crew)
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[var(--color-agro-green-bg)] text-[var(--color-agro-green-dark)] text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ $member->crew->name }}
                                    </span>
                                    @if($member->crew->members_count ?? 0)
                                        <span class="text-xs text-gray-500">
                                            {{ $member->crew->members_count }} miembros
                                        </span>
                                    @endif
                                </div>
                            @elseif($member)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Sin equipo
                                </span>
                            @else
                                <span class="text-sm text-gray-400">Sin asignar</span>
                            @endif
                        </x-table-cell>

                        <x-table-cell>
                            @php
                                $recentActivities = \App\Models\AgriculturalActivity::where('viticulturist_id', $v->id)
                                    ->where('activity_date', '>=', now()->subDays(30))
                                    ->count();
                            @endphp
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ $recentActivities }}</span>
                                    <span class="text-xs text-gray-500">últimos 30 días</span>
                                </div>
                                @if($recentActivities === 0)
                                    <span class="text-xs text-gray-400 mt-1">Sin actividad reciente</span>
                                @endif
                            </div>
                        </x-table-cell>

                        <x-table-cell>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $v->can_login ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $v->can_login ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                                </svg>
                                {{ $v->can_login ? 'Con acceso' : 'Sin acceso' }}
                            </span>
                        </x-table-cell>

                        <x-table-cell>
                            <x-table-actions align="right">
                                @if(isset($crews) && $crews->count() > 0)
                                    <div class="relative" x-data="{ open: false }">
                                        <button
                                            @click="open = !open"
                                            class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                                            title="Asignar a Equipo"
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
                                            <p class="text-sm font-semibold text-gray-700 mb-3">Asignar a Equipo</p>
                                            <select
                                                wire:model="assignToCrewId"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
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
                                                class="w-full px-4 py-2 bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white rounded-lg hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                {{ $member && $member->crew ? 'Cambiar Equipo' : 'Asignar' }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if($member && $member->crew)
                                    <button
                                        wire:click="makeIndividual({{ $v->id }})"
                                        class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                                        title="Quitar del equipo"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </button>
                                @elseif(!$member)
                                    <button
                                        wire:click="makeIndividual({{ $v->id }})"
                                        class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                                        title="Marcar como sin equipo"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Botón para enviar invitación --}}
                                @if(!$v->can_login && $v->invitation_sent_at === null)
                                    <button
                                        wire:click="sendInvitation({{ $v->id }})"
                                        wire:target="sendInvitation"
                                        class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                                        title="Enviar invitación por email"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                @elseif($v->invitation_sent_at !== null)
                                    <span class="p-2 text-green-500" title="Invitación enviada el {{ $v->invitation_sent_at->format('d/m/Y H:i') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                @endif

                                <x-action-button 
                                    variant="delete"
                                    wire:click="deleteViticulturist({{ $v->id }})"
                                    wire:confirm="¿Estás seguro de eliminar este viticultor? Esta acción no se puede deshacer."
                                />
                            </x-table-actions>
                        </x-table-cell>
                    </x-table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $viticulturists->links() }}
                </x-slot>
            @endif
        </x-data-table>
    @else
        <!-- Vista Equipos -->
        @php
            $headers = [
                ['label' => 'Nombre', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
                ['label' => 'Bodega', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
                ['label' => 'Miembros', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'],
                ['label' => 'Actividades', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
                ['label' => 'Creada', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                'Acciones',
            ];
        @endphp

        <x-data-table :headers="$headers" empty-message="No hay equipos" empty-description="Crea tu primer equipo para comenzar a gestionar tu equipo de trabajo" color="green">
            @if(isset($crewsPaginated) && $crewsPaginated->count() > 0)
                @foreach($crewsPaginated as $crew)
                    <x-table-row>
                        <x-table-cell>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $crew->name }}</div>
                                    @if($crew->description)
                                        <div class="text-sm text-gray-500 mt-1">{{ Str::limit($crew->description, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $crew->winery->name ?? 'Sin bodega' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $crew->members_count }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[var(--color-agro-blue-light)] text-[var(--color-agro-blue)] text-sm font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ $crew->activities_count }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-500">
                                {{ $crew->created_at->format('d/m/Y') }}
                            </span>
                        </x-table-cell>
                        <x-table-actions align="right">
                            <x-action-button 
                                variant="view" 
                                href="{{ route('viticulturist.personal.show', $crew) }}#miembros"
                                title="Ver / agregar miembros"
                            />
                            @can('update', $crew)
                                <x-action-button variant="edit" href="{{ route('viticulturist.personal.edit', $crew) }}" />
                            @endcan
                            @can('delete', $crew)
                                <x-action-button 
                                    variant="delete" 
                                    wire:click="deleteCrew({{ $crew->id }})"
                                    wire:confirm="¿Estás seguro de eliminar este equipo?"
                                />
                            @endcan
                        </x-table-actions>
                    </x-table-row>
                @endforeach
                <x-slot name="pagination">
                    {{ $crewsPaginated->links() }}
                </x-slot>
            @else
                <x-slot name="emptyAction">
                    @can('create', \App\Models\Crew::class)
                        <x-button href="{{ route('viticulturist.personal.create') }}" variant="primary">
                            Crear Primer Equipo
                        </x-button>
                    @endcan
                </x-slot>
            @endif
        </x-data-table>
    @endif
</div>

