<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="{{ __('Resumen Económico') }}"
    :description="__('Vista consolidada de ingresos, gastos y márgenes de tu bodega.')"
    icon="chart-bar-square"
>
    <x-slot:actions>
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="year" class="w-28">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </flux:select>
        </div>
    </x-slot:actions>
</x-agro.page-header>

{{-- ── KPIs principales ────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

    {{-- Ingresos totales --}}
    <x-agro.card class="border-l-4 border-l-green-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Ingresos cobrados') }}</p>
                <p class="text-2xl font-bold text-green-600 leading-none mt-1">
                    {{ number_format($totalRevenue, 0, ',', '.') }} €
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ $invoiceCount }} facturas · {{ $paidCount }} cobradas</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrow-trending-up" class="size-5 text-green-600" />
            </div>
        </div>
        @if($pendingRevenue > 0)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs text-amber-600 font-medium">
                    + {{ number_format($pendingRevenue, 0, ',', '.') }} € pendiente de cobro
                </p>
            </div>
        @endif
    </x-agro.card>

    {{-- Gastos de uva --}}
    <x-agro.card class="border-l-4 border-l-red-400">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Gastos de uva') }}</p>
                <p class="text-2xl font-bold text-red-500 leading-none mt-1">
                    {{ number_format($totalGrapeCost, 0, ',', '.') }} €
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ __('Liquidaciones de vendimia') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrow-trending-down" class="size-5 text-red-500" />
            </div>
        </div>
        @if($pendingGrapeCost > 0)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs text-amber-600 font-medium">
                    + {{ number_format($pendingGrapeCost, 0, ',', '.') }} € pendiente de pago
                </p>
            </div>
        @endif
    </x-agro.card>

    {{-- Costes de mantenimiento --}}
    <x-agro.card class="border-l-4 border-l-orange-400">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Mantenimiento') }}</p>
                <p class="text-2xl font-bold text-orange-500 leading-none mt-1">
                    {{ number_format($maintenanceCost, 0, ',', '.') }} €
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ __('Contenedores completados') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                <flux:icon icon="wrench-screwdriver" class="size-5 text-orange-500" />
            </div>
        </div>
    </x-agro.card>

    {{-- Margen bruto --}}
    <x-agro.card class="border-l-4 {{ $grossMargin >= 0 ? 'border-l-indigo-500' : 'border-l-red-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Margen bruto') }}</p>
                <p class="text-2xl font-bold {{ $grossMargin >= 0 ? 'text-indigo-600' : 'text-red-500' }} leading-none mt-1">
                    {{ number_format($grossMargin, 0, ',', '.') }} €
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ number_format($grossMarginPct, 1) }}% sobre ventas</p>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $grossMargin >= 0 ? 'bg-indigo-50' : 'bg-red-50' }} flex items-center justify-center shrink-0">
                <flux:icon icon="calculator" class="size-5 {{ $grossMargin >= 0 ? 'text-indigo-600' : 'text-red-500' }}" />
            </div>
        </div>
    </x-agro.card>

    {{-- Facturación pendiente --}}
    <x-agro.card class="border-l-4 border-l-amber-400">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Pendiente total') }}</p>
                <p class="text-2xl font-bold text-amber-500 leading-none mt-1">
                    {{ number_format($pendingRevenue + $pendingGrapeCost, 0, ',', '.') }} €
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ __('cobros + pagos pendientes') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <flux:icon icon="clock" class="size-5 text-amber-500" />
            </div>
        </div>
    </x-agro.card>

</div>

{{-- ── Contenido principal ─────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Evolución mensual de ingresos --}}
    <x-agro.card class="lg:col-span-2">
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                    <flux:icon icon="chart-bar" class="size-4 text-green-600" />
                </div>
                <span class="font-semibold text-zinc-900">Ingresos mensuales {{ $year }}</span>
            </div>
        </x-slot:header>

        @php $maxMonthly = $monthlyData->max('total') ?: 1; @endphp
        <div class="space-y-2 mt-2">
            @foreach($monthlyData as $m)
                <div class="flex items-center gap-3">
                    <span class="text-xs text-zinc-400 w-8 shrink-0">{{ $m['label'] }}</span>
                    <div class="flex-1 bg-zinc-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-green-500 transition-all duration-500"
                             style="width: {{ $maxMonthly > 0 ? ($m['total'] / $maxMonthly * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-zinc-700 w-24 text-right shrink-0">
                        @if($m['total'] > 0)
                            {{ number_format($m['total'], 0, ',', '.') }} €
                        @else
                            <span class="text-zinc-300">—</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </x-agro.card>

    {{-- Top clientes --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <flux:icon icon="users" class="size-4 text-blue-600" />
                </div>
                <span class="font-semibold text-zinc-900">{{ __('Top clientes') }}</span>
            </div>
        </x-slot:header>

        @if($topClients->isEmpty())
            <div class="py-6 text-center">
                <flux:icon icon="users" class="size-8 text-zinc-200 mx-auto mb-2" />
                <p class="text-sm text-zinc-400">{{ __('Sin facturas cobradas') }}</p>
            </div>
        @else
            @php $maxClient = $topClients->max('total') ?: 1; @endphp
            <div class="space-y-3 mt-2">
                @foreach($topClients as $tc)
                    @php
                        $clientName = $tc->client?->company_name
                            ?: trim(($tc->client?->first_name ?? '') . ' ' . ($tc->client?->last_name ?? ''))
                            ?: '—';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-zinc-800 truncate max-w-[140px]">{{ $clientName }}</span>
                            <span class="text-xs font-bold text-green-600 shrink-0">{{ number_format($tc->total, 0, ',', '.') }} €</span>
                        </div>
                        <div class="w-full bg-zinc-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-blue-500"
                                 style="width: {{ $maxClient > 0 ? ($tc->total / $maxClient * 100) : 0 }}%"></div>
                        </div>
                        <p class="text-[10px] text-zinc-400 mt-0.5">{{ $tc->count }} factura{{ $tc->count > 1 ? 's' : '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-agro.card>

</div>

{{-- ── Estado de bodega ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Vinos por estado --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                    <flux:icon icon="beaker" class="size-4 text-violet-600" />
                </div>
                <span class="font-semibold text-zinc-900">{{ __('Vinos en bodega') }}</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-4 gap-4 mt-2">
            <div class="text-center">
                <p class="text-2xl font-bold text-zinc-900">{{ $wineStats['total'] }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('Total') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-amber-500">{{ $wineStats['in_progress'] }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('En elaboración') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-500">{{ $wineStats['bottled'] }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('Embotellado') }}</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-500">{{ $wineStats['sold'] }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('Vendido') }}</p>
            </div>
        </div>

        @if($winesByType->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-zinc-100 space-y-2">
                @php
                    $typeLabels = \App\Models\Wine::WINE_TYPES;
                    $maxWineType = $winesByType->max('count') ?: 1;
                @endphp
                @foreach($winesByType as $wt)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-zinc-500 w-20 shrink-0">{{ __($typeLabels[$wt->wine_type] ?? $wt->wine_type) }}</span>
                        <div class="flex-1 bg-zinc-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-violet-500"
                                 style="width: {{ ($wt->count / $maxWineType) * 100 }}%"></div>
                        </div>
                        <span class="text-xs text-zinc-500 w-8 text-right shrink-0">{{ $wt->count }}</span>
                        <span class="text-xs text-zinc-400 shrink-0">
                            {{ $wt->total_liters ? number_format($wt->total_liters, 0) . ' L' : '' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-agro.card>

    {{-- Uso de depósitos --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <flux:icon icon="server-stack" class="size-4 text-blue-600" />
                </div>
                <span class="font-semibold text-zinc-900">{{ __('Capacidad de depósitos') }}</span>
            </div>
        </x-slot:header>

        <div class="mt-2">
            <div class="flex items-end justify-between mb-2">
                <div>
                    <p class="text-3xl font-bold text-zinc-900">
                        {{ number_format($containerStats['used_capacity'], 0, ',', '.') }}
                        <span class="text-base font-normal text-zinc-400">L</span>
                    </p>
                    <p class="text-xs text-zinc-400">usados de {{ number_format($containerStats['total_capacity'], 0, ',', '.') }} L totales</p>
                </div>
                <p class="text-2xl font-bold {{ $containerStats['usage_pct'] > 85 ? 'text-red-500' : ($containerStats['usage_pct'] > 60 ? 'text-amber-500' : 'text-green-500') }}">
                    {{ number_format($containerStats['usage_pct'], 0) }}%
                </p>
            </div>

            {{-- Barra de uso --}}
            <div class="w-full bg-zinc-100 rounded-full h-4 overflow-hidden">
                @php
                    $pct = min($containerStats['usage_pct'], 100);
                    $barColor = $pct > 85 ? 'bg-red-500' : ($pct > 60 ? 'bg-amber-500' : 'bg-green-500');
                @endphp
                <div class="h-4 rounded-full {{ $barColor }} transition-all duration-700"
                     style="width: {{ $pct }}%"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-zinc-100">
                <div class="text-center">
                    <p class="text-xl font-bold text-zinc-900">{{ $containerStats['total'] }}</p>
                    <p class="text-xs text-zinc-400">{{ __('depósitos activos') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-zinc-900">
                        {{ number_format($containerStats['total_capacity'] - $containerStats['used_capacity'], 0, ',', '.') }} L
                    </p>
                    <p class="text-xs text-zinc-400">{{ __('capacidad libre') }}</p>
                </div>
            </div>
        </div>
    </x-agro.card>

</div>

{{-- ── Acciones rápidas ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="{{ roleRoute('invoices.products.index') }}" wire:navigate
        class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-green-300 transition-all group">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
            <flux:icon icon="arrow-up-tray" class="size-5 text-green-600" />
        </div>
        <div>
            <p class="text-sm font-semibold text-zinc-900">{{ __('Venta de productos') }}</p>
            <p class="text-xs text-zinc-400">{{ __('Facturas de vino') }}</p>
        </div>
    </a>

    <a href="{{ roleRoute('invoices.grape-purchase.index') }}" wire:navigate
        class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
        <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center group-hover:bg-agro-100 transition-colors">
            <flux:icon icon="arrow-down-tray" class="size-5 text-agro-600" />
        </div>
        <div>
            <p class="text-sm font-semibold text-zinc-900">{{ __('Compra de uva') }}</p>
            <p class="text-xs text-zinc-400">{{ __('Liquidaciones') }}</p>
        </div>
    </a>

    <a href="{{ roleRoute('clients.index') }}" wire:navigate
        class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-blue-300 transition-all group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
            <flux:icon icon="users" class="size-5 text-blue-600" />
        </div>
        <div>
            <p class="text-sm font-semibold text-zinc-900">{{ __('Clientes') }}</p>
            <p class="text-xs text-zinc-400">{{ __('Gestión de clientes') }}</p>
        </div>
    </a>

    <a href="{{ roleRoute('financial-stats.index') }}" wire:navigate
        class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-indigo-300 transition-all group">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
            <flux:icon icon="presentation-chart-line" class="size-5 text-indigo-600" />
        </div>
        <div>
            <p class="text-sm font-semibold text-zinc-900">{{ __('Estadísticas') }}</p>
            <p class="text-xs text-zinc-400">{{ __('KPIs y tendencias') }}</p>
        </div>
    </a>
</div>

</div>
