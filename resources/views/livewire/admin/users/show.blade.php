<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Usuario: {{ $user->name }}"
        description="Detalles y estadísticas del usuario"
    >
        <x-slot:actions>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>

            @if(!$user->email_verified_at)
                <flux:button
                    wire:click="verifyEmailManually"
                    wire:confirm="¿Verificar el email de {{ $user->name }} manualmente?"
                    variant="ghost"
                    icon="check-badge"
                >
                    Verificar Email
                </flux:button>
            @endif

            <flux:button
                wire:click="sendPasswordReset"
                wire:confirm="¿Enviar email de restablecimiento de contraseña a {{ $user->email }}?"
                variant="ghost"
                icon="key"
            >
                Reset Contraseña
            </flux:button>

            <flux:button
                wire:click="toggleActive"
                wire:confirm="{{ $user->can_login ? '¿Desactivar a ' . $user->name . '?' : '¿Activar a ' . $user->name . '?' }}"
                variant="ghost"
                icon="{{ $user->can_login ? 'lock-closed' : 'lock-open' }}"
            >
                {{ $user->can_login ? 'Desactivar' : 'Activar' }}
            </flux:button>

            <flux:button
                wire:click="openEditModal"
                variant="ghost"
                icon="pencil"
            >
                Editar
            </flux:button>

            @if(!$user->isAdmin() && $user->can_login)
                <flux:button
                    wire:click="impersonate"
                    wire:confirm="¿Estás seguro de que quieres entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                    variant="primary"
                    icon="arrow-right-end-on-rectangle"
                >
                    Entrar como usuario
                </flux:button>
            @endif

            @if(!$user->isAdmin())
                <flux:button
                    wire:click="deleteUser"
                    wire:confirm="¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer."
                    variant="danger"
                    icon="trash"
                >
                    Eliminar
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
                        <span class="text-sm font-semibold">No verificado</span>
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
            @if($user->isProducer())
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Compra Uva Externa</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <x-agro.status-badge :active="$user->compra_uva_externa"
                        label="{{ $user->compra_uva_externa ? 'Activada' : 'Desactivada' }}" />
                    <flux:button
                        wire:click="toggleCompraUvaExterna"
                        variant="ghost"
                        size="xs"
                        icon="{{ $user->compra_uva_externa ? 'x-mark' : 'check' }}"
                    >{{ $user->compra_uva_externa ? 'Desactivar' : 'Activar' }}</flux:button>
                </div>
                <p class="text-xs text-zinc-400 mt-1">Módulos: viticultores externos, aforos, disputas, facturas de uva</p>
            </div>
            @endif
            @if($user->dni)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">DNI/NIF</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ mask_nif($user->dni) }}</p>
            </div>
            @endif
            @if($user->organization)
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Organización</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->organization->name }}</p>
                <p class="text-xs text-zinc-400">{{ $user->organization->type === 'denomination_of_origin' ? 'DO' : 'Bodega' }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Fecha de Registro</p>
                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-zinc-400">{{ $user->created_at->diffForHumans() }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Última Conexión</p>
                @if($user->last_login_at)
                    <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-zinc-400">{{ $user->last_login_at->diffForHumans() }}</p>
                @else
                    <p class="text-sm text-zinc-400 mt-0.5">Nunca</p>
                @endif
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

    {{-- Jerarquía de relaciones --}}
    @if(!empty($hierarchy))
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-zinc-100">
                    <flux:icon icon="share" class="size-4 text-zinc-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Relaciones</span>
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
                    <span class="font-semibold text-zinc-900 text-sm">Estadísticas de Viticultor</span>
                </div>
            </x-slot:header>

            <div class="grid grid-cols-2 gap-4 mb-4">
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

    {{-- Modal: Editar usuario --}}
    <flux:modal wire:model="showEditModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-blue-50">
                    <flux:icon icon="pencil-square" class="size-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Editar Usuario</h3>
                    <p class="text-xs text-zinc-500">Modifica los datos de {{ $user->name }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Nombre completo</label>
                    <flux:input wire:model="editName" placeholder="Nombre del usuario" />
                    @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Email</label>
                    <flux:input wire:model="editEmail" type="email" placeholder="email@ejemplo.com" />
                    @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($editEmail !== $user->email)
                        <p class="text-xs text-amber-600 mt-1">Al cambiar el email, se requerirá nueva verificación.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Rol</label>
                    <flux:select wire:model="editRole">
                        <option value="viticulturist">Viticultor</option>
                        <option value="winery">Bodega</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="producer">Productor</option>
                        <option value="admin">Admin</option>
                    </flux:select>
                    @error('editRole') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeEditModal">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="saveUser">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
