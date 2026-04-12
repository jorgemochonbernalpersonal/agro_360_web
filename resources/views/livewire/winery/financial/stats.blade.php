<div class="space-y-6 animate-fade-in">

<x-agro.page-header
    title="Estadísticas Financieras de Bodega"
    description="KPIs, tendencias y comparativas económicas de tu bodega."
    icon="presentation-chart-line"
>
    <x-slot:actions>
        <flux:select wire:model.live="year" class="w-28">
            @foreach($availableYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </flux:select>
    </x-slot:actions>
</x-agro.page-header>

{{-- ── KPIs principales ────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-agro.card class="border-l-4 border-l-green-500">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Ingresos cobrados</p>
                <p class="text-2xl font-bold text-green-600 leading-none mt-1">{{ number_format($totalRevenue, 0, ',', '.') }} &euro;</p>
                <p class="text-xs text-zinc-400 mt-1">{{ $paidCount }} de {{ $invoiceCount }} facturas</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrow-trending-up" class="size-5 text-green-600" />
            </div>
        </div>
        @if($revenueGrowth !== null)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs font-medium {{ $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}% vs {{ $year - 1 }}
                </p>
            </div>
        @endif
    </x-agro.card>

    <x-agro.card class="border-l-4 border-l-red-400">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Costes totales</p>
                <p class="text-2xl font-bold text-red-500 leading-none mt-1">{{ number_format($totalCosts, 0, ',', '.') }} &euro;</p>
                <p class="text-xs text-zinc-400 mt-1">Uva {{ number_format($grapeCost, 0, ',', '.') }} + Mant. {{ number_format($maintenanceCost, 0, ',', '.') }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrow-trending-down" class="size-5 text-red-500" />
            </div>
        </div>
        @if($costGrowth !== null)
            <div class="mt-3 pt-3 border-t border-zinc-100">
                <p class="text-xs font-medium {{ $costGrowth <= 0 ? 'text-green-600' : 'text-red-500' }}">
                    {{ $costGrowth >= 0 ? '+' : '' }}{{ number_format($costGrowth, 1) }}% coste uva vs {{ $year - 1 }}
                </p>
            </div>
        @endif
    </x-agro.card>

    <x-agro.card class="border-l-4 {{ $grossMargin >= 0 ? 'border-l-indigo-500' : 'border-l-red-500' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Margen bruto</p>
                <p class="text-2xl font-bold {{ $grossMargin >= 0 ? 'text-indigo-600' : 'text-red-500' }} leading-none mt-1">
                    {{ number_format($grossMargin, 0, ',', '.') }} &euro;
                </p>
                <p class="text-xs text-zinc-400 mt-1">{{ number_format($grossMarginPct, 1) }}% sobre ventas</p>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $grossMargin >= 0 ? 'bg-indigo-50' : 'bg-red-50' }} flex items-center justify-center shrink-0">
                <flux:icon icon="calculator" class="size-5 {{ $grossMargin >= 0 ? 'text-indigo-600' : 'text-red-500' }}" />
            </div>
        </div>
    </x-agro.card>

    <x-agro.card class="border-l-4 border-l-amber-400">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Pendiente</p>
                <p class="text-2xl font-bold text-amber-500 leading-none mt-1">{{ number_format($pendingRevenue, 0, ',', '.') }} &euro;</p>
                <p class="text-xs text-zinc-400 mt-1">
                    @if($overdueCount > 0)
                        <span class="text-red-500 font-medium">{{ $overdueCount }} vencida{{ $overdueCount > 1 ? 's' : '' }} ({{ number_format($overdueAmount, 0, ',', '.') }} &euro;)</span>
                    @else
                        Sin facturas vencidas
                    @endif
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <flux:icon icon="clock" class="size-5 text-amber-500" />
            </div>
        </div>
    </x-agro.card>
</div>

{{-- ── KPIs secundarios ────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <x-agro.stat-card label="Factura media" :value="number_format($averageInvoice, 0, ',', '.') . ' €'" icon="document-text" color="blue" />
    <x-agro.stat-card label="Tasa de cobro" :value="number_format($collectionRate, 0) . '%'" icon="check-circle" :color="$collectionRate >= 80 ? 'green' : ($collectionRate >= 50 ? 'orange' : 'red')" />
    <x-agro.stat-card label="Facturas {{ $year }}" :value="(string) $invoiceCount" :description="$prevInvoiceCount . ' en ' . ($year - 1)" icon="document-duplicate" color="purple" />
    <x-agro.stat-card label="Ingresos {{ $year - 1 }}" :value="number_format($prevRevenue, 0, ',', '.') . ' €'" description="referencia" icon="arrow-path" color="zinc" />
</div>

{{-- ── Evolución mensual ───────────────────────────────────────────── --}}
<x-agro.card>
    <x-slot:header>
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                <flux:icon icon="chart-bar" class="size-4 text-green-600" />
            </div>
            <span class="font-semibold text-zinc-900">Ingresos mensuales: {{ $year }} vs {{ $year - 1 }}</span>
        </div>
    </x-slot:header>

    @php
        $maxMonthly = max($monthlyData->max('current'), $monthlyData->max('previous'), 1);
    @endphp
    <div class="space-y-2 mt-2">
        @foreach($monthlyData as $m)
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-zinc-400 w-8 shrink-0">{{ $m['label'] }}</span>
                    <div class="flex gap-4 text-xs shrink-0">
                        <span class="font-semibold text-green-600 w-24 text-right">
                            {{ $m['current'] > 0 ? number_format($m['current'], 0, ',', '.') . ' €' : '—' }}
                        </span>
                        <span class="text-zinc-400 w-24 text-right">
                            {{ $m['previous'] > 0 ? number_format($m['previous'], 0, ',', '.') . ' €' : '—' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <div class="flex-1 relative h-4">
                        {{-- Barra año actual --}}
                        <div class="absolute inset-y-0 left-0 bg-green-500 rounded-full h-2 top-0"
                             style="width: {{ $maxMonthly > 0 ? ($m['current'] / $maxMonthly * 100) : 0 }}%"></div>
                        {{-- Barra año anterior --}}
                        <div class="absolute inset-y-0 left-0 bg-zinc-200 rounded-full h-2 top-2"
                             style="width: {{ $maxMonthly > 0 ? ($m['previous'] / $maxMonthly * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex items-center gap-6 mt-4 pt-3 border-t border-zinc-100">
        <div class="flex items-center gap-2">
            <div class="w-3 h-2 rounded-full bg-green-500"></div>
            <span class="text-xs text-zinc-500">{{ $year }}</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-3 h-2 rounded-full bg-zinc-200"></div>
            <span class="text-xs text-zinc-500">{{ $year - 1 }}</span>
        </div>
    </div>
</x-agro.card>

{{-- ── Rentabilidad por tipo de vino ───────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                    <flux:icon icon="beaker" class="size-4 text-purple-600" />
                </div>
                <span class="font-semibold text-zinc-900">Ingresos por tipo de vino</span>
            </div>
        </x-slot:header>

        @if($revenueByWineType->isNotEmpty())
            @php
                $typeLabels = \App\Models\Wine::WINE_TYPES;
                $typeColors = ['tinto' => 'bg-red-500', 'blanco' => 'bg-yellow-400', 'rosado' => 'bg-pink-400', 'espumoso' => 'bg-cyan-400'];
                $maxTypeRev = $revenueByWineType->max('revenue') ?: 1;
            @endphp
            <div class="space-y-3 mt-2">
                @foreach($revenueByWineType as $wt)
                    @php $barColor = $typeColors[$wt->wine_type] ?? 'bg-purple-400'; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full {{ $barColor }}"></div>
                                <span class="text-sm font-medium text-zinc-700">{{ $typeLabels[$wt->wine_type] ?? ucfirst($wt->wine_type) }}</span>
                            </div>
                            <span class="text-sm font-bold text-zinc-900">{{ number_format($wt->revenue, 0, ',', '.') }} &euro;</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-zinc-100 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ ($wt->revenue / $maxTypeRev) * 100 }}%"></div>
                            </div>
                            <span class="text-[10px] text-zinc-400 shrink-0">{{ $wt->units_sold }} uds · {{ $wt->invoice_count }} fact.</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-4 text-center">Sin ventas por tipo de vino en {{ $year }}.</p>
        @endif
    </x-agro.card>

    {{-- Stock de productos por tipo --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                    <flux:icon icon="archive-box" class="size-4 text-violet-600" />
                </div>
                <span class="font-semibold text-zinc-900">Stock de productos por tipo</span>
            </div>
        </x-slot:header>

        @if($productStock->isNotEmpty())
            @php $typeLabels = \App\Models\Wine::WINE_TYPES; @endphp
            <div class="space-y-4 mt-2">
                @foreach($productStock as $ps)
                    @php
                        $total = $ps->total_units ?: 1;
                        $availPct   = ($ps->available / $total) * 100;
                        $reservedPct = ($ps->reserved / $total) * 100;
                        $soldPct    = ($ps->sold / $total) * 100;
                        $margin = $ps->total_revenue > 0 && $ps->total_cost > 0
                            ? (($ps->total_revenue - $ps->total_cost) / $ps->total_revenue) * 100
                            : null;
                    @endphp
                    <div class="border-l-4 border-purple-400 pl-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-semibold text-zinc-900">{{ $typeLabels[$ps->wine_type] ?? ucfirst($ps->wine_type) }}</span>
                            @if($margin !== null)
                                <flux:badge size="sm" :color="$margin >= 30 ? 'green' : ($margin >= 0 ? 'amber' : 'red')">
                                    Margen {{ number_format($margin, 0) }}%
                                </flux:badge>
                            @endif
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs mb-2">
                            <div>
                                <span class="text-zinc-500">Disponible</span>
                                <p class="font-bold text-green-600">{{ number_format($ps->available) }}</p>
                            </div>
                            <div>
                                <span class="text-zinc-500">Reservado</span>
                                <p class="font-bold text-amber-600">{{ number_format($ps->reserved) }}</p>
                            </div>
                            <div>
                                <span class="text-zinc-500">Vendido</span>
                                <p class="font-bold text-blue-600">{{ number_format($ps->sold) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-0.5 h-2 rounded-full overflow-hidden bg-zinc-100">
                            <div class="bg-green-500" style="width: {{ $availPct }}%"></div>
                            <div class="bg-amber-500" style="width: {{ $reservedPct }}%"></div>
                            <div class="bg-blue-500" style="width: {{ $soldPct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-4 text-center">Sin productos en stock.</p>
        @endif
    </x-agro.card>
</div>

{{-- ── Top Clientes + Top Productos ────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <flux:icon icon="users" class="size-4 text-blue-600" />
                </div>
                <span class="font-semibold text-zinc-900">Top clientes {{ $year }}</span>
            </div>
        </x-slot:header>

        @if($topClients->isNotEmpty())
            @php $maxClient = $topClients->max('total') ?: 1; @endphp
            <div class="space-y-2 mt-2">
                @foreach($topClients as $i => $tc)
                    @php
                        $clientName = $tc->client?->company_name
                            ?: trim(($tc->client?->first_name ?? '') . ' ' . ($tc->client?->last_name ?? ''))
                            ?: '—';
                    @endphp
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-blue-700">{{ $i + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">{{ $clientName }}</p>
                            <div class="w-full bg-zinc-100 rounded-full h-1 mt-1">
                                <div class="h-1 rounded-full bg-blue-500" style="width: {{ ($tc->total / $maxClient) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-bold text-green-600">{{ number_format($tc->total, 0, ',', '.') }} &euro;</p>
                            <p class="text-[10px] text-zinc-400">{{ $tc->count }} fact.</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-6 text-center">Sin facturas cobradas en {{ $year }}.</p>
        @endif
    </x-agro.card>

    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                    <flux:icon icon="shopping-bag" class="size-4 text-rose-600" />
                </div>
                <span class="font-semibold text-zinc-900">Top productos {{ $year }}</span>
            </div>
        </x-slot:header>

        @if($topProducts->isNotEmpty())
            @php
                $maxProd = $topProducts->max('revenue') ?: 1;
                $typeLabels = \App\Models\Wine::WINE_TYPES;
            @endphp
            <div class="space-y-2 mt-2">
                @foreach($topProducts as $i => $tp)
                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center shrink-0">
                            <span class="text-xs font-bold text-rose-700">{{ $i + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-800 truncate">{{ $tp->name }}</p>
                            <p class="text-[10px] text-zinc-400">{{ $typeLabels[$tp->wine_type] ?? ucfirst($tp->wine_type) }} · {{ number_format($tp->units) }} uds</p>
                        </div>
                        <p class="text-xs font-bold text-green-600 shrink-0">{{ number_format($tp->revenue, 0, ',', '.') }} &euro;</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-6 text-center">Sin ventas de productos en {{ $year }}.</p>
        @endif
    </x-agro.card>
</div>

{{-- ── Facturas pendientes ─────────────────────────────────────────── --}}
@if($pendingInvoices->isNotEmpty())
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <flux:icon icon="clock" class="size-4 text-amber-600" />
                </div>
                <span class="font-semibold text-zinc-900">Facturas pendientes de cobro</span>
                <flux:badge size="sm" color="amber">{{ $pendingInvoices->count() }}</flux:badge>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 text-zinc-500 text-xs uppercase">
                        <th class="text-left px-3 py-2">Factura</th>
                        <th class="text-left px-3 py-2">Cliente</th>
                        <th class="text-left px-3 py-2">Fecha</th>
                        <th class="text-left px-3 py-2">Estado</th>
                        <th class="text-right px-3 py-2">Importe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($pendingInvoices as $inv)
                        @php
                            $cName = $inv->client?->company_name
                                ?: trim(($inv->client?->first_name ?? '') . ' ' . ($inv->client?->last_name ?? ''))
                                ?: '—';
                        @endphp
                        <tr class="hover:bg-zinc-50">
                            <td class="px-3 py-2 font-medium text-zinc-700">{{ $inv->invoice_number }}</td>
                            <td class="px-3 py-2 text-zinc-600 truncate max-w-[160px]">{{ $cName }}</td>
                            <td class="px-3 py-2 text-zinc-500">{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                <flux:badge size="sm" :color="$inv->payment_status === 'overdue' ? 'red' : 'amber'">
                                    {{ $inv->payment_status === 'overdue' ? 'Vencida' : 'Pendiente' }}
                                </flux:badge>
                            </td>
                            <td class="px-3 py-2 text-right font-bold text-zinc-800">{{ number_format($inv->total_amount, 2, ',', '.') }} &euro;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-agro.card>
@endif

</div>
