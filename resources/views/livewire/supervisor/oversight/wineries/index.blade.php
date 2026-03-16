<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Supervisión — Bodegas"
        description="Actividad de vendimia y viticultores por bodega adscrita."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card
            label="Bodegas supervisadas"
            :value="$totalWineries"
            icon="building-office-2"
            color="blue"
        />
        <x-agro.stat-card
            label="Total uva recibida (kg)"
            :value="number_format($harvestStats->sum('total_kg'), 0, ',', '.')"
            icon="scale"
            color="agro"
            :description="'Vendimia ' . $vintage"
        />
        <x-agro.stat-card
            label="Recepciones de uva"
            :value="$harvestStats->sum('reception_count')"
            icon="inbox"
            color="yellow"
            :description="'Vendimia ' . $vintage"
        />
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar bodega..."
                class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 w-52"
            />
        </div>

        @if($availableVintages->isNotEmpty())
            <select
                wire:model.live="vintageFilter"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
                @foreach($availableVintages as $v)
                    <option value="{{ $v }}">Vendimia {{ $v }}</option>
                @endforeach
            </select>
        @endif

        @if($search || $vintageFilter)
            <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
                Limpiar filtros
            </button>
        @endif
    </div>

    {{-- Table --}}
    <x-agro.data-table
        :headers="['Bodega', 'Viticultores DO', 'Recepciones ' . $vintage, 'Uva recibida (kg)']"
        emptyMessage="No hay bodegas adscritas a esta denominación."
    >
        @foreach($wineries as $winery)
            @php
                $stats = $harvestStats[$winery->id] ?? null;
            @endphp
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                    {{ $winery->name }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $vitCountByWinery[$winery->id] ?? 0 }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $stats?->reception_count ?? '—' }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    @if($stats?->total_kg)
                        {{ number_format($stats->total_kg, 0, ',', '.') }} kg
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $wineries->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
