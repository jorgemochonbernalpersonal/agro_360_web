<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Parcelas del DO"
        description="Todas las parcelas activas de los viticultores adscritos a la denominación."
    />

    {{-- Stats globales --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <x-agro.stat-card label="Parcelas" :value="$globalStats->total_plots ?? 0" icon="map" color="agro" />
        <x-agro.stat-card label="Área total (ha)" :value="number_format($globalStats->total_area ?? 0, 2)" icon="globe-europe-africa" color="blue" />
        <x-agro.stat-card label="Área elegible (ha)" :value="number_format($globalStats->total_eligible ?? 0, 2)" icon="check-circle" color="emerald" />
        <x-agro.stat-card label="Bloqueadas (PAC)" :value="$globalStats->locked_count ?? 0" icon="lock-closed" color="yellow" />
        <x-agro.stat-card label="Sin datos PAC" :value="$globalStats->without_pac ?? 0" icon="exclamation-triangle" color="red" />
        <x-agro.stat-card label="Ecológicas" :value="$globalStats->organic_count ?? 0" icon="sparkles" color="teal" />
    </div>

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Nombre o referencia..." />

        <x-agro.filter-select wire:model.live="filterVit" label="Viticultor">
            <option value="">Todos los viticultores</option>
            @foreach($viticulturists as $vit)
                <option value="{{ $vit->id }}">{{ $vit->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterOrganic" label="Tipo">
            <option value="">Todas</option>
            <option value="1">Ecológicas</option>
            <option value="0">Convencionales</option>
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterLocked" label="Estado PAC">
            <option value="">Cualquier estado</option>
            <option value="1">Bloqueadas</option>
            <option value="0">Sin bloquear</option>
        </x-agro.filter-select>

        <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
            Limpiar
        </button>
    </x-agro.filter-bar>

    {{-- Card Grid --}}
    @if($plots->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="search, filterVit, filterOrganic, filterLocked, clearFilters"
        >
            @foreach($plots as $plot)
                @php
                    $delay = min($loop->index * 50, 300);
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="plot-{{ $plot->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                <flux:icon icon="map" class="size-5 text-emerald-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $plot->name }}</h3>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($plot->code_parcel)
                                        <p class="text-xs text-zinc-400">{{ $plot->code_parcel }}</p>
                                    @endif
                                    @if($plot->is_organic)
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-600 border border-green-200">ECO</span>
                                    @endif
                                </div>
                            </div>
                            @if($plot->is_locked)
                                <flux:badge color="blue" size="sm" class="shrink-0">Bloqueada</flux:badge>
                            @else
                                <flux:badge color="green" size="sm" class="shrink-0">Activa</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        {{-- Metric boxes --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-emerald-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-emerald-400 uppercase tracking-wide mb-0.5">Área</p>
                                <p class="text-sm font-bold text-emerald-700">
                                    {{ number_format($plot->area, 2) }}
                                    <span class="text-[9px] font-normal text-emerald-400">ha</span>
                                </p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">Elegible</p>
                                <p class="text-sm font-bold text-blue-700">
                                    @if($plot->pac_eligible_area)
                                        {{ number_format($plot->pac_eligible_area, 2) }}
                                        <span class="text-[9px] font-normal text-blue-400">ha</span>
                                    @else
                                        <span class="text-amber-500 text-xs font-medium">Sin datos</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Key-value rows --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">Viticultor</span>
                            <span class="text-zinc-700 font-medium truncate ml-2">{{ $plot->viticulturist?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">Municipio</span>
                            <span class="text-zinc-600">{{ $plot->municipality?->name ?? '—' }}</span>
                        </div>

                        {{-- Varieties --}}
                        @if($plot->plantings->isNotEmpty())
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Variedades</span>
                                <div class="flex flex-wrap gap-1 justify-end">
                                    @foreach($plot->plantings->take(2) as $planting)
                                        <span class="px-1.5 py-0.5 rounded text-xs bg-zinc-100 text-zinc-600">
                                            {{ $planting->grapeVariety?->name ?? '—' }}
                                        </span>
                                    @endforeach
                                    @if($plot->plantings->count() > 2)
                                        <span class="text-xs text-zinc-400">+{{ $plot->plantings->count() - 2 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Last activity --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-400">Ultima actividad</span>
                            <span class="text-zinc-500">
                                @if($plot->lastAgriculturalActivity)
                                    {{ $plot->lastAgriculturalActivity->activity_date->format('d/m/Y') }}
                                @else
                                    <span class="text-zinc-300">Sin actividad</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$plots" />
    @else
        <x-agro.empty-state
            icon="map"
            title="No hay parcelas"
            description="No se encontraron parcelas con los filtros seleccionados."
        />
    @endif

</div>
