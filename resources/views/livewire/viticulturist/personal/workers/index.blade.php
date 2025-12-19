<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Trabajadores Individuales"
        description="Gestiona trabajadores que no pertenecen a ninguna cuadrilla"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.personal.index') }}" class="group">
                <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-gray-600 to-gray-800 text-white hover:from-gray-700 hover:to-gray-900 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a Cuadrillas
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

        <!-- Agregar Trabajador Individual -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <h2 class="text-lg font-semibold text-[var(--color-agro-green-dark)]">Agregar Trabajador Individual</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($wineries->isNotEmpty() && $wineries->count() > 1)
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bodega <span class="text-gray-500 font-normal">(opcional)</span></label>
                    <select 
                        wire:model.live="selectedWineryId" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                    >
                        <option value="">Sin bodega (solo mis viticultores)</option>
                        @foreach($wineries as $winery)
                            <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                        @endforeach
                    </select>
                </div>
                @elseif($wineries->count() === 1)
                    <input type="hidden" wire:model="selectedWineryId" value="{{ $wineries->first()->id }}">
                @endif
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Viticultor</label>
                <select 
                    wire:model="newWorkerId" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
                    <option value="">Selecciona un viticultor</option>
                    @foreach($availableViticulturists as $viticulturist)
                        <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button 
                    wire:click="addWorker" 
                    class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold"
                >
                    Agregar Trabajador
                </button>
            </div>
        </div>
        </div>

        <!-- Filtros -->
        @if($wineries->isNotEmpty() && $wineries->count() > 1)
            <x-filter-section title="Filtros de trabajadores individuales" color="green">
                <x-filter-select wire:model.live="wineryFilter">
                    <option value="">Todas las bodegas</option>
                    @foreach($wineries as $winery)
                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                    @endforeach
                </x-filter-select>

                <x-slot:actions>
                    @if($wineryFilter)
                        <x-button wire:click="clearFilters" variant="ghost" size="sm">
                            Limpiar Filtros
                        </x-button>
                    @endif
                </x-slot:actions>
            </x-filter-section>
        @endif

        <!-- Lista de Trabajadores Individuales -->
        @php
            $headers = [
                ['label' => 'Viticultor', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
                ['label' => 'Asignado por', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h2l2 9h11l2-6H9"/></svg>'],
                ['label' => 'Fecha asignación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                ['label' => 'Cuadrilla', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>'],
                'Acciones',
            ];
        @endphp

        <x-data-table 
            :headers="$headers" 
            empty-message="No hay trabajadores individuales"
            empty-description="Agrega trabajadores que no pertenezcan a ninguna cuadrilla para gestionarlos individualmente."
            color="green"
        >
            @if($workers->count() > 0)
                @foreach($workers as $worker)
                    <x-table-row>
                        <x-table-cell>
                            <div class="font-semibold text-gray-900">{{ $worker->viticulturist->name }}</div>
                            <div class="text-sm text-gray-500">{{ $worker->viticulturist->email }}</div>
                        </x-table-cell>

                        <x-table-cell>
                            <span class="text-sm text-gray-700">
                                {{ $worker->assignedBy->name ?? 'N/A' }}
                            </span>
                        </x-table-cell>

                        <x-table-cell>
                            <span class="text-sm text-gray-500">
                                {{ $worker->created_at->format('d/m/Y H:i') }}
                            </span>
                        </x-table-cell>

                        {{-- Cuadrilla actual --}}
                        <x-table-cell>
                            @if ($worker->crew)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] text-white">
                                    {{ $worker->crew->name }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">Sin cuadrilla</span>
                            @endif
                        </x-table-cell>

                        <x-table-cell>
                            <x-table-actions align="right">
                                @if($crews->count() > 0)
                                    <div class="relative" x-data="{ open: false }">
                                        <button 
                                            @click="open = !open"
                                            class="p-2 text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors" 
                                            title="Asignar a Cuadrilla"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                        <div 
                                            x-show="open"
                                            @click.away="open = false"
                                            x-transition
                                            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-10 border border-gray-200 p-4"
                                        >
                                            <p class="text-sm font-semibold text-gray-700 mb-3">Asignar a Cuadrilla</p>
                                            <select 
                                                wire:model="assignToCrewId"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3"
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
                                                class="w-full px-4 py-2 bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white rounded-lg hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Asignar
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <x-action-button 
                                    variant="delete"
                                    wire:click="removeWorker({{ $worker->id }})"
                                    wire:confirm="¿Estás seguro de remover este trabajador individual?"
                                    title="Remover trabajador"
                                />
                            </x-table-actions>
                        </x-table-cell>
                    </x-table-row>
                @endforeach

                <x-slot name="pagination">
                    {{ $workers->links() }}
                </x-slot>
            @endif
        </x-data-table>
</div>

