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
        icon-color="from-purple-600 to-purple-800"
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
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <h2 class="text-lg font-semibold text-purple-600">Agregar Trabajador Individual</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if($wineries->isNotEmpty() && $wineries->count() > 1)
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bodega <span class="text-gray-500 font-normal">(opcional)</span></label>
                    <select 
                        wire:model.live="selectedWineryId" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all"
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
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all"
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
                    class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-800 text-white hover:from-purple-700 hover:to-purple-900 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    @if(empty($newWorkerId)) disabled @endif
                >
                    Agregar Trabajador
                </button>
            </div>
        </div>
        </div>

        <!-- Filtros -->
        @if($wineries->isNotEmpty() && $wineries->count() > 1)
        <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-purple-600">Filtros</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <select 
                    wire:model.live="wineryFilter" 
                    class="w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all appearance-none cursor-pointer"
                >
                    <option value="">Todas las bodegas</option>
                    @foreach($wineries as $winery)
                        <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($wineryFilter)
        <div class="mt-4 flex justify-end">
            <button wire:click="clearFilters" class="text-sm text-purple-600 hover:underline font-medium">
                Limpiar filtros
            </button>
        </div>
        @endif
        </div>
        @endif

        <!-- Lista de Trabajadores Individuales -->
        <div class="glass-card rounded-xl overflow-hidden">
        @if($workers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-purple-600 to-purple-800 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Viticultor</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Asignado por</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Fecha de Asignación</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($workers as $worker)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $worker->viticulturist->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $worker->viticulturist->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $worker->assignedBy->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $worker->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Asignar a Cuadrilla -->
                                        @if($crews->count() > 0)
                                        <div class="relative" x-data="{ open: false, workerId: {{ $worker->id }} }">
                                            <button 
                                                @click="open = !open"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
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
                                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                                                    @if(empty($assignToCrewId)) disabled @endif
                                                >
                                                    Asignar
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        <button 
                                            wire:click="removeWorker({{ $worker->id }})" 
                                            wire:confirm="¿Estás seguro de remover este trabajador individual?"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                            title="Remover"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $workers->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No hay trabajadores individuales</h3>
                <p class="text-gray-600 mb-6">Agrega trabajadores que no pertenezcan a ninguna cuadrilla para gestionarlos individualmente.</p>
            </div>
        @endif
        </div>
</div>

