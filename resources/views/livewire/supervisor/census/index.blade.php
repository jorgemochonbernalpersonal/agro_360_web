<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Censo"
        description="Bodegas y viticultores adscritos a la denominación de origen."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <x-agro.stat-card
            label="Bodegas adscritas"
            :value="$wineryCount"
            icon="building-office-2"
            color="blue"
        />
        <x-agro.stat-card
            label="Viticultores DO"
            :value="$viticulturistCount"
            icon="users"
            color="agro"
        />
    </div>

    {{-- Search bar --}}
    <div class="flex items-center gap-2">
        <div class="relative flex-1 max-w-xs">
            <flux:icon icon="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar..."
                class="pl-9 pr-3 py-1.5 w-full text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300"
            />
        </div>
        @if($search)
            <button wire:click="clearSearch" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
                Limpiar
            </button>
        @endif
    </div>

    {{-- Tabs + Table --}}
    <div>
        <x-agro.tabs
            :tabs="[
                'wineries'       => ['label' => 'Bodegas',      'count' => $wineryCount],
                'viticulturists' => ['label' => 'Viticultores', 'count' => $viticulturistCount],
            ]"
            :active="$currentTab"
        />

        @if($currentTab === 'wineries')
            <x-agro.data-table
                :headers="['Bodega', 'Email', 'Viticultores DO']"
                emptyMessage="No hay bodegas adscritas a esta denominación."
            >
                @foreach($items as $winery)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                            {{ $winery->name }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $winery->email }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $vitCountByWinery[$winery->id] ?? 0 }}
                        </td>
                    </tr>
                @endforeach

                <x-slot name="pagination">
                    {{ $items->links() }}
                </x-slot>
            </x-agro.data-table>
        @else
            <x-agro.data-table
                :headers="['Viticultor', 'Email', 'Parcelas']"
                emptyMessage="No hay viticultores adscritos a esta denominación."
            >
                @foreach($items as $viticulturist)
                    <tr class="hover:bg-zinc-50 transition">
                        <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                            {{ $viticulturist->name }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $viticulturist->email }}
                        </td>
                        <td class="px-6 py-3 text-sm text-zinc-500">
                            {{ $viticulturist->plots_count }}
                        </td>
                    </tr>
                @endforeach

                <x-slot name="pagination">
                    {{ $items->links() }}
                </x-slot>
            </x-agro.data-table>
        @endif
    </div>

</div>
