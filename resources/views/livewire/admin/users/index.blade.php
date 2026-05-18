<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Usuarios"
        description="Gestiona todos los usuarios del sistema"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">
                Exportar CSV
            </flux:button>
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                Nuevo Usuario
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas --}}
    <div x-data="{
        open: localStorage.getItem('admin-users-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('admin-users-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
            <div class="grid grid-cols-2 gap-4">
                <x-agro.stat-card label="Total Usuarios"  :value="$stats['total']"       icon="users"        color="purple" />
                <x-agro.stat-card label="Activos"          :value="$stats['active']"      icon="check-circle" color="agro"   />
                <x-agro.stat-card label="Verificados"      :value="$stats['verified']"    icon="envelope"     color="blue"   />
                <x-agro.stat-card label="Beta Activos"     :value="$stats['beta_active']" icon="clock"        color="orange" />
            </div>
        </div>
    </div>

    {{-- Tabs + Filtros + Tabla --}}
    <x-agro.card :padding="false">
        {{-- Tabs --}}
        @php
            $tabs = [
                'all'           => ['label' => 'Todos',        'count' => $stats['total']],
                'admin'         => ['label' => 'Admins',       'count' => $stats['by_role']['admin']],
                'supervisor'    => ['label' => 'Supervisores', 'count' => $stats['by_role']['supervisor']],
                'winery'        => ['label' => 'Bodegas',      'count' => $stats['by_role']['winery']],
                'viticulturist' => ['label' => 'Viticultores', 'count' => $stats['by_role']['viticulturist']],
                'producer'      => ['label' => 'Productores',  'count' => $stats['by_role']['producer']],
            ];
        @endphp
        <div class="px-6 pt-5">
            <x-agro.tabs :$tabs :active="$currentTab" />
        </div>

        {{-- Filtros --}}
        <div class="px-6 pb-3">
            <x-agro.filter-bar class="mb-0">
                <x-agro.filter-input wire:model.live="search" placeholder="Buscar por nombre o email..." />
                <x-agro.filter-select wire:model.live="filterActive">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </x-agro.filter-select>
                <x-agro.filter-select wire:model.live="filterVerified">
                    <option value="">Verificación email</option>
                    <option value="1">Verificado</option>
                    <option value="0">No verificado</option>
                </x-agro.filter-select>
                <x-agro.filter-select wire:model.live="filterBeta">
                    <option value="">Estado beta</option>
                    <option value="active">Beta activo</option>
                    <option value="expired">Beta expirado</option>
                    <option value="never">Sin beta</option>
                </x-agro.filter-select>
                <flux:button
                    wire:click="toggleInternal"
                    variant="ghost"
                    size="sm"
                    icon="bug-ant"
                    tooltip="{{ $showInternal ? 'Ocultar usuarios internos' : 'Mostrar usuarios internos (demo/test)' }}"
                    @class(['text-amber-500 bg-amber-50' => $showInternal])
                >
                    Internos
                </flux:button>
            </x-agro.filter-bar>
        </div>

        {{-- Banner: modo usuarios internos --}}
        @if($showInternal)
        <div class="mx-6 mb-3 flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm text-amber-800">
            <flux:icon icon="bug-ant" class="size-4 flex-shrink-0" />
            <span>Mostrando también cuentas internas (demo / test / maestro). Las estadísticas siempre reflejan solo usuarios reales.</span>
            <button wire:click="toggleInternal" class="ml-auto text-amber-600 hover:text-amber-800 font-medium text-xs">Ocultar</button>
        </div>
        @endif

        {{-- Fecha fin beta configurable --}}
        <div class="px-6 pb-3 flex items-center gap-2 text-xs text-zinc-500 border-t border-zinc-100 pt-3">
            <flux:icon icon="clock" class="size-3.5 flex-shrink-0" />
            <span>Fecha fin beta para nuevas activaciones:</span>
            <input
                type="date"
                wire:model.live="betaEndsAt"
                class="text-xs border border-zinc-200 rounded-md px-2 py-1 text-zinc-700 focus:outline-none focus:ring-2 focus:ring-agro-500"
            />
        </div>

        {{-- Barra de selección masiva --}}
        @if(count($selectedUsers) > 0)
        <div class="px-6 py-3 bg-blue-50 border-t border-blue-100 flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-blue-800">{{ count($selectedUsers) }} seleccionados</span>
            <div class="flex items-center gap-1.5 ml-2">
                <flux:button size="xs" variant="ghost" wire:click="bulkActivate"
                    wire:confirm="¿Activar los {{ count($selectedUsers) }} usuarios seleccionados?">
                    Activar
                </flux:button>
                <flux:button size="xs" variant="ghost" wire:click="bulkDeactivate"
                    wire:confirm="¿Desactivar los {{ count($selectedUsers) }} usuarios seleccionados?">
                    Desactivar
                </flux:button>
                <flux:button size="xs" variant="ghost" wire:click="bulkEnableBeta"
                    wire:confirm="¿Activar beta para los {{ count($selectedUsers) }} usuarios seleccionados?">
                    Beta ON
                </flux:button>
                <flux:button size="xs" variant="ghost" wire:click="bulkDisableBeta"
                    wire:confirm="¿Desactivar beta para los {{ count($selectedUsers) }} usuarios seleccionados?">
                    Beta OFF
                </flux:button>
            </div>
            <flux:button size="xs" variant="ghost" class="ml-auto text-zinc-500" wire:click="clearSelection">
                Deseleccionar
            </flux:button>
        </div>
        @endif

        {{-- Tabla --}}
        <div class="overflow-x-auto border-t border-zinc-100">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input
                                type="checkbox"
                                class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500"
                                x-data="{}"
                                :checked="$wire.selectedUsers.length === {{ count($pageUserIds) }} && {{ count($pageUserIds) }} > 0"
                                @change="$event.target.checked
                                    ? $wire.selectedUsers = @js($pageUserIds)
                                    : $wire.selectedUsers = []"
                            />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Última conexión</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-100">
                    @forelse($users as $user)
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
                        <x-agro.table-row>
                            <x-agro.table-cell class="px-4 w-10">
                                <input
                                    type="checkbox"
                                    class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500"
                                    wire:model.live="selectedUsers"
                                    value="{{ $user->id }}"
                                />
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                                        <flux:icon icon="user" class="size-4 text-purple-600" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <p class="text-sm font-semibold text-zinc-900">{{ $user->name }}</p>
                                            @if($user->isInternal())
                                                <flux:badge color="yellow" size="sm">Interno</flux:badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-zinc-400">ID: {{ $user->id }}</p>
                                    </div>
                                </div>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <flux:badge :color="$roleInfo['color']" size="sm">{{ $roleInfo['label'] }}</flux:badge>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="flex items-center gap-1.5 text-sm text-zinc-700">
                                    <flux:icon icon="envelope" class="size-4 text-zinc-400 flex-shrink-0" />
                                    <span>{{ $user->email }}</span>
                                </div>
                                @if($user->email_verified_at)
                                    <div class="flex items-center gap-1 mt-0.5 text-xs text-agro-600">
                                        <flux:icon icon="check-circle" class="size-3" />
                                        <span>Verificado</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 mt-0.5 text-xs text-zinc-400">
                                        <flux:icon icon="x-circle" class="size-3" />
                                        <span>No verificado</span>
                                    </div>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <div class="flex flex-col gap-1">
                                    <x-agro.status-badge :active="$user->can_login" />
                                    @if($user->is_beta_user)
                                        @if($user->beta_ends_at && $user->beta_ends_at->isPast())
                                            <x-agro.status-badge label="Beta expirado" type="gray" />
                                        @else
                                            <x-agro.status-badge label="Beta activo" type="warning" />
                                        @endif
                                    @endif
                                </div>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                <p class="text-sm text-zinc-700">{{ $user->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-zinc-400">{{ $user->created_at->diffForHumans() }}</p>
                            </x-agro.table-cell>

                            <x-agro.table-cell>
                                @if($user->last_login_at)
                                    <p class="text-sm text-zinc-700">{{ $user->last_login_at->format('d/m/Y H:i') }}</p>
                                    <p class="text-xs text-zinc-400">{{ $user->last_login_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-xs text-zinc-400">Nunca</p>
                                @endif
                            </x-agro.table-cell>

                            <x-agro.table-cell align="right">
                                <div class="flex items-center gap-1 justify-end">
                                    <x-agro.action-button
                                        variant="view"
                                        href="{{ route('admin.users.show', $user->id) }}"
                                    />

                                    @if(!$user->isAdmin() || $user->id === auth()->id())
                                        <x-agro.action-button
                                            :variant="$user->can_login ? 'deactivate' : 'activate'"
                                            wireClick="toggleActive({{ $user->id }})"
                                            :wireConfirm="$user->can_login ? '¿Desactivar este usuario?' : '¿Activar este usuario?'"
                                        />
                                    @endif

                                    @if(!$user->isAdmin())
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="clock"
                                            wire:click="toggleBeta({{ $user->id }})"
                                            wire:confirm="{{ $user->is_beta_user ? '¿Quitar acceso beta a este usuario?' : '¿Dar acceso beta a este usuario?' }}"
                                            tooltip="{{ $user->is_beta_user ? 'Quitar beta' : 'Dar beta' }}"
                                            @class(['text-yellow-500' => $user->is_beta_user])
                                        />
                                    @endif

                                    @if(!$user->isAdmin() && $user->can_login)
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="arrow-right-end-on-rectangle"
                                            wire:click="impersonate({{ $user->id }})"
                                            wire:confirm="¿Entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                                            tooltip="Entrar como este usuario"
                                        />
                                    @endif

                                    @if(!$user->isAdmin())
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            class="text-red-400 hover:text-red-600"
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer."
                                            tooltip="Eliminar usuario"
                                        />
                                    @endif
                                </div>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-agro.empty-state
                                    icon="users"
                                    message="No se encontraron usuarios"
                                    description="Intenta ajustar los filtros de búsqueda"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200">
                {{ $users->links() }}
            </div>
        @endif
    </x-agro.card>

    {{-- Modal: Crear usuario --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-purple-50">
                    <flux:icon icon="user-plus" class="size-5 text-purple-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Nuevo Usuario</h3>
                    <p class="text-xs text-zinc-500">Crea una nueva cuenta en el sistema</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Nombre completo</label>
                    <flux:input wire:model="createName" placeholder="Nombre del usuario" />
                    @error('createName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Email</label>
                    <flux:input wire:model="createEmail" type="email" placeholder="email@ejemplo.com" />
                    @error('createEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Rol</label>
                    <flux:select wire:model="createRole">
                        <option value="viticulturist">Viticultor</option>
                        <option value="winery">Bodega</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="producer">Productor</option>
                        <option value="admin">Admin</option>
                    </flux:select>
                    @error('createRole') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Contraseña</label>
                    <flux:input wire:model="createPassword" type="password" placeholder="Mínimo 8 caracteres" />
                    @error('createPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="createForceReset" class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500" />
                        <span class="text-sm text-zinc-700">Forzar cambio de contraseña en primer acceso</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="createSendVerification" class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500" />
                        <span class="text-sm text-zinc-700">Enviar email de verificación</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeCreateModal">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="createUser">Crear Usuario</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
