<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Dashboard Administrador"
        description="Panel de control principal con estadísticas generales del sistema"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">
                Exportar Usuarios CSV
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estadísticas Principales --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Total Usuarios"
            :value="$stats['users']['total']"
            :description="$stats['users']['active'] . ' activos'"
            icon="users"
            color="purple"
        />

        <x-agro.stat-card
            label="Parcelas"
            :value="$stats['plots']['total']"
            :description="number_format($stats['plots']['total_area'], 2) . ' ha totales'"
            icon="map"
            color="agro"
        />

        <x-agro.stat-card
            label="Clientes"
            :value="$stats['clients']['total']"
            :description="$stats['clients']['active'] . ' activos'"
            icon="user-group"
            color="blue"
        />

        <x-agro.stat-card
            label="Facturas Este Año"
            :value="$stats['invoices']['this_year']"
            :description="number_format($stats['invoices']['this_year_amount'], 2) . ' €'"
            icon="document-text"
            color="orange"
        />
    </div>

    {{-- Estadísticas Secundarias --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-purple-50">
                        <flux:icon icon="users" class="size-4 text-purple-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Usuarios por Rol</span>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Viticultores</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['users']['by_role']['viticulturist'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Bodegas</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['users']['by_role']['winery'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Supervisores</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['users']['by_role']['supervisor'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Administradores</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['users']['by_role']['admin'] }}</span>
                </div>
            </div>
        </x-agro.card>

        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-blue-50">
                        <flux:icon icon="chart-bar" class="size-4 text-blue-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Actividad</span>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Actividades este año</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['activities']['this_year'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Actividades este mes</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['activities']['this_month'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Nuevos usuarios este mes</span>
                    <x-agro.status-badge :label="$stats['users']['new_this_month']" type="success" />
                </div>
            </div>
        </x-agro.card>

        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-red-50">
                        <flux:icon icon="question-mark-circle" class="size-4 text-red-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Soporte</span>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Tickets abiertos</span>
                    <x-agro.status-badge :label="$stats['support']['open']" type="danger" />
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">En progreso</span>
                    <x-agro.status-badge :label="$stats['support']['in_progress']" type="warning" />
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">Nuevos esta semana</span>
                    <span class="font-semibold text-zinc-900">{{ $stats['support']['new_this_week'] }}</span>
                </div>
            </div>
        </x-agro.card>
    </div>

    {{-- Accesos Rápidos --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Accesos Rápidos</h2>
        <div class="grid grid-cols-2 gap-4">
            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.security-log.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-100 transition-colors">
                        <flux:icon icon="shield-exclamation" class="size-5 text-orange-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-orange-600 transition-colors text-sm">Log de Seguridad</p>
                        <p class="text-xs text-zinc-500 truncate">Eventos y alertas</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-orange-400 transition-colors" />
                </a>
            </x-agro.card>

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.notifications.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <flux:icon icon="paper-airplane" class="size-5 text-blue-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-blue-600 transition-colors text-sm">Notificaciones</p>
                        <p class="text-xs text-zinc-500 truncate">Email masivo a usuarios</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-blue-400 transition-colors" />
                </a>
            </x-agro.card>
            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition-colors">
                        <flux:icon icon="users" class="size-5 text-purple-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-purple-600 transition-colors text-sm">Usuarios</p>
                        <p class="text-xs text-zinc-500 truncate">Gestión por roles</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-purple-400 transition-colors" />
                </a>
            </x-agro.card>

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.support.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                        <flux:icon icon="question-mark-circle" class="size-5 text-red-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-red-600 transition-colors text-sm">Soporte</p>
                        <p class="text-xs text-zinc-500 truncate">Tickets del sistema</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-red-400 transition-colors" />
                </a>
            </x-agro.card>

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.plots.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0 group-hover:bg-agro-100 transition-colors">
                        <flux:icon icon="map" class="size-5 text-agro-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-agro-600 transition-colors text-sm">Parcelas</p>
                        <p class="text-xs text-zinc-500 truncate">Todas las parcelas</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-agro-400 transition-colors" />
                </a>
            </x-agro.card>

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.sigpac.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                        <flux:icon icon="document-text" class="size-5 text-blue-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-blue-600 transition-colors text-sm">SIGPACs</p>
                        <p class="text-xs text-zinc-500 truncate">Códigos SIGPAC</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-blue-400 transition-colors" />
                </a>
            </x-agro.card>

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0 group-hover:bg-green-100 transition-colors">
                        <flux:icon icon="credit-card" class="size-5 text-green-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-green-600 transition-colors text-sm">Suscripciones</p>
                        <p class="text-xs text-zinc-500 truncate">Pagos y planes</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-green-400 transition-colors" />
                </a>
            </x-agro.card>
        </div>
    </div>
</div>
