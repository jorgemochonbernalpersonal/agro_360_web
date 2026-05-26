<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Cumplimiento PAC') }}"
        :description="__('Estado de los datos PAC de las parcelas de los viticultores adscritos al DO.')"
    />

    {{-- Stats globales --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <x-agro.stat-card :label="__('Total parcelas')" :value="$stats->total_plots ?? 0" icon="map" color="agro" />
        <x-agro.stat-card :label="__('Área total (ha)')" :value="number_format($stats->total_area ?? 0, 2)" icon="globe-europe-africa" color="blue" />
        <x-agro.stat-card :label="__('Área elegible (ha)')" :value="number_format($stats->total_eligible ?? 0, 2)" icon="check-circle" color="emerald" />
        <x-agro.stat-card :label="__('Con datos PAC')" :value="$stats->with_pac ?? 0" icon="document-check" color="teal" />
        <x-agro.stat-card :label="__('Sin datos PAC')" :value="$stats->without_pac ?? 0" icon="exclamation-triangle" color="red" />
    </div>

    {{-- Barra de progreso PAC --}}
    @php
        $totalPlots = $stats->total_plots ?? 0;
        $withPac    = $stats->with_pac ?? 0;
        $pct        = $totalPlots > 0 ? round(($withPac / $totalPlots) * 100) : 0;
    @endphp
    <x-agro.card>
        <div class="px-4 py-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-zinc-700">{{ __('Cobertura de datos PAC') }}</span>
                <span class="text-sm font-semibold {{ $pct >= 90 ? 'text-emerald-600' : ($pct >= 60 ? 'text-amber-600' : 'text-red-500') }}">
                    {{ $pct }}%
                </span>
            </div>
            <x-agro.progress-bar :value="$pct" :color="$pct >= 90 ? 'emerald' : ($pct >= 60 ? 'amber' : 'red')" />
            <p class="text-xs text-zinc-400 mt-2">
                {{ $withPac }} de {{ $totalPlots }} parcelas tienen área elegible definida.
                @if($stats->locked_count > 0)
                    · {{ $stats->locked_count }} bloqueadas para declaración PAC.
                @endif
            </p>
        </div>
    </x-agro.card>

    {{-- Card grid: Estado PAC por viticultor --}}
    <div>
        <h2 class="text-sm font-semibold text-zinc-700 mb-3">{{ __('Estado PAC por viticultor') }}</h2>

        @if(count($viticulturists) > 0)
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                wire:loading.class="opacity-60 pointer-events-none"
            >
                @foreach($viticulturists as $vit)
                    @php
                        $row  = $pacByVit[$vit->id] ?? null;
                        $vtot = $row?->total_plots ?? 0;
                        $vmis = $row?->missing_pac ?? 0;
                        $vpct = $vtot > 0 ? round((($vtot - $vmis) / $vtot) * 100) : 0;
                        $delay = min($loop->index * 50, 300);
                    @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="pac-vit-{{ $vit->id }}"
                    >
                        <x-slot:header>
                            <x-agro.card-item-header
                                icon="user"
                                :title="$vit->name"
                                :subtitle="$vtot . ' parcelas'"
                                iconBg="bg-emerald-100"
                                iconColor="text-emerald-600"
                                size="md"
                                radius="xl"
                            >
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium shrink-0
                                    {{ $vpct >= 90 ? 'bg-emerald-50 text-emerald-700' : ($vpct >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $vpct }}%
                                </span>
                            </x-agro.card-item-header>
                        </x-slot:header>

                        <div class="flex-1 space-y-3">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Área') }}</p>
                                    <p class="text-sm font-bold text-blue-700">
                                        {{ number_format($row?->total_area ?? 0, 2) }}
                                        <span class="text-[9px] font-normal text-blue-400">ha</span>
                                    </p>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">{{ __('Elegible') }}</p>
                                    <p class="text-sm font-bold text-emerald-700">
                                        {{ number_format($row?->eligible_area ?? 0, 2) }}
                                        <span class="text-[9px] font-normal text-emerald-400">ha</span>
                                    </p>
                                </div>
                                <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                    <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Sin PAC') }}</p>
                                    <p class="text-sm font-bold {{ $vmis > 0 ? 'text-amber-600' : 'text-zinc-400' }}">
                                        {{ $vmis }}
                                    </p>
                                </div>
                            </div>

                            @if(($row?->locked_plots ?? 0) > 0)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-400">{{ __('Bloqueadas') }}</span>
                                    <span class="text-blue-600 font-medium">{{ $row->locked_plots }}</span>
                                </div>
                            @endif
                        </div>
                    </x-agro.card>
                @endforeach
            </div>
        @else
            <x-agro.empty-state icon="users" title="{{ __('Sin viticultores') }}" :description="__('No hay viticultores adscritos.')" />
        @endif
    </div>

    {{-- Filtros + Parcelas --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVit" :label="__('Viticultor')">
            <option value="">{{ __('Todos') }}</option>
            @foreach($viticulturists as $vit)
                <option value="{{ $vit->id }}">{{ $vit->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterStatus" :label="__('Estado PAC')">
            <option value="">{{ __('Todos los estados') }}</option>
            <option value="missing_pac">{{ __('Sin datos PAC') }}</option>
            <option value="locked">{{ __('Bloqueadas') }}</option>
            <option value="ok">{{ __('Correctas') }}</option>
        </x-agro.filter-select>

        <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">{{ __('Limpiar') }}</button>
    </x-agro.filter-bar>

    {{-- Parcelas Card Grid --}}
    @if($plots->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="filterVit, filterStatus, clearFilters"
        >
            @foreach($plots as $plot)
                @php
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="pac-plot-{{ $plot->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="map"
                            :title="$plot->name"
                            :subtitle="$plot->code_parcel ?: null"
                            iconBg="bg-teal-100"
                            iconColor="text-teal-600"
                            size="md"
                            radius="xl"
                        >
                            @if($plot->is_locked)
                                <flux:badge color="blue" size="sm">{{ __('Bloqueada') }}</flux:badge>
                            @elseif(!$plot->pac_eligible_area)
                                <flux:badge color="yellow" size="sm">{{ __('Pendiente') }}</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">OK</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Área') }}</p>
                                <p class="text-sm font-bold text-zinc-700">{{ number_format($plot->area, 2) }}</p>
                            </div>
                            <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">{{ __('Elegible') }}</p>
                                <p class="text-sm font-bold text-emerald-700">
                                    @if($plot->pac_eligible_area)
                                        {{ number_format($plot->pac_eligible_area, 2) }}
                                    @else
                                        <span class="text-amber-500 text-xs">—</span>
                                    @endif
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Coef.') }}</p>
                                <p class="text-sm font-bold text-blue-700">
                                    {{ $plot->eligibility_coefficient ? number_format($plot->eligibility_coefficient, 4) : '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">{{ __('Viticultor') }}</span>
                            <span class="text-zinc-700 font-medium truncate ml-2">{{ $plot->viticulturist?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">{{ __('Municipio') }}</span>
                            <span class="text-zinc-600 truncate ml-2">{{ $plot->municipality?->name ?? '—' }}</span>
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$plots" />
    @else
        <x-agro.empty-state
            icon="map"
            title="{{ __('Sin parcelas') }}"
            :description="__('No hay parcelas con los filtros seleccionados.')"
        />
    @endif

</div>
