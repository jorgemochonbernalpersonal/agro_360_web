<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Superficies Admisibles PAC"
        description="Revisa y actualiza la superficie elegible PAC de cada parcela."
    />

    <x-agro.stats-section key="pac-surfaces">
        <x-agro.stat-card label="Total parcelas" :value="$stats['total']" icon="map" color="zinc" />
        <x-agro.stat-card label="Con datos PAC" :value="$stats['with_pac']" icon="check-circle" color="green" />
        <x-agro.stat-card label="Sin datos PAC" :value="$stats['without_pac']" icon="exclamation-triangle" color="amber" />
        <x-agro.stat-card label="Superficie admisible" :value="number_format($stats['total_eligible'], 2) . ' ha'" icon="globe-alt" color="agro" />
    </x-agro.stats-section>

    <x-agro.filter-bar :active-count="collect([$search, $filterPac])->filter()->count()">
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar parcela..." />
        <x-agro.filter-select wire:model.live="filterPac" label="Datos PAC">
            <option value="">Todas</option>
            <option value="with">Con datos PAC</option>
            <option value="without">Sin datos PAC</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.loading-grid target="search, filterPac" />
    <div wire:loading.remove wire:target="search, filterPac">
        @if($plots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($plots as $plot)
                    @php
                        $delay = min($loop->index * 50, 300);
                        $eligible = (float) ($plot->pac_eligible_area ?? 0);
                        $area     = (float) ($plot->area ?? 0);
                        $coef     = $area > 0 && $eligible > 0 ? round($eligible / $area, 4) : null;
                        $hasPac   = $plot->pac_eligible_area !== null;
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
                                    <p class="text-xs text-zinc-500">{{ $plot->municipality?->name ?? 'Sin municipio' }}</p>
                                </div>
                                @if($hasPac)
                                    <flux:badge color="green" size="sm" class="shrink-0">PAC</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm" class="shrink-0">Sin PAC</flux:badge>
                                @endif
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Área total</p>
                                    <p class="text-lg font-bold text-agro-700 leading-none">{{ $area > 0 ? number_format($area, 3) : '—' }}</p>
                                    @if($area > 0)<p class="text-[10px] text-agro-400 mt-0.5">ha</p>@endif
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Admisible</p>
                                    @if($hasPac)
                                        <p class="text-lg font-bold text-green-700 leading-none">{{ number_format($eligible, 3) }}</p>
                                        <p class="text-[10px] text-agro-400 mt-0.5">ha</p>
                                    @else
                                        <p class="text-lg font-bold text-amber-500 leading-none">—</p>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                @if($plot->non_eligible_area)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">No admisible</span>
                                        <span class="text-red-600 font-medium">{{ number_format($plot->non_eligible_area, 3) }} ha</span>
                                    </div>
                                @endif
                                @if($coef !== null)
                                    <div class="flex items-center justify-between">
                                        <span class="text-zinc-400">Coef. admis.</span>
                                        <span class="font-mono font-medium {{ $coef < 0.9 ? 'text-amber-600' : 'text-zinc-700' }}">{{ number_format($coef, 4) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ route('plots.edit', $plot) }}" wire:navigate class="{{ $btnBase }}" title="Editar">
                                    <flux:icon icon="pencil" class="size-4" />
                                </a>
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
            <div class="mt-6">
                <x-agro.pagination :paginator="$plots" />
            </div>
        @else
            <x-agro.empty-state
                icon="map"
                title="No hay parcelas"
                description="Crea tus parcelas para gestionar sus superficies PAC."
            />
        @endif
    </div>

</div>
