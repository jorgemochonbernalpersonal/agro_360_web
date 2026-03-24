<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Supervisión — Viticultores"
        description="Parcelas, plantaciones y actividad de los viticultores adscritos a la denominación."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$totalGrowers"
            icon="users"
            color="agro"
        />
        <x-agro.stat-card
            label="Total parcelas"
            :value="$growers->sum('plots_count')"
            icon="map"
            color="blue"
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
                class="pl-9 pr-3 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 w-52"
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
        :headers="['Viticultor', 'Parcelas', 'Plantaciones activas', 'Área total (ha)', 'Última actividad']"
        emptyMessage="No hay viticultores adscritos a esta denominación."
    >
        @foreach($growers as $grower)
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                    <a href="{{ route('supervisor.oversight.growers.show', $grower) }}" wire:navigate
                       class="hover:text-emerald-700 transition">{{ $grower->name }}</a>
                    <div class="text-xs text-zinc-400">{{ $grower->email }}</div>
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $grower->plots_count }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    {{ $plantingCountByVit[$grower->id] ?? 0 }}
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    @if($grower->plots_sum_area)
                        {{ number_format($grower->plots_sum_area, 2) }} ha
                    @else
                        —
                    @endif
                </td>
                <td class="px-6 py-3 text-sm text-zinc-500">
                    @if(isset($lastActivityByVit[$grower->id]))
                        {{ \Carbon\Carbon::parse($lastActivityByVit[$grower->id])->format('d/m/Y') }}
                    @else
                        <span class="text-zinc-300">Sin actividad</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $growers->links() }}
        </x-slot>
    </x-agro.data-table>

</div>
