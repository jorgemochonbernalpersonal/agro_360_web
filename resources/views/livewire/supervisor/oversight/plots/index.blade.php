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
        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Nombre o referencia..."
                class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 w-52"
            />
        </div>

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

    {{-- Tabla --}}
    <x-agro.data-table
        :headers="['Parcela', 'Viticultor', 'Municipio', 'Variedad/es', 'Área (ha)', 'Elegible (ha)', 'Estado', 'Última actividad']"
        emptyMessage="No se encontraron parcelas con los filtros seleccionados."
    >
        @foreach($plots as $plot)
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3">
                    <div class="text-sm font-medium text-zinc-800">{{ $plot->name }}</div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        @if($plot->code_parcel)
                            <span class="text-xs text-zinc-400">{{ $plot->code_parcel }}</span>
                        @endif
                        @if($plot->is_organic)
                            <span class="inline-flex px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-600 border border-green-200">ECO</span>
                        @endif
                        @if($plot->is_locked)
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-600 border border-blue-200">
                                <flux:icon icon="lock-closed" class="size-3" />PAC
                            </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-3 text-sm text-zinc-600">
                    {{ $plot->viticulturist?->name ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plot->municipality?->name ?? '—' }}
                </td>
                <td class="px-6 py-3">
                    @if($plot->plantings->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            @foreach($plot->plantings->take(2) as $planting)
                                <span class="px-1.5 py-0.5 rounded text-xs bg-zinc-100 text-zinc-600">
                                    {{ $planting->grapeVariety?->name ?? '—' }}
                                </span>
                            @endforeach
                            @if($plot->plantings->count() > 2)
                                <span class="text-xs text-zinc-400">+{{ $plot->plantings->count() - 2 }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-sm text-zinc-300">—</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-700 font-medium">
                    {{ number_format($plot->area, 2) }}
                </td>
                <td class="px-6 py-3 text-sm">
                    @if($plot->pac_eligible_area)
                        <span class="text-zinc-600">{{ number_format($plot->pac_eligible_area, 2) }}</span>
                    @else
                        <span class="text-amber-500 text-xs">Sin datos</span>
                    @endif
                </td>
                <td class="px-6 py-3">
                    @if($plot->is_locked)
                        <x-agro.status-badge status="locked" label="Bloqueada" color="blue" />
                    @else
                        <x-agro.status-badge status="active" label="Activa" color="green" />
                    @endif
                </td>
                <td class="px-6 py-3 text-xs text-zinc-400">
                    @if($plot->lastAgriculturalActivity)
                        {{ $plot->lastAgriculturalActivity->activity_date->format('d/m/Y') }}
                    @else
                        <span class="text-zinc-300">Sin actividad</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $plots->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
