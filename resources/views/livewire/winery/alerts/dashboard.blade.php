<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="{{ __('Centro de Alertas') }}"
    :description="__('Resumen de incidencias activas en tu bodega.')"
    icon="bell-alert"
>
</x-agro.page-header>

@php
    $totalAlerts = $lowSo2Wines->count()
        + $nearlyFullContainers->count()
        + $lowStockLots->count()
        + $lowLabelBatches->count()
        + $lowStockSupplies->count()
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
            title="{{ __('Todo en orden') }}"
            :description="__('No hay alertas activas en este momento. Tu bodega está al día.')"
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

    {{-- SO₂ bajo --}}
    @if($lowSo2Wines->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="beaker" class="w-5 h-5 text-red-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    SO₂ libre bajo en vinos en proceso
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                        {{ $lowSo2Wines->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($lowSo2Wines as $analysis)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $analysis->wine?->name }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">
                                SO₂ libre:
                                <span class="font-semibold">{{ $analysis->free_so2 ?? $analysis->so2_free ?? '—' }} mg/L</span>
                                · Análisis del {{ $analysis->analysis_date?->format('d/m/Y') }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="eye"
                            href="{{ roleRoute('wines.show', $analysis->wine) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Depósitos casi llenos --}}
    @if($nearlyFullContainers->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="exclamation-circle" class="w-5 h-5 text-amber-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Depósitos casi llenos (≥ 85%)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                        {{ $nearlyFullContainers->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($nearlyFullContainers as $container)
                    @php $pct = $container->getOccupancyPercentage(); @endphp
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $container->name }}
                                @if($container->containerRoom) <span class="text-zinc-400 font-normal">· {{ $container->containerRoom->name }}</span> @endif
                            </p>
                            <p class="text-sm {{ $pct >= 95 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ number_format($container->used_capacity, 0) }} / {{ number_format($container->capacity, 0) }} L
                                ({{ $pct }}%)
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('containers.edit', $container) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Lotes con stock bajo --}}
    @if($lowStockLots->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="archive-box-x-mark" class="w-5 h-5 text-orange-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Lotes de producto con poco stock (≤ 10%)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                        {{ $lowStockLots->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($lowStockLots as $lot)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $lot->name }} {{ $lot->vintage ? '('.$lot->vintage.')' : '' }}</p>
                            <p class="text-sm text-orange-600 dark:text-orange-400">
                                Disponible: <span class="font-semibold">{{ number_format($lot->available_quantity, 0) }}</span>
                                de {{ number_format($lot->initial_quantity, 0) }} {{ $lot->unit }}
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('product-lots.edit', $lot) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

    {{-- Etiquetas casi agotadas --}}
    @if($lowLabelBatches->count())
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <flux:icon icon="tag" class="w-5 h-5 text-orange-500" />
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">
                    Lotes de etiquetas casi agotados (≤ 10%)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300">
                        {{ $lowLabelBatches->count() }}
                    </span>
                </h3>
            </div>
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($lowLabelBatches as $batch)
                    @php $available = $batch->total_quantity - $batch->used_quantity - $batch->wasted_quantity; @endphp
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $batch->name }}</p>
                            <p class="text-sm text-orange-600 dark:text-orange-400">
                                Quedan: <span class="font-semibold">{{ number_format($available) }}</span>
                                de {{ number_format($batch->total_quantity) }} etiquetas
                            </p>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('label-batches.edit', $batch) }}" wire:navigate />
                    </li>
                @endforeach
            </ul>
        </x-agro.card>
    @endif

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
