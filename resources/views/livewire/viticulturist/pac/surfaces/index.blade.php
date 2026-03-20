<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Superficies Admisibles PAC"
        description="Revisa y actualiza la superficie elegible PAC de cada parcela."
    />

    <div x-data="{
        open: localStorage.getItem('pac-surfaces-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('pac-surfaces-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card label="Total parcelas" :value="$stats['total']" icon="map" color="zinc" />
            <x-agro.stat-card label="Con datos PAC" :value="$stats['with_pac']" icon="check-circle" color="green" />
            <x-agro.stat-card label="Sin datos PAC" :value="$stats['without_pac']" icon="exclamation-triangle" color="amber" />
            <x-agro.stat-card label="Superficie admisible" :value="number_format($stats['total_eligible'], 2) . ' ha'" icon="globe-alt" color="agro" />
        </div>
        </div>
    </div>

    <x-agro.filter-bar :active-count="collect([$search, $filterPac])->filter()->count()">
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar parcela..." />
        <x-agro.filter-select wire:model.live="filterPac" label="Datos PAC">
            <option value="">Todas</option>
            <option value="with">Con datos PAC</option>
            <option value="without">Sin datos PAC</option>
        </x-agro.filter-select>
    </x-agro.filter-bar>

    @if($plots->isEmpty())
        <x-agro.empty-state
            icon="map"
            title="No hay parcelas"
            description="Crea tus parcelas para gestionar sus superficies PAC."
        />
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Parcela', 'Municipio', 'Área total', 'Sup. admisible PAC', 'No admisible', 'Coef. admis.', '']">
                @foreach($plots as $plot)
                    @php
                        $eligible = (float) ($plot->pac_eligible_area ?? 0);
                        $area     = (float) ($plot->area ?? 0);
                        $coef     = $area > 0 && $eligible > 0 ? round($eligible / $area, 4) : null;
                        $hasPac   = $plot->pac_eligible_area !== null;
                    @endphp
                    <x-agro.table-row wire:key="plot-{{ $plot->id }}">
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-900">{{ $plot->name }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $plot->municipality?->name ?? '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $area > 0 ? number_format($area, 3) . ' ha' : '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($hasPac)
                                <span class="font-medium text-green-700">{{ number_format($eligible, 3) }} ha</span>
                            @else
                                <x-agro.status-badge color="amber" label="Sin definir" />
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($plot->non_eligible_area)
                                <span class="text-red-600 text-sm">{{ number_format($plot->non_eligible_area, 3) }} ha</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($coef !== null)
                                <span class="font-mono text-sm {{ $coef < 0.9 ? 'text-amber-600' : 'text-zinc-700' }}">
                                    {{ number_format($coef, 4) }}
                                </span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button size="sm" variant="ghost" icon="pencil"
                                href="{{ route('plots.edit', $plot) }}" wire:navigate />
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>

        <x-agro.pagination :paginator="$plots" />
    @endif

</div>

