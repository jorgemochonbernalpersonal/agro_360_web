<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        title="Trabajadores Individuales"
        description="Gestiona trabajadores que no pertenecen a ninguna cuadrilla"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.personal.index') }}" variant="outline" icon="arrow-left">
                Volver a Cuadrillas
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

        <!-- Agregar Trabajador Individual -->
        <x-agro.card>
            <div class="flex items-center gap-2 mb-4">
                <div class="p-1.5 rounded-lg bg-agro-50">
                    <flux:icon icon="plus" class="size-4 text-agro-600" />
                </div>
                <span class="font-semibold text-zinc-900">Agregar Trabajador Individual</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($wineries->isNotEmpty() && $wineries->count() > 1)
                <div class="relative">
                    <label class="block text-sm font-medium text-zinc-700 mb-2">Bodega <span class="text-zinc-500 font-normal">(opcional)</span></label>
                    <flux:select
                        wire:model.live="selectedWineryId"
                    >
                        <option value="">Sin bodega (solo mis viticultores)</option>
                        @foreach($wineries as $winery)
                            <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                @elseif($wineries->count() === 1)
                    <input type="hidden" wire:model="selectedWineryId" value="{{ $wineries->first()->id }}">
                @endif
            <div class="relative">
                <label class="block text-sm font-medium text-zinc-700 mb-2">Viticultor</label>
                <flux:select
                    wire:model="newWorkerId"
                >
                    <option value="">Selecciona un viticultor</option>
                    @foreach($availableViticulturists as $viticulturist)
                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex items-end">
                <flux:button
                    wire:click="addWorker"
                    variant="primary"
                    class="w-full"
                >
                    Agregar Trabajador
                </flux:button>
            </div>
        </div>
        </x-agro.card>

        <!-- Filtros -->
        @if($wineries->isNotEmpty() && $wineries->count() > 1)
            <x-agro.filter-bar>
                <x-agro.filter-select wire:model.live="wineryFilter">
                    <option value="">Todas las bodegas</option>
                    @foreach($wineries as $winery)
                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                    @endforeach
                </x-agro.filter-select>
                @if($wineryFilter)
                    <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
                @endif
            </x-agro.filter-bar>
        @endif

        <!-- Lista de Trabajadores Individuales -->
        <x-agro.data-table
            :headers="['Viticultor', 'Asignado por', 'Fecha asignacion', 'Cuadrilla', 'Acciones']"
            empty-message="No hay trabajadores individuales"
            empty-description="Agrega trabajadores que no pertenezcan a ninguna cuadrilla para gestionarlos individualmente."
        >
            @if($workers->count() > 0)
                @foreach($workers as $worker)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <div class="font-semibold text-zinc-900">{{ $worker->viticulturist->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $worker->viticulturist->email }}</div>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-sm text-zinc-700">
                                {{ $worker->assignedBy->name ?? 'N/A' }}
                            </span>
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <span class="text-sm text-zinc-500">
                                {{ $worker->created_at->format('d/m/Y H:i') }}
                            </span>
                        </x-agro.table-cell>

                        {{-- Cuadrilla actual --}}
                        <x-agro.table-cell>
                            @if ($worker->crew)
                                <flux:badge color="green">{{ $worker->crew->name }}</flux:badge>
                            @else
                                <span class="text-sm text-zinc-500">Sin cuadrilla</span>
                            @endif
                        </x-agro.table-cell>

                        <x-agro.table-cell>
                            <div class="flex items-center justify-end gap-2">
                                @if($crews->count() > 0)
                                    <div class="relative" x-data="{ open: false }">
                                        <button
                                            @click="open = !open"
                                            class="p-2 text-agro-700 hover:bg-agro-50 rounded-lg transition-colors"
                                            title="Asignar a Cuadrilla"
                                        >
                                            <flux:icon icon="plus" class="size-5" />
                                        </button>
                                        <div
                                            x-show="open"
                                            @click.away="open = false"
                                            x-transition
                                            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-10 border border-zinc-200 p-4"
                                        >
                                            <p class="text-sm font-semibold text-zinc-700 mb-3">Asignar a Cuadrilla</p>
                                            <select
                                                wire:model="assignToCrewId"
                                                class="w-full px-3 py-2 border border-zinc-300 rounded-lg mb-3"
                                            >
                                                <option value="">Selecciona una cuadrilla</option>
                                                @foreach($crews as $crew)
                                                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                                @endforeach
                                            </select>
                                            <button
                                                wire:click="assignToCrew({{ $worker->id }})"
                                                wire:target="assignToCrew"
                                                x-on:click="open = false"
                                                class="w-full px-4 py-2 bg-agro-600 text-white rounded-lg hover:bg-agro-700 transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Asignar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <x-agro.action-button
                                    variant="delete"
                                    wire:click="removeWorker({{ $worker->id }})"
                                    wire:confirm="Estas seguro de remover este trabajador individual?"
                                />
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $workers->links() }}
                </x-slot>
            @endif
        </x-agro.data-table>
</div>
