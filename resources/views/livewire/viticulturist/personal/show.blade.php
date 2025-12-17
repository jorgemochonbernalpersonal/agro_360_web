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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        :title="$crew->name"
        :description="$crew->description ?? 'Detalles de la cuadrilla'"
        icon-color="from-[var(--color-agro-blue)] to-blue-700"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                @can('update', $crew)
                    <a href="{{ route('viticulturist.personal.edit', $crew) }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all font-semibold">
                        Editar
                    </a>
                @endcan
                <a href="{{ route('viticulturist.personal.index') }}" class="px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold">
                    Volver
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Estadísticas -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold text-[var(--color-agro-blue)] mb-4">Estadísticas de la Cuadrilla</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-3xl font-bold text-[var(--color-agro-blue)]">{{ $stats['members_count'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Miembros</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-3xl font-bold text-green-600">{{ $stats['activities_count'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Actividades</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-600">{{ $stats['subordinates_count'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Subordinados</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Información General -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-[var(--color-agro-blue)] mb-4">Información General</h3>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-gray-600">Bodega:</span>
                    <span class="ml-2 text-gray-900">{{ $crew->winery->name ?? 'Sin bodega' }}</span>
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-600">Líder:</span>
                    <span class="ml-2 text-gray-900">{{ $crew->viticulturist->name }}</span>
                </div>
                @if($crew->description)
                <div>
                    <span class="text-sm font-semibold text-gray-600">Descripción:</span>
                    <p class="mt-1 text-gray-900">{{ $crew->description }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm font-semibold text-gray-600">Creada:</span>
                    <span class="ml-2 text-gray-900">{{ $crew->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Gestión de Miembros -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-[var(--color-agro-blue)]">Miembros de la Cuadrilla</h3>
                <a href="{{ route('viticulturist.personal.hierarchy') }}" class="text-sm text-[var(--color-agro-blue)] hover:underline">
                    Gestionar Jerarquía
                </a>
            </div>

            <!-- Filtro de modo -->
            <div class="mb-4 flex items-center gap-4">
                <span class="text-sm font-semibold text-gray-600">Mostrar:</span>
                <div class="flex gap-2">
                    <button 
                        wire:click="$set('filterMode', 'all')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filterMode === 'all' ? 'bg-[var(--color-agro-blue)] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        Todos
                    </button>
                    <button 
                        wire:click="$set('filterMode', 'hierarchy')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $filterMode === 'hierarchy' ? 'bg-[var(--color-agro-blue)] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        Solo Subordinados
                    </button>
                </div>
            </div>

            <!-- Agregar Miembro -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <form wire:submit.prevent="addMember" class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Agregar Miembro</label>
                        <select 
                            wire:model="newMemberId" 
                            class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-blue)] focus:border-[var(--color-agro-blue)] transition"
                        >
                            <option value="">Seleccionar viticultor...</option>
                            @foreach($availableViticulturists as $viticulturist)
                                <option value="{{ $viticulturist->id }}">
                                    {{ $viticulturist->name }} ({{ $viticulturist->email }})
                                    @if(isset($viticulturist->is_individual_worker) && $viticulturist->is_individual_worker)
                                        - Trabajador Individual
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if($availableViticulturists->isEmpty())
                            <p class="mt-2 text-sm text-gray-500">No hay viticultores disponibles para agregar.</p>
                        @endif
                    </div>
                    <button 
                        type="submit" 
                        class="w-full px-4 py-2 rounded-lg bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 text-white hover:from-blue-700 hover:to-[var(--color-agro-blue)] transition-all font-semibold"
                        @if($availableViticulturists->isEmpty()) disabled @endif
                    >
                        Agregar Miembro
                    </button>
                </form>
            </div>

            <!-- Lista de Miembros -->
            <div class="space-y-2">
                @if($crew->members->count() > 0)
                    @foreach($crew->members as $member)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 hover:border-[var(--color-agro-blue)] transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 flex items-center justify-center text-white font-bold">
                                    {{ substr($member->viticulturist->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $member->viticulturist->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $member->viticulturist->email }}</div>
                                </div>
                            </div>
                            <button 
                                wire:click="removeMember({{ $member->id }})"
                                wire:confirm="¿Estás seguro de remover este miembro?"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Remover"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p>No hay miembros en esta cuadrilla</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actividades Recientes -->
    @if($crew->activities->count() > 0)
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold text-[var(--color-agro-blue)] mb-4">Actividades Recientes</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Fecha</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tipo</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Parcela</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($crew->activities->take(10) as $activity)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $activity->activity_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($activity->activity_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $activity->plot->name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

