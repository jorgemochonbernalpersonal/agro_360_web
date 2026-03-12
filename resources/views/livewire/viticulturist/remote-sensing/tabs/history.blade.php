{{-- Tab Historial: selector de período, gráfico NDVI, estadísticas, comparación y predicción --}}
<div wire:key="history-tab-content-{{ $selectedPlotId ?? 'none' }}-{{ $historyPeriod }}">

    {{-- Selector de período --}}
    <div class="mb-6 bg-white rounded-lg border border-zinc-200 p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-zinc-700 mb-2">📅 Período de análisis</label>
                <flux:select wire:model.live="historyPeriod">
                    <option value="7_days">Últimos 7 días</option>
                    <option value="30_days">Últimos 30 días</option>
                    <option value="90_days">Últimos 90 días (por defecto)</option>
                    <option value="current_season">Temporada actual (Abril - Hoy)</option>
                    <option value="last_season">Temporada anterior (Abril - Octubre)</option>
                    <option value="1_year">Último año</option>
                    <option value="custom">🎯 Personalizado</option>
                </flux:select>
            </div>

            @if($historyPeriod === 'custom')
                <div class="flex-1">
                    <label class="block text-sm font-medium text-zinc-700 mb-2">Rango personalizado</label>
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <input type="date"
                                   wire:model="customStartDate"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                   placeholder="Desde">
                        </div>
                        <span class="text-zinc-500">→</span>
                        <div class="flex-1">
                            <input type="date"
                                   wire:model="customEndDate"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                   placeholder="Hasta">
                        </div>
                        <button wire:click="applyCustomDateRange"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                            Aplicar
                        </button>
                    </div>
                    <p class="text-xs text-zinc-500 mt-1">Máximo: 2 años de rango</p>
                </div>
            @endif
        </div>

        <div class="mt-3 flex items-center gap-4 text-sm text-zinc-600">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Mostrando: <strong>{{ $historyDays }} días</strong>
            </span>
            @if(count($historicalData) > 0)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <strong>{{ count($historicalData) }}</strong> registros
                </span>
            @endif
        </div>
    </div>

    {{-- Gráfico NDVI --}}
    <h3 class="text-md font-semibold mb-3">📈 Evolución NDVI</h3>

    @if(count($historicalData) > 0)
        <div class="bg-white rounded-lg border border-zinc-200 p-4 mb-4">
            <div class="h-64 flex items-end justify-between gap-1 bg-zinc-50 rounded-lg p-4">
                @foreach($historicalData as $data)
                    @php
                        $height = ($data['ndvi'] + 1) / 2 * 100;
                        $color  = match($data['health_status'] ?? 'moderate') {
                            'excellent' => 'bg-green-600',
                            'good'      => 'bg-green-500',
                            'moderate'  => 'bg-yellow-500',
                            'poor'      => 'bg-orange-500',
                            'critical'  => 'bg-red-500',
                            default     => 'bg-zinc-400',
                        };
                    @endphp
                    <div class="flex-1 flex flex-col items-center group relative {{ $data['high_clouds'] ? 'opacity-40' : '' }}">
                        <div class="absolute bottom-full mb-2 hidden group-hover:block bg-zinc-900 text-white text-xs rounded py-2 px-3 z-10 whitespace-nowrap">
                            <div class="font-semibold">{{ $data['fullDate'] }}</div>
                            <div>NDVI: {{ number_format($data['ndvi'], 3) }}</div>
                            <div class="text-zinc-300 capitalize">{{ $data['health_status'] ?? 'N/A' }}</div>
                            @if($data['high_clouds'])
                                <div class="text-orange-400 mt-1">☁️ {{ $data['cloud_coverage'] }}% nubes — dato poco fiable</div>
                            @else
                                <div class="text-zinc-400">☁️ {{ $data['cloud_coverage'] }}% nubes</div>
                            @endif
                        </div>
                        <div class="w-full {{ $color }} rounded-t transition-all hover:opacity-80 cursor-pointer"
                             style="height: {{ max(10, $height) }}%;"></div>
                        @if($data['high_clouds'])
                            <div class="text-orange-400 text-xs mt-0.5 leading-none">☁</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between text-xs text-zinc-500 mt-2 px-2">
                @if(count($historicalData) > 0)
                    <span>← {{ $historicalData[count($historicalData) - 1]['fullDate'] ?? '' }}</span>
                    <span>{{ $historicalData[0]['fullDate'] ?? '' }} →</span>
                @endif
            </div>

            @php $highCloudDays = count(array_filter($historicalData, fn($d) => $d['high_clouds'])); @endphp
            @if($highCloudDays > 0)
                <p class="mt-2 text-xs text-orange-600 flex items-center gap-1">
                    <flux:icon icon="cloud" variant="micro" />
                    {{ $highCloudDays }} {{ $highCloudDays === 1 ? 'fecha' : 'fechas' }} con &gt;60% nubes aparecen atenuadas — datos poco fiables
                </p>
            @endif
        </div>

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            @php
                $ndviValues = array_column($historicalData, 'ndvi');
                $avgNdvi    = count($ndviValues) > 0 ? array_sum($ndviValues) / count($ndviValues) : 0;
                $maxNdvi    = count($ndviValues) > 0 ? max($ndviValues) : 0;
                $minNdvi    = count($ndviValues) > 0 ? min($ndviValues) : 0;
                $stdDev     = count($ndviValues) > 1
                    ? sqrt(array_sum(array_map(fn($x) => pow($x - $avgNdvi, 2), $ndviValues)) / count($ndviValues))
                    : 0;
            @endphp

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                <div class="text-xs text-blue-600 mb-1">Promedio</div>
                <div class="text-2xl font-bold text-blue-900">{{ number_format($avgNdvi, 3) }}</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                <div class="text-xs text-green-600 mb-1">Máximo</div>
                <div class="text-2xl font-bold text-green-900">{{ number_format($maxNdvi, 3) }}</div>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center">
                <div class="text-xs text-orange-600 mb-1">Mínimo</div>
                <div class="text-2xl font-bold text-orange-900">{{ number_format($minNdvi, 3) }}</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                <div class="text-xs text-purple-600 mb-1">Variabilidad</div>
                <div class="text-2xl font-bold text-purple-900">{{ number_format($stdDev, 3) }}</div>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <button wire:click="exportCSV"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar CSV
            </button>
            <button wire:click="exportPDF"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Exportar PDF
            </button>
            <button wire:click="toggleComparison"
                    class="inline-flex items-center px-4 py-2 {{ $showComparison ? 'bg-purple-600 hover:bg-purple-700' : 'bg-zinc-600 hover:bg-zinc-700' }} text-white rounded-lg transition-colors text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ $showComparison ? 'Ocultar' : 'Comparar' }} Períodos
            </button>
        </div>

        {{-- Comparación de períodos --}}
        @if($showComparison)
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-purple-900">📊 Comparación de Períodos</h4>
                    <flux:select wire:model.live="comparisonPeriod">
                        <option value="last_year">Mismo período año anterior</option>
                        <option value="last_season">Temporada anterior</option>
                        <option value="same_month_last_year">Mismo mes año anterior</option>
                    </flux:select>
                </div>

                @if(count($comparisonData) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <h5 class="text-sm font-semibold text-zinc-700 mb-3">Período Actual</h5>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">NDVI Promedio:</span>
                                    <span class="font-bold text-green-600">{{ number_format($avgNdvi, 3) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">Máximo:</span>
                                    <span class="font-semibold">{{ number_format($maxNdvi, 3) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">Registros:</span>
                                    <span class="font-semibold">{{ count($historicalData) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4">
                            <h5 class="text-sm font-semibold text-zinc-700 mb-3">Período Comparación</h5>
                            @php
                                $compNdviValues = array_column($comparisonData, 'ndvi');
                                $compAvgNdvi    = count($compNdviValues) > 0 ? array_sum($compNdviValues) / count($compNdviValues) : 0;
                                $compMaxNdvi    = count($compNdviValues) > 0 ? max($compNdviValues) : 0;
                            @endphp
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">NDVI Promedio:</span>
                                    <span class="font-bold text-blue-600">{{ number_format($compAvgNdvi, 3) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">Máximo:</span>
                                    <span class="font-semibold">{{ number_format($compMaxNdvi, 3) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-600">Registros:</span>
                                    <span class="font-semibold">{{ count($comparisonData) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $avgDiff       = $avgNdvi - $compAvgNdvi;
                        $percentChange = $compAvgNdvi > 0 ? ($avgDiff / $compAvgNdvi) * 100 : 0;
                    @endphp
                    <div class="mt-4 bg-white rounded-lg p-4 text-center">
                        <div class="text-sm text-zinc-600 mb-1">Diferencia Promedio</div>
                        <div class="text-3xl font-bold {{ $avgDiff > 0 ? 'text-green-600' : ($avgDiff < 0 ? 'text-red-600' : 'text-zinc-600') }}">
                            {{ $avgDiff > 0 ? '+' : '' }}{{ number_format($avgDiff, 3) }}
                            <span class="text-lg">({{ $percentChange > 0 ? '+' : '' }}{{ number_format($percentChange, 1) }}%)</span>
                        </div>
                        <div class="text-sm mt-2">
                            @if($avgDiff > 0.05)     <span class="text-green-600">✅ Mejora significativa respecto al período anterior</span>
                            @elseif($avgDiff < -0.05)<span class="text-red-600">⚠️ Reducción respecto al período anterior</span>
                            @else                     <span class="text-zinc-600">➡️ Valores similares al período anterior</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-center text-purple-700 py-4">No hay datos disponibles para el período de comparación</p>
                @endif
            </div>
        @endif

        {{-- Alertas del período --}}
        @if(count($periodAlerts) > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-semibold text-yellow-900 mb-3">⚠️ Alertas Detectadas ({{ count($periodAlerts) }})</h4>

                <div class="mb-3">
                    <label class="text-sm text-zinc-700 font-medium">Umbral NDVI:</label>
                    <input type="range"
                           wire:model.live="ndviThreshold"
                           min="0" max="1" step="0.05"
                           class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex justify-between text-xs text-zinc-600 mt-1">
                        <span>0.0</span>
                        <span class="font-bold text-yellow-700">{{ number_format($ndviThreshold, 2) }}</span>
                        <span>1.0</span>
                    </div>
                </div>

                <div class="space-y-2 max-h-60 overflow-y-auto">
                    @foreach($periodAlerts as $alert)
                        <div class="flex items-start gap-3 p-3 bg-white rounded-lg {{ $alert['severity'] === 'critical' ? 'border-l-4 border-red-500' : 'border-l-4 border-yellow-500' }}">
                            <span class="text-2xl">{{ $alert['severity'] === 'critical' ? '🚨' : '⚠️' }}</span>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-zinc-900">{{ $alert['message'] }}</div>
                                <div class="text-xs text-zinc-600 mt-1">Tipo: {{ $alert['type'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Predicción de tendencia --}}
        @if(!empty($trendPrediction))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-blue-900 mb-3">🔮 Predicción de Tendencia (próximos 7 días)</h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-white rounded-lg p-3 text-center">
                        <div class="text-xs text-zinc-600 mb-1">Tendencia</div>
                        <div class="text-2xl font-bold {{ $trendPrediction['trend'] === 'improving' ? 'text-green-600' : ($trendPrediction['trend'] === 'declining' ? 'text-red-600' : 'text-zinc-600') }}">
                            @if($trendPrediction['trend'] === 'improving')    ↗️ Mejorando
                            @elseif($trendPrediction['trend'] === 'declining') ↘️ Declinando
                            @else                                              → Estable
                            @endif
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center">
                        <div class="text-xs text-zinc-600 mb-1">Pendiente</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($trendPrediction['slope'], 4) }}</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center">
                        <div class="text-xs text-zinc-600 mb-1">Confianza (R²)</div>
                        <div class="text-2xl font-bold text-purple-900">{{ number_format($trendPrediction['confidence'] * 100, 1) }}%</div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4">
                    <h5 class="text-sm font-semibold text-zinc-700 mb-3">Valores Predichos:</h5>
                    <div class="grid grid-cols-7 gap-2">
                        @foreach($trendPrediction['predictions'] as $pred)
                            <div class="text-center p-2 bg-blue-50 rounded">
                                <div class="text-xs text-zinc-600">+{{ $pred['day'] }}d</div>
                                <div class="text-sm font-bold text-blue-900">{{ number_format($pred['ndvi'], 3) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-3 text-xs text-blue-700 bg-white rounded p-2">
                    <strong>Nota:</strong> Las predicciones se basan en regresión lineal de los datos históricos.
                    Confianza alta (&gt;80%) indica patrón consistente. Úsalo solo como guía orientativa.
                </div>
            </div>
        @endif

    @else
        <div class="text-center py-12 bg-zinc-50 rounded-lg border border-zinc-200">
            <svg class="w-16 h-16 mx-auto mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-zinc-600 font-medium mb-1">No hay datos históricos</p>
            <p class="text-sm text-zinc-500">Para el período seleccionado</p>
        </div>
    @endif

</div>{{-- cierre wire:key history-tab --}}
