<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="__('Usuario') . ': ' . $user->name"
        :description="__('Detalles y estadísticas del usuario')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left">
                {{ __('Volver') }}
            </flux:button>

            @if(!$user->email_verified_at)
                <flux:button
                    wire:click="verifyEmailManually"
                    wire:confirm="{{ __('¿Verificar el email de :name manualmente?', ['name' => $user->name]) }}"
                    variant="ghost"
                    icon="check-badge"
                >
                    {{ __('Verificar Email') }}
                </flux:button>
            @endif

            <flux:button
                wire:click="sendPasswordReset"
                wire:confirm="{{ __('¿Enviar email de restablecimiento de contraseña a :email?', ['email' => $user->email]) }}"
                variant="ghost"
                icon="key"
            >
                {{ __('Reset Contraseña') }}
            </flux:button>

            <flux:button
                wire:click="toggleActive"
                wire:confirm="{{ $user->can_login ? __('¿Desactivar a :name?', ['name' => $user->name]) : __('¿Activar a :name?', ['name' => $user->name]) }}"
                variant="ghost"
                icon="{{ $user->can_login ? 'lock-closed' : 'lock-open' }}"
            >
                {{ $user->can_login ? __('Desactivar') : __('Activar') }}
            </flux:button>

            <flux:button
                wire:click="openEditModal"
                variant="ghost"
                icon="pencil"
            >{{ __('Editar') }}</flux:button>

            @if(!$user->isAdmin() && $user->can_login)
                <flux:button
                    wire:click="impersonate"
                    wire:confirm="{{ __('¿Estás seguro de que quieres entrar como :name? Podrás volver a tu sesión de admin en cualquier momento.', ['name' => $user->name]) }}"
                    variant="primary"
                    icon="arrow-right-end-on-rectangle"
                >
                    {{ __('Entrar como usuario') }}
                </flux:button>
            @endif

            @if(!$user->isAdmin())
                <flux:button
                    wire:click="confirmDeleteUser"
                    variant="danger"
                    icon="trash"
                >
                    {{ __('Eliminar') }}
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Información Básica --}}
    @php
        $roleMap = [
            'admin'         => ['label' => 'Admin',      'color' => 'purple'],
            'supervisor'    => ['label' => 'Supervisor', 'color' => 'blue'],
            'winery'        => ['label' => 'Bodega',     'color' => 'violet'],
            'viticulturist' => ['label' => 'Viticultor', 'color' => 'green'],
            'producer'      => ['label' => 'Productor',  'color' => 'amber'],
        ];
        $roleInfo = $roleMap[$user->role] ?? ['label' => ucfirst($user->role), 'color' => null];
    @endphp

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-purple-50">
                    <flux:icon icon="information-circle" class="size-4 text-purple-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Información Básica') }}</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Nombre') }}</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Email') }}</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Rol') }}</p>
                <div class="mt-0.5">
                    <flux:badge :color="$roleInfo['color']" size="sm">{{ $roleInfo['label'] }}</flux:badge>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Estado') }}</p>
                <div class="mt-0.5">
                    <x-agro.status-badge :active="$user->can_login" />
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Email Verificado') }}</p>
                @if($user->email_verified_at)
                    <div class="flex items-center gap-1.5 mt-0.5 text-agro-600">
                        <flux:icon icon="check-circle" class="size-4" />
                        <span class="text-sm font-semibold">{{ __('Sí') }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 mt-0.5 text-zinc-400">
                        <flux:icon icon="x-circle" class="size-4" />
                        <span class="text-sm font-semibold">{{ __('No verificado') }}</span>
                    </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Beta') }}</p>
                <div class="mt-0.5">
                    @if($user->is_beta_user)
                        @if($user->beta_ends_at && $user->beta_ends_at->isPast())
                            <x-agro.status-badge :label="__('Beta Expirado')" type="gray" />
                        @else
                            <x-agro.status-badge :label="__('Beta Activo')" type="warning" />
                        @endif
                        @if($user->beta_ends_at)
                            <p class="text-xs text-zinc-400 mt-1">Hasta: {{ $user->beta_ends_at->format('d/m/Y') }}</p>
                        @endif
                    @else
                        <span class="text-sm text-zinc-400">{{ __('Sin beta') }}</span>
                    @endif
                </div>
            </div>
            @unless($user->isAdmin())
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Fundador') }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($user->is_founder)
                        <flux:badge color="amber" size="sm" icon="star">{{ __('Fundador') }}</flux:badge>
                    @else
                        <span class="text-sm text-zinc-400">{{ __('No') }}</span>
                    @endif
                    <flux:button
                        wire:click="toggleFounder"
                        wire:confirm="{{ $user->is_founder
                            ? __('¿Quitar el estado de fundador a :name?', ['name' => $user->name])
                            : __('¿Marcar a :name como fundador? Se le extenderá la beta a 1 año.', ['name' => $user->name]) }}"
                        variant="ghost"
                        size="xs"
                        icon="{{ $user->is_founder ? 'x-mark' : 'star' }}"
                    >{{ $user->is_founder ? __('Quitar') : __('Hacer fundador') }}</flux:button>
                </div>
                @php $maxSlots = config('app.founder_max_slots', 25); $taken = \App\Models\User::where('is_founder', true)->count(); @endphp
                <p class="text-xs text-zinc-400 mt-1">{{ $taken }} / {{ $maxSlots }} plazas ocupadas · {{ __('Beneficio: 1 año de plan Completo gratis') }}</p>
            </div>
            @endunless
            @if($user->isProducer())
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Compra Uva Externa') }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <x-agro.status-badge :active="$user->compra_uva_externa"
                        label="{{ $user->compra_uva_externa ? __('Activada') : __('Desactivada') }}" />
                    <flux:button
                        wire:click="toggleCompraUvaExterna"
                        variant="ghost"
                        size="xs"
                        icon="{{ $user->compra_uva_externa ? 'x-mark' : 'check' }}"
                    >{{ $user->compra_uva_externa ? __('Desactivar') : __('Activar') }}</flux:button>
                </div>
                <p class="text-xs text-zinc-400 mt-1">{{ __('Módulos: viticultores externos, aforos, disputas, facturas de uva') }}</p>
            </div>
            @endif
            @if($user->dni)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('DNI/NIF') }}</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ mask_nif($user->dni) }}</p>
            </div>
            @endif
            @if($user->organization)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Organización') }}</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->organization->name }}</p>
                <p class="text-xs text-zinc-400">{{ $user->organization->type === 'denomination_of_origin' ? __('DO') : __('Bodega') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Fecha de Registro') }}</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-zinc-400">{{ $user->created_at->diffForHumans() }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Última Conexión') }}</p>
                @if($user->last_login_at)
                    <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->last_login_at->diffForHumans() }}</p>
                @else
                    <p class="text-sm text-zinc-400 mt-0.5">{{ __('Nunca') }}</p>
                @endif
            </div>
            @if($user->email_verified_at)
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Verificación de Email') }}</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->email_verified_at->diffForHumans() }}</p>
                </div>
            @endif
        </div>
    </x-agro.card>

    {{-- Jerarquía de relaciones --}}
    @if(!empty($hierarchy))
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-zinc-100">
                    <flux:icon icon="share" class="size-4 text-zinc-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Relaciones') }}</span>
            </div>
        </x-slot:header>

        <div class="space-y-4">
            @if(isset($hierarchy['viticulturists']) && $hierarchy['viticulturists']->count() > 0)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">
                    Viticultores vinculados ({{ $hierarchy['viticulturists']->count() }})
                </p>
                <div class="space-y-1.5">
                    @foreach($hierarchy['viticulturists'] as $rel)
                    <a href="{{ route('admin.users.show', $rel->id) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors group">
                        <div class="w-7 h-7 rounded-md bg-agro-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="user" class="size-3.5 text-agro-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 group-hover:text-agro-600 truncate">{{ $rel->name }}</p>
                            <p class="text-xs text-zinc-400 truncate">{{ $rel->email }}</p>
                        </div>
                        <x-agro.status-badge :active="$rel->can_login" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($hierarchy['wineries']) && $hierarchy['wineries']->count() > 0)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">
                    Bodegas vinculadas ({{ $hierarchy['wineries']->count() }})
                </p>
                <div class="space-y-1.5">
                    @foreach($hierarchy['wineries'] as $rel)
                    <a href="{{ route('admin.users.show', $rel->id) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors group">
                        <div class="w-7 h-7 rounded-md bg-violet-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="building-office" class="size-3.5 text-violet-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 group-hover:text-violet-600 truncate">{{ $rel->name }}</p>
                            <p class="text-xs text-zinc-400 truncate">{{ $rel->email }}</p>
                        </div>
                        <x-agro.status-badge :active="$rel->can_login" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($hierarchy['supervised_wineries']) && $hierarchy['supervised_wineries']->count() > 0)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">
                    Bodegas supervisadas ({{ $hierarchy['supervised_wineries']->count() }})
                </p>
                <div class="space-y-1.5">
                    @foreach($hierarchy['supervised_wineries'] as $rel)
                    <a href="{{ route('admin.users.show', $rel->id) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors group">
                        <div class="w-7 h-7 rounded-md bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="building-office" class="size-3.5 text-blue-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 group-hover:text-blue-600 truncate">{{ $rel->name }}</p>
                            <p class="text-xs text-zinc-400 truncate">{{ $rel->email }}</p>
                        </div>
                        <x-agro.status-badge :active="$rel->can_login" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($hierarchy['supervised_viticulturists']) && $hierarchy['supervised_viticulturists']->count() > 0)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">
                    Viticultores supervisados ({{ $hierarchy['supervised_viticulturists']->count() }})
                </p>
                <div class="space-y-1.5">
                    @foreach($hierarchy['supervised_viticulturists'] as $rel)
                    <a href="{{ route('admin.users.show', $rel->id) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors group">
                        <div class="w-7 h-7 rounded-md bg-agro-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="user" class="size-3.5 text-agro-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 group-hover:text-agro-600 truncate">{{ $rel->name }}</p>
                            <p class="text-xs text-zinc-400 truncate">{{ $rel->email }}</p>
                        </div>
                        <x-agro.status-badge :active="$rel->can_login" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </x-agro.card>
    @endif

    {{-- Estadísticas de Viticultor --}}
    @if(isset($stats['viticulturist']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Estadísticas de Viticultor') }}</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <x-agro.stat-card
                    :label="__('Parcelas')"
                    :value="$stats['viticulturist']['plots']['total']"
                    :description="number_format($stats['viticulturist']['plots']['total_area'], 2) . ' ha'"
                    icon="map"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Clientes')"
                    :value="$stats['viticulturist']['clients']['total']"
                    :description="$stats['viticulturist']['clients']['active'] . ' activos'"
                    icon="user-group"
                    color="blue"
                />
                <x-agro.stat-card
                    :label="__('Facturas')"
                    :value="$stats['viticulturist']['invoices']['total']"
                    :description="number_format($stats['viticulturist']['invoices']['this_year_amount'], 2) . ' € este año'"
                    icon="document-text"
                    color="purple"
                />
                <x-agro.stat-card
                    :label="__('Actividades')"
                    :value="$stats['viticulturist']['activities']['total']"
                    :description="$stats['viticulturist']['activities']['this_year'] . ' este año'"
                    icon="clipboard-document-list"
                    color="orange"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-zinc-100">
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Campañas') }}</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-1">
                        {{ $stats['viticulturist']['campaigns']['total'] }} total
                        <span class="text-zinc-400 font-normal">({{ $stats['viticulturist']['campaigns']['active'] }} activas)</span>
                    </p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Tipos de Clientes') }}</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-1">
                        {{ $stats['viticulturist']['clients']['individual'] }} particulares,
                        {{ $stats['viticulturist']['clients']['company'] }} empresas
                    </p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Actividades Este Mes') }}</p>
                    <p class="text-lg font-bold text-zinc-900 mt-1">{{ $stats['viticulturist']['activities']['this_month'] }}</p>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Estadísticas de Bodega --}}
    @if(isset($stats['winery']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-violet-50">
                        <flux:icon icon="building-office" class="size-4 text-violet-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Estadísticas de Bodega') }}</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                <x-agro.stat-card
                    :label="__('Viticultores')"
                    :value="$stats['winery']['viticulturists']['total']"
                    icon="users"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Cuadrillas')"
                    :value="$stats['winery']['crews']['total']"
                    icon="user-group"
                    color="blue"
                />
                <x-agro.stat-card
                    :label="__('Clientes')"
                    :value="$stats['winery']['clients']['total']"
                    :description="$stats['winery']['clients']['active'] . ' activos'"
                    icon="identification"
                    color="purple"
                />
                <x-agro.stat-card
                    :label="__('Facturas')"
                    :value="$stats['winery']['invoices']['total']"
                    :description="$stats['winery']['invoices']['pending'] . ' pendientes'"
                    icon="document-text"
                    color="orange"
                />
                <x-agro.stat-card
                    :label="__('Vinos')"
                    :value="$stats['winery']['wines']['total']"
                    :description="number_format($stats['winery']['wines']['total_liters'], 0) . ' L'"
                    icon="beaker"
                    color="violet"
                />
                <x-agro.stat-card
                    :label="__('Depósitos')"
                    :value="$stats['winery']['containers']['total']"
                    :description="$stats['winery']['containers']['active'] . ' activos'"
                    icon="archive-box"
                    color="zinc"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-zinc-100">
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Facturación este año') }}</p>
                    <p class="text-lg font-bold text-zinc-900 mt-1">{{ number_format($stats['winery']['invoices']['this_year_amount'], 2) }} €</p>
                    <p class="text-xs text-zinc-400">{{ $stats['winery']['invoices']['this_year'] }} facturas</p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Vinos en elaboración') }}</p>
                    <p class="text-lg font-bold text-zinc-900 mt-1">{{ $stats['winery']['wines']['in_progress'] }}</p>
                    <p class="text-xs text-zinc-400">{{ $stats['winery']['wines']['bottled'] }} embotellados</p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Facturación total') }}</p>
                    <p class="text-lg font-bold text-zinc-900 mt-1">{{ number_format($stats['winery']['invoices']['total_amount'], 2) }} €</p>
                </div>
            </div>
        </x-agro.card>
    @endif

    {{-- Estadísticas de Supervisor --}}
    @if(isset($stats['supervisor']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-blue-50">
                        <flux:icon icon="shield-check" class="size-4 text-blue-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Estadísticas de Supervisor') }}</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-agro.stat-card
                    :label="__('Bodegas Supervisadas')"
                    :value="$stats['supervisor']['wineries']['total']"
                    :description="$stats['supervisor']['wineries']['active'] . ' activas · ' . $stats['supervisor']['wineries']['inactive'] . ' inactivas'"
                    icon="building-office"
                    color="blue"
                />
                <x-agro.stat-card
                    :label="__('Viticultores Supervisados')"
                    :value="$stats['supervisor']['viticulturists']['total']"
                    :description="$stats['supervisor']['viticulturists']['active'] . ' activos · ' . $stats['supervisor']['viticulturists']['inactive'] . ' inactivos'"
                    icon="users"
                    color="agro"
                />
            </div>
        </x-agro.card>
    @endif

    {{-- Estadísticas de Administrador --}}
    @if(isset($stats['admin']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-purple-50">
                        <flux:icon icon="shield-check" class="size-4 text-purple-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Estado del Sistema') }}</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <x-agro.stat-card
                    :label="__('Usuarios Totales')"
                    :value="$stats['admin']['users']['total']"
                    :description="$stats['admin']['users']['active'] . ' activos'"
                    icon="users"
                    color="purple"
                />
                <x-agro.stat-card
                    :label="__('Parcelas')"
                    :value="$stats['admin']['plots']['total']"
                    :description="number_format($stats['admin']['plots']['total_area'], 1) . ' ha'"
                    icon="map"
                    color="agro"
                />
                <x-agro.stat-card
                    :label="__('Tickets abiertos')"
                    :value="$stats['admin']['support']['open']"
                    :description="$stats['admin']['support']['new_this_week'] . ' nuevos esta semana'"
                    icon="chat-bubble-left-ellipsis"
                    color="orange"
                />
                <x-agro.stat-card
                    :label="__('Facturas pendientes')"
                    :value="$stats['admin']['invoices']['pending']"
                    :description="number_format($stats['admin']['invoices']['this_year_amount'], 0) . ' € facturado este año'"
                    icon="document-text"
                    color="blue"
                />
            </div>

            <div class="bg-zinc-50 rounded-lg px-4 py-3">
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ __('Nuevos usuarios este mes') }}</p>
                <p class="text-2xl font-bold text-zinc-900 mt-1">{{ $stats['admin']['users']['new_this_month'] }}</p>
            </div>
        </x-agro.card>
    @endif

    {{-- Historial de cambios --}}
    @if($userHistory->count() > 0)
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-zinc-100">
                    <flux:icon icon="clock" class="size-4 text-zinc-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Historial de actividad') }}</span>
                <flux:badge color="zinc" size="sm">{{ __('Últimos 20') }}</flux:badge>
            </div>
        </x-slot:header>

        @php
            $eventLabels = [
                'user_created_by_admin'           => ['label' => 'Cuenta creada por admin',        'color' => 'agro',   'icon' => 'user-plus'],
                'user_edited_by_admin'            => ['label' => 'Datos editados por admin',        'color' => 'blue',   'icon' => 'pencil'],
                'user_account_toggled'            => ['label' => 'Estado activado/desactivado',     'color' => 'yellow', 'icon' => 'power'],
                'user_beta_toggled'               => ['label' => 'Beta activado/desactivado',       'color' => 'violet', 'icon' => 'beaker'],
                'user_founder_toggled'            => ['label' => 'Fundador activado/desactivado',   'color' => 'amber',  'icon' => 'star'],
                'email_verified_manually_by_admin'=> ['label' => 'Email verificado por admin',      'color' => 'agro',   'icon' => 'check-badge'],
                'impersonation_started'           => ['label' => 'Sesión impersonada',              'color' => 'orange', 'icon' => 'arrow-right-end-on-rectangle'],
                'admin_readonly_toggled'          => ['label' => 'Permiso solo-lectura cambiado',   'color' => 'zinc',   'icon' => 'shield-check'],
                'login'                           => ['label' => 'Inicio de sesión',                'color' => 'agro',   'icon' => 'arrow-left-end-on-rectangle'],
                'logout'                          => ['label' => 'Cierre de sesión',                'color' => 'zinc',   'icon' => 'arrow-right-start-on-rectangle'],
                'failed_login'                    => ['label' => 'Intento de login fallido',        'color' => 'red',    'icon' => 'exclamation-triangle'],
                'password_reset_requested'        => ['label' => 'Reset de contraseña solicitado', 'color' => 'yellow', 'icon' => 'key'],
                'password_changed'                => ['label' => 'Contraseña cambiada',             'color' => 'blue',   'icon' => 'key'],
            ];
        @endphp

        <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-px bg-zinc-100"></div>
            <div class="space-y-4 pl-10">
                @foreach($userHistory as $event)
                    @php
                        $info = $eventLabels[$event->event] ?? ['label' => $event->event, 'color' => 'zinc', 'icon' => 'circle-stack'];
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-6 top-1 w-3 h-3 rounded-full border-2 border-white ring-1 ring-zinc-200 bg-white flex items-center justify-center">
                            <div class="w-1.5 h-1.5 rounded-full
                                {{ $info['color'] === 'red' ? 'bg-red-500' :
                                   ($info['color'] === 'agro' ? 'bg-agro-500' :
                                   ($info['color'] === 'blue' ? 'bg-blue-500' :
                                   ($info['color'] === 'orange' ? 'bg-orange-500' :
                                   ($info['color'] === 'violet' ? 'bg-violet-500' :
                                   ($info['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-zinc-400')))))}}">
                            </div>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$info['color']" size="sm">{{ $info['label'] }}</flux:badge>
                                    @if($event->ip)
                                        <span class="text-xs text-zinc-400 font-mono">{{ $event->ip }}</span>
                                    @endif
                                </div>
                                @if($event->message)
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ $event->message }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-zinc-400 whitespace-nowrap flex-shrink-0">
                                {{ $event->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-agro.card>
    @endif

    {{-- Panel: Read-only admin (solo visible si el usuario visto es admin) --}}
    @if($user->isAdmin() && $user->id !== auth()->id())
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg {{ $user->is_readonly_admin ? 'bg-zinc-100' : 'bg-purple-50' }}">
                    <flux:icon icon="shield-check" class="size-4 {{ $user->is_readonly_admin ? 'text-zinc-500' : 'text-purple-600' }}" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">{{ __('Permisos de Administrador') }}</span>
            </div>
        </x-slot:header>

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-900">{{ __('Modo solo lectura') }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">@if($user->is_readonly_admin){{ __('Este admin puede ver el panel pero no puede realizar cambios.') }}@else{{ __('Este admin tiene acceso completo al panel de administración.') }}@endif</p>
            </div>
            <div class="flex items-center gap-3">
                @if($user->is_readonly_admin)
                    <flux:badge color="zinc" size="sm">{{ __('Solo lectura') }}</flux:badge>
                @else
                    <flux:badge color="purple" size="sm">{{ __('Acceso completo') }}</flux:badge>
                @endif
                <flux:button
                    wire:click="toggleReadOnlyAdmin"
                    wire:confirm="{{ $user->is_readonly_admin ? __('¿Dar acceso completo a :name?', ['name' => $user->name]) : __('¿Restringir a solo lectura a :name?', ['name' => $user->name]) }}"
                    variant="ghost"
                    size="sm"
                    icon="{{ $user->is_readonly_admin ? 'lock-open' : 'lock-closed' }}"
                >
                    {{ $user->is_readonly_admin ? __('Dar acceso completo') : __('Restringir a solo lectura') }}
                </flux:button>
            </div>
        </div>
    </x-agro.card>
    @endif

    {{-- Modal: Editar usuario --}}
    <flux:modal wire:model="showEditModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-blue-50">
                    <flux:icon icon="pencil-square" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Editar Usuario') }}</h3>
                    <p class="text-xs text-zinc-500">Modifica los datos de {{ $user->name }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">{{ __('Nombre completo') }}</label>
                    <flux:input wire:model="editName" :placeholder="__('Nombre del usuario')" />
                    @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">{{ __('Email') }}</label>
                    <flux:input wire:model="editEmail" type="email" :placeholder="__('email@ejemplo.com')" />
                    @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($editEmail !== $user->email)
                        <p class="text-xs text-amber-600 mt-1">{{ __('Al cambiar el email, se requerirá nueva verificación.') }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">{{ __('Rol') }}</label>
                    <flux:select wire:model="editRole">
                        <option value="viticulturist">{{ __('Viticultor') }}</option>
                        <option value="winery">{{ __('Bodega') }}</option>
                        <option value="supervisor">{{ __('Supervisor') }}</option>
                        <option value="producer">{{ __('Productor') }}</option>
                        <option value="admin">{{ __('Admin') }}</option>
                    </flux:select>
                    @error('editRole') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeEditModal">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" wire:click="saveUser">{{ __('Guardar Cambios') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Notas internas del admin --}}
    <div class="mt-6 space-y-3">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Notas internas del admin') }}</h2>
        <x-agro.card>
            <div class="space-y-3">
                @forelse($adminNotes as $note)
                    <div class="flex items-start gap-3 group">
                        <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-xs font-bold text-amber-600">{{ strtoupper(substr($note->admin?->name ?? 'A', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-zinc-700">{{ $note->admin?->name ?? 'Admin' }}</span>
                                <span class="text-xs text-zinc-400">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-zinc-700 mt-0.5 whitespace-pre-wrap">{{ $note->note }}</p>
                        </div>
                        <flux:button wire:click="deleteNote({{ $note->id }})" wire:confirm="{{ __('¿Eliminar esta nota?') }}"
                            variant="ghost" size="sm" icon="trash"
                            class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-opacity" />
                    </div>
                @empty
                    <p class="text-sm text-zinc-400 text-center py-2">{{ __('Sin notas. Solo visibles para administradores.') }}</p>
                @endforelse
                <div class="border-t border-zinc-100 pt-3 flex gap-3">
                    <flux:textarea wire:model="newNote" rows="2" :placeholder="__('Añadir nota interna...')" class="flex-1" />
                    <flux:button wire:click="addNote" variant="primary" size="sm" icon="plus" class="self-end">{{ __('Añadir') }}</flux:button>
                </div>
                @error('newNote') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </x-agro.card>
    </div>

    {{-- Delete confirmation modal --}}
    <flux:modal wire:model="showDeleteModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 rounded-lg bg-red-50">
                    <flux:icon icon="exclamation-triangle" class="size-5 text-red-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Eliminar usuario') }}</h3>
                    <p class="text-xs text-zinc-500">{{ __('Esta acción no se puede deshacer.') }}</p>
                </div>
            </div>

            <p class="text-sm text-zinc-700">
                {{ __('Vas a eliminar a :name. Se borrarán de forma permanente todos sus datos asociados:', ['name' => $user->name]) }}
            </p>

            @if(count($deleteCounts) > 0)
                <ul class="mt-3 space-y-1.5 rounded-lg bg-red-50 border border-red-100 p-3">
                    @foreach($deleteCounts as $label => $count)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-zinc-700">{{ $label }}</span>
                            <span class="font-semibold text-red-700">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 text-sm text-zinc-500">{{ __('El usuario no tiene datos asociados registrados.') }}</p>
            @endif

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="danger" icon="trash" wire:click="deleteUser">{{ __('Eliminar definitivamente') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
