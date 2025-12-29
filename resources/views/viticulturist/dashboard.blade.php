@php
    use App\Models\Plot;
    use App\Models\AgriculturalActivity;
    use App\Models\PlotPlanting;
    use App\Models\Harvest;
    use App\Services\DashboardAlertsService;
    
    $user = auth()->user();
    
    // =======================
    // KPIs ESENCIALES
    // =======================
    
    // Parcelas y Área
    $totalPlots = Plot::forUser($user)->count();
    $totalArea = Plot::forUser($user)->sum('area') ?? 0;
    
    // Actividades este mes
    $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->count();
    
    // Total cosechado este año
    $totalHarvested = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereYear('activity_date', date('Y'));
    })->sum('total_weight') ?? 0;
    
    // =======================
    // DATOS PARA GRÁFICOS
    // =======================
    
    // Distribución por variedad
    $userPlotIds = Plot::forUser($user)->pluck('id');
    $plotsByVariety = PlotPlanting::selectRaw('grape_variety_id, COUNT(DISTINCT plot_id) as count')
        ->whereIn('plot_id', $userPlotIds)
        ->whereNotNull('grape_variety_id')
        ->where('status', 'active')
        ->groupBy('grape_variety_id')
        ->with('grapeVariety')
        ->get();
    
    // Actividades recientes (últimas 5)
    $recentActivities = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->with(['plot'])
        ->orderBy('activity_date', 'desc')
        ->take(5)
        ->get();
    
    // Cosechas recientes (últimas 4)
    $recentHarvests = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id);
    })
    ->with(['activity.plot', 'plotPlanting.grapeVariety'])
    ->orderBy('harvest_start_date', 'desc')
    ->take(4)
    ->get();
    
    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        {{-- Header --}}
        <x-page-header
            :icon="$dashboardIcon"
            title="Dashboard"
            description="Resumen de tu viñedo"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        {{-- KPI Cards - 4 esenciales --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" data-cy="dashboard-kpi-cards">
            {{-- Parcelas + Área --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5" data-cy="kpi-plots">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Parcelas</p>
                        <p class="text-3xl font-bold text-[var(--color-agro-green-dark)]">{{ $totalPlots }}</p>
                        <p class="text-xs text-gray-400">{{ number_format($totalArea, 1) }} ha</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Actividades --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5" data-cy="kpi-activities">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Actividades</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $activitiesThisMonth }}</p>
                        <p class="text-xs text-gray-400">este mes</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Cosechado --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5" data-cy="kpi-harvest">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Cosechado</p>
                        <p class="text-3xl font-bold text-amber-600">{{ number_format($totalHarvested / 1000, 1) }}</p>
                        <p class="text-xs text-gray-400">toneladas {{ date('Y') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Acceso rápido Teledetección --}}
            <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg border-2 border-green-200 p-5 hover:shadow-xl hover:border-green-300 transition-all" data-cy="kpi-remote-sensing">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700">🛰️ Teledetección</p>
                        <p class="text-lg font-bold text-green-800">Ver mapa NDVI</p>
                        <p class="text-xs text-green-600">Análisis satelital</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-200 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Distribución por Variedad --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="chart-variety">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">🍇 Distribución por Variedad</h3>
                </div>
                
                @if($plotsByVariety->count() > 0)
                    <div class="space-y-3">
                        @foreach($plotsByVariety as $index => $variety)
                            @php
                                $percentage = ($variety->count / $totalPlots) * 100;
                                $colors = ['bg-green-500', 'bg-purple-500', 'bg-blue-500', 'bg-amber-500', 'bg-rose-500'];
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $variety->grapeVariety->name ?? 'Sin variedad' }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $variety->count }} parcelas</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6">No hay datos de variedades</p>
                @endif
            </div>

            {{-- Actividades Recientes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-activities">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">📋 Actividades Recientes</h3>
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:underline">
                        Ver todas →
                    </a>
                </div>
                
                @if($recentActivities->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-sm">
                                    @switch($activity->activity_type)
                                        @case('treatment') 💊 @break
                                        @case('harvest') 🍇 @break
                                        @case('pruning') ✂️ @break
                                        @case('fertilization') 🌿 @break
                                        @default 📝
                                    @endswitch
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ ucfirst($activity->activity_type) }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->plot->name ?? 'Sin parcela' }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $activity->activity_date->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6">No hay actividades registradas</p>
                @endif
            </div>
        </div>

        {{-- Quick Links Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-purple-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-xl">📓</div>
                <div>
                    <p class="font-semibold text-gray-900">Cuaderno Digital</p>
                    <p class="text-xs text-gray-500">Registrar actividades</p>
                </div>
            </a>
            
            <a href="{{ route('viticulturist.invoices.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-indigo-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-xl">📊</div>
                <div>
                    <p class="font-semibold text-gray-900">Facturación</p>
                    <p class="text-xs text-gray-500">Ver estadísticas</p>
                </div>
            </a>
            
            <a href="{{ route('plots.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-green-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-xl">🗺️</div>
                <div>
                    <p class="font-semibold text-gray-900">Parcelas</p>
                    <p class="text-xs text-gray-500">Gestionar terrenos</p>
                </div>
            </a>
            
            <a href="{{ route('viticulturist.official-reports.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-amber-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-xl">📄</div>
                <div>
                    <p class="font-semibold text-gray-900">Informes PAC</p>
                    <p class="text-xs text-gray-500">Documentación oficial</p>
                </div>
            </a>
        </div>

        {{-- PAC Compliance Section (Collapsible) --}}
        <div x-data="{ pacOpen: false }" class="bg-white rounded-xl shadow-lg border border-gray-200">
            <button @click="pacOpen = !pacOpen" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900">📊 Cumplimiento PAC</span>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': pacOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div x-show="pacOpen" x-collapse class="px-6 pb-6 space-y-6">
                {{-- Parcelas PAC --}}
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3">Parcelas</h4>
                    @livewire('viticulturist.plots-dashboard')
                </div>
                
                {{-- Plantaciones PAC --}}
                <div>
                    <h4 class="font-semibold text-gray-700 mb-3">Plantaciones</h4>
                    @livewire('viticulturist.plantings-dashboard')
                </div>
            </div>
        </div>

        {{-- Cosechas Recientes --}}
        @if($recentHarvests->count() > 0)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-harvests">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">🍇 Cosechas Recientes</h3>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($recentHarvests as $harvest)
                        <div class="p-3 rounded-lg border border-gray-200 hover:border-purple-300 transition-colors">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $harvest->activity->plot->name ?? 'Sin parcela' }}</p>
                            <p class="text-sm font-bold text-purple-600 mt-1">{{ number_format($harvest->total_weight, 0) }} kg</p>
                            <p class="text-xs text-gray-400">{{ $harvest->harvest_start_date->format('d/m/Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
