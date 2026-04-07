<div class="space-y-6 animate-fade-in">

    {{-- Onboarding --}}
    @livewire('producer.onboarding-checklist')

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900">
                {{ now()->hour < 14 ? 'Buenos días' : 'Buenas tardes' }}, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-zinc-400 mt-0.5">
                Campaña <span class="font-semibold text-agro-700">{{ $vintageYear }}</span>
                · Productor (campo + bodega)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="outline" icon="map" href="{{ route('producer.plots.index') }}" wire:navigate size="sm">
                Mis parcelas
            </flux:button>
            <flux:button variant="primary" icon="plus" href="{{ route('producer.grape-reception.create') }}" wire:navigate size="sm">
                Nueva recepción
            </flux:button>
        </div>
    </div>

    {{-- KPI bar: hoy en bodega --}}
    @if($todayKg > 0 || $todayCount > 0)
        <div class="bg-agro-600 rounded-2xl p-4 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest text-agro-200 mb-3">Bodega · Hoy</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-3xl font-bold">{{ number_format($todayKg, 0) }} <span class="text-lg font-normal text-agro-200">kg</span></p>
                    <p class="text-xs text-agro-200 mt-0.5">Recibidos hoy</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $todayCount }}</p>
                    <p class="text-xs text-agro-200 mt-0.5">Entradas hoy</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ number_format($totalKgCampaign, 0) }} <span class="text-lg font-normal text-agro-200">kg</span></p>
                    <p class="text-xs text-agro-200 mt-0.5">Total campaña {{ $vintageYear }}</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $totalReceptions }}</p>
                    <p class="text-xs text-agro-200 mt-0.5">Recepciones campaña</p>
                </div>
            </div>
        </div>
    @endif

    {{-- KPIs 4 columnas --}}
    <div class="grid grid-cols-2 gap-4">

        {{-- Campo: parcelas --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-agro-50 flex items-center justify-center shrink-0">
                <flux:icon icon="map" class="size-5 text-agro-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Campo · Parcelas</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $totalPlots }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">{{ number_format($totalArea, 1) }} ha</p>
            </div>
        </x-agro.card>

        {{-- Campo: actividades --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <flux:icon icon="clipboard-document-list" class="size-5 text-purple-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Campo · Actividades</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $activitiesThisMonth }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">este mes</p>
            </div>
        </x-agro.card>

        {{-- Bodega: kg campaña --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <flux:icon icon="scale" class="size-5 text-amber-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Bodega · Kg campaña</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ number_format($totalKgCampaign / 1000, 1) }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">toneladas {{ $vintageYear }}</p>
            </div>
        </x-agro.card>

        {{-- Bodega: lotes de vino --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <flux:icon icon="beaker" class="size-5 text-violet-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Bodega · Lotes</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $wineLotCount }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">de vino en bodega</p>
            </div>
        </x-agro.card>

    </div>

    {{-- KPIs segunda fila: vinos, fermentaciones, contenedores --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                <flux:icon icon="arrows-right-left" class="size-5 text-red-600" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Vinos activos</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ $activeWines }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">en elaboración</p>
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
                    <flux:icon icon="fire" class="size-5 text-zinc-400" />
                </div>
            @endif
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Fermentando</p>
                <p class="text-2xl font-bold {{ $activeFermentations > 0 ? 'text-orange-600' : 'text-zinc-900' }} leading-none">{{ $activeFermentations }}</p>
                <p class="text-xs text-zinc-400 mt-0.5">vinos fermentando</p>
            </div>
        </x-agro.card>

        {{-- Contenedores: uso --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-cyan-50 flex items-center justify-center shrink-0">
                <flux:icon icon="cube" class="size-5 text-cyan-600" />
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Contenedores</p>
                <p class="text-2xl font-bold text-zinc-900 leading-none">{{ number_format($containerUsage['pct'], 0) }}%</p>
                <div class="w-full bg-zinc-200 rounded-full h-1.5 mt-1.5">
                    <div class="h-1.5 rounded-full {{ $containerUsage['pct'] > 90 ? 'bg-red-500' : ($containerUsage['pct'] > 70 ? 'bg-amber-500' : 'bg-cyan-500') }}"
                         style="width: {{ $containerUsage['pct'] }}%"></div>
                </div>
            </div>
        </x-agro.card>

        {{-- Alertas de rendimiento --}}
        <x-agro.card class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ ($alertsExceeded + $alertsAtRisk) > 0 ? 'bg-red-50' : 'bg-green-50' }} flex items-center justify-center shrink-0">
                <flux:icon icon="exclamation-triangle" class="size-5 {{ ($alertsExceeded + $alertsAtRisk) > 0 ? 'text-red-500' : 'text-green-500' }}" />
            </div>
            <div>
                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest">Alertas rend.</p>
                @if($alertsExceeded > 0 || $alertsAtRisk > 0)
                    <div class="flex items-center gap-2">
                        @if($alertsExceeded > 0)
                            <span class="text-sm font-bold text-red-600">{{ $alertsExceeded }} superado{{ $alertsExceeded > 1 ? 's' : '' }}</span>
                        @endif
                        @if($alertsAtRisk > 0)
                            <span class="text-sm font-bold text-amber-600">{{ $alertsAtRisk }} en riesgo</span>
                        @endif
                    </div>
                @else
                    <p class="text-sm font-bold text-green-600">Todo OK</p>
                @endif
            </div>
        </x-agro.card>

    </div>

    {{-- Alertas activas --}}
    @if($maintenanceOverdue > 0)
        <a href="{{ route('producer.containers.index') }}" wire:navigate
           class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl hover:shadow-md hover:border-red-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <flux:icon icon="wrench-screwdriver" class="size-5 text-red-600" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-800">{{ $maintenanceOverdue }} mantenimiento{{ $maintenanceOverdue > 1 ? 's' : '' }} vencido{{ $maintenanceOverdue > 1 ? 's' : '' }}</p>
                <p class="text-xs text-red-600">Hay contenedores con mantenimiento programado pendiente</p>
            </div>
            <flux:icon icon="arrow-right" class="size-4 text-red-400 shrink-0" />
        </a>
    @endif

    {{-- CTA fermentación --}}
    <a href="{{ route('producer.fermentation-controls.index') }}" wire:navigate
        class="flex items-center gap-3 p-4 bg-orange-50 border border-orange-200 rounded-2xl hover:shadow-md hover:border-orange-300 transition-all group">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors shrink-0">
            <flux:icon icon="fire" class="size-5 text-orange-600" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-zinc-900">Controles de Fermentación</p>
            <p class="text-xs text-zinc-500">Ver histórico y registrar nuevo control</p>
        </div>
        <flux:icon icon="arrow-right" class="size-4 text-orange-400 shrink-0" />
    </a>

    {{-- Contenido principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Campo: actividades recientes --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                            <flux:icon icon="clipboard-document-list" class="size-4 text-purple-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">Actividades recientes</span>
                        <flux:badge color="purple" size="sm">Campo</flux:badge>
                    </div>
                    <a href="{{ route('producer.digital-notebook') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todas →</a>
                </div>
            </x-slot:header>

            @if($recentActivities->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="clipboard-document-list" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay actividades registradas</p>
                    <a href="{{ route('producer.digital-notebook') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        Ir al cuaderno de campo →
                    </a>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentActivities as $activity)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center shrink-0 text-base">
                                @switch($activity->activity_type)
                                    @case('phytosanitary') 💊 @break
                                    @case('harvest') 🍇 @break
                                    @case('pruning') ✂️ @break
                                    @case('fertilization') 🌿 @break
                                    @case('irrigation') 💧 @break
                                    @default 📝
                                @endswitch
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900">{{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $activity->plot?->name ?? '—' }}</p>
                            </div>
                            <span class="text-xs text-zinc-400 shrink-0">{{ $activity->activity_date->format('d/m') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

        {{-- Bodega: fermentaciones activas --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                            <flux:icon icon="fire" class="size-4 text-orange-500" />
                        </div>
                        <span class="font-semibold text-zinc-900">Fermentaciones</span>
                        <flux:badge color="orange" size="sm">Bodega</flux:badge>
                    </div>
                    <a href="{{ route('producer.fermentation-controls.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todas →</a>
                </div>
            </x-slot:header>

            @if($recentFermentations->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="fire" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay controles registrados</p>
                    <a href="{{ route('producer.fermentation-controls.create') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        Registrar primer control →
                    </a>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentFermentations as $ctrl)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg {{ $ctrl->isFermenting() ? 'bg-orange-50' : 'bg-green-50' }} flex items-center justify-center shrink-0">
                                <flux:icon icon="fire" class="size-4 {{ $ctrl->isFermenting() ? 'text-orange-500' : 'text-green-500' }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $ctrl->wine?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $ctrl->container?->name ?? '—' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                @if($ctrl->density !== null)
                                    <p class="text-sm font-bold {{ $ctrl->isFermenting() ? 'text-orange-600' : 'text-green-600' }}">
                                        {{ number_format($ctrl->density, 4) }}
                                    </p>
                                @endif
                                <p class="text-xs text-zinc-400">{{ \Carbon\Carbon::parse($ctrl->control_date)->format('d/m H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

        {{-- Bodega: últimas recepciones --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-agro-50 flex items-center justify-center">
                            <flux:icon icon="archive-box-arrow-down" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">Últimas recepciones</span>
                        <flux:badge color="green" size="sm">Bodega</flux:badge>
                    </div>
                    <a href="{{ route('producer.grape-reception.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todas →</a>
                </div>
            </x-slot:header>

            @if($recentReceptions->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="archive-box-arrow-down" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay recepciones en la campaña activa</p>
                    <a href="{{ route('producer.grape-reception.create') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        Registrar primera recepción →
                    </a>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentReceptions as $r)
                        <a href="{{ route('producer.grape-reception.show', $r) }}" wire:navigate
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

    </div>

    {{-- Fila extra: trasvases + viticultores --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Trasvases recientes --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                            <flux:icon icon="arrows-right-left" class="size-4 text-violet-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">Trasvases recientes</span>
                        <flux:badge color="violet" size="sm">Bodega</flux:badge>
                    </div>
                    <a href="{{ route('producer.wine-transfers.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todos</a>
                </div>
            </x-slot:header>

            @if($recentTransfers->isEmpty())
                <div class="py-6 text-center">
                    <flux:icon icon="arrows-right-left" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay trasvases registrados</p>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentTransfers as $t)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                                <flux:icon icon="arrows-right-left" class="size-4 text-violet-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $t->wine?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">
                                    {{ $t->fromContainer?->name ?? '—' }} → {{ $t->toContainer?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-violet-600">{{ number_format($t->volume_liters, 0) }} L</p>
                                <p class="text-xs text-zinc-400">{{ $t->transfer_date?->format('d/m') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

        {{-- Viticultores vinculados --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <flux:icon icon="user-group" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900">Viticultores vinculados</span>
                        <flux:badge color="blue" size="sm">{{ $linkedViticulturists }}</flux:badge>
                        @if($pendingViticulturists > 0)
                            <flux:badge color="amber" size="sm">{{ $pendingViticulturists }} pend.</flux:badge>
                        @endif
                    </div>
                    @if(Auth::user()->compra_uva_externa)
                        <a href="{{ route('producer.viticulturists.index') }}" wire:navigate
                            class="text-xs text-agro-600 hover:underline font-medium">Gestionar</a>
                    @endif
                </div>
            </x-slot:header>

            @if($recentViticulturists->isEmpty())
                <div class="py-6 text-center">
                    <flux:icon icon="user-group" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay viticultores vinculados</p>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentViticulturists as $wv)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-sm font-bold text-blue-600">
                                {{ strtoupper(substr($wv->viticulturist?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $wv->viticulturist?->name ?? '—' }}</p>
                                <p class="text-xs text-zinc-400 truncate">{{ $wv->viticulturist?->email ?? '—' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-agro.card>

    </div>

    {{-- Accesos rápidos --}}
    <div>
        <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">Accesos rápidos</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            <a href="{{ route('producer.digital-notebook') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-purple-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                    <flux:icon icon="book-open" class="size-5 text-purple-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Cuaderno campo</p>
                    <p class="text-xs text-zinc-400">Registrar actividades</p>
                </div>
            </a>

            <a href="{{ route('producer.harvests.index') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center group-hover:bg-agro-100 transition-colors">
                    <flux:icon icon="truck" class="size-5 text-agro-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Mis entregas</p>
                    <p class="text-xs text-zinc-400">Vendimia campo</p>
                </div>
            </a>

            <a href="{{ route('producer.grape-reception.create') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center group-hover:bg-agro-100 transition-colors">
                    <flux:icon icon="plus-circle" class="size-5 text-agro-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Nueva recepción</p>
                    <p class="text-xs text-zinc-400">Entrada de uva bodega</p>
                </div>
            </a>

            <a href="{{ route('producer.harvest-summary.index') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <flux:icon icon="chart-bar" class="size-5 text-amber-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Cuadro de mando</p>
                    <p class="text-xs text-zinc-400">Vendimia bodega</p>
                </div>
            </a>

            <a href="{{ route('producer.fermentation-controls.create') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-orange-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                    <flux:icon icon="fire" class="size-5 text-orange-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Control fermentación</p>
                    <p class="text-xs text-zinc-400">Registro de parámetros</p>
                </div>
            </a>

            <a href="{{ route('producer.wine-transfers.create') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-violet-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center group-hover:bg-violet-100 transition-colors">
                    <flux:icon icon="arrows-right-left" class="size-5 text-violet-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Nuevo trasvase</p>
                    <p class="text-xs text-zinc-400">Trasiego / Coupage</p>
                </div>
            </a>

        </div>
    </div>

</div>
