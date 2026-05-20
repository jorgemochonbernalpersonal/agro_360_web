<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Dashboard Administrador"
        description="Panel de control principal con estadísticas generales del sistema"
    >
        <x-slot:actions>
            <span class="text-xs text-zinc-400 self-center">
                Actualizado {{ \Carbon\Carbon::createFromTimestamp($cachedAt)->diffForHumans() }}
            </span>
            <flux:button wire:click="refreshStats" variant="ghost" icon="arrow-path" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="refreshStats">Actualizar</span>
                <span wire:loading wire:target="refreshStats">Actualizando…</span>
            </flux:button>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">
                Exportar CSV
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

    {{-- Métricas SaaS --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Métricas SaaS</h2>
        <div class="grid grid-cols-2 gap-4">
            {{-- MRR --}}
            <x-agro.card>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                        <flux:icon icon="currency-euro" class="size-5 text-agro-600" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-zinc-500 font-medium uppercase tracking-wide">MRR</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($stats['saas']['mrr'], 2) }} €</p>
                        <p class="text-xs text-zinc-400 mt-0.5">ARR {{ number_format($stats['saas']['arr'], 0) }} €</p>
                    </div>
                </div>
            </x-agro.card>

            {{-- Suscripciones activas --}}
            <x-agro.card>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <flux:icon icon="credit-card" class="size-5 text-blue-600" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-zinc-500 font-medium uppercase tracking-wide">Activas</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ $stats['saas']['active'] }}</p>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ $stats['saas']['active_monthly'] }} mensual · {{ $stats['saas']['active_yearly'] }} anual
                        </p>
                    </div>
                </div>
            </x-agro.card>

            {{-- Nuevas vs Canceladas este mes --}}
            <x-agro.card>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-zinc-600">Nuevas este mes</span>
                        <span class="text-sm font-semibold {{ $stats['saas']['new_this_month'] > 0 ? 'text-agro-600' : 'text-zinc-900' }}">
                            +{{ $stats['saas']['new_this_month'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-zinc-600">Canceladas este mes</span>
                        <span class="text-sm font-semibold {{ $stats['saas']['cancelled_this_month'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">
                            −{{ $stats['saas']['cancelled_this_month'] }}
                        </span>
                    </div>
                    <div class="border-t border-zinc-100 pt-2 flex justify-between items-center">
                        <span class="text-sm font-medium text-zinc-700">Neto</span>
                        @php $net = $stats['saas']['net_new']; @endphp
                        <span class="text-sm font-bold {{ $net > 0 ? 'text-agro-600' : ($net < 0 ? 'text-red-600' : 'text-zinc-500') }}">
                            {{ $net >= 0 ? '+' : '' }}{{ $net }}
                        </span>
                    </div>
                </div>
            </x-agro.card>

            {{-- Churn rate --}}
            <x-agro.card>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $stats['saas']['churn_rate'] >= 5 ? 'bg-red-50' : ($stats['saas']['churn_rate'] >= 2 ? 'bg-orange-50' : 'bg-agro-50') }} flex items-center justify-center flex-shrink-0">
                        <flux:icon icon="arrow-trending-down" class="size-5 {{ $stats['saas']['churn_rate'] >= 5 ? 'text-red-600' : ($stats['saas']['churn_rate'] >= 2 ? 'text-orange-500' : 'text-agro-600') }}" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-zinc-500 font-medium uppercase tracking-wide">Churn mensual</p>
                        <p class="text-2xl font-bold {{ $stats['saas']['churn_rate'] >= 5 ? 'text-red-600' : ($stats['saas']['churn_rate'] >= 2 ? 'text-orange-500' : 'text-zinc-900') }}">
                            {{ $stats['saas']['churn_rate'] }}%
                        </p>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ $stats['saas']['churn_rate'] < 2 ? 'Saludable' : ($stats['saas']['churn_rate'] < 5 ? 'Atención' : 'Crítico') }}
                        </p>
                    </div>
                </div>
            </x-agro.card>
        </div>
    </div>

    {{-- Registros últimos 14 días --}}
    @if(!empty($registrationsChart))
    <div>
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Nuevos registros — últimos 14 días</h2>
        <x-agro.card>
            @php
                $maxReg = max(array_column($registrationsChart, 'count') ?: [0]);
                $maxReg = max($maxReg, 1);
                $totalReg = array_sum(array_column($registrationsChart, 'count'));
            @endphp
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-zinc-400">{{ $totalReg }} registro(s) en el periodo</span>
                <span class="text-xs text-zinc-400">Máx: {{ $maxReg }}/día</span>
            </div>
            <div class="flex items-end gap-1 h-12">
                @foreach($registrationsChart as $day)
                    @php
                        $barH = $day['count'] > 0 ? max(3, (int) round(($day['count'] / $maxReg) * 48)) : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-0.5 group cursor-default">
                        <div
                            class="w-full bg-agro-500 rounded-sm opacity-75 group-hover:opacity-100 transition-opacity"
                            style="height: {{ $barH }}px"
                            title="{{ $day['label'] }}: {{ $day['count'] }}"
                        ></div>
                    </div>
                @endforeach
            </div>
            <div class="flex mt-1">
                @foreach($registrationsChart as $i => $day)
                    <div class="flex-1 text-center">
                        @if($i % 7 === 0)
                            <span class="text-[9px] text-zinc-400">{{ $day['label'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    </div>
    @endif

    {{-- Funnel de activación --}}
    @if(!empty($activationFunnel))
    <div>
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Funnel de activación</h2>
        <x-agro.card>
            <div class="space-y-3">
                @foreach($activationFunnel as $i => $step)
                    @php
                        $colors = ['bg-purple-500', 'bg-blue-500', 'bg-agro-500', 'bg-orange-500', 'bg-teal-500'];
                        $color  = $colors[$i] ?? 'bg-zinc-400';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-zinc-600">{{ $step['label'] }}</span>
                            <span class="text-xs font-semibold text-zinc-900">{{ $step['count'] }} <span class="font-normal text-zinc-400">({{ $step['pct'] }}%)</span></span>
                        </div>
                        <div class="h-1.5 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $color }} rounded-full transition-all duration-500" style="width: {{ $step['pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    </div>
    @endif

    {{-- Alertas de seguridad + parcelas huérfanas --}}
    @if($suspiciousUsers->count() > 0 || $orphanedPlots > 0)
    <div class="space-y-3">
        <h2 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Alertas</h2>

        @if($suspiciousUsers->count() > 0)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <flux:icon icon="shield-exclamation" class="size-5 text-red-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-900">
                        {{ $suspiciousUsers->count() }} cuenta(s) con intentos de login fallidos en las últimas 24h
                    </p>
                    <div class="mt-2 space-y-1">
                        @foreach($suspiciousUsers as $s)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-red-800 font-medium truncate">{{ $s->email }}</span>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-2">
                                <span class="text-red-600 font-semibold">{{ $s->attempts }} intentos</span>
                                @if($s->ip)
                                    <span class="text-red-400 font-mono">{{ $s->ip }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.security-log.index') }}" class="inline-block mt-2 text-xs font-medium text-red-700 hover:text-red-900">
                        Ver log de seguridad →
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if($orphanedPlots > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center gap-3">
                <flux:icon icon="map" class="size-5 text-amber-600 flex-shrink-0" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-amber-900">
                        {{ $orphanedPlots }} parcela(s) huérfanas — sin viticultor activo vinculado
                    </p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        El propietario está inactivo o fue eliminado.
                    </p>
                </div>
                <a href="{{ route('admin.plots.index') }}" class="flex-shrink-0 text-xs font-medium text-amber-700 hover:text-amber-900">
                    Ver parcelas →
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif

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
                <a href="{{ route('admin.health.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0 group-hover:bg-agro-100 transition-colors">
                        <flux:icon icon="heart" class="size-5 text-agro-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-agro-600 transition-colors text-sm">Salud del Sistema</p>
                        <p class="text-xs text-zinc-500 truncate">Servicios y métricas</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-agro-400 transition-colors" />
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

            <x-agro.card class="hover-lift transition-all duration-200">
                <a href="{{ route('admin.failed-jobs.index') }}" class="flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                        <flux:icon icon="x-circle" class="size-5 text-red-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-zinc-900 group-hover:text-red-600 transition-colors text-sm">Jobs Fallidos</p>
                        <p class="text-xs text-zinc-500 truncate">Cola de trabajos</p>
                    </div>
                    <flux:icon icon="chevron-right" class="size-4 text-zinc-300 ml-auto group-hover:text-red-400 transition-colors" />
                </a>
            </x-agro.card>
        </div>
    </div>
</div>
