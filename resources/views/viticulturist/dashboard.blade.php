@php
    use App\Models\Plot;
    use App\Models\AgriculturalActivity;
    use App\Models\SigpacCode;
    use App\Models\PlotPlanting;
    
    // KPIs
    $totalPlots = Plot::count();
    $totalArea = Plot::sum('area') ?? 0;
    $activitiesThisYear = AgriculturalActivity::whereYear('created_at', date('Y'))->count();
    $averageAreaPerPlot = $totalPlots > 0 ? $totalArea / $totalPlots : 0;
    
    // Distribución por variedad (usando plot_plantings)
    $plotsByVariety = PlotPlanting::selectRaw('grape_variety_id, COUNT(DISTINCT plot_id) as count, SUM(area_planted) as total_area')
        ->whereNotNull('grape_variety_id')
        ->where('status', 'active')
        ->groupBy('grape_variety_id')
        ->with('grapeVariety')
        ->get();
    
    // Actividades por tipo (últimos 30 días)
    $activitiesByType = AgriculturalActivity::selectRaw('activity_type, COUNT(*) as count')
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('activity_type')
        ->get();
    
    // Últimas 5 actividades
    $recentActivities = AgriculturalActivity::with('plot')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    // Top 3 parcelas más activas
    $topPlots = Plot::withCount(['activities' => function($query) {
            $query->where('created_at', '>=', now()->subMonths(3));
        }])
        ->orderBy('activities_count', 'desc')
        ->take(3)
        ->get();
        
    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        <!-- Header -->
        <x-page-header
            :icon="$dashboardIcon"
            title="Dashboard"
            description="Resumen general de tu actividad agrícola"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Parcelas -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Parcelas</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ $totalPlots }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)]/20 to-[var(--color-agro-green)]/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Área Total -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Área Total</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ number_format($totalArea, 1) }}</p>
                        <p class="text-xs text-gray-400 mt-1">hectáreas</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Actividades Año -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Actividades {{ date('Y') }}</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ $activitiesThisYear }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Promedio Área -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Promedio/Parcela</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ number_format($averageAreaPerPlot, 1) }}</p>
                        <p class="text-xs text-gray-400 mt-1">ha/parcela</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Distribución por Variedad -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Distribución por Variedad</h3>
                    <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                
                @if($plotsByVariety->count() > 0)
                    <div class="space-y-3">
                        @foreach($plotsByVariety as $index => $variety)
                            @php
                                $percentage = ($variety->count / $totalPlots) * 100;
                                $colors = ['bg-[var(--color-agro-green-dark)]', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $variety->grapeVariety->name ?? 'Sin variedad' }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $variety->count }} ({{ number_format($percentage,  1) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $color }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos de variedades disponibles</p>
                @endif
            </div>

            <!-- Actividades por Tipo (últimos 30 días) -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Actividades por Tipo (30 días)</h3>
                    <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                
                @if($activitiesByType->count() > 0)
                    <div class="space-y-3">
                        @foreach($activitiesByType as $index => $activity)
                            @php
                                $typeNames = [
                                    'phytosanitary' => 'Tratamientos',
                                    'fertilization' => 'Fertilización',
                                    'irrigation' => 'Riego',
                                    'cultural' => 'Culturales',
                                    'observation' => 'Observaciones',
                                ];
                                $maxCount = $activitiesByType->max('count');
                                $percentage = ($activity->count / $maxCount) * 100;
                                $colors = ['bg-[var(--color-agro-green-dark)]', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $typeNames[$activity->activity_type] ?? $activity->activity_type }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $activity->count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="{{ $color }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay actividades registradas en los últimos 30 días</p>
                @endif
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Últimas Actividades -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Últimas Actividades</h3>
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors">
                        Ver todas →
                    </a>
                </div>
                
                @if($recentActivities->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)]/20 to-[var(--color-agro-green)]/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ ucfirst($activity->activity_type) }} - {{ $activity->plot->name ?? 'Sin parcela' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay actividades registradas aún</p>
                @endif
            </div>

            <!-- Top Parcelas Más Activas -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Parcelas Más Activas</h3>
                    <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                
                @if($topPlots->count() > 0)
                    <div class="space-y-4">
                        @foreach($topPlots as $index => $plot)
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)]/20 to-[var(--color-agro-green)]/20 flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-[var(--color-agro-green-dark)]">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $plot->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $plot->activities_count }} actividades (últimos 3 meses)</p>
                                </div>
                                <a href="{{ route('plots.show', $plot) }}" wire:navigate class="text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay datos de actividad en parcelas</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
