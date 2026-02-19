<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Usuarios"
        description="Gestiona todos los usuarios del sistema"
    />

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card label="Total Usuarios"  :value="$stats['total']"       icon="users"        color="purple" />
        <x-agro.stat-card label="Activos"          :value="$stats['active']"      icon="check-circle" color="agro"   />
        <x-agro.stat-card label="Verificados"      :value="$stats['verified']"    icon="envelope"     color="blue"   />
        <x-agro.stat-card label="Beta Activos"     :value="$stats['beta_active']" icon="clock"        color="orange" />
    </div>

    {{-- Tabs + Filtros + Tabla en un solo card --}}
    <x-agro.card :padding="false">
        {{-- Tabs --}}
        @php
            $tabs = [
                'all'           => ['label' => 'Todos',        'count' => $stats['total']],
                'admin'         => ['label' => 'Admins',       'count' => $stats['by_role']['admin']],
                'supervisor'    => ['label' => 'Supervisores', 'count' => $stats['by_role']['supervisor']],
                'winery'        => ['label' => 'Bodegas',      'count' => $stats['by_role']['winery']],
                'viticulturist' => ['label' => 'Viticultores', 'count' => $stats['by_role']['viticulturist']],
            ];
        @endphp
        <div class="px-6 pt-5">
            <x-agro.tabs :$tabs :active="$currentTab" />
        </div>

        {{-- Filtros --}}
        <div class="px-6 pb-4">
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
            </x-agro.filter-bar>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto border-t border-zinc-100">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-600 uppercase tracking-wider">Registro</th>
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
                            ];
                            $roleInfo = $roleMap[$user->role] ?? ['label' => ucfirst($user->role), 'color' => null];
                        @endphp
                        <x-agro.table-row>
                            <x-agro.table-cell>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                                        <flux:icon icon="user" class="size-4 text-purple-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">{{ $user->name }}</p>
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
                                            icon="arrow-right-on-rectangle"
                                            wire:click="impersonate({{ $user->id }})"
                                            wire:confirm="¿Entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                                            tooltip="Entrar como este usuario"
                                        />
                                    @endif
                                </div>
                            </x-agro.table-cell>
                        </x-agro.table-row>
                    @empty
                        <tr>
                            <td colspan="6">
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
</div>
