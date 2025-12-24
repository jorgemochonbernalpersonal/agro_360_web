<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Usuario: {{ $user->name }}"
        description="Detalles y estadísticas del usuario"
        icon-color="from-purple-500 to-purple-700"
    >
        <x-slot:actionButton>
            <x-button href="{{ route('admin.users.index') }}" variant="secondary">
                Volver a Lista
            </x-button>
            @if(!$user->isAdmin() && $user->can_login)
                <x-button 
                    wire:click="impersonate"
                    wire:confirm="¿Estás seguro de que quieres entrar como {{ $user->name }}? Podrás volver a tu sesión de admin en cualquier momento."
                    variant="primary"
                >
                    Entrar como este usuario
                </x-button>
            @endif
        </x-slot:actionButton>
    </x-page-header>

    {{-- Información Básica --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Información Básica
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Nombre</p>
                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Email</p>
                <p class="font-semibold text-gray-900">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Rol</p>
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                       ($user->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 
                       ($user->role === 'winery' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800')) }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Estado</p>
                <x-status-badge :active="$user->can_login" />
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Email Verificado</p>
                @if($user->email_verified_at)
                    <div class="flex items-center gap-2 text-green-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold">Sí</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold">No</span>
                    </div>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Beta</p>
                @if($user->is_beta_user)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        {{ $user->beta_ends_at && $user->beta_ends_at->isPast() ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600' }}">
                        {{ $user->beta_ends_at && $user->beta_ends_at->isPast() ? 'Beta Expirado' : 'Beta Activo' }}
                    </span>
                    @if($user->beta_ends_at)
                        <p class="text-xs text-gray-500 mt-1">Hasta: {{ $user->beta_ends_at->format('d/m/Y') }}</p>
                    @endif
                @else
                    <span class="text-sm text-gray-400">Sin beta</span>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Fecha de Registro</p>
                <p class="font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
            </div>
            @if($user->email_verified_at)
                <div>
                    <p class="text-sm text-gray-500 mb-1">Email Verificado</p>
                    <p class="font-semibold text-gray-900">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email_verified_at->diffForHumans() }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Estadísticas según Rol --}}
    @if(isset($stats['viticulturist']))
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Estadísticas de Viticultor
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <p class="text-sm font-medium text-green-700">Parcelas</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['viticulturist']['plots']['total'] }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ number_format($stats['viticulturist']['plots']['total_area'], 2) }} ha</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <p class="text-sm font-medium text-blue-700">Clientes</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['viticulturist']['clients']['total'] }}</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $stats['viticulturist']['clients']['active'] }} activos</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <p class="text-sm font-medium text-purple-700">Facturas</p>
                    <p class="text-2xl font-bold text-purple-900 mt-1">{{ $stats['viticulturist']['invoices']['total'] }}</p>
                    <p class="text-xs text-purple-600 mt-1">{{ number_format($stats['viticulturist']['invoices']['this_year_amount'], 2) }} € este año</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <p class="text-sm font-medium text-orange-700">Actividades</p>
                    <p class="text-2xl font-bold text-orange-900 mt-1">{{ $stats['viticulturist']['activities']['total'] }}</p>
                    <p class="text-xs text-orange-600 mt-1">{{ $stats['viticulturist']['activities']['this_year'] }} este año</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Campañas</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['viticulturist']['campaigns']['total'] }} total ({{ $stats['viticulturist']['campaigns']['active'] }} activas)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Tipos de Clientes</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['viticulturist']['clients']['individual'] }} particulares, {{ $stats['viticulturist']['clients']['company'] }} empresas</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Actividades Este Mes</p>
                    <p class="text-xl font-bold text-gray-900">{{ $stats['viticulturist']['activities']['this_month'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(isset($stats['winery']))
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Estadísticas de Bodega
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200">
                    <p class="text-sm font-medium text-indigo-700">Viticultores Asociados</p>
                    <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $stats['winery']['viticulturists']['total'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <p class="text-sm font-medium text-blue-700">Cuadrillas</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['winery']['crews']['total'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(isset($stats['supervisor']))
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Estadísticas de Supervisor
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <p class="text-sm font-medium text-blue-700">Bodegas Supervisadas</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['supervisor']['wineries']['total'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <p class="text-sm font-medium text-green-700">Viticultores Supervisados</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['supervisor']['viticulturists']['total'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(isset($stats['admin']))
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Información de Administrador
            </h3>
            <p class="text-gray-700">{{ $stats['admin']['note'] }}</p>
        </div>
    @endif
</div>

