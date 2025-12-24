<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
    @endphp
    
    <x-page-header 
        :icon="$icon"
        title="Gestión de Plagas y Enfermedades"
        description="Base de conocimiento para identificación y tratamiento"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    />

    {{-- Alertas de Riesgo --}}
    @if($pestsInRisk->count() > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Plagas/Enfermedades en Período de Riesgo ({{ now()->format('F') }})</h3>
                    <div class="mt-2 text-sm text-amber-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($pestsInRisk as $riskPest)
                                <li>
                                    <a href="{{ route('viticulturist.pest-management.show', $riskPest) }}" class="font-medium hover:underline">
                                        {{ $riskPest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <x-filter-section title="Filtros de Búsqueda" color="green">
        <x-filter-input 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por nombre o nombre científico..."
        />
        <x-filter-select wire:model.live="typeFilter">
            <option value="all">Todos los tipos</option>
            <option value="pest">Solo Plagas</option>
            <option value="disease">Solo Enfermedades</option>
        </x-filter-select>
        <div class="flex items-center">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input 
                    type="checkbox" 
                    wire:model.live="showOnlyRisk"
                    class="rounded border-gray-300 text-[var(--color-agro-green)] focus:ring-[var(--color-agro-green)]"
                >
                <span class="text-sm font-medium text-gray-700">Solo en riesgo ahora</span>
            </label>
        </div>
    </x-filter-section>

    {{-- Grid de Plagas/Enfermedades --}}
    @if($pests->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pests as $pest)
                <a href="{{ route('viticulturist.pest-management.show', $pest) }}" 
                   class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-[var(--color-agro-green-light)] transition-all p-6 group">
                    
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-lg group-hover:text-[var(--color-agro-green-dark)] transition-colors">{{ $pest->name }}</h3>
                            @if($pest->scientific_name)
                                <p class="text-xs text-gray-500 italic mt-1">{{ $pest->scientific_name }}</p>
                            @endif
                        </div>
                        
                        @if($pest->isInRiskPeriod())
                            <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full flex-shrink-0">
                                Riesgo
                            </span>
                        @endif
                    </div>

                    {{-- Descripción --}}
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ $pest->description }}
                    </p>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $pest->type === 'pest' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $pest->type === 'pest' ? 'Plaga' : 'Enfermedad' }}
                        </span>
                        <span class="text-xs text-[var(--color-agro-green)] font-medium group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                            Ver detalles
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $pests->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No se encontraron resultados</h3>
            <p class="mt-2 text-gray-600">Intenta ajustar los filtros de búsqueda</p>
        </div>
    @endif
</div>
