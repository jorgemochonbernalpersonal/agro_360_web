<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Usuarios"
        description="Gestiona todos los usuarios del sistema"
        icon-color="from-purple-500 to-purple-700"
    />

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <p class="text-sm font-medium text-purple-700">Total Usuarios</p>
            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <p class="text-sm font-medium text-green-700">Activos</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <p class="text-sm font-medium text-blue-700">Verificados</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['verified'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
            <p class="text-sm font-medium text-orange-700">Beta Activos</p>
            <p class="text-3xl font-bold text-orange-900 mt-1">{{ $stats['beta_active'] }}</p>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto">
                <button 
                    wire:click="switchTab('all')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                        {{ $currentTab === 'all' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Todos ({{ $stats['total'] }})</span>
                </button>
                
                <button 
                    wire:click="switchTab('admin')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                        {{ $currentTab === 'admin' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Admins ({{ $stats['by_role']['admin'] }})</span>
                </button>

                <button 
                    wire:click="switchTab('supervisor')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                        {{ $currentTab === 'supervisor' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Supervisores ({{ $stats['by_role']['supervisor'] }})</span>
                </button>

                <button 
                    wire:click="switchTab('winery')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                        {{ $currentTab === 'winery' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Bodegas ({{ $stats['by_role']['winery'] }})</span>
                </button>

                <button 
                    wire:click="switchTab('viticulturist')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                        {{ $currentTab === 'viticulturist' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Viticultores ({{ $stats['by_role']['viticulturist'] }})</span>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            <div class="space-y-6">
                {{-- Filtros --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-input wire:model.live="search" type="text" placeholder="Buscar por nombre o email..." />
                        <x-select wire:model.live="filterActive">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </x-select>
                        <x-select wire:model.live="filterVerified">
                            <option value="">Todos</option>
                            <option value="1">Email verificado</option>
                            <option value="0">Email no verificado</option>
                        </x-select>
                        <x-select wire:model.live="filterBeta">
                            <option value="">Todos</option>
                            <option value="active">Beta activo</option>
                            <option value="expired">Beta expirado</option>
                            <option value="never">Sin beta</option>
                        </x-select>
                    </div>
                </div>

                {{-- Tabla --}}
                @php
                    $headers = [
                        ['label' => 'Usuario', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                        ['label' => 'Rol', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'],
                        ['label' => 'Email', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'],
                        ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                        ['label' => 'Registro', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                        'Acciones',
                    ];
                @endphp

                <x-data-table :headers="$headers" empty-message="No se encontraron usuarios" empty-description="No hay usuarios que coincidan con los filtros seleccionados">
                    @if($users->count() > 0)
                        @foreach($users as $user)
                            <x-table-row>
                                <x-table-cell>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </x-table-cell>
                                <x-table-cell>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                           ($user->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 
                                           ($user->role === 'winery' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800')) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="text-sm text-gray-700">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                        @if($user->email_verified_at)
                                            <div class="flex items-center gap-1 mt-1 text-green-600">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs">Verificado</span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1 mt-1 text-gray-400">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs">No verificado</span>
                                            </div>
                                        @endif
                                    </div>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="flex flex-col gap-1">
                                        <x-status-badge :active="$user->can_login" />
                                        @if($user->is_beta_user)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                                {{ $user->beta_ends_at && $user->beta_ends_at->isPast() ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600' }}">
                                                {{ $user->beta_ends_at && $user->beta_ends_at->isPast() ? 'Beta expirado' : 'Beta activo' }}
                                            </span>
                                        @endif
                                    </div>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="text-sm text-gray-700">
                                        <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                                    </div>
                                </x-table-cell>
                                <x-table-actions align="right">
                                    {{-- Botón "Ver detalles" --}}
                                    <x-action-button 
                                        variant="view" 
                                        href="{{ route('admin.users.show', $user->id) }}"
                                        title="Ver detalles"
                                    />
                                    {{-- Botón Activar/Desactivar --}}
                                    @if(!$user->isAdmin() || $user->id === auth()->id())
                                        <button 
                                            wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->can_login ? '¿Desactivar este usuario?' : '¿Activar este usuario?' }}"
                                            class="p-2 rounded-lg transition-all duration-200 {{ $user->can_login ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }}"
                                            title="{{ $user->can_login ? 'Desactivar usuario' : 'Activar usuario' }}"
                                        >
                                            @if($user->can_login)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endif
                                    {{-- Botón Beta --}}
                                    @if(!$user->isAdmin())
                                        <button 
                                            wire:click="toggleBeta({{ $user->id }})"
                                            wire:confirm="{{ $user->is_beta_user ? '¿Quitar acceso beta a este usuario?' : '¿Dar acceso beta a este usuario?' }}"
                                            class="p-2 rounded-lg transition-all duration-200 {{ $user->is_beta_user ? 'text-yellow-600 hover:bg-yellow-50' : 'text-gray-600 hover:bg-gray-50' }}"
                                            title="{{ $user->is_beta_user ? 'Quitar beta' : 'Dar beta' }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @endif
                                    {{-- Botón "Entrar como" - Impersonación --}}
                                    @if(!$user->isAdmin() && $user->can_login)
                                        <button 
                                            wire:click="impersonate({{ $user->id }})"
                                            wire:confirm="¿Estás seguro de que quieres entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                                            class="p-2 rounded-lg transition-all duration-200 text-blue-600 hover:bg-blue-50"
                                            title="Entrar como este usuario"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    @endif
                                </x-table-actions>
                            </x-table-row>
                        @endforeach
                        <x-slot name="pagination">
                            {{ $users->links() }}
                        </x-slot>
                    @else
                        <x-slot name="emptyAction">
                            <p class="text-sm text-gray-500">Intenta ajustar los filtros de búsqueda</p>
                        </x-slot>
                    @endif
                </x-data-table>
            </div>
        </div>
    </div>
</div>

