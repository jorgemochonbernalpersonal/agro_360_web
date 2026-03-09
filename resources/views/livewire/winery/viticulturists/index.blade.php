<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="Mis Viticultores" description="Viticultores vinculados a tu bodega" />

    {{-- Toolbar: search + acciones --}}
    <div class="flex items-center gap-3">

        {{-- Search --}}
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <flux:icon icon="magnifying-glass" class="size-4 text-zinc-400" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o email..."
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm placeholder:text-zinc-400 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition" />
        </div>

        {{-- Separador --}}
        <div class="w-px h-8 bg-zinc-200 shrink-0"></div>

        {{-- Invitar existente --}}
        <flux:button href="{{ route('winery.viticulturists.invite') }}" variant="ghost" icon="link">
            Invitar existente
        </flux:button>

        {{-- Añadir Viticultor --}}
        <flux:button href="{{ route('winery.viticulturists.create') }}" variant="primary" icon="plus">
            Añadir Viticultor
        </flux:button>

    </div>

    {{-- Grid de Viticultores — skeleton durante carga --}}
    <div wire:loading wire:target="search, nextPage, previousPage, gotoPage">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
            @for ($i = 0; $i < 6; $i++)
                <x-agro.skeleton-card />
            @endfor
        </div>
    </div>

    {{-- Grid de Viticultores — contenido real --}}
    <div wire:loading.remove wire:target="search, nextPage, previousPage, gotoPage">
        @if ($relations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
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

                        $btnBase    = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                        $btnPrimary = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-agro-600 hover:bg-agro-50 transition-colors';
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
                                        <p class="text-xs text-zinc-400 italic truncate">Sin email registrado</p>
                                    @endif
                                </div>
                                <x-agro.status-badge :active="$v->can_login"
                                    :label="$v->can_login ? 'Con acceso' : 'Sin acceso'"
                                    class="shrink-0" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Parcelas</span>
                                <a href="{{ route('winery.plots.index') }}"
                                    class="flex items-center gap-1 font-semibold text-agro-700 hover:underline">
                                    <flux:icon icon="map" class="size-4" />
                                    {{ $v->plots_count }} {{ Str::plural('parcela', $v->plots_count) }}
                                </a>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Origen</span>
                                <flux:badge :color="$src['color']" size="sm">{{ $src['label'] }}</flux:badge>
                            </div>
                            @if ($inviteStatus)
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-500">Invitación</span>
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
                                    <a href="{{ route('winery.viticulturists.show', $v->id) }}"
                                        class="{{ $btnBase }}" title="Ver viticultor">
                                        <flux:icon icon="eye" class="size-4" />
                                    </a>
                                </div>

                                {{-- Separador vertical --}}
                                <div class="w-px h-5 bg-zinc-200 mx-1"></div>

                                {{-- Grupo derecho: gestionar --}}
                                <div class="flex items-center gap-0.5">
                                    @if ($relation->source === 'own')
                                        <a href="{{ route('winery.viticulturists.edit', $v->id) }}"
                                            class="{{ $btnBase }}" title="Editar">
                                            <flux:icon icon="pencil-square" class="size-4" />
                                        </a>
                                        @if (!$v->can_login)
                                            <a href="{{ route('winery.viticulturists.show', $v->id) }}"
                                                class="{{ $btnPrimary }}"
                                                title="{{ $v->invitation_token ? 'Ver invitación' : 'Invitar al portal' }}">
                                                <flux:icon icon="paper-airplane" class="size-4" />
                                            </a>
                                        @endif
                                    @endif
                                </div>

                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $relations->links() }}
            </div>
        @else
            <x-agro.empty-state
                icon="users"
                message="No hay viticultores vinculados"
                description="Añade viticultores para gestionar sus parcelas y vendimias"
            >
                <x-slot:action>
                    <flux:button href="{{ route('winery.viticulturists.create') }}" variant="primary" icon="plus">
                        Añadir Viticultor
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @endif
    </div>

</div>
