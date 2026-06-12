<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Equipos y Personal')"
        :description="__('Administra tus equipos de trabajo y viticultores')"
    />

    {{-- Tabs --}}
    <x-agro.tabs
        :tabs="[
            'personal' => ['label' => __('Personal'),  'count' => $viticulturistsCount ?? 0],
            'crews'    => ['label' => __('Equipos'),    'count' => $crewsCount ?? 0],
        ]"
        :active="$viewMode"
        wireMethod="switchView"
    />

    {{-- Toolbar --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3">

            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="{{ $viewMode === 'personal' ? __('Buscar por nombre o email...') : __('Buscar por nombre o descripción...') }}"
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition"
                />
            </div>

            @php
                $filterCount = ($wineryFilter ? 1 : 0)
                    + ($viewMode === 'personal' && $crewFilter ? 1 : 0)
                    + ($viewMode === 'personal' && $statusFilter ? 1 : 0);
            @endphp
            <button
                x-on:click="$dispatch('open-modal', 'personal-filters')"
                class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-medium text-zinc-700 hover:bg-zinc-50 shadow-sm transition-colors"
            >
                <flux:icon icon="adjustments-horizontal" class="size-4 text-zinc-500" />
                {{ __('Filtros') }}
                @if($filterCount > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-agro-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>

            <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

            @if($viewMode === 'crews')
                @can('create', \App\Models\Crew::class)
                    <flux:button href="{{ roleRoute('viticulturist.personal.create') }}" variant="primary" icon="plus">
                        {{ __('Nuevo Equipo') }}
                    </flux:button>
                @endcan
            @else
                <flux:button href="{{ roleRoute('viticulturist.viticulturists.create') }}" variant="primary" icon="user-plus">
                    {{ __('Nuevo Viticultor') }}
                </flux:button>
            @endif

        </div>

        {{-- Active filter chips --}}
        @if($search || $wineryFilter || ($viewMode === 'personal' && ($crewFilter || $statusFilter)))
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-400">{{ __('Filtros activos:') }}</span>

                @if($search)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        <flux:icon icon="magnifying-glass" class="size-3" />
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($wineryFilter && isset($wineries))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ __('Bodega:') }} {{ $wineries->firstWhere('id', $wineryFilter)?->name ?? $wineryFilter }}
                        <button wire:click="$set('wineryFilter', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($viewMode === 'personal' && $crewFilter && isset($crews))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ __('Equipo:') }} {{ $crews->firstWhere('id', $crewFilter)?->name ?? $crewFilter }}
                        <button wire:click="$set('crewFilter', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                @if($viewMode === 'personal' && $statusFilter)
                    @php $statusLabels = ['in_crew' => __('En equipo'), 'individual' => __('Sin equipo'), 'unassigned' => __('Sin asignar')]; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-agro-50 text-agro-700 text-xs font-medium rounded-full border border-agro-200">
                        {{ $statusLabels[$statusFilter] ?? $statusFilter }}
                        <button wire:click="$set('statusFilter', '')" class="hover:text-agro-900 ml-0.5">
                            <flux:icon icon="x-mark" class="size-3" />
                        </button>
                    </span>
                @endif

                <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 underline">
                    {{ __('Limpiar todo') }}
                </button>
            </div>
        @endif
    </div>

    @if($viewMode === 'personal')
        {{-- ===== VISTA PERSONAL ===== --}}
        @if(isset($viticulturists) && $viticulturists->count() > 0)
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="search, wineryFilter, crewFilter, statusFilter, clearFilters, switchView"
            >
                @foreach($viticulturists as $i => $v)
                    @php
                        $member    = $membersByViticulturist->get($v->id) ?? null;
                        $vWineries = $wineriesByViticulturist->get($v->id) ?? collect();
                        $recentActivities = \App\Models\AgriculturalActivity::where('viticulturist_id', $v->id)
                            ->where('activity_date', '>=', now()->subDays(30))
                            ->count();
                    @endphp

                    <x-agro.card
                        wire:key="viticulturist-{{ $v->id }}"
                        class="animate-fade-in-up hover:-translate-y-1"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-zinc-500">{{ strtoupper(substr($v->name, 0, 1)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">
                                        {{ $v->name }}
                                        @if($v->id === auth()->id())
                                            <span class="text-agro-600 font-normal text-xs ml-1">({{ __('Yo') }})</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $v->email }}</p>
                                </div>
                                <flux:badge :color="$v->can_login ? 'green' : null" size="sm" class="shrink-0">
                                    {{ $v->can_login ? __('Acceso') : __('Sin acceso') }}
                                </flux:badge>
                            </div>
                        </x-slot:header>

                        {{-- Equipo --}}
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="user-group" class="size-3.5 text-zinc-400 shrink-0" />
                            @if($member && $member->crew)
                                <span class="text-xs text-agro-700 font-medium truncate">{{ $member->crew->name }}</span>
                            @elseif($member)
                                <span class="text-xs text-zinc-400 italic">{{ __('Sin equipo') }}</span>
                            @else
                                <span class="text-xs text-zinc-400 italic">{{ __('Sin asignar') }}</span>
                            @endif
                        </div>

                        {{-- Bodegas + Actividad --}}
                        <div class="grid {{ $vWineries->count() > 0 ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                            @if($vWineries->count() > 0)
                                <div class="bg-zinc-50 rounded-xl p-2.5">
                                    <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-1">{{ __('Bodega') }}</p>
                                    <p class="text-xs font-semibold text-zinc-700 truncate">{{ $vWineries->first()->name }}</p>
                                    @if($vWineries->count() > 1)
                                        <p class="text-[10px] text-zinc-400">+{{ $vWineries->count() - 1 }} {{ __('más') }}</p>
                                    @endif
                                </div>
                            @endif
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Actividad 30d') }}</p>
                                <p class="text-sm font-bold text-agro-700">{{ $recentActivities }}</p>
                            </div>
                        </div>

                        <x-slot:footer>
                            @if($v->id === auth()->id())
                                {{-- Propia cuenta: sin acciones de gestión --}}
                                <p class="text-xs text-zinc-400 italic text-center py-1">{{ __('Tu propia cuenta') }}</p>
                            @else
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    {{-- Asignar a equipo --}}
                                    @if(isset($crews) && $crews->count() > 0)
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors" :title="__('Asignar a equipo')">
                                                <flux:icon icon="user-group" class="size-4" />
                                            </button>
                                            <div
                                                x-show="open"
                                                @click.away="open = false"
                                                x-transition
                                                class="absolute left-0 bottom-10 w-64 bg-white rounded-xl shadow-xl z-10 border border-zinc-200 p-4"
                                            >
                                                <p class="text-sm font-semibold text-zinc-700 mb-3">{{ $member && $member->crew ? __('Cambiar equipo') : __('Asignar a equipo') }}</p>
                                                <flux:select wire:model="assignToCrewId" class="mb-3">
                                                    <flux:select.option value="">{{ __('Selecciona un equipo') }}</flux:select.option>
                                                    @foreach($crews as $crew)
                                                        <flux:select.option value="{{ $crew->id }}">{{ $crew->name }}</flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                                <flux:button
                                                    wire:click="assignToCrew({{ $v->id }})"
                                                    x-on:click="open = false"
                                                    variant="primary"
                                                    class="w-full"
                                                    size="sm"
                                                >
                                                    {{ __('Asignar') }}
                                                </flux:button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Quitar de equipo / marcar individual --}}
                                    <x-agro.action-button
                                        icon="user"
                                        variant="default"
                                        wire:click="makeIndividual({{ $v->id }})"
                                        :title="$member && $member->crew ? __('Quitar del equipo') : __('Marcar sin equipo')"
                                    />
                                </div>

                                <div class="flex items-center gap-1">
                                    {{-- Invitación --}}
                                    @if(!$v->can_login && $v->invitation_sent_at === null)
                                        <x-agro.action-button
                                            icon="envelope"
                                            variant="primary"
                                            wire:click="sendInvitation({{ $v->id }})"
                                            :title="__('Enviar invitación')"
                                        />
                                    @elseif($v->invitation_sent_at !== null)
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-agro-500" :title="__('Invitación enviada el') . ' ' . $v->invitation_sent_at->format('d/m/Y')">
                                            <flux:icon icon="check-circle" class="size-4" />
                                        </span>
                                    @endif

                                    {{-- Eliminar --}}
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="deleteViticulturist({{ $v->id }})"
                                        wire:confirm="{{ __('¿Seguro que deseas eliminar este viticultor? Esta acción no se puede deshacer.') }}"
                                        :title="__('Eliminar viticultor')"
                                    />
                                </div>
                            </div>
                            @endif
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$viticulturists" />

        @else
            <x-agro.empty-state
                icon="users"
                :message="__('No hay viticultores')"
                :description="$search || $wineryFilter || $crewFilter || $statusFilter ? __('Ningún viticultor coincide con los filtros aplicados.') : __('Agrega tu primer viticultor para comenzar a gestionar tu equipo.')"
            >
                @if($search || $wineryFilter || $crewFilter || $statusFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        <flux:button href="{{ roleRoute('viticulturist.viticulturists.create') }}" variant="primary" icon="user-plus">
                            {{ __('Nuevo Viticultor') }}
                        </flux:button>
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif

    @else
        {{-- ===== VISTA EQUIPOS ===== --}}
        @if(isset($crewsPaginated) && $crewsPaginated->count() > 0)
            <div
                class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="search, wineryFilter, clearFilters, switchView"
            >
                @foreach($crewsPaginated as $i => $crew)

                    <x-agro.card
                        wire:key="crew-{{ $crew->id }}"
                        class="animate-fade-in-up hover:-translate-y-1"
                        style="animation-delay: {{ min($i * 50, 400) }}ms"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-agro-50 rounded-full flex items-center justify-center shrink-0">
                                    <flux:icon icon="user-group" class="size-4 text-agro-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $crew->name }}</p>
                                    @if($crew->description)
                                        <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $crew->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </x-slot:header>

                        {{-- Bodega --}}
                        <div class="flex items-center gap-2 mb-3">
                            <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                            <span class="text-xs text-zinc-600 truncate">{{ $crew->winery->name ?? __('Sin bodega') }}</span>
                        </div>

                        {{-- Métricas --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-agro-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-agro-600 font-medium uppercase tracking-wide mb-0.5">{{ __('Miembros') }}</p>
                                <p class="text-sm font-bold text-agro-700">{{ $crew->members_count }}</p>
                            </div>
                            <div class="bg-zinc-50 rounded-xl p-2.5">
                                <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">{{ __('Actividades') }}</p>
                                <p class="text-sm font-bold text-zinc-700">{{ $crew->activities_count }}</p>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <x-agro.action-button
                                        variant="view"
                                        href="{{ roleRoute('viticulturist.personal.show', $crew) }}#miembros"
                                        :title="__('Ver equipo')"
                                    />
                                    @can('update', $crew)
                                        <x-agro.action-button
                                            variant="edit"
                                            href="{{ roleRoute('viticulturist.personal.edit', $crew) }}"
                                            :title="__('Editar')"
                                        />
                                    @endcan
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-zinc-400">{{ $crew->created_at->format('d/m/Y') }}</span>
                                    @can('delete', $crew)
                                        <x-agro.action-button
                                            variant="delete"
                                            wire:click="deleteCrew({{ $crew->id }})"
                                            wire:confirm="{{ __('¿Seguro que deseas eliminar este equipo?') }}"
                                            :title="__('Eliminar')"
                                        />
                                    @endcan
                                </div>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro.pagination :paginator="$crewsPaginated" />

        @else
            <x-agro.empty-state
                icon="user-group"
                :message="__('No hay equipos')"
                :description="$search || $wineryFilter ? __('Ningún equipo coincide con los filtros aplicados.') : __('Crea tu primer equipo para organizar a tus viticultores.')"
            >
                @if($search || $wineryFilter)
                    <x-slot:action>
                        <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                    </x-slot:action>
                @else
                    <x-slot:action>
                        @can('create', \App\Models\Crew::class)
                            <flux:button href="{{ roleRoute('viticulturist.personal.create') }}" variant="primary" icon="plus">
                                {{ __('Nuevo Equipo') }}
                            </flux:button>
                        @endcan
                    </x-slot:action>
                @endif
            </x-agro.empty-state>
        @endif
    @endif

    {{-- Modal Filtros --}}
    <x-agro.filter-modal
        name="personal-filters"
        :hasActiveFilters="$wineryFilter || ($viewMode === 'personal' && ($crewFilter || $statusFilter))"
        clearAction="clearFilters"
    >
            @if(isset($wineries) && $wineries->count() > 1)
                <x-agro.filter-select :label="__('Bodega')" wire:model.live="wineryFilter" :placeholder="__('Todas las bodegas')">
                    @foreach($wineries as $winery)
                        <flux:select.option value="{{ $winery->id }}">{{ $winery->name }}</flux:select.option>
                    @endforeach
                </x-agro.filter-select>
            @endif

            @if($viewMode === 'personal')
                @if(isset($crews) && $crews->count() > 0)
                    <x-agro.filter-select :label="__('Equipo')" wire:model.live="crewFilter" :placeholder="__('Todos los equipos')">
                        @foreach($crews as $crew)
                            <flux:select.option value="{{ $crew->id }}">{{ $crew->name }}</flux:select.option>
                        @endforeach
                    </x-agro.filter-select>
                @endif
                <x-agro.filter-select :label="__('Estado')" wire:model.live="statusFilter" :placeholder="__('Todos los estados')">
                    <flux:select.option value="in_crew">{{ __('En equipo') }}</flux:select.option>
                    <flux:select.option value="individual">{{ __('Sin equipo') }}</flux:select.option>
                    <flux:select.option value="unassigned">{{ __('Sin asignar') }}</flux:select.option>
                </x-agro.filter-select>
            @endif
    </x-agro.filter-modal>

</div>
