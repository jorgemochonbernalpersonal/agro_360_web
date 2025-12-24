@php
    use App\Models\Plot;
    use App\Models\AgriculturalActivity;
    use App\Models\PlotPlanting;
    use App\Models\Harvest;
    use App\Models\Container;
    use App\Models\Campaign;
    
    $user = auth()->user();
    
    // =======================
    // KPIs AGRÍCOLAS
    // =======================
    
    // Parcelas
    $totalPlots = Plot::forUser($user)->count();
    $totalArea = Plot::forUser($user)->sum('area') ?? 0;
    $averageAreaPerPlot = $totalPlots > 0 ? $totalArea / $totalPlots : 0;
    
    // Actividades
    $activitiesThisYear = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->whereYear('created_at', date('Y'))
        ->count();
    
    $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->count();
    
    // Cosechas
    $totalHarvested = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereYear('activity_date', date('Y'));
    })
    ->sum('total_weight') ?? 0;
    
    $harvestsThisMonth = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereMonth('activity_date', date('m'))
          ->whereYear('activity_date', date('Y'));
    })
    ->count();
    
    // Contenedores disponibles
    $availableContainers = Container::where('user_id', $user->id)
        ->whereDoesntHave('harvests')
        ->where('archived', false)
        ->count();
    
    // Tratamientos activos (actividades de tipo treatment en últimos 30 días)
    $activeTreatments = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->where('activity_type', 'treatment')
        ->where('created_at', '>=', now()->subDays(30))
        ->count();
    
    // =======================
    // GRÁFICOS Y LISTAS
    // =======================
    
    // Distribución por variedad
    $userPlotIds = Plot::forUser($user)->pluck('id');
    $plotsByVariety = PlotPlanting::selectRaw('grape_variety_id, COUNT(DISTINCT plot_id) as count, SUM(area_planted) as total_area')
        ->whereIn('plot_id', $userPlotIds)
        ->whereNotNull('grape_variety_id')
        ->where('status', 'active')
        ->groupBy('grape_variety_id')
        ->with('grapeVariety')
        ->get();
    
    // Actividades recientes (últimas 10)
    $recentActivities = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->with(['plot', 'campaign'])
        ->orderBy('activity_date', 'desc')
        ->take(10)
        ->get();
    
    // Próximas actividades programadas (si existe campo de fecha futura)
    // Por ahora, mostrar las más recientes como placeholder
    
    // Cosechas recientes
    $recentHarvests = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id);
    })
    ->with(['activity.plot', 'plotPlanting.grapeVariety', 'container'])
    ->orderBy('harvest_start_date', 'desc')
    ->take(8)
    ->get();
    
    // =======================
    // ALERTAS AGRÍCOLAS
    // =======================
    
    $alerts = [];
    
    // Parcelas con plazo de seguridad activo (sacar de PlotApplicationWithdrawals si existe)
    // Por ahora, ejemplo de alertas:
    
    // Contenedores bajos
    if ($availableContainers < 5) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "Solo quedan {$availableContainers} contenedores disponibles",
            'route' => route('viticulturist.digital-notebook.containers.index')
        ];
    }
    
    // Actividades este mes
    if ($activitiesThisMonth === 0) {
        $alerts[] = [
            'type' => 'info',
            'message' => "No has registrado actividades este mes",
            'route' => route('viticulturist.digital-notebook')
        ];
    }
    
    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        {{-- Header --}}
        <x-page-header
            :icon="$dashboardIcon"
            title="Dashboard"
            description="Gestión operativa de tu viñedo"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        {{-- Alertas --}}
        @if(count($alerts) > 0)
            <div class="space-y-2" data-cy="dashboard-alerts">
                @foreach($alerts as $alert)
                    <div class="bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-50 border-l-4 border-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-400 p-4 rounded-lg" data-cy="dashboard-alert-{{ $alert['type'] }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm font-medium text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-800">{{ $alert['message'] }}</p>
                            </div>
                            @if(isset($alert['route']))
                                <a href="{{ $alert['route'] }}" wire:navigate class="text-sm font-semibold text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-600 hover:text-{{ $alert['type'] === 'warning' ? 'yellow' : 'blue' }}-800">
                                    Ver →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- KPI Cards - Primera Fila (Básicos) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-cy="dashboard-kpi-cards">
            {{-- Total Parcelas --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-total-plots">
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

            {{-- Área Total --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-total-area">
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

            {{-- Actividades Este Mes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-activities-month">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Actividades</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ $activitiesThisMonth }}</p>
                        <p class="text-xs text-gray-400 mt-1">este mes</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Cosechado --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-total-harvested">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Cosechado {{ date('Y') }}</p>
                        <p class="text-4xl font-bold text-[var(--color-agro-green-dark)]">{{ number_format($totalHarvested / 1000, 1) }}</p>
                        <p class="text-xs text-gray-400 mt-1">toneladas</p>
                    </div>
                    <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards - Segunda Fila (Operativos) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-cy="dashboard-operational-kpis">
            {{-- Tratamientos Activos --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-active-treatments">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Tratamientos</p>
                        <p class="text-3xl font-bold text-green-600">{{ $activeTreatments }}</p>
                        <p class="text-xs text-gray-400 mt-1">últimos 30 días</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Contenedores Disponibles --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow duration-300" data-cy="kpi-available-containers">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Contenedores</p>
                        <p class="text-3xl font-bold text-teal-600">{{ $availableContainers }}</p>
                        <p class="text-xs text-gray-400 mt-1">disponibles</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-teal-100 to-teal-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Enlace a Estadísticas Financieras --}}
            <a href="{{ route('viticulturist.invoices.index') }}" wire:navigate class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl shadow-lg border-2 border-indigo-200 p-6 hover:shadow-xl hover:border-indigo-300 transition-all duration-300" data-cy="dashboard-financial-stats-link">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 mb-1">📊 Facturación</p>
                        <p class="text-lg font-bold text-indigo-900">Ver estadísticas</p>
                        <p class="text-xs text-indigo-500 mt-1">Análisis comercial completo</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-cy="dashboard-charts">
            {{-- Distribución por Variedad --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="chart-variety-distribution">
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
                                    <span class="text-sm font-bold text-gray-900">{{ $variety->count }} ({{ number_format($percentage, 1) }}%)</span>
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

            {{-- Actividades Recientes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-activities-section">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Actividades Recientes</h3>
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors" data-cy="view-all-activities-link">
                        Ver todas →
                    </a>
                </div>
                
                @if($recentActivities->count() > 0)
                    <div class="space-y-3" data-cy="recent-activities-list">
                        @foreach($recentActivities->take(5) as $activity)
                            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors" data-cy="recent-activity-item">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst($activity->activity_type) }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->plot->name ?? 'Sin parcela' }} - {{ $activity->activity_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No hay actividades registradas</p>
                @endif
            </div>
        </div>

        {{-- Cosechas Recientes --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-harvests-section">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Cosechas Recientes</h3>
                <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:text-[var(--color-agro-green)] transition-colors" data-cy="view-all-harvests-link">
                    Ver registro →
                </a>
            </div>
            
            @if($recentHarvests->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" data-cy="recent-harvests-list">
                    @foreach($recentHarvests as $harvest)
                        <div class="p-4 rounded-lg border border-gray-200 hover:border-[var(--color-agro-green)] hover:shadow-md transition-all" data-cy="recent-harvest-item">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate">{{ $harvest->activity->plot->name ?? 'Sin parcela' }}</p>
                                    <p class="text-xs font-bold text-purple-600 mt-1">{{ number_format($harvest->total_weight, 0) }} kg</p>
                                    <p class="text-xs text-gray-400">{{ $harvest->harvest_start_date->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay cosechas registradas</p>
            @endif
        </div>
    </div>
</x-app-layout>
