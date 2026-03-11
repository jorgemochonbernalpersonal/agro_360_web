<div class="space-y-6 animate-fade-in">

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
            <flux:button variant="outline" icon="map" href="{{ route('plots.index') }}" wire:navigate size="sm">
                Mis parcelas
            </flux:button>
            <flux:button variant="primary" icon="plus" href="{{ route('winery.grape-reception.create') }}" wire:navigate size="sm">
                Nueva recepción
            </flux:button>
        </div>
    </div>

    {{-- KPI bar: hoy en bodega --}}
    @if($todayKg > 0 || $todayCount > 0)
        <div class="bg-agro-600 rounded-2xl p-4 text-white">
            <p class="text-xs font-semibold uppercase tracking-widest text-agro-200 mb-3">Bodega · Hoy</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

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

    {{-- Contenido principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

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
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todas →</a>
                </div>
            </x-slot:header>

            @if($recentActivities->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="clipboard-document-list" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay actividades registradas</p>
                    <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate
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
                    <a href="{{ route('winery.grape-reception.index') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline font-medium">Ver todas →</a>
                </div>
            </x-slot:header>

            @if($recentReceptions->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon icon="archive-box-arrow-down" class="size-10 text-zinc-200 mx-auto mb-2" />
                    <p class="text-sm text-zinc-400">No hay recepciones en la campaña activa</p>
                    <a href="{{ route('winery.grape-reception.create') }}" wire:navigate
                        class="text-xs text-agro-600 hover:underline mt-1 inline-block">
                        Registrar primera recepción →
                    </a>
                </div>
            @else
                <div class="space-y-1">
                    @foreach($recentReceptions as $r)
                        <a href="{{ route('winery.grape-reception.show', $r) }}" wire:navigate
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

    {{-- Accesos rápidos --}}
    <div>
        <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wide mb-3">Accesos rápidos</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            <a href="{{ route('viticulturist.digital-notebook') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-purple-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                    <flux:icon icon="book-open" class="size-5 text-purple-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Cuaderno campo</p>
                    <p class="text-xs text-zinc-400">Registrar actividades</p>
                </div>
            </a>

            <a href="{{ route('viticulturist.harvests.index') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center group-hover:bg-agro-100 transition-colors">
                    <flux:icon icon="truck" class="size-5 text-agro-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Mis entregas</p>
                    <p class="text-xs text-zinc-400">Vendimia campo</p>
                </div>
            </a>

            <a href="{{ route('winery.grape-reception.create') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-agro-50 flex items-center justify-center group-hover:bg-agro-100 transition-colors">
                    <flux:icon icon="plus-circle" class="size-5 text-agro-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Nueva recepción</p>
                    <p class="text-xs text-zinc-400">Entrada de uva bodega</p>
                </div>
            </a>

            <a href="{{ route('winery.harvest-summary.index') }}" wire:navigate
                class="flex items-center gap-3 p-4 bg-white border border-zinc-200 rounded-2xl hover:shadow-md hover:border-agro-300 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <flux:icon icon="chart-bar" class="size-5 text-amber-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">Cuadro de mando</p>
                    <p class="text-xs text-zinc-400">Vendimia bodega</p>
                </div>
            </a>

        </div>
    </div>

</div>
