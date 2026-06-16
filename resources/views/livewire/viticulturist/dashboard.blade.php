<div class="space-y-6 animate-fade-in">
    {{-- Onboarding Components --}}
    @livewire('viticulturist.onboarding-welcome')
    @livewire('viticulturist.onboarding-checklist')

    {{-- Avisos de bodegas --}}
    @livewire('viticulturist.announcements-banner')

    {{-- Alertas del sistema --}}
    @if($this->alerts->isNotEmpty())
        <div class="space-y-2" data-cy="dashboard-alerts">
            @foreach($this->alerts->take(3) as $alert)
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg border
                    {{ $alert['type'] === 'danger'  ? 'bg-red-50 border-red-200 text-red-800'     : '' }}
                    {{ $alert['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : '' }}
                    {{ $alert['type'] === 'info'    ? 'bg-blue-50 border-blue-200 text-blue-800'   : '' }}">
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
                            {{ $alert['action_text'] ?? __('Ver') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Header --}}
    <x-page-header
        icon='<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'
        :title="__('Dashboard')"
        :description="__('Resumen de tu viñedo')"
        icon-color="from-agro-600 to-agro-800"
    />

    {{-- Contexto: última actividad o sugerencia --}}
    @if($this->isNewUser)
        <div class="bg-agro-50 border border-agro-200 rounded-xl p-5" data-cy="welcome-context">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-agro-500 flex items-center justify-center text-white text-2xl flex-shrink-0">
                    🌱
                </div>
                <div>
                    <h3 class="font-bold text-agro-900">{{ __('Tu viñedo digital te espera') }}</h3>
                    <p class="text-sm text-agro-700 mt-1">
                        {{ __('Registra tu primera actividad en el cuaderno y empieza a llevar el control de tu explotación. El cuaderno de campo es obligatorio para el cumplimiento PAC.') }}
                    </p>
                    <div class="flex gap-3 mt-3">
                        <flux:button href="{{ route('viticulturist.quick-entry') }}" wire:navigate variant="primary" size="sm" icon="plus">{{ __('Registrar actividad') }}</flux:button>
                        <flux:button href="{{ route('viticulturist.digital-notebook') }}" wire:navigate variant="outline" size="sm">{{ __('Ver cuaderno') }}</flux:button>
                    </div>
                </div>
            </div>
        </div>
    @elseif($this->daysSinceLastActivity !== null && $this->daysSinceLastActivity > 7)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4" data-cy="inactivity-reminder">
            <div class="flex items-center gap-3">
                <span class="text-xl">📝</span>
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-900">
                        {{ __('Llevas') }} <strong>{{ $this->daysSinceLastActivity }} {{ $this->daysSinceLastActivity === 1 ? __('día') : __('días') }}</strong> {{ __('sin registrar actividades.') }}
                    </p>
                    <p class="text-xs text-amber-700">{{ __('Mantener el cuaderno al día es clave para el cumplimiento PAC.') }}</p>
                </div>
                <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate
                   class="flex-shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors">
                    {{ __('Registrar ahora') }}
                </a>
            </div>
        </div>
    @endif

    {{-- KPI Cards - 4 esenciales --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" data-cy="dashboard-kpi-cards">
        {{-- Parcelas + Área --}}
        <div data-cy="kpi-plots">
            <x-agro.stat-card :label="__('Parcelas')" :value="$this->totalPlots" :description="number_format($this->totalArea, 1).' ha'" icon="map" color="green" />
        </div>

        {{-- Actividades --}}
        <div data-cy="kpi-activities">
            <x-agro.stat-card :label="__('Actividades')" :value="$this->activitiesThisMonth" :description="__('este mes')" icon="document-text" color="purple" />
        </div>

        {{-- Cosechado --}}
        <div data-cy="kpi-harvest">
            <x-agro.stat-card :label="__('Cosechado')" :value="number_format($this->totalHarvested / 1000, 1).' t'" :description="__('toneladas').' '.now()->year" icon="star" color="amber" />
        </div>

        {{-- Teledetección - condicional --}}
        @php $ndvi = $this->ndviData; @endphp
        @if($ndvi['ndvi'] !== null)
            <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate class="bg-agro-50 rounded-xl shadow-lg border-2 border-agro-200 p-5 hover:shadow-xl hover:border-agro-300 transition-all" data-cy="kpi-remote-sensing">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-agro-700">{{ __('NDVI') }}</p>
                        <p class="text-3xl font-bold {{ $ndvi['ndvi'] >= 0.5 ? 'text-agro-700' : ($ndvi['ndvi'] >= 0.35 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($ndvi['ndvi'], 2) }}</p>
                        <p class="text-xs text-agro-600 truncate">{{ $ndvi['plotName'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-agro-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-agro-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </a>
        @elseif($ndvi['hasSigpac'])
            <a href="{{ route('remote-sensing.dashboard') }}" wire:navigate class="bg-agro-50 rounded-xl shadow-lg border-2 border-agro-200 p-5 hover:shadow-xl hover:border-agro-300 transition-all" data-cy="kpi-remote-sensing">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-agro-700">{{ __('Teledetección') }}</p>
                        <p class="text-base font-bold text-agro-800">{{ __('Ver mapa NDVI') }}</p>
                        <p class="text-xs text-agro-600">{{ __('Análisis satelital') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-agro-200 flex items-center justify-center">
                        <svg class="w-5 h-5 text-agro-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @else
            <a href="{{ route('plots.index') }}" wire:navigate class="bg-white rounded-xl shadow-lg border-2 border-dashed border-agro-300 p-5 hover:shadow-xl hover:border-agro-400 transition-all" data-cy="kpi-remote-sensing-setup">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-agro-700">{{ __('Teledetección') }}</p>
                        <p class="text-xs font-semibold text-zinc-600 mt-1">{{ __('Vincula tus parcelas con SIGPAC para ver análisis satelital gratis') }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-agro-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-agro-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                </div>
            </a>
        @endif
    </div>

    {{-- Consejo estacional --}}
    <x-agro.card data-cy="seasonal-tip">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center text-xl flex-shrink-0">
                {{ $this->currentTip['icon'] }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-zinc-900">{{ $this->currentTip['title'] }}</h3>
                <p class="text-xs text-zinc-600 mt-1">{{ $this->currentTip['tip'] }}</p>
            </div>
            <a href="{{ route($this->currentTip['route']) }}" wire:navigate
               class="flex-shrink-0 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200 transition-colors">
                {{ $this->currentTip['action'] }}
            </a>
        </div>
    </x-agro.card>

    {{-- PAC Compliance Section --}}
    <div x-data="{ pacOpen: true }" class="bg-white rounded-xl shadow-lg border border-agro-200">
        <button @click="pacOpen = !pacOpen" class="w-full px-6 py-4 flex items-center justify-between hover:bg-zinc-50 transition-colors rounded-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-agro-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-lg font-bold text-zinc-900">{{ __('Cumplimiento PAC') }}</span>
                    <p class="text-xs text-zinc-500">{{ __('Obligatorio para recibir ayudas de la PAC') }}</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-zinc-400 transition-transform" :class="{ 'rotate-180': pacOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="pacOpen" x-collapse class="px-6 pb-6 space-y-6">
            <div>
                <h4 class="font-semibold text-zinc-700 mb-3">{{ __('Parcelas') }}</h4>
                @livewire('viticulturist.plots-dashboard')
            </div>
            <div>
                <h4 class="font-semibold text-zinc-700 mb-3">{{ __('Plantaciones') }}</h4>
                @livewire('viticulturist.plantings-dashboard')
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Distribución por Variedad --}}
        <x-agro.card data-cy="chart-variety">
            <h3 class="text-base font-bold text-zinc-900 mb-4">{{ __('Distribución por Variedad') }}</h3>

            @if($this->plotsByVariety->count() > 0)
                <div class="space-y-3">
                    @foreach($this->plotsByVariety as $index => $variety)
                        @php
                            $percentage = ($variety->count / max($this->totalPlots, 1)) * 100;
                            $colors = ['bg-agro-500', 'bg-purple-500', 'bg-blue-500', 'bg-amber-500', 'bg-rose-500'];
                            $color = $colors[$index % count($colors)];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-zinc-700">{{ $variety->grapeVariety->name ?? __('Sin variedad') }}</span>
                                <span class="text-sm font-bold text-zinc-900">{{ $variety->count }} {{ __('parcelas') }}</span>
                            </div>
                            <div class="w-full bg-zinc-200 rounded-full h-2">
                                <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="map" :title="__('Sin plantaciones')" :description="__('Aún no tienes plantaciones registradas')">
                    <flux:button href="{{ route('plots.index') }}" wire:navigate variant="primary" size="sm">{{ __('Añadir plantaciones a tus parcelas') }}</flux:button>
                </x-agro.empty-state>
            @endif
        </x-agro.card>

        {{-- Actividades Recientes --}}
        <x-agro.card data-cy="recent-activities">
            @php
                $activityTypeLabels = [
                    'treatment'     => __('Tratamiento'),
                    'harvest'       => __('Cosecha'),
                    'pruning'       => __('Poda'),
                    'fertilization' => __('Fertilización'),
                    'irrigation'    => __('Riego'),
                    'cultural_work' => __('Labor cultural'),
                    'observation'   => __('Observación'),
                ];
            @endphp
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-zinc-900">{{ __('Actividades Recientes') }}</h3>
                @if($this->recentActivities->count() > 0)
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate class="text-sm font-medium text-agro-600 hover:underline">
                        {{ __('Ver todas') }}
                    </a>
                @endif
            </div>

            @if($this->recentActivities->count() > 0)
                <div class="space-y-3">
                    @foreach($this->recentActivities as $activity)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50">
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
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $activityTypeLabels[$activity->activity_type] ?? ucfirst($activity->activity_type) }}</p>
                                <p class="text-xs text-zinc-500">{{ $activity->plot->name ?? __('Sin parcela') }}</p>
                            </div>
                            <span class="text-xs text-zinc-400">{{ $activity->activity_date->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state icon="document-text" :title="__('Cuaderno vacío')" :description="__('Registra tratamientos, riegos, fertilizaciones y más')">
                    <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        {{ __('Primera actividad') }}
                    </a>
                </x-agro.empty-state>
            @endif
        </x-agro.card>
    </div>

    {{-- Quick Links Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @if($this->isNewUser)
            <a href="{{ route('viticulturist.quick-entry') }}" wire:navigate class="bg-agro-50 rounded-xl shadow border-2 border-agro-200 p-4 hover:shadow-lg hover:border-agro-400 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-agro-500 flex items-center justify-center text-xl text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-zinc-900">{{ __('Entrada rápida') }}</p>
                    <p class="text-xs text-agro-600 font-medium">{{ __('Actividad en 2 pasos') }}</p>
                </div>
            </a>

            <x-agro.quick-link href="{{ route('viticulturist.digital-notebook') }}" wire:navigate icon="book-open" color="purple" :label="__('Cuaderno Digital')" :description="__('Tu cuaderno de campo')" />
            <x-agro.quick-link href="{{ route('plots.index') }}" wire:navigate icon="map" color="agro" :label="__('Parcelas')" :description="__('Gestionar terrenos')" />
            <x-agro.quick-link href="{{ route('viticulturist.pac.dashboard') }}" wire:navigate icon="shield-check" color="amber" :label="__('PAC')" :description="__('Cumplimiento normativo')" :locked="!$this->hasActiveAccess" />
        @else
            <x-agro.quick-link href="{{ route('viticulturist.quick-entry') }}" wire:navigate icon="bolt" color="agro" :label="__('Entrada rápida')" :description="__('Actividad en 2 pasos')" />
            <x-agro.quick-link href="{{ route('viticulturist.digital-notebook') }}" wire:navigate icon="book-open" color="purple" :label="__('Cuaderno Digital')" :description="__('Registrar actividades')" />
            <x-agro.quick-link href="{{ route('viticulturist.invoices.harvest-sale.index') }}" wire:navigate icon="document-text" color="indigo" :label="__('Facturación')" :description="__('Ver estadísticas')" :locked="!$this->hasActiveAccess" />
            <x-agro.quick-link href="{{ route('plots.index') }}" wire:navigate icon="map" color="agro" :label="__('Parcelas')" :description="__('Gestionar terrenos')" />
        @endif
    </div>

    {{-- Cosechas Recientes --}}
    @if($this->recentHarvests->count() > 0)
        <x-agro.card data-cy="recent-harvests">
            <h3 class="text-base font-bold text-zinc-900 mb-4">{{ __('Cosechas Recientes') }}</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($this->recentHarvests as $harvest)
                    <div class="p-3 rounded-lg border border-zinc-200 hover:border-purple-300 transition-colors">
                        <p class="text-sm font-semibold text-zinc-900 truncate">{{ $harvest->plotPlanting->grapeVariety->name ?? __('Sin variedad') }}</p>
                        <p class="text-xs text-zinc-500 truncate">{{ $harvest->activity->plot->name ?? __('Sin parcela') }}</p>
                        <p class="text-sm font-bold text-purple-600 mt-1">{{ number_format($harvest->total_weight, 0) }} kg</p>
                        <p class="text-xs text-zinc-400">{{ $harvest->harvest_start_date->format('d/m/Y') }}</p>
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    @endif
</div>
