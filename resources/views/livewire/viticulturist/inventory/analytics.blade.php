<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <x-agro.page-header
        title="{{ __('Analíticas de Inventario') }}"
        :description="__('Estadísticas y proyecciones de consumo de productos fitosanitarios')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.warehouse.index', ['tab' => 'fitosanitarios']) }}" variant="ghost" icon="arrow-left">
                Volver al Inventario
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-agro.stat-card :label="__('Total Productos')" :value="$stats['total_products']" icon="archive-box" color="blue" />
        <x-agro.stat-card :label="__('Valor Total')" :value="number_format($stats['total_value'], 2) . ' €'" icon="banknotes" color="green" />
        <x-agro.stat-card :label="__('Stock Bajo')" :value="$stats['low_stock_count']" icon="exclamation-triangle" color="yellow" />
        <x-agro.stat-card :label="__('Próximos a Caducar')" :value="$stats['expiring_count']" icon="calendar" color="red" />
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-agro.card>
            <x-slot:header>
                <flux:heading size="lg">{{ __('Consumo Mensual (Últimos 12 meses)') }}</flux:heading>
            </x-slot:header>
            <canvas id="consumptionChart" height="300"></canvas>
        </x-agro.card>
        <x-agro.card>
            <x-slot:header>
                <flux:heading size="lg">{{ __('Top 5 Productos Más Consumidos') }}</flux:heading>
            </x-slot:header>
            <canvas id="topProductsChart" height="300"></canvas>
        </x-agro.card>
    </div>

    {{-- Proyecciones de Agotamiento --}}
    @if(count($projections) > 0)
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-red-50">
                        <flux:icon icon="exclamation-triangle" class="size-4 text-red-600" />
                    </div>
                    <div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Proyección de Agotamiento de Stock') }}</span>
                        <p class="text-xs text-zinc-400">{{ __('Estimación basada en consumo promedio de los últimos 30 días') }}</p>
                    </div>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($projections as $projection)
                    @php
                        $statusColor = match($projection['status']) {
                            'critical' => 'red',
                            'warning'  => 'yellow',
                            default    => 'green',
                        };
                        $statusLabel = match($projection['status']) {
                            'critical' => 'Crítico',
                            'warning'  => 'Advertencia',
                            default    => 'OK',
                        };
                        $bgClass = match($projection['status']) {
                            'critical' => 'bg-red-50',
                            'warning'  => 'bg-amber-50',
                            default    => 'bg-agro-50',
                        };
                    @endphp
                    <div class="p-4 {{ $bgClass }} rounded-xl border border-zinc-100">
                        <div class="flex items-start justify-between mb-3">
                            <p class="font-semibold text-zinc-900 text-sm leading-tight">{{ $projection['product'] }}</p>
                            <flux:badge :color="$statusColor" size="sm">{{ $statusLabel }}</flux:badge>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <p class="text-zinc-500">{{ __('Stock actual') }}</p>
                                <p class="font-bold text-zinc-700">{{ number_format($projection['current_stock'], 2) }} {{ $projection['unit'] }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500">{{ __('Días restantes') }}</p>
                                <p class="font-bold text-zinc-700">{{ $projection['days_until_empty'] }} días</p>
                            </div>
                            <div>
                                <p class="text-zinc-500">{{ __('Consumo diario') }}</p>
                                <p class="font-medium text-zinc-600">{{ number_format($projection['avg_daily_consumption'], 3) }} {{ $projection['unit'] }}</p>
                            </div>
                            <div>
                                <p class="text-zinc-500">{{ __('Agotamiento est.') }}</p>
                                <p class="font-medium text-zinc-600">{{ $projection['estimated_empty_date']->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    @endif

    {{-- Productos con Baja Rotación --}}
    @if(count($slowMoving) > 0)
        <x-agro.card>
            <x-slot:header>
                <div>
                    <flux:heading size="lg">{{ __('Productos con Baja Rotación') }}</flux:heading>
                    <flux:subheading>Productos sin movimiento reciente (posible stock muerto)</flux:subheading>
                </div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($slowMoving as $item)
                    <div class="p-4 bg-zinc-50 rounded-lg border border-zinc-200">
                        <p class="font-semibold text-zinc-900">{{ $item['product'] }}</p>
                        <p class="text-sm text-zinc-600 mt-1">Stock: {{ number_format($item['quantity'], 2) }} {{ $item['unit'] }}</p>
                        <p class="text-sm text-zinc-600">Valor: {{ number_format($item['value'], 2) }}€</p>
                        <p class="text-sm text-orange-600 font-medium mt-2">
                            Sin movimiento: {{ $item['days_without_movement'] }} días
                        </p>
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Consumo Mensual
    const ctxConsumption = document.getElementById('consumptionChart');
    new Chart(ctxConsumption, {
        type: 'line',
        data: {
            labels: @js($monthlyConsumption['labels']),
            datasets: [{
                label: 'Cantidad Consumida',
                data: @js($monthlyConsumption['consumed']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de Top Productos
    const ctxTop = document.getElementById('topProductsChart');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: @js(array_column($topProducts, 'name')),
            datasets: [{
                label: 'Cantidad Consumida',
                data: @js(array_column($topProducts, 'total_consumed')),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
