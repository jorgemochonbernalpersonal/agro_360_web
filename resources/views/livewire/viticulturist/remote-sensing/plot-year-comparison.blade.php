<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            📊 Comparativa Año a Año
        </h3>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Año 1:</label>
                <select wire:model.live="year1" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                    @if(empty($availableYears))
                        <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                    @endif
                </select>
            </div>
            <span class="text-gray-400">vs</span>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Año 2:</label>
                <select wire:model.live="year2" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                    @if(empty($availableYears))
                        <option value="{{ now()->year }}">{{ now()->year }}</option>
                    @endif
                </select>
            </div>
        </div>
    </div>

    @if(!empty($comparisonData))
        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                <div class="text-sm text-gray-600 mb-1">NDVI Promedio {{ $year1 }}</div>
                <div class="text-2xl font-bold text-green-700">
                    {{ $comparisonData['year1']['summary']['avg_ndvi'] !== null ? number_format($comparisonData['year1']['summary']['avg_ndvi'], 3) : 'N/A' }}
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                <div class="text-sm text-gray-600 mb-1">NDVI Promedio {{ $year2 }}</div>
                <div class="text-2xl font-bold text-blue-700">
                    {{ $comparisonData['year2']['summary']['avg_ndvi'] !== null ? number_format($comparisonData['year2']['summary']['avg_ndvi'], 3) : 'N/A' }}
                </div>
            </div>
            <div class="rounded-lg p-4 border 
                @if(isset($comparisonData['comparison']['ndvi_trend']))
                    @if($comparisonData['comparison']['ndvi_trend'] === 'improving')
                        bg-gradient-to-br from-green-50 to-green-100 border-green-300
                    @elseif($comparisonData['comparison']['ndvi_trend'] === 'declining')
                        bg-gradient-to-br from-red-50 to-red-100 border-red-300
                    @else
                        bg-gradient-to-br from-gray-50 to-gray-100 border-gray-300
                    @endif
                @else
                    bg-gradient-to-br from-gray-50 to-gray-100 border-gray-300
                @endif
            ">
                <div class="text-sm text-gray-600 mb-1">Variación</div>
                <div class="text-2xl font-bold
                    @if(isset($comparisonData['comparison']['ndvi_change_percent']))
                        @if($comparisonData['comparison']['ndvi_change_percent'] > 0)
                            text-green-700
                        @elseif($comparisonData['comparison']['ndvi_change_percent'] < 0)
                            text-red-700
                        @else
                            text-gray-700
                        @endif
                    @else
                        text-gray-700
                    @endif
                ">
                    @if(isset($comparisonData['comparison']['ndvi_change_percent']) && $comparisonData['comparison']['ndvi_change_percent'] !== null)
                        {{ $comparisonData['comparison']['ndvi_change_percent'] > 0 ? '+' : '' }}{{ number_format($comparisonData['comparison']['ndvi_change_percent'], 1) }}%
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>

        {{-- Monthly Chart --}}
        <div class="h-64 mb-4">
            <canvas id="yearComparisonChart"></canvas>
        </div>

        {{-- Monthly Data Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Mes</th>
                        <th class="px-4 py-2 text-right text-green-700">NDVI {{ $year1 }}</th>
                        <th class="px-4 py-2 text-right text-blue-700">NDVI {{ $year2 }}</th>
                        <th class="px-4 py-2 text-right text-gray-600">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    @endphp
                    @for($m = 1; $m <= 12; $m++)
                        @php
                            $y1Data = collect($comparisonData['year1']['data'])->firstWhere('month', $m);
                            $y2Data = collect($comparisonData['year2']['data'])->firstWhere('month', $m);
                            $diff = ($y1Data && $y2Data) ? ($y2Data['ndvi_avg'] - $y1Data['ndvi_avg']) : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $months[$m-1] }}</td>
                            <td class="px-4 py-2 text-right text-green-700">
                                {{ $y1Data ? number_format($y1Data['ndvi_avg'], 3) : '-' }}
                            </td>
                            <td class="px-4 py-2 text-right text-blue-700">
                                {{ $y2Data ? number_format($y2Data['ndvi_avg'], 3) : '-' }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $diff !== null ? ($diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-500')) : 'text-gray-400' }}">
                                @if($diff !== null)
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p>No hay datos disponibles para la comparación</p>
        </div>
    @endif
</div>

@script
<script>
    document.addEventListener('livewire:initialized', () => {
        renderChart();
    });

    function renderChart() {
        const ctx = document.getElementById('yearComparisonChart');
        if (!ctx) return;

        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        const data1 = @json($comparisonData['year1']['data'] ?? []);
        const data2 = @json($comparisonData['year2']['data'] ?? []);
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        const dataset1 = months.map((_, i) => {
            const record = data1.find(d => d.month === i + 1);
            return record ? record.ndvi_avg : null;
        });

        const dataset2 = months.map((_, i) => {
            const record = data2.find(d => d.month === i + 1);
            return record ? record.ndvi_avg : null;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: '{{ $year1 }}',
                        data: dataset1,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        fill: true,
                        tension: 0.4,
                        spanGaps: true,
                    },
                    {
                        label: '{{ $year2 }}',
                        data: dataset2,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
                        spanGaps: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 1,
                        title: { display: true, text: 'NDVI' }
                    }
                }
            }
        });
    }

    Livewire.hook('morph.updated', () => {
        renderChart();
    });
</script>
@endscript
