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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Gestión de Campañas"
        description="Administra y visualiza todas tus campañas vitícolas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            @can('create', \App\Models\Campaign::class)
                <a href="{{ route('viticulturist.campaign.create') }}" class="group">
                    <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva Campaña
                    </button>
                </a>
            @endcan
        </x-slot:actionButton>
    </x-page-header>

    <!-- Filtros -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-[var(--color-agro-green-dark)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-[var(--color-agro-green-dark)]">Filtros de Búsqueda</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Buscar por nombre o descripción..."
                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                >
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <select 
                    wire:model.live="yearFilter" 
                    class="w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all appearance-none cursor-pointer"
                >
                    <option value="">Todos los años</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>
        @if($search || $yearFilter)
            <div class="mt-4 flex justify-end">
                <button 
                    wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-semibold text-[var(--color-agro-green-dark)] hover:bg-[var(--color-agro-green-bg)] rounded-lg transition-colors"
                >
                    Limpiar Filtros
                </button>
            </div>
        @endif
    </div>

    <!-- Tabla de Campañas -->
    <div class="glass-card rounded-2xl overflow-hidden shadow-xl">
        @if($campaigns->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-[var(--color-agro-green-bg)] to-[var(--color-agro-green-bright)]/30">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Campaña</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Año</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Período</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Actividades</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[var(--color-agro-green-dark)] uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-[var(--color-agro-green-bg)]/40 transition-all">
                                <td class="px-6 py-4">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $campaign->name }}</span>
                                        @if($campaign->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($campaign->description, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $campaign->year }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($campaign->start_date && $campaign->end_date)
                                        <span class="text-sm text-gray-700">
                                            {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($campaign->active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-green-50 text-green-700 ring-1 ring-green-600/20">
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-700 ring-1 ring-gray-600/20">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-gray-900">{{ $campaign->activities_count }}</span>
                                    <span class="text-xs text-gray-500"> actividades</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @can('view', $campaign)
                                            <a 
                                                href="{{ route('viticulturist.campaign.show', $campaign) }}"
                                                class="px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Ver detalles"
                                            >
                                                Ver
                                            </a>
                                        @endcan
                                        @can('update', $campaign)
                                            <a 
                                                href="{{ route('viticulturist.campaign.edit', $campaign) }}"
                                                class="px-3 py-1 text-xs font-semibold text-green-600 hover:bg-green-50 rounded-lg transition"
                                                title="Editar"
                                            >
                                                Editar
                                            </a>
                                        @endcan
                                        @if(!$campaign->active)
                                            @can('activate', $campaign)
                                                <button 
                                                    wire:click="activate({{ $campaign->id }})"
                                                    class="px-3 py-1 text-xs font-semibold text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                                    title="Activar"
                                                >
                                                    Activar
                                                </button>
                                            @endcan
                                        @endif
                                        @can('delete', $campaign)
                                            @if($campaign->activities_count === 0)
                                                <button 
                                                    wire:click="delete({{ $campaign->id }})"
                                                    wire:confirm="¿Estás seguro de eliminar esta campaña?"
                                                    class="px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition"
                                                    title="Eliminar"
                                                >
                                                    Eliminar
                                                </button>
                                            @else
                                                <span class="px-3 py-1 text-xs text-gray-400" title="No se puede eliminar: tiene actividades">
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
            
            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-[var(--color-agro-green-bg)]/30 to-transparent">
                {{ $campaigns->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-16 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No hay campañas registradas</h3>
                <p class="text-gray-500 mb-6">
                    @if($search || $yearFilter)
                        No se encontraron campañas con los filtros seleccionados
                    @else
                        Comienza creando tu primera campaña vitícola
                    @endif
                </p>
                @can('create', \App\Models\Campaign::class)
                    <a href="{{ route('viticulturist.campaign.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all shadow-lg hover:shadow-xl font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Primera Campaña
                    </a>
                @endcan
            </div>
        @endif
    </div>
</div>
