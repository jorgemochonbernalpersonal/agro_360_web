<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Maquinaria"
        description="Gestiona tu maquinaria y equipos agricolas"
    >
        <x-slot:actions>
            @can('create', \App\Models\Machinery::class)
                <flux:button href="{{ route('viticulturist.machinery.create') }}" variant="primary" icon="plus">
                    Nueva Maquinaria
                </flux:button>
            @endcan
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Tabs Navigation -->
    <x-agro.card :padding="false">
        <div class="border-b border-zinc-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'active' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <flux:icon icon="check-circle" class="size-5" />
                    <span>Activas</span>
                    @if($stats['active'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'active' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $stats['active'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'inactive' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <flux:icon icon="x-circle" class="size-5" />
                    <span>Inactivas</span>
                    @if($stats['inactive'] > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTab === 'inactive' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $stats['inactive'] }}
                        </span>
                    @endif
                </button>

                <button
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTab === 'statistics' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <flux:icon icon="chart-bar" class="size-5" />
                    <span>Estadisticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- ACTIVE/INACTIVE TABS --}}
            @if($currentTab === 'active' || $currentTab === 'inactive')
            <!-- Filtros -->
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <x-agro.filter-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre, marca, modelo..."
                />
                <x-agro.filter-select wire:model.live="typeFilter">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </x-agro.filter-select>
                @if($search || $typeFilter)
                    <flux:button wire:click="clearFilters" variant="ghost" size="sm">
                        Limpiar Filtros
                    </flux:button>
                @endif
            </div>

    <x-agro.data-table :headers="['Maquinaria', 'Tipo', 'Marca/Modelo', 'ROMA', 'Estado', 'Actividades', 'Acciones']" empty-message="No hay maquinaria registrada" empty-description="Comienza agregando tu primera maquinaria o equipo agricola">
        @if($machinery->count() > 0)
            @foreach($machinery as $item)
                <x-agro.table-row>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-agro-50 flex items-center justify-center">
                                <flux:icon icon="cog-6-tooth" class="size-5 text-agro-600" />
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-900">{{ $item->name }}</div>
                                @if($item->year)
                                    <div class="text-xs text-zinc-500 mt-1">Ano: {{ $item->year }}</div>
                                @endif
                            </div>
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm font-medium text-zinc-900">{{ $item->type }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($item->brand || $item->model)
                            <span class="text-sm text-zinc-700">
                                {{ $item->brand }} {{ $item->model }}
                            </span>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($item->roma_registration)
                            <span class="text-sm font-medium text-zinc-900">{{ $item->roma_registration }}</span>
                        @else
                            <span class="text-sm text-zinc-400">-</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex items-center gap-2 flex-wrap">
                            <x-agro.status-badge :active="$item->active" />
                            @if($item->is_rented)
                                <x-agro.status-badge label="Alquilada" type="info" />
                            @endif
                        </div>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="text-sm font-semibold text-zinc-900">{{ $item->activities_count }}</span>
                        <span class="text-xs text-zinc-500"> actividades</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <div class="flex items-center justify-end gap-2">
                            @can('view', $item)
                                <x-agro.action-button variant="view" href="{{ route('viticulturist.machinery.show', $item) }}" />
                            @endcan
                            @can('update', $item)
                                <x-agro.action-button variant="edit" href="{{ route('viticulturist.machinery.edit', $item) }}" />
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
                                            <flux:icon icon="x-circle" class="size-5" />
                                        @else
                                            <flux:icon icon="check-circle" class="size-5" />
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="toggleActive({{ $item->id }})" class="inline-block">
                                        <flux:icon icon="arrow-path" class="size-5 animate-spin" />
                                    </span>
                                </button>
                            @endcan
                        </div>
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
            <x-slot name="pagination">
                {{ $machinery->links() }}
            </x-slot>
        @else
            <x-slot name="emptyAction">
                @can('create', \App\Models\Machinery::class)
                    <flux:button href="{{ route('viticulturist.machinery.create') }}" variant="primary" icon="plus">
                        Agregar Maquinaria
                    </flux:button>
                @endcan
            </x-slot>
        @endif
    </x-agro.data-table>
            @endif

            {{-- STATISTICS TAB --}}
            @if($currentTab === 'statistics')
                <div class="space-y-6">
                    {{-- Filtro de Ano --}}
                    <div class="flex justify-end">
                        <flux:select wire:model.live="yearFilter" class="w-auto">
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    {{-- KPIs --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-agro.stat-card label="Total Maquinaria" :value="$stats['total']" description="Todas las maquinas" icon="cog-6-tooth" color="agro" />
                        <x-agro.stat-card label="Actividades {{ $yearFilter }}" :value="$advancedStats['totalActivities'] ?? 0" description="Este ano" icon="clipboard-document-list" color="blue" />
                        <x-agro.stat-card label="Maquinaria Activa" :value="$stats['active']" :description="'De ' . $stats['total'] . ' totales'" icon="check-circle" color="purple" />
                        <x-agro.stat-card label="Con Actividades" :value="$advancedStats['withActivities'] ?? 0" description="Este ano" icon="chart-bar" color="orange" />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Distribucion por Tipo --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-agro-50">
                                        <flux:icon icon="chart-pie" class="size-4 text-agro-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Distribucion por Tipo</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-4">
                                @forelse(($advancedStats['typeStats'] ?? []) as $type => $data)
                                    @php
                                        $total = ($advancedStats['typeStats'] ?? [])->sum('count');
                                        $percentage = $total > 0 ? ($data['count'] / $total) * 100 : 0;
                                    @endphp
                                    <div>
                                        <div class="flex justify-between mb-2">
                                            <span class="text-sm font-medium text-zinc-700">{{ $type }}</span>
                                            <span class="text-sm font-bold text-zinc-900">{{ $data['count'] }} ({{ number_format($percentage, 1) }}%)</span>
                                        </div>
                                        <div class="w-full bg-zinc-200 rounded-full h-3">
                                            <div class="bg-agro-500 h-3 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <div class="flex gap-4 mt-1 text-xs text-zinc-500">
                                            <span>Activas: {{ $data['active'] }}</span>
                                            <span>Inactivas: {{ $data['inactive'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-zinc-500 text-center py-4">No hay datos de tipos</p>
                                @endforelse
                            </div>
                        </x-agro.card>

                        {{-- Propiedad vs Alquiler --}}
                        <x-agro.card>
                            <x-slot:header>
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 rounded-lg bg-blue-50">
                                        <flux:icon icon="arrow-trending-up" class="size-4 text-blue-600" />
                                    </div>
                                    <span class="font-semibold text-zinc-900 text-sm">Propiedad vs Alquiler</span>
                                </div>
                            </x-slot:header>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Propias</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['ownedCount'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $ownedPct = $stats['total'] > 0 ? (($advancedStats['ownedCount'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ $ownedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Alquiladas</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['rentedCount'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $rentedPct = $stats['total'] > 0 ? (($advancedStats['rentedCount'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $rentedPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con Actividades</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withActivities'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $withActPct = $stats['total'] > 0 ? (($advancedStats['withActivities'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-purple-500 h-3 rounded-full" style="width: {{ $withActPct }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-zinc-700">Con ROMA</span>
                                        <span class="text-sm font-bold text-zinc-900">{{ $advancedStats['withRoma'] ?? 0 }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-3">
                                        @php
                                            $romaPct = $stats['total'] > 0 ? (($advancedStats['withRoma'] ?? 0) / $stats['total']) * 100 : 0;
                                        @endphp
                                        <div class="bg-orange-500 h-3 rounded-full" style="width: {{ $romaPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    </div>

                    {{-- Top 10 Maquinaria Mas Usada --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-agro-50">
                                    <flux:icon icon="trophy" class="size-4 text-agro-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Top 10 Maquinaria Mas Usada ({{ $yearFilter }})</span>
                            </div>
                        </x-slot:header>
                        <div class="space-y-3">
                            @forelse(($advancedStats['mostUsed'] ?? []) as $index => $machinery)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-agro-500 text-white flex items-center justify-center font-bold text-sm">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <p class="font-semibold text-zinc-900">{{ $machinery['name'] }}</p>
                                            <p class="text-xs text-zinc-500">{{ $machinery['type'] }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-agro-700">{{ $machinery['activities_count'] }} actividades</span>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-center py-4">No hay datos de uso</p>
                            @endforelse
                        </div>
                    </x-agro.card>

                    {{-- Nuevas Maquinarias --}}
                    <x-agro.card>
                        <x-slot:header>
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-purple-50">
                                    <flux:icon icon="arrow-trending-up" class="size-4 text-purple-600" />
                                </div>
                                <span class="font-semibold text-zinc-900 text-sm">Nuevas Maquinarias (Ultimos 12 meses)</span>
                            </div>
                        </x-slot:header>
                        <div class="h-64 flex items-end justify-between gap-2">
                            @foreach(($advancedStats['newMachineryByMonth'] ?? []) as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-agro-500 rounded-t-lg transition-all hover:bg-agro-700"
                                        style="height: {{ $month['count'] > 0 ? ($month['count'] / max(collect($advancedStats['newMachineryByMonth'] ?? [])->pluck('count')->max(), 1)) * 100 : 5 }}%"
                                        title="{{ $month['count'] }} maquinarias"></div>
                                    <span class="text-xs text-zinc-600 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-agro.card>
                </div>
            @endif
        </div>
    </x-agro.card>
</div>
