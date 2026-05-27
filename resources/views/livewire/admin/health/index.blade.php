<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Salud del Sistema') }}"
        :description="__('Estado de los servicios y métricas de la plataforma')"
    >
        <x-slot:actions>
            <span class="text-xs text-zinc-400 self-center">
                Actualizado {{ $refreshedAt->format('H:i:s') }}
            </span>
            <flux:button wire:click="refresh" variant="ghost" icon="arrow-path" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="refresh">{{ __('Actualizar') }}</span>
                <span wire:loading wire:target="refresh">{{ __('Actualizando…') }}</span>
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @php
        $statusIcon  = fn($s) => match($s) { 'ok' => 'check-circle', 'warning' => 'exclamation-triangle', default => 'x-circle' };
        $statusColor = fn($s) => match($s) { 'ok' => 'text-agro-600', 'warning' => 'text-orange-500', default => 'text-red-600' };
        $statusBg    = fn($s) => match($s) { 'ok' => 'bg-agro-50', 'warning' => 'bg-orange-50', default => 'bg-red-50' };
        $statusBadge = fn($s) => match($s) { 'ok' => 'agro', 'warning' => 'yellow', default => 'red' };
    @endphp

    {{-- Resumen de estado --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @foreach([
            ['label' => 'Cola',       'key' => 'queue'],
            ['label' => 'Base datos', 'key' => 'database'],
            ['label' => 'Caché',      'key' => 'cache'],
            ['label' => 'Disco',      'key' => 'disk'],
            ['label' => 'Seguridad',  'key' => 'security'],
            ['label' => 'Email',      'key' => 'email'],
            ['label' => 'PayPal',     'key' => 'paypal'],
            ['label' => 'App',        'key' => 'app'],
        ] as $item)
            @php $s = $health[$item['key']]['status'] ?? 'ok'; @endphp
            <div class="rounded-xl border {{ $s === 'ok' ? 'border-agro-100' : ($s === 'warning' ? 'border-orange-200' : 'border-red-200') }} {{ $statusBg($s) }} px-4 py-3 flex items-center gap-3">
                <flux:icon :icon="$statusIcon($s)" class="size-5 flex-shrink-0 {{ $statusColor($s) }}" />
                <div>
                    <p class="text-xs font-semibold text-zinc-700">{{ $item['label'] }}</p>
                    <flux:badge :color="$statusBadge($s)" size="sm">{{ strtoupper($s) }}</flux:badge>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Cola de trabajos --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['queue']['status']) }}">
                            <flux:icon icon="queue-list" class="size-4 {{ $statusColor($health['queue']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Cola de trabajos') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['queue']['status'])" size="sm">{{ strtoupper($health['queue']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Driver') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['app']['queue_driver'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Jobs pendientes') }}</span>
                    <span class="text-sm font-semibold text-zinc-900">{{ $health['queue']['pending'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Jobs fallidos') }}</span>
                    @php $failed = $health['queue']['failed'] ?? 0; @endphp
                    <span class="text-sm font-semibold {{ $failed > 0 ? 'text-red-600' : 'text-zinc-900' }}">{{ $failed }}</span>
                </div>
            </div>
        </x-agro.card>

        {{-- Base de datos --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['database']['status']) }}">
                            <flux:icon icon="circle-stack" class="size-4 {{ $statusColor($health['database']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Base de datos') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['database']['status'])" size="sm">{{ strtoupper($health['database']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Estado') }}</span>
                    <span class="text-sm font-semibold {{ $health['database']['status'] === 'ok' ? 'text-agro-600' : 'text-red-600' }}">
                        {{ $health['database']['status'] === 'error' ? __('Sin conexión') : __('Conectado') }}
                    </span>
                </div>
                @if($health['database']['latency'] !== null)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Latencia') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['database']['latency'] }} ms</span>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Tamaño') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['database']['size_mb'] ?? '—' }} MB</span>
                </div>
            </div>
        </x-agro.card>

        {{-- Caché --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['cache']['status']) }}">
                            <flux:icon icon="bolt" class="size-4 {{ $statusColor($health['cache']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Caché') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['cache']['status'])" size="sm">{{ strtoupper($health['cache']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Driver') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['app']['cache_driver'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Lectura / escritura') }}</span>
                    <span class="text-sm font-semibold {{ $health['cache']['status'] === 'ok' ? 'text-agro-600' : 'text-red-600' }}">
                        {{ $health['cache']['status'] === 'ok' ? __('OK') : __('Fallo') }}
                    </span>
                </div>
                @if(isset($health['cache']['error']))
                <p class="text-xs text-red-500 font-mono">{{ $health['cache']['error'] }}</p>
                @endif
            </div>
        </x-agro.card>

        {{-- Disco --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['disk']['status']) }}">
                            <flux:icon icon="server-stack" class="size-4 {{ $statusColor($health['disk']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Disco') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['disk']['status'])" size="sm">{{ strtoupper($health['disk']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-xs text-zinc-500 mb-1">
                        <span>{{ $health['disk']['used_gb'] }} GB usados de {{ $health['disk']['total_gb'] }} GB</span>
                        <span class="font-semibold {{ $health['disk']['used_pct'] >= 90 ? 'text-red-600' : ($health['disk']['used_pct'] >= 70 ? 'text-orange-500' : 'text-zinc-600') }}">
                            {{ $health['disk']['used_pct'] }}%
                        </span>
                    </div>
                    <x-agro.progress-bar
                        :value="$health['disk']['used_pct']"
                        :color="$health['disk']['used_pct'] >= 90 ? 'red' : ($health['disk']['used_pct'] >= 70 ? 'orange' : 'agro')"
                    />
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Logs') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['disk']['logs_mb'] }} MB</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Storage/app') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['disk']['app_mb'] }} MB</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Libre') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['disk']['free_gb'] }} GB</span>
                </div>
            </div>
        </x-agro.card>

        {{-- Seguridad (últimas 24h) --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['security']['status']) }}">
                            <flux:icon icon="shield-exclamation" class="size-4 {{ $statusColor($health['security']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">Seguridad <span class="text-xs text-zinc-400 font-normal">{{ __('últimas 24h') }}</span></span>
                    </div>
                    <flux:badge :color="$statusBadge($health['security']['status'])" size="sm">{{ strtoupper($health['security']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Eventos totales') }}</span>
                    <span class="text-sm font-semibold text-zinc-900">{{ $health['security']['total'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Alertas / Errores') }}</span>
                    <span class="text-sm font-semibold {{ $health['security']['alerts'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">
                        {{ $health['security']['alerts'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Login fallidos') }}</span>
                    <span class="text-sm font-semibold {{ $health['security']['failed_logins'] > 5 ? 'text-orange-600' : 'text-zinc-900' }}">
                        {{ $health['security']['failed_logins'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Cuentas bloqueadas') }}</span>
                    <span class="text-sm font-semibold {{ $health['security']['locked'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">
                        {{ $health['security']['locked'] }}
                    </span>
                </div>
            </div>
            <x-slot:footer>
                <a href="{{ route('admin.security-log.index') }}" class="text-xs text-agro-600 hover:text-agro-700 font-medium">
                    Ver log completo →
                </a>
            </x-slot:footer>
        </x-agro.card>

        {{-- Email SMTP --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['email']['status']) }}">
                            <flux:icon icon="envelope" class="size-4 {{ $statusColor($health['email']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Email (SMTP)') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['email']['status'])" size="sm">{{ strtoupper($health['email']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Driver') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['email']['driver'] ?? '—' }}</span>
                </div>
                @if(isset($health['email']['host']))
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Host') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono truncate max-w-[140px]">{{ $health['email']['host'] }}</span>
                </div>
                @endif
                @if(isset($health['email']['latency']))
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Latencia') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['email']['latency'] }} ms</span>
                </div>
                @endif
                @if(isset($health['email']['note']))
                <p class="text-xs text-zinc-400">{{ $health['email']['note'] }}</p>
                @endif
                @if(isset($health['email']['error']))
                <p class="text-xs text-red-500 font-mono">{{ $health['email']['error'] }}</p>
                @endif
            </div>
        </x-agro.card>

        {{-- PayPal --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg {{ $statusBg($health['paypal']['status']) }}">
                            <flux:icon icon="credit-card" class="size-4 {{ $statusColor($health['paypal']['status']) }}" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('PayPal API') }}</span>
                    </div>
                    <flux:badge :color="$statusBadge($health['paypal']['status'])" size="sm">{{ strtoupper($health['paypal']['status']) }}</flux:badge>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Modo') }}</span>
                    <flux:badge :color="($health['paypal']['mode'] ?? '') === 'live' ? 'agro' : 'yellow'" size="sm">
                        {{ $health['paypal']['mode'] ?? '—' }}
                    </flux:badge>
                </div>
                @if(isset($health['paypal']['host']))
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Endpoint') }}</span>
                    <span class="text-xs font-mono text-zinc-500 truncate max-w-[140px]">{{ $health['paypal']['host'] }}</span>
                </div>
                @endif
                @if(isset($health['paypal']['latency']))
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Latencia') }}</span>
                    <span class="text-sm font-semibold text-zinc-900 font-mono">{{ $health['paypal']['latency'] }} ms</span>
                </div>
                @endif
                @if(isset($health['paypal']['error']))
                <p class="text-xs text-red-500 font-mono">{{ $health['paypal']['error'] }}</p>
                @endif
            </div>
        </x-agro.card>

        {{-- Información de la app --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-zinc-100">
                        <flux:icon icon="information-circle" class="size-4 text-zinc-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Información') }}</span>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('PHP') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['app']['php_version'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Laravel') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['app']['laravel_version'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Entorno') }}</span>
                    <flux:badge :color="$health['app']['environment'] === 'production' ? 'agro' : 'yellow'" size="sm">
                        {{ $health['app']['environment'] }}
                    </flux:badge>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Debug mode') }}</span>
                    <flux:badge :color="$health['app']['debug'] ? 'red' : 'agro'" size="sm">
                        {{ $health['app']['debug'] ? 'ON' : 'OFF' }}
                    </flux:badge>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-zinc-600">{{ __('Timezone') }}</span>
                    <span class="text-sm font-mono text-zinc-900">{{ $health['app']['timezone'] }}</span>
                </div>
            </div>
        </x-agro.card>

    </div>
</div>
