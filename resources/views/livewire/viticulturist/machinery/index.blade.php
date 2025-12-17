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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Maquinaria"
        description="Gestiona tu maquinaria y equipos agrícolas"
        icon-color="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
    >
        <x-slot:actionButton>
            @can('create', \App\Models\Machinery::class)
                <a href="{{ route('viticulturist.machinery.create') }}" class="group">
                    <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-brown-dark)] to-[var(--color-agro-brown)] text-white hover:from-[var(--color-agro-brown)] hover:to-[var(--color-agro-brown-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Maquinaria
                    </button>
                </a>
            @endcan
        </x-slot:actionButton>
    </x-page-header>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-[var(--color-agro-brown-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-[var(--color-agro-brown-dark)]">Filtros de Búsqueda</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Buscar por nombre, marca, modelo..."
                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all"
                >
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <select 
                    wire:model.live="typeFilter" 
                    class="w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all appearance-none cursor-pointer"
                >
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <div class="relative">
                <select 
                    wire:model.live="activeFilter" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-brown-dark)] focus:border-transparent transition-all appearance-none cursor-pointer"
                >
                    <option value="">Todas</option>
                    <option value="1">Activas</option>
                    <option value="0">Inactivas</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>
        @if($search || $typeFilter || $activeFilter !== '')
            <div class="mt-4 flex justify-end">
                <button 
                    wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-semibold text-[var(--color-agro-brown-dark)] hover:bg-[var(--color-agro-brown-bg)] rounded-lg transition-colors"
                >
                    Limpiar Filtros
                </button>
            </div>
        @endif
    </div>

    <!-- Lista de Maquinaria -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-xl">
        @if($machinery->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-[var(--color-agro-brown-bg)] to-[var(--color-agro-brown-bright)]/30">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Maquinaria</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Marca/Modelo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">ROMA</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Actividades</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-brown-dark)] uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($machinery as $item)
                            <tr class="hover:bg-[var(--color-agro-brown-bg)]/40 transition-all">
                                <td class="px-6 py-4">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $item->name }}</span>
                                        @if($item->year)
                                            <p class="text-xs text-gray-500 mt-1">Año: {{ $item->year }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $item->type }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->brand || $item->model)
                                        <span class="text-sm text-gray-700">
                                            {{ $item->brand }} {{ $item->model }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->roma_registration)
                                        <span class="text-sm font-medium text-gray-900">{{ $item->roma_registration }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-green-50 text-green-700 ring-1 ring-green-600/20">
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-700 ring-1 ring-gray-600/20">
                                            Inactiva
                                        </span>
                                    @endif
                                    @if($item->is_rented)
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 ml-2">
                                            Alquilada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-gray-900">{{ $item->activities_count }}</span>
                                    <span class="text-xs text-gray-500"> actividades</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @can('view', $item)
                                            <a 
                                                href="{{ route('viticulturist.machinery.show', $item) }}"
                                                class="px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Ver detalles"
                                            >
                                                Ver
                                            </a>
                                        @endcan
                                        @can('update', $item)
                                            <a 
                                                href="{{ route('viticulturist.machinery.edit', $item) }}"
                                                class="px-3 py-1 text-xs font-semibold text-green-600 hover:bg-green-50 rounded-lg transition"
                                                title="Editar"
                                            >
                                                Editar
                                            </a>
                                        @endcan
                                        @can('delete', $item)
                                            @if($item->activities_count === 0)
                                                <button 
                                                    wire:click="delete({{ $item->id }})"
                                                    wire:confirm="¿Estás seguro de eliminar esta maquinaria?"
                                                    class="px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition"
                                                    title="Eliminar"
                                                >
                                                    Eliminar
                                                </button>
                                            @else
                                                <span class="px-3 py-1 text-xs font-semibold text-gray-400" title="No se puede eliminar porque tiene actividades">
                                                    Eliminar
                                                </span>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $machinery->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No hay maquinaria registrada</h3>
                <p class="text-gray-600 mb-6">Comienza agregando tu primera maquinaria o equipo agrícola.</p>
                @can('create', \App\Models\Machinery::class)
                    <a href="{{ route('viticulturist.machinery.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-brown-dark)] to-[var(--color-agro-brown)] text-white hover:from-[var(--color-agro-brown)] hover:to-[var(--color-agro-brown-dark)] transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar Maquinaria
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>
