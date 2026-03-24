<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Cumplimiento PAC"
        description="Estado de los datos PAC de las parcelas de los viticultores adscritos al DO."
    />

    {{-- Stats globales --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <x-agro.stat-card label="Total parcelas" :value="$stats->total_plots ?? 0" icon="map" color="agro" />
        <x-agro.stat-card label="Área total (ha)" :value="number_format($stats->total_area ?? 0, 2)" icon="globe-europe-africa" color="blue" />
        <x-agro.stat-card label="Área elegible (ha)" :value="number_format($stats->total_eligible ?? 0, 2)" icon="check-circle" color="emerald" />
        <x-agro.stat-card label="Con datos PAC" :value="$stats->with_pac ?? 0" icon="document-check" color="teal" />
        <x-agro.stat-card label="Sin datos PAC" :value="$stats->without_pac ?? 0" icon="exclamation-triangle" color="red" />
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
                <span class="text-sm font-medium text-zinc-700">Cobertura de datos PAC</span>
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

    {{-- Tabla por viticultor --}}
    <x-agro.card>
        <x-slot name="header">
            <span class="font-medium text-zinc-700">Estado PAC por viticultor</span>
        </x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100">
                        <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Viticultor</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Parcelas</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Área total</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Área elegible</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-zinc-400 uppercase tracking-wide">Sin PAC</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-zinc-400 uppercase tracking-wide">Bloqueadas</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-zinc-400 uppercase tracking-wide">Cobertura</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @forelse($viticulturists as $vit)
                        @php
                            $row  = $pacByVit[$vit->id] ?? null;
                            $vtot = $row?->total_plots ?? 0;
                            $vmis = $row?->missing_pac ?? 0;
                            $vpct = $vtot > 0 ? round((($vtot - $vmis) / $vtot) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-zinc-50 transition">
                            <td class="px-4 py-3 font-medium text-zinc-800">{{ $vit->name }}</td>
                            <td class="px-4 py-3 text-right text-zinc-600">{{ $vtot }}</td>
                            <td class="px-4 py-3 text-right text-zinc-600">{{ number_format($row?->total_area ?? 0, 2) }} ha</td>
                            <td class="px-4 py-3 text-right text-zinc-600">{{ number_format($row?->eligible_area ?? 0, 2) }} ha</td>
                            <td class="px-4 py-3 text-center">
                                @if($vmis > 0)
                                    <span class="text-amber-600 font-medium">{{ $vmis }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(($row?->locked_plots ?? 0) > 0)
                                    <span class="text-blue-600 font-medium">{{ $row->locked_plots }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $vpct >= 90 ? 'bg-emerald-50 text-emerald-700' : ($vpct >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $vpct }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-zinc-400">Sin viticultores</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-agro.card>

    {{-- Filtros + Tabla de parcelas --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVit" label="Viticultor">
            <option value="">Todos</option>
            @foreach($viticulturists as $vit)
                <option value="{{ $vit->id }}">{{ $vit->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterStatus" label="Estado PAC">
            <option value="">Todos los estados</option>
            <option value="missing_pac">Sin datos PAC</option>
            <option value="locked">Bloqueadas</option>
            <option value="ok">Correctas</option>
        </x-agro.filter-select>

        <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
            Limpiar
        </button>
    </x-agro.filter-bar>

    <x-agro.data-table
        :headers="['Parcela', 'Viticultor', 'Municipio', 'Área (ha)', 'Elegible (ha)', 'Coef.', 'Estado']"
        emptyMessage="No hay parcelas con los filtros seleccionados."
    >
        @foreach($plots as $plot)
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3">
                    <div class="text-sm font-medium text-zinc-800">{{ $plot->name }}</div>
                    @if($plot->code_parcel)
                        <div class="text-xs text-zinc-400">{{ $plot->code_parcel }}</div>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-600">{{ $plot->viticulturist?->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm text-zinc-500">{{ $plot->municipality?->name ?? '—' }}</td>
                <td class="px-6 py-3 text-sm text-zinc-700">{{ number_format($plot->area, 2) }}</td>
                <td class="px-6 py-3 text-sm">
                    @if($plot->pac_eligible_area)
                        {{ number_format($plot->pac_eligible_area, 2) }}
                    @else
                        <span class="text-amber-500 text-xs font-medium">Sin datos</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plot->eligibility_coefficient ? number_format($plot->eligibility_coefficient, 4) : '—' }}
                </td>
                <td class="px-6 py-3">
                    @if($plot->is_locked)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                            <flux:icon icon="lock-closed" class="size-3" /> Bloqueada
                        </span>
                    @elseif(!$plot->pac_eligible_area)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">
                            Pendiente PAC
                        </span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                            OK
                        </span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $plots->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
