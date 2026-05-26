<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="{{ __('Mis Viticultores') }}" :description="__('Viticultores vinculados a tu bodega')" />

    {{-- Stats --}}
    <x-agro.stats-section key="winery-vit">
        <x-agro.stat-card
            :label="__('Activos')"
            :value="$stats['active']"
            :description="__('Con acceso al portal')"
            icon="check-circle"
            color="green"
        />
        <x-agro.stat-card
            :label="__('Pendientes')"
            :value="$stats['pending']"
            :description="__('Sin activar o invitar')"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Propios')"
            :value="$stats['own']"
            :description="__('Creados por tu bodega')"
            icon="user-plus"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Con cuaderno')"
            :value="$stats['with_notebook']"
            :description="__('Acceso al cuaderno de campo')"
            icon="book-open"
            color="orange"
        />
    </x-agro.stats-section>

    {{-- Toolbar: search + acciones --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <x-agro.search-input wire:model.live.debounce.300ms="search" :placeholder="__('Buscar por nombre o email...')" />

        {{-- Filtro estado --}}
        <flux:select wire:model.live="statusFilter">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            <flux:select.option value="active">{{ __('Activos') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Pendientes') }}</flux:select.option>
        </flux:select>

        {{-- Filtro origen --}}
        <flux:select wire:model.live="sourceFilter">
            <flux:select.option value="">{{ __('Todos los orígenes') }}</flux:select.option>
            <flux:select.option value="own">{{ __('Propios') }}</flux:select.option>
            <flux:select.option value="supervisor">{{ __('Asignados por D.O.') }}</flux:select.option>
            <flux:select.option value="viticulturist">{{ __('Viticultor') }}</flux:select.option>
            <flux:select.option value="self">{{ __('Autoregistro') }}</flux:select.option>
        </flux:select>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Exportar Excel --}}
        <flux:button wire:click="export" variant="ghost" icon="arrow-down-tray">{{ __('Exportar') }}</flux:button>

        {{-- Asignar de D.O. (solo si hay supervisor vinculado) --}}
        @if($hasSupervisors)
            <flux:button wire:click="openDOModal" variant="ghost" icon="building-library">{{ __('Asignar de D.O.') }}</flux:button>
        @endif

        {{-- Invitar existente --}}
        <flux:button href="{{ roleRoute('viticulturists.invite') }}" variant="ghost" icon="link">
            Invitar existente
        </flux:button>

        {{-- Añadir Viticultor --}}
        <flux:button href="{{ roleRoute('viticulturists.create') }}" variant="primary" icon="plus">
            Añadir Viticultor
        </flux:button>

    </div>

    {{-- Grid de Viticultores — skeleton durante carga --}}
    <x-agro.loading-grid target="search, nextPage, previousPage, gotoPage" :count="6" />

    {{-- Grid de Viticultores — contenido real --}}
    <div wire:loading.remove wire:target="search, nextPage, previousPage, gotoPage">
        @if ($relations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-2">
                @foreach ($relations as $relation)
                    @php
                        $v     = $relation->viticulturist;
                        $delay = min($loop->index * 50, 300);

                        $sourceLabels = [
                            'own'           => ['label' => 'Propio',       'color' => 'blue'],
                            'supervisor'    => ['label' => 'D.O.',          'color' => 'purple'],
                            'viticulturist' => ['label' => 'Viticultor',   'color' => 'zinc'],
                            'self'          => ['label' => 'Autoregistro', 'color' => 'green'],
                        ];
                        $src = $sourceLabels[$relation->source] ?? ['label' => $relation->source, 'color' => 'zinc'];

                        // Estado de invitación (solo para propios sin acceso)
                        $inviteStatus = null;
                        if ($relation->source === 'own' && !$v->can_login) {
                            if ($v->invitation_token) {
                                $inviteStatus = ($v->invitation_expires_at && $v->invitation_expires_at->isPast())
                                    ? ['label' => 'Invitación caducada', 'color' => 'red',   'icon' => 'clock']
                                    : ['label' => 'Invitación enviada',  'color' => 'amber', 'icon' => 'paper-airplane'];
                            } else {
                                $inviteStatus = ['label' => 'Sin invitar', 'color' => 'zinc', 'icon' => 'envelope'];
                            }
                        }

                    @endphp

                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="viticulturist-card-{{ $relation->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                                    <span class="text-sm font-bold text-agro-700">
                                        {{ strtoupper(substr($v->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">{{ $v->name }}</h3>
                                    @if ($v->email && !str_starts_with($v->email, 'viticultores.'))
                                        <p class="text-xs text-zinc-500 truncate">{{ $v->email }}</p>
                                    @else
                                        <p class="text-xs text-zinc-400 italic truncate">{{ __('Sin email registrado') }}</p>
                                    @endif
                                </div>
                                <x-agro.status-badge :active="$v->can_login"
                                    :label="$v->can_login ? 'Con acceso' : 'Sin acceso'"
                                    class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">{{ __('Parcelas') }}</span>
                                <a href="{{ roleRoute('plots.index') }}"
                                    class="flex items-center gap-1 font-semibold text-agro-700 hover:underline">
                                    <flux:icon icon="map" class="size-4" />
                                    {{ $v->plots_count }} {{ Str::plural('parcela', $v->plots_count) }}
                                </a>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">{{ __('Origen') }}</span>
                                <flux:badge :color="$src['color']" size="sm">{{ $src['label'] }}</flux:badge>
                            </div>
                            @if ($v->can_login)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">{{ __('Email') }}</span>
                                    @if($v->email_verified_at)
                                        <flux:badge color="green" icon="check-circle" size="sm">{{ __('Verificado') }}</flux:badge>
                                    @else
                                        <flux:badge color="amber" icon="exclamation-triangle" size="sm">{{ __('Sin verificar') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">{{ __('Cuaderno') }}</span>
                                    @if($relation->notebook_access)
                                        <flux:badge color="green" icon="book-open" size="sm">{{ __('Acceso') }}</flux:badge>
                                    @else
                                        <flux:badge icon="lock-closed" size="sm">{{ __('Sin acceso') }}</flux:badge>
                                    @endif
                                </div>
                            @endif
                            @if ($inviteStatus)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">{{ __('Invitación') }}</span>
                                    <flux:badge
                                        :color="$inviteStatus['color']"
                                        :icon="$inviteStatus['icon']"
                                        size="sm"
                                    >
                                        {{ $inviteStatus['label'] }}
                                    </flux:badge>
                                </div>
                            @endif
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">

                                {{-- Grupo izquierdo: navegar --}}
                                <div class="flex items-center gap-0.5">
                                    <x-agro.action-button variant="view" href="{{ roleRoute('viticulturists.show', $v->id) }}" title="{{ __('Ver viticultor') }}" />
                                </div>

                                {{-- Separador vertical --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: gestionar --}}
                                <div class="flex items-center gap-0.5">
                                    @if ($relation->source === 'own')
                                        <x-agro.action-button variant="edit" href="{{ roleRoute('viticulturists.edit', $v->id) }}" title="{{ __('Editar') }}" />
                                        @if (!$v->can_login)
                                            <x-agro.action-button icon="paper-airplane" variant="primary" href="{{ roleRoute('viticulturists.show', $v->id) }}" title="{{ $v->invitation_token ? 'Ver invitación' : 'Invitar al portal' }}" />
                                        @endif
                                        <x-agro.action-button icon="link-slash" variant="danger" wire:click="unlinkViticulturist({{ $v->id }})" wire:confirm="{{ __('¿Desvincular a :name de tu bodega? Se revocará el acceso al cuaderno.', ['name' => $v->name]) }}" title="{{ __('Desvincular') }}" />
                                    @elseif ($relation->source === 'supervisor')
                                        <x-agro.action-button icon="x-mark" variant="danger" wire:click="unassignFromDO({{ $v->id }})" wire:confirm="{{ __('¿Desasignar a :name de tu bodega?', ['name' => $v->name]) }}" title="{{ __('Desasignar de D.O.') }}" />
                                    @endif
                                </div>

                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <x-agro-pagination :paginator="$relations" />
        @else
            <x-agro.empty-state
                icon="users"
                :message="__('No hay viticultores vinculados')"
                :description="__('Añade viticultores para gestionar sus parcelas y vendimias')"
            >
                <x-slot:action>
                    <flux:button href="{{ roleRoute('viticulturists.create') }}" variant="primary" icon="plus">
                        Añadir Viticultor
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

    {{-- Modal: Asignar viticultor de D.O. --}}
    <flux:modal wire:model="showDOModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2 rounded-lg bg-purple-50">
                    <flux:icon icon="building-library" class="size-5 text-purple-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Asignar viticultor de D.O.') }}</h3>
                    <p class="text-xs text-zinc-500">{{ __('Elige un viticultor del pool de tu denominación de origen') }}</p>
                </div>
            </div>

            {{-- Búsqueda --}}
            <div class="relative mb-4">
                <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-zinc-400 pointer-events-none" />
                <input
                    wire:model.live.debounce.300ms="doSearch"
                    type="text"
                    placeholder="{{ __('Buscar por nombre o email...') }}"
                    class="w-full pl-9 pr-3 py-2 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-300"
                />
            </div>

            {{-- Lista del pool --}}
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @forelse($doPool as $sv)
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-lg border border-zinc-100 hover:border-purple-200 hover:bg-purple-50 transition">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">{{ $sv->viticulturist->name }}</p>
                            <p class="text-xs text-zinc-400 truncate">
                                @if(!str_starts_with($sv->viticulturist->email ?? '', 'viticultores.'))
                                    {{ $sv->viticulturist->email }}
                                @else
                                    Sin email · D.O. {{ $sv->supervisor->name }}
                                @endif
                            </p>
                        </div>
                        <flux:button
                            wire:click="assignFromDO({{ $sv->viticulturist_id }})"
                            size="xs"
                            variant="ghost"
                            icon="plus"
                        >
                            Asignar
                        </flux:button>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-zinc-400">
                        @if($doSearch)
                            Sin resultados para "{{ $doSearch }}"
                        @else
                            Todos los viticultores de la D.O. ya están asignados
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="mt-5 pt-4 border-t border-zinc-100 flex justify-end">
                <flux:button variant="ghost" wire:click="closeDOModal">{{ __('Cerrar') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
