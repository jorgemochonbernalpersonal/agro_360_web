<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Trabajadores Individuales"
        description="Gestiona trabajadores que no pertenecen a ninguna cuadrilla"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.personal.index') }}" variant="outline" icon="arrow-left">
                Cuadrillas
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Formulario agregar trabajador --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-agro-100 rounded-lg flex items-center justify-center shrink-0">
                    <flux:icon icon="user-plus" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900 text-sm">Agregar Trabajador Individual</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($wineries->isNotEmpty() && $wineries->count() > 1)
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">Bodega <span class="text-zinc-400 font-normal">(opcional)</span></label>
                    <flux:select wire:model.live="selectedWineryId">
                        <option value="">Sin bodega (solo mis viticultores)</option>
                        @foreach($wineries as $winery)
                            <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            @elseif($wineries->count() === 1)
                <input type="hidden" wire:model="selectedWineryId" value="{{ $wineries->first()->id }}">
            @endif

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Viticultor</label>
                <flux:select wire:model="newWorkerId">
                    <option value="">Selecciona un viticultor</option>
                    @foreach($availableViticulturists as $viticulturist)
                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex items-end">
                <flux:button wire:click="addWorker" variant="primary" class="w-full">
                    Agregar Trabajador
                </flux:button>
            </div>
        </div>
    </x-agro.card>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        @if($wineries->count() > 1)
            <div class="flex-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <flux:icon icon="building-office-2" class="size-4 text-zinc-400" />
                </div>
                <select
                    wire:model.live="wineryFilter"
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm text-zinc-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-agro-500 focus:border-transparent transition appearance-none"
                >
                    <option value="">Todas las bodegas</option>
                    @foreach($wineries as $winery)
                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- Card grid --}}
    @if($workers->count() > 0)
        <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="wineryFilter"
        >
            @foreach($workers as $i => $worker)
                @php
                    $btnBase   = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors';
                    $btnDanger = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 transition-colors';
                @endphp

                <x-agro.card
                    wire:key="worker-{{ $worker->id }}"
                    class="animate-fade-in-up hover:-translate-y-1"
                    style="animation-delay: {{ min($i * 50, 400) }}ms"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-zinc-100 rounded-full flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-zinc-500">{{ strtoupper(substr($worker->viticulturist->name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-zinc-900 text-sm truncate leading-tight">{{ $worker->viticulturist->name }}</p>
                                <p class="text-xs text-zinc-400 leading-tight mt-0.5 truncate">{{ $worker->viticulturist->email }}</p>
                            </div>
                        </div>
                    </x-slot:header>

                    {{-- Cuadrilla --}}
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon icon="user-group" class="size-3.5 text-zinc-400 shrink-0" />
                        @if($worker->crew)
                            <span class="text-xs text-agro-700 font-medium">{{ $worker->crew->name }}</span>
                        @else
                            <span class="text-xs text-zinc-400 italic">Sin cuadrilla</span>
                        @endif
                    </div>

                    {{-- Asignado por + fecha --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Asignado por</p>
                            <p class="text-xs font-semibold text-zinc-700 truncate">{{ $worker->assignedBy?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-zinc-50 rounded-xl p-2.5">
                            <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wide mb-0.5">Fecha</p>
                            <p class="text-xs font-semibold text-zinc-700">{{ $worker->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-between">
                            {{-- Asignar a cuadrilla --}}
                            @if($crews->count() > 0)
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="{{ $btnBase }}" title="Asignar a cuadrilla">
                                        <flux:icon icon="user-group" class="size-4" />
                                    </button>
                                    <div
                                        x-show="open"
                                        @click.away="open = false"
                                        x-transition
                                        class="absolute left-0 bottom-10 w-64 bg-white rounded-xl shadow-xl z-10 border border-zinc-200 p-4"
                                    >
                                        <p class="text-sm font-semibold text-zinc-700 mb-3">Asignar a cuadrilla</p>
                                        <select wire:model="assignToCrewId"
                                                class="w-full px-3 py-2 text-sm border border-zinc-200 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-agro-400">
                                            <option value="">Selecciona una cuadrilla</option>
                                            @foreach($crews as $crew)
                                                <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                            @endforeach
                                        </select>
                                        <flux:button
                                            wire:click="assignToCrew({{ $worker->id }})"
                                            x-on:click="open = false"
                                            variant="primary"
                                            class="w-full"
                                            size="sm"
                                        >
                                            Asignar
                                        </flux:button>
                                    </div>
                                </div>
                            @else
                                <div></div>
                            @endif

                            {{-- Eliminar --}}
                            <button
                                wire:click="removeWorker({{ $worker->id }})"
                                wire:confirm="¿Seguro que deseas remover este trabajador individual?"
                                class="{{ $btnDanger }}"
                                title="Remover trabajador"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($workers->hasPages())
            <div class="flex justify-center">{{ $workers->links() }}</div>
        @endif

    @else
        <x-agro.empty-state
            icon="users"
            message="No hay trabajadores individuales"
            description="Agrega trabajadores que no pertenezcan a ninguna cuadrilla para gestionarlos individualmente."
        />
    @endif

</div>
