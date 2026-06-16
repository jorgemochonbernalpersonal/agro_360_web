<div class="space-y-6 animate-fade-in">

    {{-- Header con saludo --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">
                {{ now()->hour < 14 ? __('Buenos días') : __('Buenas tardes') }}, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-zinc-400 mt-0.5">
                {{ __('Campaña activa') }}: <span class="font-semibold text-agro-700">{{ $vintageYear }}</span>
                @if($activeCampaign)
                    · <span class="text-zinc-500">{{ $activeCampaign->year }}</span>
                @endif
            </p>
        </div>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('grape-reception.create') }}" wire:navigate>
            {{ __('Nueva recepción') }}
        </flux:button>
    </div>

    {{-- Onboarding checklist --}}
    @livewire('winery.onboarding-checklist')

    {{-- Stat-bar: hoy --}}
    @if($todayKg > 0 || $todayCount > 0)
        <div class="bg-agro-600 rounded-2xl p-4 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest text-agro-200 mb-3">{{ __('Hoy') }}</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-3xl font-bold">{{ number_format($todayKg, 0) }} <span class="text-lg font-normal text-agro-200">kg</span></p>
                    <p class="text-xs text-agro-200 mt-0.5">{{ __('Recibidos hoy') }}</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $todayCount }}</p>
                    <p class="text-xs text-agro-200 mt-0.5">{{ __('Entradas hoy') }}</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ number_format($totalKgCampaign, 0) }} <span class="text-lg font-normal text-agro-200">kg</span></p>
                    <p class="text-xs text-agro-200 mt-0.5">{{ __('Total campaña') }} {{ $vintageYear }}</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $totalReceptions }}</p>
                    <p class="text-xs text-agro-200 mt-0.5">{{ __('Recepciones campaña') }}</p>
                </div>
            </div>
        </div>
    @else
        {{-- Sin recepciones hoy: stat-bar neutral --}}
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card
                :label="__('Total campaña').' '.$vintageYear"
                :value="number_format($totalKgCampaign, 0) . ' kg'"
                color="agro"
            />
            <x-agro.stat-card
                :label="__('Recepciones')"
                :value="$totalReceptions"
                :description="$openBatchCount . ' ' . ($openBatchCount == 1 ? __('abierto') : __('abiertos')) . ($closedBatchCount > 0 ? ' · ' . $closedBatchCount . ' ' . ($closedBatchCount == 1 ? __('cerrado') : __('cerrados')) : '')"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Viticultores')"
                :value="$viticulturistCount"
                :description="$pendingViticulturistCount > 0 ? $pendingViticulturistCount . ' ' . ($pendingViticulturistCount > 1 ? __('pendientes') : __('pendiente')) : null"
                color="zinc"
            />
            <x-agro.stat-card
                :label="__('Lotes de vino')"
                :value="$wineLotCount"
                color="zinc"
            />
        </div>
    @endif

    {{-- Alertas --}}
    @if($alertsExceeded > 0 || $alertsAtRisk > 0 || $maintenanceOverdue > 0)
        <div class="flex flex-wrap gap-3">
            @if($alertsExceeded > 0)
                <a href="{{ roleRoute('harvest-summary.index', ['alertFilter' => 'exceeded']) }}" wire:navigate
                    class="flex items-center gap-2 px-4 py-2.5 bg-red-50 border border-red-200 rounded-xl text-sm font-medium text-red-700 hover:bg-red-100 transition-colors">
                    <flux:icon icon="exclamation-triangle" class="size-4" />
                    {{ $alertsExceeded }} {{ $alertsExceeded > 1 ? __('lotes superados') : __('lote superado') }}
                    <flux:icon icon="arrow-right" class="size-3.5" />
                </a>
            @endif
            @if($alertsAtRisk > 0)
                <a href="{{ roleRoute('harvest-summary.index', ['alertFilter' => 'at_risk']) }}" wire:navigate
                    class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-sm font-medium text-amber-700 hover:bg-amber-100 transition-colors">
                    <flux:icon icon="bell-alert" class="size-4" />
                    {{ $alertsAtRisk }} {{ __('en riesgo (≥80%)') }}
                    <flux:icon icon="arrow-right" class="size-3.5" />
                </a>
            @endif
            @if($maintenanceOverdue > 0)
                <a href="{{ roleRoute('containers.index') }}" wire:navigate
                    class="flex items-center gap-2 px-4 py-2.5 bg-orange-50 border border-orange-200 rounded-xl text-sm font-medium text-orange-700 hover:bg-orange-100 transition-colors">
                    <flux:icon icon="wrench-screwdriver" class="size-4" />
                    {{ $maintenanceOverdue }} {{ $maintenanceOverdue > 1 ? __('mantenimientos vencidos') : __('mantenimiento vencido') }}
                    <flux:icon icon="arrow-right" class="size-3.5" />
                </a>
            @endif
        </div>
    @endif

    {{-- ── Módulo Bodega: KPIs ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrows-right-left" class="size-5 text-violet-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Vinos en elaboración') }}</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $activeWines }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('vinos activos') }}</p>
            </div>
        </x-agro.card>

        <x-agro.card class="flex items-center gap-4">
            @if($activeFermentations > 0)
                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center shrink-0 relative">
                    <flux:icon icon="fire" class="size-5 text-orange-500" />
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full text-[9px] text-white font-bold flex items-center justify-center">{{ $activeFermentations }}</span>
                </div>
            @else
                <div class="w-11 h-11 rounded-xl bg-zinc-50 flex items-center justify-center shrink-0">
                    <flux:icon icon="fire" class="size-5 text-zinc-300" />
                </div>
            @endif
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Fermentando') }}</p>
                <p class="text-2xl font-bold {{ $activeFermentations > 0 ? 'text-orange-600' : 'text-zinc-900' }} leading-none">{{ $activeFermentations }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('vinos activos (últimas 72h)') }}</p>
            </div>
        </x-agro.card>

        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                <flux:icon icon="calculator" class="size-5 text-agro-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Costes') }} {{ $vintageYear }}</p>
                @if($totalCostsYear > 0)
                    <p class="text-2xl font-bold text-zinc-900 leading-none">
                        {{ number_format($totalCostsYear, 0, ',', '.') }} <span class="text-sm font-medium text-zinc-400">€</span>
                    </p>
                @else
                    <p class="text-2xl font-bold text-zinc-300 leading-none">—</p>
                @endif
                <a href="{{ roleRoute('production-costs.index') }}" wire:navigate class="text-xs text-agro-600 hover:underline">{{ __('Ver desglose') }} →</a>
            </div>
        </x-agro.card>

        <x-agro.card class="sm:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">{{ __('Uso de depósitos') }}</p>
                <p class="text-sm font-bold {{ $containerUsage['pct'] > 85 ? 'text-red-500' : ($containerUsage['pct'] > 60 ? 'text-amber-500' : 'text-green-500') }}">
                    {{ number_format($containerUsage['pct'], 0) }}%
                </p>
            </div>
            <div class="w-full bg-zinc-100 rounded-full h-2.5 overflow-hidden">
                @php $barColor = $containerUsage['pct'] > 85 ? 'bg-red-500' : ($containerUsage['pct'] > 60 ? 'bg-amber-500' : 'bg-green-500'); @endphp
                <div class="h-2.5 rounded-full {{ $barColor }} transition-all"
                     style="width: {{ $containerUsage['pct'] }}%"></div>
            </div>
            <div class="flex justify-between mt-1.5">
                <p class="text-xs text-zinc-500">{{ number_format($containerUsage['used'], 0, ',', '.') }} L {{ __('usados') }}</p>
                <p class="text-xs text-zinc-400">{{ __('de') }} {{ number_format($containerUsage['total'], 0, ',', '.') }} L {{ __('totales') }}</p>
            </div>
        </x-agro.card>

    </div>

    {{-- Contenido principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Últimas recepciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-agro-50 flex items-center justify-center">
                            <flux:icon icon="archive-box-arrow-down" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">{{ __('Últimas recepciones') }}</span>
                    </div>
                    <a href="{{ roleRoute('grape-reception.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">{{ __('Ver todas') }} →</a>
                </div>
            </x-slot:header>

            @if($recentReceptions->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="archive-box-arrow-down" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">{{ __('No hay recepciones en la campaña activa') }}</p>
                    <a href="{{ roleRoute('grape-reception.create') }}" wire:navigate class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        {{ __('Registrar primera recepción') }} →
                    </a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentReceptions as $r)
                        <a href="{{ roleRoute('grape-reception.show', $r) }}" wire:navigate
                            class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors group">
                            <div class="w-9 h-9 rounded-lg bg-agro-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="scale" class="size-4 text-agro-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">
                                    {{ $r->batch?->viticulturist?->name ?? '—' }}
                                </p>
                                <p class="text-xs text-zinc-400 truncate">
                                    {{ $r->plotPlanting?->grapeVariety?->name ?? '—' }}
                                    · {{ $r->plotPlanting?->plot?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-agro-700">{{ number_format($r->total_weight, 0) }} kg</p>
                                <p class="text-xs text-zinc-400">{{ $r->harvest_start_date?->format('d/m') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

        {{-- Fermentaciones recientes --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                            <flux:icon icon="fire" class="size-4 text-orange-500" />
                        </div>
                        <span class="font-semibold text-zinc-900">{{ __('Fermentaciones') }}</span>
                    </div>
                    <a href="{{ roleRoute('fermentation-controls.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">{{ __('Ver todos') }} →</a>
                </div>
            </x-slot:header>

            @if($recentFermentations->isEmpty())
                <div class="py-6 text-center">
                    <flux:icon icon="fire" class="size-8 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">{{ __('Sin controles registrados') }}</p>
                    <a href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline mt-1 inline-block">{{ __('Registrar control') }} →</a>
                </div>
            @else
                <div class="space-y-2 mt-1">
                    @foreach($recentFermentations as $ctrl)
                        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-zinc-50">
                            <div class="w-8 h-8 rounded-lg {{ $ctrl->isFermenting() ? 'bg-orange-50' : 'bg-green-50' }} flex items-center justify-center shrink-0">
                                <flux:icon icon="fire" class="size-4 {{ $ctrl->isFermenting() ? 'text-orange-500' : 'text-green-500' }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $ctrl->wine?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $ctrl->container?->name ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                @if($ctrl->density !== null)
                                    <p class="text-xs font-bold {{ $ctrl->isFermenting() ? 'text-orange-600' : 'text-green-600' }}">
                                        ρ {{ number_format($ctrl->density, 4) }}
                                    </p>
                                @endif
                                <p class="text-[10px] text-zinc-400">{{ \Carbon\Carbon::parse($ctrl->control_date)->format('d/m H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

        {{-- Viticultores --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-agro-50 flex items-center justify-center">
                            <flux:icon icon="users" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">{{ __('Viticultores vinculados') }}</span>
                    </div>
                    <a href="{{ roleRoute('viticulturists.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">{{ __('Ver todos') }} →</a>
                </div>
            </x-slot:header>

            @if($recentViticulturists->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="users" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">{{ __('No hay viticultores vinculados') }}</p>
                    <a href="{{ roleRoute('viticulturists.invite') }}" wire:navigate class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        {{ __('Invitar viticultor') }} →
                    </a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($recentViticulturists as $wv)
                        <a href="{{ roleRoute('viticulturists.show', $wv->viticulturist) }}" wire:navigate
                            class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-full bg-agro-100 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-agro-700">
                                    {{ strtoupper(substr($wv->viticulturist?->name ?? '?', 0, 1)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $wv->viticulturist?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $wv->viticulturist?->email ?? '' }}</p>
                            </div>
                            <span class="text-xs text-zinc-300 shrink-0">{{ $wv->created_at->format('d/m/y') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

    </div>

    {{-- Accesos rápidos --}}
    <div>
        <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">{{ __('Accesos rápidos') }}</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <x-agro.quick-link href="{{ roleRoute('harvest-summary.index') }}" wire:navigate icon="chart-bar" color="agro" :label="__('Cuadro de mando')" :description="__('Vendimia en tiempo real')" />
            <x-agro.quick-link href="{{ roleRoute('grape-reception.create') }}" wire:navigate icon="plus-circle" color="agro" :label="__('Nueva recepción')" :description="__('Registrar entrada de uva')" />
            <x-agro.quick-link href="{{ roleRoute('product-lots.index') }}" wire:navigate icon="beaker" color="agro" :label="__('Lotes de vino')" :description="__('Control de bodega')" />
            <x-agro.quick-link href="{{ roleRoute('invoices.grape-purchase.index') }}" wire:navigate icon="document-text" color="agro" :label="__('Facturación')" :description="__('Liquidaciones y ventas')" />
            <x-agro.quick-link href="{{ roleRoute('fermentation-controls.create') }}" wire:navigate icon="fire" color="orange" :label="__('Control fermentación')" :description="__('Registrar parámetros')" />
            <x-agro.quick-link href="{{ roleRoute('wine-transfers.create') }}" wire:navigate icon="arrows-right-left" color="violet" :label="__('Nuevo trasvase')" :description="__('Trasiego / Coupage')" />
            <x-agro.quick-link href="{{ roleRoute('financial-summary.index') }}" wire:navigate icon="chart-bar-square" color="agro" :label="__('Resumen económico')" :description="__('Ingresos y márgenes')" />

        </div>
    </div>

</div>
