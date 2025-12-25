<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        title="Maquinaria"
        description="Gestiona tu maquinaria y equipos agrícolas"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            @can('create', \App\Models\Machinery::class)
                <x-button href="{{ route('viticulturist.machinery.create') }}" variant="primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Maquinaria
                </x-button>
            @endcan
        </x-slot:actionButton>
    </x-page-header>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button 
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activas</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>
                
                <button 
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Inactivas</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-[var(--color-agro-green-dark)] text-white' : 'bg-gray-200 text-gray-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button 
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadísticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
            <!-- Filtros -->
            <x-filter-section title="Filtros de Búsqueda" color="green">
                <x-filter-input 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nombre, marca, modelo..."
                />
                <x-filter-select wire:model.live="typeFilter">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </x-filter-select>
                <x-slot:actions>
                    @if($search || $typeFilter)
                        <x-button wire:click="clearFilters" variant="ghost" size="sm">
                            Limpiar Filtros
                        </x-button>
                    @endif
                </x-slot:actions>
            </x-filter-section>

    @php
        $headers = [
            ['label' => 'Maquinaria', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['label' => 'Tipo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'Marca/Modelo', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>'],
            ['label' => 'ROMA', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'],
            ['label' => 'Estado', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            ['label' => 'Actividades', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
            'Acciones',
        ];
    @endphp

    <x-data-table :headers="$headers" empty-message="No hay maquinaria registrada" empty-description="Comienza agregando tu primera maquinaria o equipo agrícola" color="green">
        @if($machinery->count() > 0)
            @foreach($machinery as $item)
                <x-table-row>
                    <x-table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                                @if($item->year)
                                    <div class="text-xs text-gray-500 mt-1">Año: {{ $item->year }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm font-medium text-gray-900">{{ $item->type }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        @if($item->brand || $item->model)
                            <span class="text-sm text-gray-700">
                                {{ $item->brand }} {{ $item->model }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @if($item->roma_registration)
                            <span class="text-sm font-medium text-gray-900">{{ $item->roma_registration }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center gap-2 flex-wrap">
                            <x-status-badge :active="$item->active" />
                            @if($item->is_rented)
                                <x-status-badge label="Alquilada" type="info" />
                            @endif
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="text-sm font-semibold text-gray-900">{{ $item->activities_count }}</span>
                        <span class="text-xs text-gray-500"> actividades</span>
                    </x-table-cell>
                    <x-table-actions>
                        @can('view', $item)
                            <x-action-button variant="view" href="{{ route('viticulturist.machinery.show', $item) }}" />
                        @endcan
                        @can('update', $item)
                            <x-action-button variant="edit" href="{{ route('viticulturist.machinery.edit', $item) }}" />
                        @endcan
                        @can('update', $item)
                            <button 
                                wire:click="toggleActive({{ $item->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleActive({{ $item->id }})"
                                class="p-2 rounded-lg transition-all duration-200 group/btn {{ $item->active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} disabled:opacity-50 disabled:cursor-not-allowed"
                                title="{{ $item->active ? 'Desactivar maquinaria' : 'Activar maquinaria' }}"
                            >
                                <span wire:loading.remove wire:target="toggleActive({{ $item->id }})">
                                    @if($item->active)
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </span>
                                <span wire:loading wire:target="toggleActive({{ $item->id }})" class="inline-block">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        @endcan
                    </x-table-actions>
                </x-table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $machinery->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Machinery::class)
                    <x-button href="{{ route('viticulturist.machinery.create') }}" variant="primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar Maquinaria
                    </x-button>
                @endcan
            </x-slot>
        @endif
    </x-data-table>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Año --}}
                    <div class="flex justify-end">
                        <select wire:model.live="yearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green)] focus:border-transparent">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <p class="text-sm font-medium text-green-700">Total Maquinaria</p>
                            <p class="text-3xl font-bold text-green-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-xs text-green-600 mt-2">Todas las máquinas</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                            <p class="text-sm font-medium text-blue-700">Actividades {{ $yearFilter }}</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $advancedStats['totalActivities'] ?? 0 }}</p>
                            <p class="text-xs text-blue-600 mt-2">Este año</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm font-medium text-purple-700">Maquinaria Activa</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['active'] }}</p>
                            <p class="text-xs text-purple-600 mt-2">De {{ $stats['total'] }} totales</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm font-medium text-orange-700">Con Actividades</p>
                            <p class="text-3xl font-bold text-orange-900 mt-1">{{ $advancedStats['withActivities'] ?? 0 }}</p>
                            <p class="text-xs text-orange-600 mt-2">Este año</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribución por Tipo --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Distribución por Tipo</h3>
                            <div class="space-y-4">
                                @forelse(($advancedStats['typeStats'] ?? []) as $type => $data)
                                    @php
                                        $total = ($advancedStats['typeStats'] ?? [])->sum('count');
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ $type }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div class="bg-[var(--color-agro-green)] h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                            <span>Activas: {{ $data['active'] }}</span>
                                            <span>Inactivas: {{ $data['inactive'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 text-center py-4">No hay datos de tipos</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Propiedad vs Alquiler --}}
                        <div class="bg-white rounded-xl p-6 border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">🏭 Propiedad vs Alquiler</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Propias</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['ownedCount'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $ownedPct = $stats['total'] > 0 ? (($advancedStats['ownedCount'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $ownedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Alquiladas</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['rentedCount'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $rentedPct = $stats['total'] > 0 ? (($advancedStats['rentedCount'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $rentedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con Actividades</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withActivities'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $withActPct = $stats['total'] > 0 ? (($advancedStats['withActivities'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $withActPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Con ROMA</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $advancedStats['withRoma'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        @php
                                            $romaPct = $stats['total'] > 0 ? (($advancedStats['withRoma'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-orange-500 h-3 rounded-full" style="width: {{ $romaPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Top 10 Maquinaria Más Usada --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🏆 Top 10 Maquinaria Más Usada ({{ $yearFilter }})</h3>
                        <div class="space-y-3">
                            @forelse(($advancedStats['mostUsed'] ?? []) as $index => $machinery)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-agro-green)] text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $machinery['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $machinery['type'] }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-[var(--color-agro-green-dark)]">{{ $machinery['activities_count'] }} actividades</span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No hay datos de uso</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Nuevas Maquinarias --}}
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📈 Nuevas Maquinarias (Últimos 12 meses)</h3>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newMachineryByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-[var(--color-agro-green)] rounded-t-lg transition-all hover:bg-[var(--color-agro-green-dark)]" 
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newMachineryByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} maquinarias"></div>
                                    <span class="text-xs text-gray-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
