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

    {{-- Search + Assign button --}}
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

        @if($currentTab === 'wineries')
            <flux:button wire:click="openAssignModal" variant="primary" size="sm" icon="plus" class="ml-auto">
                Adscribir bodega
            </flux:button>
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
                :headers="['Bodega', 'Email', 'Viticultores DO', '']"
                emptyMessage="No hay bodegas adscritas a esta denominación."
            >
                @foreach($items as $winery)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <a href="{{ route('supervisor.oversight.wineries.show', $winery) }}" wire:navigate
                               class="font-medium text-zinc-800 hover:text-indigo-600 transition">
                                {{ $winery->name }}
                            </a>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="text-zinc-500">{{ $winery->email }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $vitCountByWinery[$winery->id] ?? 0 }}
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <flux:button
                                wire:click="unassignWinery({{ $winery->id }})"
                                wire:confirm="¿Desadscribir {{ $winery->name }} de la denominación? Los viticultores asignados por la DO a esta bodega también se desvincularán."
                                variant="ghost"
                                size="sm"
                                icon="x-mark"
                            >
                                Desadscribir
                            </flux:button>
                        </x-agro.table-cell>
                    </x-agro.table-row>
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
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <span class="font-medium text-zinc-800">{{ $viticulturist->name }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="text-zinc-500">{{ $viticulturist->email }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $viticulturist->plots_count }}
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $items->links() }}
                </x-slot>
            </x-agro.data-table>
        @endif
    </div>

    {{-- Modal adscribir bodega --}}
    <flux:modal wire:model="showAssignModal" name="assign-winery" class="w-full max-w-lg">
        <div class="p-6 space-y-4">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Adscribir bodega</h3>
                <p class="text-sm text-zinc-500 mt-0.5">Busca y adscribe una bodega a tu denominación de origen.</p>
            </div>

            <flux:input wire:model.live="assignSearch" placeholder="Buscar por nombre o email…" icon="magnifying-glass" />

            @if($availableWineries->isEmpty())
                <p class="text-sm text-zinc-400 text-center py-6">
                    {{ $assignSearch ? 'Sin resultados para esta búsqueda.' : 'No hay bodegas disponibles para adscribir.' }}
                </p>
            @else
                <div class="divide-y divide-zinc-100 max-h-72 overflow-y-auto -mx-6 px-6">
                    @foreach($availableWineries as $winery)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="size-7 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                                    <flux:icon icon="building-office-2" class="size-3.5 text-blue-500" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-800 truncate">{{ $winery->name }}</p>
                                    @if($winery->email)
                                        <p class="text-xs text-zinc-400 truncate">{{ $winery->email }}</p>
                                    @endif
                                </div>
                            </div>
                            <flux:button
                                wire:click="assignWinery({{ $winery->id }})"
                                variant="primary"
                                size="sm"
                                class="shrink-0 ml-3"
                            >
                                Adscribir
                            </flux:button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end pt-2">
                <flux:button wire:click="closeAssignModal" variant="ghost">Cerrar</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
