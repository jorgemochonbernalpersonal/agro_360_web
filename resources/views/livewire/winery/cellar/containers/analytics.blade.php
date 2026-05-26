<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Analítica de Contenedores') }}"
        :description="__('Visión global del inventario: ocupación, distribución y estado')"
    >
        <x-slot:actions>
            <flux:button wire:click="exportCsv" variant="ghost" icon="arrow-down-tray">{{ __('Exportar CSV') }}</flux:button>
            <flux:button href="{{ roleRoute('containers.index') }}" variant="outline" icon="beaker">
                Ver contenedores
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <flux:select wire:model.live="typeFilter" size="sm" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($types as $t)
                <flux:select.option value="{{ $t->id }}">{{ $t->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="roomFilter" size="sm" class="w-44">
            <flux:select.option value="">{{ __('Todas las salas') }}</flux:select.option>
            @foreach($rooms as $r)
                <flux:select.option value="{{ $r->id }}">{{ $r->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </x-agro.filter-bar>

    {{-- ── KPIs ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <x-agro.stat-card :label="__('Total depósitos')"    :value="$total"        icon="cube"                color="zinc" />
        <x-agro.stat-card :label="__('Con vino')"           :value="$withWine"     icon="beaker"              color="violet" />
        <x-agro.stat-card :label="__('Con uva/cosecha')"    :value="$withHarvest"  icon="scale"               color="amber" />
        <x-agro.stat-card :label="__('Ambos contenidos')"   :value="$bothContent"  icon="arrows-right-left"   color="blue" />
        <x-agro.stat-card :label="__('Vacíos')"             :value="$empty"        icon="cube"                color="agro" />
        <x-agro.stat-card :label="__('Ocupación global')"   :value="$globalPct . '%'" icon="chart-bar"        color="{{ $globalPct > 85 ? 'red' : ($globalPct > 60 ? 'amber' : 'agro') }}" />
    </div>

    {{-- Capacidad global --}}
    <x-agro.card>
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">{{ __('Capacidad total') }}</p>
                <p class="text-2xl font-bold text-zinc-700">{{ number_format($totalCapacity, 0) }} <span class="text-sm font-normal text-zinc-400">kg</span></p>
            </div>
            <div>
                <p class="text-xs text-amber-400 uppercase tracking-wide mb-1">{{ __('Uva en depósitos') }}</p>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($usedHarvest, 0) }} <span class="text-sm font-normal text-amber-400">kg</span></p>
            </div>
            <div>
                <p class="text-xs text-violet-400 uppercase tracking-wide mb-1">{{ __('Vino elaborado') }}</p>
                <p class="text-2xl font-bold text-violet-600">{{ number_format($usedWine, 1) }} <span class="text-sm font-normal text-violet-400">L</span></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex justify-between text-xs text-zinc-400 mb-1">
                <span>{{ __('Ocupación global') }}</span>
                <span class="font-semibold text-zinc-600">{{ $globalPct }}%</span>
            </div>
            <div class="w-full h-3 bg-zinc-100 rounded-full overflow-hidden flex">
                @if($totalCapacity > 0)
                    <div class="h-full bg-amber-400 transition-all" style="width: {{ min(($usedHarvest / $totalCapacity) * 100, 100) }}%"></div>
                    <div class="h-full bg-violet-500 transition-all" style="width: {{ min(($usedWine / $totalCapacity) * 100, 100 - min(($usedHarvest / $totalCapacity)*100,100)) }}%"></div>
                @endif
            </div>
            <div class="flex gap-4 mt-1.5 text-xs text-zinc-400">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Uva</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-violet-500 inline-block"></span>Vino</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-zinc-200 inline-block"></span>Libre</span>
            </div>
        </div>
    </x-agro.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── Buckets de utilización ──────────────────────────────────── --}}
        <x-agro.card title="{{ __('Distribución por ocupación') }}">
            @php
                $bucketColors = [
                    '0-20'   => 'bg-agro-400',
                    '20-40'  => 'bg-blue-400',
                    '40-60'  => 'bg-amber-400',
                    '60-80'  => 'bg-orange-400',
                    '80-100' => 'bg-red-400',
                    '>100'   => 'bg-red-700',
                ];
                $bucketLabels = [
                    '0-20'   => '0 – 20%',
                    '20-40'  => '20 – 40%',
                    '40-60'  => '40 – 60%',
                    '60-80'  => '60 – 80%',
                    '80-100' => '80 – 100%',
                    '>100'   => 'Excedido',
                ];
            @endphp
            <div class="space-y-3">
                @foreach($buckets as $key => $count)
                <div class="flex items-center gap-3">
                    <span class="text-xs text-zinc-500 w-20 shrink-0">{{ $bucketLabels[$key] }}</span>
                    <div class="flex-1 h-5 bg-zinc-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $bucketColors[$key] }} rounded-full transition-all"
                            style="width: {{ $bucketsMax > 0 ? round($count / $bucketsMax * 100) : 0 }}%">
                        </div>
                    </div>
                    <span class="text-sm font-bold text-zinc-600 w-6 text-right">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </x-agro.card>

        {{-- ── Por tipo ──────────────────────────────────────────────── --}}
        <x-agro.card title="{{ __('Por tipo de contenedor') }}">
            @if($byType->count())
            <div class="space-y-3">
                @foreach($byType as $row)
                @php
                    $pct = $row->total_cap > 0 ? min(round(($row->harvest_used + $row->wine_used) / $row->total_cap * 100), 100) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-zinc-700">{{ $row->name }}</span>
                        <span class="text-zinc-400">{{ $row->count }} uds · {{ $pct }}%</span>
                    </div>
                    <div class="w-full h-2.5 bg-zinc-100 rounded-full overflow-hidden">
                        <div class="h-full bg-agro-500 rounded-full transition-all"
                            style="width: {{ round($row->count / $typeMax * 100) }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-sm text-zinc-400 text-center py-4">{{ __('Sin datos') }}</p>
            @endif
        </x-agro.card>

        {{-- ── Por sala ──────────────────────────────────────────────── --}}
        <x-agro.card title="{{ __('Por sala / ubicación') }}">
            @if($byRoom->count())
            <div class="space-y-3">
                @foreach($byRoom as $row)
                @php
                    $pct = $row->total_cap > 0 ? min(round($row->used / $row->total_cap * 100), 100) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-zinc-700">{{ $row->room }}</span>
                        <span class="text-zinc-400">{{ $row->count }} · {{ $pct }}% ocup.</span>
                    </div>
                    <div class="w-full h-2.5 bg-zinc-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-400 rounded-full transition-all"
                            style="width: {{ round($row->count / $roomMax * 100) }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <p class="text-sm text-zinc-400 text-center py-4">{{ __('Sin salas configuradas') }}</p>
            @endif
        </x-agro.card>

        {{-- ── Top 5 más llenos ──────────────────────────────────────── --}}
        <x-agro.card title="{{ __('Top 5 más llenos') }}">
            @forelse($topFilled as $c)
            @php
                $used = $c->used_capacity + $c->wine_volume_liters;
                $pct  = $c->capacity > 0 ? min(round($used / $c->capacity * 100, 1), 100) : 0;
            @endphp
            <div class="flex items-center gap-3 py-2 border-b border-zinc-50 last:border-0">
                <a href="{{ roleRoute('containers.show', $c) }}" class="flex-1 min-w-0 hover:text-agro-600 transition-colors">
                    <p class="font-medium text-zinc-800 text-sm truncate">{{ $c->name }}</p>
                    <p class="text-xs text-zinc-400">{{ $c->containerType?->name ?? '' }} · {{ $c->containerRoom?->name ?? 'Sin sala' }}</p>
                </a>
                <div class="text-right shrink-0">
                    <span class="text-sm font-bold {{ $pct >= 90 ? 'text-red-600' : 'text-zinc-700' }}">{{ $pct }}%</span>
                    <p class="text-xs text-zinc-400">{{ number_format($used, 0) }} / {{ number_format($c->capacity, 0) }}</p>
                </div>
            </div>
            @empty
                <p class="text-sm text-zinc-400 text-center py-4">{{ __('Sin contenedores con contenido') }}</p>
            @endforelse
        </x-agro.card>

    </div>

    {{-- ── Top 5 más vacíos ──────────────────────────────────────────────── --}}
    <x-agro.card title="{{ __('Top 5 con mayor capacidad libre') }}">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
            @forelse($topEmpty as $c)
            @php
                $used  = $c->used_capacity + $c->wine_volume_liters;
                $free  = max(0, $c->capacity - $used);
                $pct   = $c->capacity > 0 ? round($used / $c->capacity * 100, 1) : 0;
            @endphp
            <a href="{{ roleRoute('containers.show', $c) }}"
                class="bg-agro-50 hover:bg-agro-100 rounded-xl p-4 text-center transition-colors block">
                <flux:icon icon="cube" class="size-6 text-agro-400 mx-auto mb-2" />
                <p class="font-semibold text-zinc-700 text-sm truncate">{{ $c->name }}</p>
                <p class="text-xs text-zinc-400 mb-2">{{ $c->containerType?->name ?? '' }}</p>
                <p class="text-lg font-bold text-agro-600">{{ number_format($free, 0) }}<span class="text-xs font-normal text-agro-400">{{ __('kg libre') }}</span></p>
                <p class="text-xs text-zinc-400">{{ $pct }}% ocupado</p>
            </a>
            @empty
                <p class="text-sm text-zinc-400 col-span-5 text-center py-4">{{ __('Sin datos') }}</p>
            @endforelse
        </div>
    </x-agro.card>

</div>
