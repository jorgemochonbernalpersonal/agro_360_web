<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Viticultores DO"
        description="Viticultores gestionados directamente por la denominación de origen."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$totalGrowerCount"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            label="Total parcelas activas"
            :value="$plotStatsByVit->sum('plot_count')"
            icon="map"
            color="blue"
        />
        <x-agro.stat-card
            label="Superficie total (ha)"
            :value="number_format($plotStatsByVit->sum('total_area'), 2)"
            icon="square-3-stack-3d"
            color="yellow"
        />
    </div>

    {{-- Search --}}
    <div class="flex items-center gap-2">
        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar viticultor..."
                class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-agro-300 w-52"
            />
        </div>
        @if($search)
            <button wire:click="clearSearch" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
                Limpiar
            </button>
        @endif
    </div>

    {{-- Table --}}
    <x-agro.data-table
        :headers="['Viticultor', 'Bodegas asignadas', 'Parcelas', 'Sup. total (ha)', 'Plantaciones activas']"
        emptyMessage="No hay viticultores adscritos a esta denominación."
    >
        @foreach($growers as $grower)
            @php
                $plots     = $plotStatsByVit[$grower->id]   ?? null;
                $plantings = $activePlantingsByVit[$grower->id] ?? null;
            @endphp
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                    <div>{{ $grower->name }}</div>
                    <div class="text-xs text-zinc-400">{{ $grower->email }}</div>
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $wineryNamesByVit[$grower->id] ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plots?->plot_count ?? 0 }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    @if($plots?->total_area)
                        {{ number_format($plots->total_area, 2) }} ha
                    @else
                        —
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plantings?->planting_count ?? 0 }}
                    @if($plantings?->planted_area)
                        <span class="text-xs text-zinc-400">({{ number_format($plantings->planted_area, 2) }} ha)</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $growers->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
