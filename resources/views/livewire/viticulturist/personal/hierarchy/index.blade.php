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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Gestión de Jerarquía"
        description="Administra tus subordinados y estructura organizacional"
        icon-color="from-purple-600 to-purple-800"
    >
        <x-slot:actionButton>
            <a href="{{ route('viticulturist.personal.index') }}" class="group">
                <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-600 text-white hover:bg-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </button>
            </a>
        </x-slot:actionButton>
    </x-page-header>

        <!-- Filtros -->
        @if($wineries->isNotEmpty() && $wineries->count() > 1)
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-purple-600">Filtrar por Bodega</h2>
        </div>
        <div class="flex items-center gap-4">
            <select 
                wire:model.live="wineryFilter" 
                class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-transparent transition-all"
            >
                <option value="">Todas las bodegas</option>
                @foreach($wineries as $winery)
                    <option value="{{ $winery->id }}">{{ $winery->name }}</option>
                @endforeach
            </select>
            @if($wineryFilter)
            <button wire:click="clearFilters" class="px-4 py-3 rounded-xl bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all font-semibold">
                Limpiar
            </button>
            @endif
        </div>
    </div>
    @endif

    <!-- Agregar Subordinado -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold text-purple-600 mb-4">Agregar Subordinado</h3>
        <form wire:submit.prevent="addSubordinate" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($wineries->isNotEmpty() && $wineries->count() > 1)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bodega <span class="text-gray-500 font-normal">(opcional)</span></label>
                    <select 
                        wire:model.live="selectedWineryId" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition"
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
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Viticultor *</label>
                    <select 
                        wire:model="newSubordinateId" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition"
                        required
                    >
                        <option value="">Seleccionar viticultor...</option>
                        @foreach($availableViticulturists as $viticulturist)
                            <option value="{{ $viticulturist->id }}">{{ $viticulturist->name }} ({{ $viticulturist->email }})</option>
                        @endforeach
                    </select>
                    @if($availableViticulturists->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">No hay viticultores disponibles.</p>
                    @endif
                </div>
            </div>
            <button 
                type="submit" 
                class="w-full md:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-800 text-white hover:from-purple-800 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold"
                @if(empty($newSubordinateId) || $availableViticulturists->isEmpty()) disabled @endif
            >
                Agregar Subordinado
            </button>
        </form>
    </div>

    <!-- Lista de Subordinados -->
    <div class="glass-card rounded-xl overflow-hidden">
        <h3 class="text-lg font-bold text-purple-600 mb-4 p-6 pb-0">Mis Subordinados</h3>
        @if($subordinates->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-purple-600 to-purple-800 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Viticultor</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Bodega</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Asignado</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($subordinates as $hierarchy)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-purple-800 flex items-center justify-center text-white font-bold">
                                            {{ substr($hierarchy->childViticulturist->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $hierarchy->childViticulturist->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $hierarchy->childViticulturist->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $hierarchy->winery->name ?? 'Sin bodega' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $hierarchy->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        <button 
                                            wire:click="removeSubordinate({{ $hierarchy->id }})"
                                            wire:confirm="¿Estás seguro de remover este subordinado?"
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
                {{ $subordinates->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No tienes subordinados</h3>
                <p class="text-gray-600">Agrega viticultores como subordinados para gestionar tu estructura organizacional.</p>
            </div>
        @endif
    </div>
</div>

