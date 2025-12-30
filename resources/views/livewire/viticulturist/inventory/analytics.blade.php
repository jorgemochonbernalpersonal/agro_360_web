<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Analíticas de Inventario"
        description="Estadísticas y proyecciones de consumo de productos fitosanitarios"
        icon-color="from-purple-500 to-purple-600"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.inventory.index') }}">
                <button class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                    Volver al Inventario
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Productos -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Productos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_products'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Valor Total -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Valor Total</p>
                    <p class="text-3xl font-bold text-[var(--color-agro-green)] mt-1">{{ number_format($stats['total_value'], 2) }}€</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Stock Bajo</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1">{{ $stats['low_stock_count'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-orange-100">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Próximos a Caducar -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Próximos a Caducar</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['expiring_count'] }}</p>
                </div>
                <div class="p-3 rounded-full bg-red-100">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Consumo Mensual -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Consumo Mensual (Últimos 12 meses)</h3>
            <canvas id="consumptionChart" height="300"></canvas>
        </div>

        <!-- Top 5 Productos -->
        <div class="glass-card p-6 rounded-xl border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Productos Más Consumidos</h3>
            <canvas id="topProductsChart" height="300"></canvas>
        </div>
    </div>

    <!-- Proyecciones de Agotamiento -->
    @if(count($projections) > 0)
        <div class="glass-card rounded-xl border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Proyección de Agotamiento de Stock</h3>
                <p class="text-sm text-gray-600 mt-1">Estimación basada en consumo promedio de los últimos 30 días</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumo Diario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días Restantes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Estimada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($projections as $projection)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $projection['product'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ number_format($projection['current_stock'], 2) }} {{ $projection['unit'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ number_format($projection['avg_daily_consumption'], 3) }} {{ $projection['unit'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ $projection['days_until_empty'] }} días
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $projection['estimated_empty_date']->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($projection['status'] === 'critical')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            🔴 Crítico
                                        </span>
                                    @elseif($projection['status'] === 'warning')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            🟡 Advertencia
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            🟢 OK
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Productos con Baja Rotación -->
    @if(count($slowMoving) > 0)
        <div class="glass-card rounded-xl border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Productos con Baja Rotación</h3>
                <p class="text-sm text-gray-600 mt-1">Productos sin movimiento reciente (posible stock muerto)</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($slowMoving as $item)
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="font-semibold text-gray-900">{{ $item['product'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">Stock: {{ number_format($item['quantity'], 2) }} {{ $item['unit'] }}</p>
                            <p class="text-sm text-gray-600">Valor: {{ number_format($item['value'], 2) }}€</p>
                            <p class="text-sm text-orange-600 font-medium mt-2">
                                Sin movimiento: {{ $item['days_without_movement'] }} días
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Botón Exportar -->
    <div class="flex justify-end">
        <a href="{{ route('viticulturist.inventory.export') }}" class="px-6 py-3 rounded-lg bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:shadow-lg transition-all font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exportar a Excel
        </a>
    </div>
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
