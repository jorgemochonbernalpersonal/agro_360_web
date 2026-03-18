<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Centro de Alertas"
    description="Resumen de incidencias activas en tu bodega."
    icon="bell-alert"
>
</x-agro.page-header>

@php
    $totalAlerts = $lowStockSupplies->count()
        + $expiredDocuments->count()
        + $expiringDocuments->count()
        + $expiredCerts->count()
        + $expiringCerts->count()
        + $expiringSanitary->count()
        + $expiringBottlingAuths->count();
@endphp

@if($totalAlerts === 0)
    <x-agro.card>
        <x-agro.empty-state
            icon="check-circle"
            title="Todo en orden"
            description="No hay alertas activas en este momento. Tu bodega está al día."
        />
    </x-agro.card>
@else
    {{-- Summary counter --}}
    <x-agro.card>
        <div class="flex items-center gap-4 p-2">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30">
                <flux:icon icon="bell-alert" class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $totalAlerts }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $totalAlerts === 1 ? 'alerta activa' : 'alertas activas' }} en tu bodega
                </p>
            </div>
        </div>
    </x-agro.card>

    {{-- Low stock --}}
    @if($lowStockSupplies->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="exclamation-triangle" class="w-5 h-5 text-orange-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Stock bajo
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                        {{ $lowStockSupplies->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($lowStockSupplies as $supply)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $supply->name }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Stock actual: <span class="font-semibold text-red-600">{{ number_format($supply->current_stock, 3) }}</span>
                                — Mínimo: {{ number_format($supply->min_stock_alert, 3) }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('winery-supplies.edit', $supply) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expired documents --}}
    @if($expiredDocuments->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="x-circle" class="w-5 h-5 text-red-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Documentos caducados
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                        {{ $expiredDocuments->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiredDocuments as $doc)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $doc->title }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">
                                Caducó el {{ $doc->expiry_date->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('documents.edit', $doc) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expiring documents --}}
    @if($expiringDocuments->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="clock" class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Documentos próximos a caducar
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                        {{ $expiringDocuments->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiringDocuments as $doc)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $doc->title }}</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                Caduca en {{ now()->diffInDays($doc->expiry_date) }} días
                                ({{ $doc->expiry_date->format('d/m/Y') }})
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('documents.edit', $doc) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expired eco certifications --}}
    @if($expiredCerts->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="x-circle" class="w-5 h-5 text-red-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Certificaciones ecológicas caducadas
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                        {{ $expiredCerts->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiredCerts as $cert)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $cert->name }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">
                                Caducó el {{ $cert->valid_until->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('eco-certifications.edit', $cert) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expiring eco certifications --}}
    @if($expiringCerts->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="clock" class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Certificaciones próximas a caducar
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                        {{ $expiringCerts->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiringCerts as $cert)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $cert->name }}</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                Vence el {{ $cert->valid_until->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('eco-certifications.edit', $cert) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expiring sanitary registrations --}}
    @if($expiringSanitary->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="clock" class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Registros sanitarios próximos a renovar
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                        {{ $expiringSanitary->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiringSanitary as $reg)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $reg->registration_number }}</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                Renovación: {{ $reg->renewal_date->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('sanitary-registrations.edit', $reg) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Expiring bottling authorizations --}}
    @if($expiringBottlingAuths->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="clock" class="w-5 h-5 text-yellow-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Autorizaciones de embotellado próximas a vencer
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                        {{ $expiringBottlingAuths->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($expiringBottlingAuths as $auth)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $auth->authorization_number }}</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-400">
                                Vence el {{ $auth->valid_until->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('bottling-authorizations.edit', $auth) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif
@endif
</div>
