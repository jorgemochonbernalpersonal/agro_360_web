<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Supervisión — Bodegas"
        description="Actividad de vendimia y viticultores por bodega adscrita."
    >
        <x-slot:actions>
            <flux:button wire:click="openLinkModal" variant="primary" icon="plus">
                Adscribir bodega
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div x-data="{
        open: localStorage.getItem('oversight-wineries-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('oversight-wineries-stats-open', String(this.open));
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
        </div>
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
        :headers="['Bodega', 'Viticultores DO', 'Recepciones ' . $vintage, 'Uva recibida (kg)', '']"
        emptyMessage="No hay bodegas adscritas a esta denominación."
    >
        @foreach($wineries as $winery)
            @php
                $stats = $harvestStats[$winery->id] ?? null;
            @endphp
            <tr class="hover:bg-zinc-50 transition">
                <td class="px-6 py-3 text-sm font-medium text-zinc-800">
                    <a href="{{ route('supervisor.oversight.wineries.show', $winery) }}"
                       wire:navigate
                       class="hover:text-blue-600 transition">
                        {{ $winery->name }}
                    </a>
                    <div class="text-xs text-zinc-400">{{ $winery->email }}</div>
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
                <td class="px-6 py-3 text-right">
                    <flux:button
                        wire:click="unlinkWinery({{ $winery->id }})"
                        wire:confirm="¿Desvincular {{ addslashes($winery->name) }} de la denominación? Se eliminarán también las asignaciones de viticultores DO a esta bodega."
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                        class="text-zinc-400 hover:text-red-500"
                    />
                </td>
            </tr>
        @endforeach

        <x-slot name="pagination">
            {{ $wineries->links() }}
        </x-slot>
    </x-agro.data-table>

    {{-- Modal: adscribir bodega existente --}}
    <flux:modal wire:model="showLinkModal" class="max-w-lg">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Adscribir bodega a la denominación</flux:heading>
                <flux:text class="mt-1 text-zinc-500">
                    Busca la bodega por nombre o email y confírmala para incluirla bajo la supervisión de esta DO.
                </flux:text>
            </div>

            <div>
                <flux:label>Buscar bodega</flux:label>
                <flux:input
                    wire:model.live.debounce.300ms="linkSearch"
                    placeholder="Nombre o email de la bodega..."
                    icon="magnifying-glass"
                    autofocus
                />
                <p class="mt-1 text-xs text-zinc-400">Introduce al menos 2 caracteres para buscar.</p>
            </div>

            @if($linkCandidates->isNotEmpty())
                <div class="border border-zinc-200 rounded-xl divide-y divide-zinc-100 overflow-hidden">
                    @foreach($linkCandidates as $candidate)
                        <button
                            type="button"
                            wire:click="$set('linkWineryId', {{ $candidate->id }})"
                            class="w-full flex items-center justify-between px-4 py-3 text-left text-sm hover:bg-zinc-50 transition {{ $linkWineryId === $candidate->id ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : '' }}"
                        >
                            <div>
                                <span class="font-medium text-zinc-800">{{ $candidate->name }}</span>
                                <span class="ml-2 text-xs text-zinc-400">{{ $candidate->email }}</span>
                            </div>
                            @if($linkWineryId === $candidate->id)
                                <flux:icon icon="check-circle" class="w-4 h-4 text-blue-600 flex-shrink-0" />
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif(strlen($linkSearch) >= 2)
                <p class="text-sm text-zinc-400 text-center py-3">
                    No se encontraron bodegas con ese nombre o email.
                </p>
            @endif

            @error('linkWineryId')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-3 pt-1">
                <flux:button wire:click="closeLinkModal" variant="ghost">Cancelar</flux:button>
                <flux:button
                    wire:click="linkWinery"
                    variant="primary"
                    :disabled="!$linkWineryId"
                >
                    Adscribir bodega
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>

