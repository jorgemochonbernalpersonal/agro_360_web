<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Usuario: {{ $user->name }}"
        description="Detalles y estadísticas del usuario"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
            @if(!$user->isAdmin() && $user->can_login)
                <flux:button
                    wire:click="impersonate"
                    wire:confirm="¿Estás seguro de que quieres entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                    variant="primary"
                    icon="arrow-right-on-rectangle"
                >
                    Entrar como usuario
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
        ];
        $roleInfo = $roleMap[$user->role] ?? ['label' => ucfirst($user->role), 'color' => null];
    @endphp

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-purple-50">
                    <flux:icon icon="information-circle" class="size-4 text-purple-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Información Básica</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Nombre</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Email</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Rol</p>
                <div class="mt-0.5">
                    <flux:badge :color="$roleInfo['color']" size="sm">{{ $roleInfo['label'] }}</flux:badge>
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Estado</p>
                <div class="mt-0.5">
                    <x-agro.status-badge :active="$user->can_login" />
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Email Verificado</p>
                @if($user->email_verified_at)
                    <div class="flex items-center gap-1.5 mt-0.5 text-agro-600">
                        <flux:icon icon="check-circle" class="size-4" />
                        <span class="text-sm font-semibold">Sí</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 mt-0.5 text-zinc-400">
                        <flux:icon icon="x-circle" class="size-4" />
                        <span class="text-sm font-semibold">No</span>
                    </div>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Beta</p>
                <div class="mt-0.5">
                    @if($user->is_beta_user)
                        @if($user->beta_ends_at && $user->beta_ends_at->isPast())
                            <x-agro.status-badge label="Beta Expirado" type="gray" />
                        @else
                            <x-agro.status-badge label="Beta Activo" type="warning" />
                        @endif
                        @if($user->beta_ends_at)
                            <p class="text-xs text-zinc-400 mt-1">Hasta: {{ $user->beta_ends_at->format('d/m/Y') }}</p>
                        @endif
                    @else
                        <span class="text-sm text-zinc-400">Sin beta</span>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Fecha de Registro</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-zinc-400">{{ $user->created_at->diffForHumans() }}</p>
            </div>
            @if($user->email_verified_at)
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Verificación de Email</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->email_verified_at->diffForHumans() }}</p>
                </div>
            @endif
        </div>
    </x-agro.card>

    {{-- Estadísticas de Viticultor --}}
    @if(isset($stats['viticulturist']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="chart-bar" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Estadísticas de Viticultor</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <x-agro.stat-card
                    label="Parcelas"
                    :value="$stats['viticulturist']['plots']['total']"
                    :description="number_format($stats['viticulturist']['plots']['total_area'], 2) . ' ha'"
                    icon="map"
                    color="agro"
                />
                <x-agro.stat-card
                    label="Clientes"
                    :value="$stats['viticulturist']['clients']['total']"
                    :description="$stats['viticulturist']['clients']['active'] . ' activos'"
                    icon="user-group"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Facturas"
                    :value="$stats['viticulturist']['invoices']['total']"
                    :description="number_format($stats['viticulturist']['invoices']['this_year_amount'], 2) . ' € este año'"
                    icon="document-text"
                    color="purple"
                />
                <x-agro.stat-card
                    label="Actividades"
                    :value="$stats['viticulturist']['activities']['total']"
                    :description="$stats['viticulturist']['activities']['this_year'] . ' este año'"
                    icon="clipboard-document-list"
                    color="orange"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-zinc-100">
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Campañas</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-1">
                        {{ $stats['viticulturist']['campaigns']['total'] }} total
                        <span class="text-zinc-400 font-normal">({{ $stats['viticulturist']['campaigns']['active'] }} activas)</span>
                    </p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Tipos de Clientes</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-1">
                        {{ $stats['viticulturist']['clients']['individual'] }} particulares,
                        {{ $stats['viticulturist']['clients']['company'] }} empresas
                    </p>
                </div>
                <div class="bg-zinc-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Actividades Este Mes</p>
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
                    <span class="font-semibold text-zinc-900 text-sm">Estadísticas de Bodega</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-agro.stat-card
                    label="Viticultores Asociados"
                    :value="$stats['winery']['viticulturists']['total']"
                    icon="users"
                    color="purple"
                />
                <x-agro.stat-card
                    label="Cuadrillas"
                    :value="$stats['winery']['crews']['total']"
                    icon="user-group"
                    color="blue"
                />
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
                    <span class="font-semibold text-zinc-900 text-sm">Estadísticas de Supervisor</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-agro.stat-card
                    label="Bodegas Supervisadas"
                    :value="$stats['supervisor']['wineries']['total']"
                    icon="building-office"
                    color="blue"
                />
                <x-agro.stat-card
                    label="Viticultores Supervisados"
                    :value="$stats['supervisor']['viticulturists']['total']"
                    icon="users"
                    color="agro"
                />
            </div>
        </x-agro.card>
    @endif

    {{-- Información de Admin --}}
    @if(isset($stats['admin']))
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-purple-50">
                        <flux:icon icon="shield-check" class="size-4 text-purple-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Información de Administrador</span>
                </div>
            </x-slot:header>
            <p class="text-sm text-zinc-600">{{ $stats['admin']['note'] }}</p>
        </x-agro.card>
    @endif
</div>
