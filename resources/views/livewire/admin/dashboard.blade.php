@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>';
    $badgeIcon = '<svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    
    // Iconos para feature cards
    $iconUsers = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    $iconSupport = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $iconPlots = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>';
    $iconPlantings = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4a1 1 0 001 1h3m10-5v4a1 1 0 01-1 1h-3M5 21v-4a1 1 0 011-1h3m10 5v-4a1 1 0 00-1-1h-3"/></svg>';
    $iconSigpac = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
    $iconConfig = '<svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
@endphp

<div class="space-y-6 animate-fade-in">
    <x-page-header
        :icon="$icon"
        title="Dashboard Administrador"
        description="Panel de control principal con estadísticas generales del sistema"
        icon-color="from-purple-500 to-purple-700"
        :badge-icon="$badgeIcon"
    />

    {{-- Estadísticas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-700">Total Usuarios</p>
                    <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['users']['total'] }}</p>
                    <p class="text-xs text-purple-600 mt-1">{{ $stats['users']['active'] }} activos</p>
                </div>
                <svg class="w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-700">Parcelas</p>
                    <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['plots']['total'] }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ number_format($stats['plots']['total_area'], 2) }} ha</p>
                </div>
                <svg class="w-12 h-12 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-700">Clientes</p>
                    <p class="text-3xl font-bold text-blue-900 mt-1">{{ $stats['clients']['total'] }}</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $stats['clients']['active'] }} activos</p>
                </div>
                <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-700">Facturas Este Año</p>
                    <p class="text-3xl font-bold text-orange-900 mt-1">{{ $stats['invoices']['this_year'] }}</p>
                    <p class="text-xs text-orange-600 mt-1">{{ number_format($stats['invoices']['this_year_amount'], 2) }} €</p>
                </div>
                <svg class="w-12 h-12 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Estadísticas Secundarias --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Usuarios por Rol
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Viticultores</span>
                    <span class="font-bold text-gray-900">{{ $stats['users']['by_role']['viticulturist'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Bodegas</span>
                    <span class="font-bold text-gray-900">{{ $stats['users']['by_role']['winery'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Supervisores</span>
                    <span class="font-bold text-gray-900">{{ $stats['users']['by_role']['supervisor'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Administradores</span>
                    <span class="font-bold text-gray-900">{{ $stats['users']['by_role']['admin'] }}</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Actividad
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Actividades Este Año</span>
                    <span class="font-bold text-gray-900">{{ $stats['activities']['this_year'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Actividades Este Mes</span>
                    <span class="font-bold text-gray-900">{{ $stats['activities']['this_month'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Nuevos Usuarios Este Mes</span>
                    <span class="font-bold text-gray-900">{{ $stats['users']['new_this_month'] }}</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Soporte
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Tickets Abiertos</span>
                    <span class="font-bold text-red-600">{{ $stats['support']['open'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">En Progreso</span>
                    <span class="font-bold text-yellow-600">{{ $stats['support']['in_progress'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Nuevos Esta Semana</span>
                    <span class="font-bold text-gray-900">{{ $stats['support']['new_this_week'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Cards de Funcionalidades --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-feature-card
            href="{{ route('admin.users.index') }}"
            :icon="$iconUsers"
            title="Usuarios"
            description="Gestiona todos los usuarios del sistema por rol"
            icon-gradient="from-purple-500 to-purple-700"
            hover-border="hover:border-purple-500/50"
            hover-text="group-hover:text-purple-600"
        />

        <x-feature-card
            href="{{ route('admin.support.index') }}"
            :icon="$iconSupport"
            title="Tickets de Soporte"
            description="Gestiona todos los tickets de soporte del sistema"
            icon-gradient="from-red-500 to-red-700"
            hover-border="hover:border-red-500/50"
            hover-text="group-hover:text-red-600"
        />

        <x-feature-card
            href="{{ route('admin.plots.index') }}"
            :icon="$iconPlots"
            title="Parcelas"
            description="Visualiza todas las parcelas del sistema"
            icon-gradient="from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]"
        />

        <x-feature-card
            href="{{ route('admin.sigpac.index') }}"
            :icon="$iconSigpac"
            title="SIGPACs"
            description="Visualiza todos los códigos SIGPAC del sistema"
            icon-gradient="from-[var(--color-agro-blue)] to-blue-700"
            hover-border="hover:border-[var(--color-agro-blue)]/50"
            hover-text="group-hover:text-[var(--color-agro-blue)]"
        />
    </div>
</div>

