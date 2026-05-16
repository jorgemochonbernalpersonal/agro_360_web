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
        <x-agro.search-input wire:model.live.debounce.300ms="search" placeholder="Buscar..." />
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

    {{-- Tabs + Cards --}}
    <div>
        <x-agro.tabs
            :tabs="[
                'wineries'       => ['label' => 'Bodegas',      'count' => $wineryCount],
                'viticulturists' => ['label' => 'Viticultores', 'count' => $viticulturistCount],
            ]"
            :active="$currentTab"
        />

        {{-- Loading skeleton --}}
        <x-agro.loading-grid target="search, switchTab, nextPage, previousPage" />

        <div wire:loading.remove wire:target="search, switchTab, nextPage, previousPage">
            @if($currentTab === 'wineries')
                @if($items->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($items as $winery)
                            @php
                                $delay = min($loop->index * 50, 300);
                                $vitCount = $vitCountByWinery[$winery->id] ?? 0;
                            @endphp
                            <x-agro.card
                                class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                                style="animation-delay: {{ $delay }}ms;"
                                wire:key="winery-{{ $winery->id }}"
                            >
                                <x-slot:header>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                            <flux:icon icon="building-office-2" class="size-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('supervisor.oversight.wineries.show', $winery) }}" wire:navigate
                                               class="font-bold text-zinc-900 truncate block hover:text-indigo-600 transition">
                                                {{ $winery->name }}
                                            </a>
                                            <p class="text-xs text-zinc-500 truncate">{{ $winery->email }}</p>
                                        </div>
                                    </div>
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-1 gap-2">
                                        <div class="bg-blue-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-blue-400 uppercase tracking-widest mb-0.5">Viticultores DO</p>
                                            <p class="text-2xl font-bold text-blue-700 leading-none">{{ $vitCount }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Email</span>
                                            <span class="text-zinc-700 font-medium truncate ml-2 max-w-[60%] text-right">{{ $winery->email }}</span>
                                        </div>
                                    </div>
                                </div>

                                <x-slot:footer>
                                    @php
                                        $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                                        $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                                    @endphp
                                    <div class="flex items-center justify-end gap-0.5">
                                        <a href="{{ route('supervisor.oversight.wineries.show', $winery) }}" wire:navigate class="{{ $btnBase }}" title="Ver">
                                            <flux:icon icon="eye" class="size-4" />
                                        </a>
                                        <button
                                            wire:click="unassignWinery({{ $winery->id }})"
                                            wire:confirm="¿Desadscribir {{ $winery->name }} de la denominación? Los viticultores asignados por la DO a esta bodega también se desvincularán."
                                            class="{{ $btnDanger }}"
                                            title="Desadscribir"
                                        >
                                            <flux:icon icon="x-mark" class="size-4" />
                                        </button>
                                    </div>
                                </x-slot:footer>
                            </x-agro.card>
                        @endforeach
                    </div>

                    <div class="mt-6">{{ $items->links() }}</div>
                @else
                    <x-agro.empty-state icon="building-office-2" title="No hay bodegas" description="No hay bodegas adscritas a esta denominación." />
                @endif

            @else
                @if($items->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($items as $viticulturist)
                            @php $delay = min($loop->index * 50, 300); @endphp
                            <x-agro.card
                                class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                                style="animation-delay: {{ $delay }}ms;"
                                wire:key="vit-{{ $viticulturist->id }}"
                            >
                                <x-slot:header>
                                    <x-agro.card-item-header
                                        icon="user"
                                        :title="$viticulturist->name"
                                        :subtitle="$viticulturist->email"
                                        iconBg="bg-agro-100"
                                        iconColor="text-agro-600"
                                        size="md"
                                        radius="xl"
                                    />
                                </x-slot:header>

                                <div class="flex-1 space-y-4">
                                    <div class="grid grid-cols-1 gap-2">
                                        <div class="bg-agro-50 rounded-xl p-3">
                                            <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Parcelas</p>
                                            <p class="text-2xl font-bold text-agro-700 leading-none">{{ $viticulturist->plots_count }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-zinc-400">Email</span>
                                            <span class="text-zinc-700 font-medium truncate ml-2 max-w-[60%] text-right">{{ $viticulturist->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>

                    <div class="mt-6">{{ $items->links() }}</div>
                @else
                    <x-agro.empty-state icon="users" title="No hay viticultores" description="No hay viticultores adscritos a esta denominación." />
                @endif
            @endif
        </div>
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
