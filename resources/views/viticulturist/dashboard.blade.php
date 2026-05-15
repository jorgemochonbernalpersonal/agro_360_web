@php
    use App\Models\Plot;
    use App\Models\AgriculturalActivity;
    use App\Models\PlotPlanting;
    use App\Models\PlotRemoteSensing;
    use App\Models\Harvest;
    use App\Services\DashboardAlertsService;

    $user = auth()->user();

    // =======================
    // KPIs ESENCIALES
    // =======================

    // Parcelas y Área
    $plots = Plot::forUser($user)->get();
    $totalPlots = $plots->count();
    $totalArea = $plots->sum('area') ?? 0;

    // Actividades este mes
    $activitiesThisMonth = AgriculturalActivity::where('viticulturist_id', $user->id)
        ->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->count();

    // Total actividades (para saber si es usuario nuevo)
    $totalActivities = AgriculturalActivity::where('viticulturist_id', $user->id)->count();
    $isNewUser = $totalActivities === 0;

    // Total cosechado este año
    $totalHarvested = Harvest::whereHas('activity', function($q) use ($user) {
        $q->where('viticulturist_id', $user->id)
          ->whereYear('activity_date', date('Y'));
    })->sum('total_weight') ?? 0;

    // =======================
    // DATOS PARA GRÁFICOS
    // =======================

    // Distribución por variedad
    $userPlotIds = $plots->pluck('id');
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

    // =======================
    // TELEDETECCIÓN (condicional)
    // =======================
    $latestNdvi = null;
    $ndviPlotName = null;
    $hasSigpac = false;
    if ($totalPlots > 0) {
        $hasSigpac = $plots->first()?->sigpacCodes()->exists() ?? false;
        $latestSensing = PlotRemoteSensing::whereIn('plot_id', $userPlotIds)
            ->whereNotNull('ndvi_mean')
            ->orderByDesc('image_date')
            ->with('plot')
            ->first();
        if ($latestSensing) {
            $latestNdvi = $latestSensing->ndvi_mean;
            $ndviPlotName = $latestSensing->plot->name ?? '';
        }
    }

    // =======================
    // ALERTAS
    // =======================
    $alertsService = app(DashboardAlertsService::class);
    $alerts = $alertsService->getAlerts($user);

    // =======================
    // ÚLTIMA ACTIVIDAD
    // =======================
    $lastActivity = $recentActivities->first();
    $daysSinceLastActivity = $lastActivity
        ? (int) $lastActivity->activity_date->diffInDays(now())
        : null;

    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        {{-- Onboarding Components --}}
        @livewire('viticulturist.onboarding-welcome')
        @livewire('viticulturist.onboarding-checklist')

        {{-- Avisos de bodegas --}}
        @livewire('viticulturist.announcements-banner')

        {{-- Alertas del sistema --}}
        @if($alerts->isNotEmpty())
            <div class="space-y-2" data-cy="dashboard-alerts">
                @foreach($alerts->take(3) as $alert)
                    <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg border
                        {{ $alert['type'] === 'danger' ? 'bg-red-50 border-red-200 text-red-800' : '' }}
                        {{ $alert['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : '' }}
                        {{ $alert['type'] === 'info' ? 'bg-blue-50 border-blue-200 text-blue-800' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-lg flex-shrink-0">{{ $alert['icon'] }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">{{ $alert['title'] }}</p>
                                <p class="text-xs opacity-80">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                        @if(!empty($alert['action_url']))
                            <a href="{{ $alert['action_url'] }}" wire:navigate
                               class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/80 hover:bg-white transition-colors">
                                {{ $alert['action_text'] ?? 'Ver' }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Header --}}
        <x-page-header
            :icon="$dashboardIcon"
            title="Dashboard"
            description="Resumen de tu viñedo"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        {{-- Contexto: última actividad o sugerencia --}}
        @if($isNewUser)
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-5" data-cy="welcome-context">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-500 flex items-center justify-center text-white text-2xl flex-shrink-0">
                        🌱
                    </div>
                    <div>
                        <h3 class="font-bold text-green-900">Tu viñedo digital te espera</h3>
                        <p class="text-sm text-green-700 mt-1">
                            Registra tu primera actividad en el cuaderno y empieza a llevar el control de tu explotación.
                            El cuaderno de campo es obligatorio para el cumplimiento PAC.
                        </p>
                        <div class="flex gap-3 mt-3">
                            <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate
                               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Registrar actividad
                            </a>
                            <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate
                               class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-green-50 text-green-700 text-sm font-semibold rounded-lg border border-green-300 transition-colors">
                                Ver cuaderno
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($daysSinceLastActivity !== null && $daysSinceLastActivity > 7)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4" data-cy="inactivity-reminder">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📝</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-amber-900">
                            Llevas <strong>{{ $daysSinceLastActivity }} {{ $daysSinceLastActivity === 1 ? 'día' : 'días' }}</strong> sin registrar actividades.
                        </p>
                        <p class="text-xs text-amber-700">Mantener el cuaderno al día es clave para el cumplimiento PAC.</p>
                    </div>
                    <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate
                       class="flex-shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        Registrar ahora
                    </a>
                </div>
            </div>
        @endif

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

            {{-- Teledetección - condicional --}}
            @if($latestNdvi !== null)
                {{-- Tiene datos NDVI: mostrar mini-preview --}}
                <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg border-2 border-green-200 p-5 hover:shadow-xl hover:border-green-300 transition-all" data-cy="kpi-remote-sensing">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-700">NDVI</p>
                            <p class="text-3xl font-bold {{ $latestNdvi >= 0.5 ? 'text-green-700' : ($latestNdvi >= 0.35 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($latestNdvi, 2) }}</p>
                            <p class="text-xs text-green-600 truncate">{{ $ndviPlotName }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-green-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @elseif($hasSigpac)
                {{-- Tiene SIGPAC pero no datos NDVI aún --}}
                <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg border-2 border-green-200 p-5 hover:shadow-xl hover:border-green-300 transition-all" data-cy="kpi-remote-sensing">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-700">Teledetección</p>
                            <p class="text-base font-bold text-green-800">Ver mapa NDVI</p>
                            <p class="text-xs text-green-600">Análisis satelital</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-green-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @else
                {{-- No tiene SIGPAC: CTA para completar datos --}}
                <a href="{{ route('plots.index') }}" wire:navigate class="bg-white rounded-xl shadow-lg border-2 border-dashed border-green-300 p-5 hover:shadow-xl hover:border-green-400 transition-all" data-cy="kpi-remote-sensing-setup">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-700">Teledetección</p>
                            <p class="text-xs font-semibold text-gray-600 mt-1">Vincula tus parcelas con SIGPAC para ver análisis satelital gratis</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @endif
        </div>

        {{-- PAC Compliance Section - ARRIBA y abierto por defecto --}}
        <div x-data="{ pacOpen: true }" class="bg-white rounded-xl shadow-lg border border-green-200">
            <button @click="pacOpen = !pacOpen" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <span class="text-lg font-bold text-gray-900">Cumplimiento PAC</span>
                        <p class="text-xs text-gray-500">Obligatorio para recibir ayudas de la PAC</p>
                    </div>
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

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Distribución por Variedad --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="chart-variety">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Distribución por Variedad</h3>
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
                    <div class="text-center py-6">
                        <p class="text-gray-500 mb-3">Aún no tienes plantaciones registradas</p>
                        <a href="{{ route('plots.index') }}" wire:navigate
                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-semibold rounded-lg border border-green-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Añadir plantaciones a tus parcelas
                        </a>
                    </div>
                @endif
            </div>

            {{-- Actividades Recientes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-activities">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Actividades Recientes</h3>
                    @if($recentActivities->count() > 0)
                        <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:underline">
                            Ver todas
                        </a>
                    @endif
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
                                        @case('irrigation') 💧 @break
                                        @case('cultural_work') 🚜 @break
                                        @case('observation') 🔍 @break
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
                    <div class="text-center py-6">
                        <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500 mb-1">Tu cuaderno de campo está vacío</p>
                        <p class="text-xs text-gray-400 mb-3">Registra tratamientos, riegos, fertilizaciones y más</p>
                        <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate
                           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Primera actividad
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Links Row - Contextuales --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @if($isNewUser)
                {{-- Usuario nuevo: priorizar acciones de valor --}}
                <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow border-2 border-green-200 p-4 hover:shadow-lg hover:border-green-400 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center text-xl text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Entrada rápida</p>
                        <p class="text-xs text-green-600 font-medium">Actividad en 2 pasos</p>
                    </div>
                </a>

                <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-purple-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Cuaderno Digital</p>
                        <p class="text-xs text-gray-500">Tu cuaderno de campo</p>
                    </div>
                </a>

                <a href="{{ route('plots.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-green-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Parcelas</p>
                        <p class="text-xs text-gray-500">Gestionar terrenos</p>
                    </div>
                </a>

                <a href="{{ route('viticulturist.pac.dashboard') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-amber-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">PAC</p>
                        <p class="text-xs text-gray-500">Cumplimiento normativo</p>
                    </div>
                </a>
            @else
                {{-- Usuario con actividad: mostrar accesos completos --}}
                <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate class="bg-white rounded-xl shadow border border-agro-200 p-4 hover:shadow-lg hover:border-agro-400 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-agro-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-agro-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Entrada rápida</p>
                        <p class="text-xs text-gray-500">Actividad en 2 pasos</p>
                    </div>
                </a>

                <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-purple-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Cuaderno Digital</p>
                        <p class="text-xs text-gray-500">Registrar actividades</p>
                    </div>
                </a>

                <a href="{{ route('viticulturist.invoices.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-indigo-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Facturación</p>
                        <p class="text-xs text-gray-500">Ver estadísticas</p>
                    </div>
                </a>

                <a href="{{ route('plots.index') }}" wire:navigate class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-green-300 transition-all flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Parcelas</p>
                        <p class="text-xs text-gray-500">Gestionar terrenos</p>
                    </div>
                </a>
            @endif
        </div>

        {{-- Cosechas Recientes --}}
        @if($recentHarvests->count() > 0)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6" data-cy="recent-harvests">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Cosechas Recientes</h3>
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
