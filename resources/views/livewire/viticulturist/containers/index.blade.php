<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Contenedores"
        description="Gestiona tus barricas, depositos y tanques de bodega"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.digital-notebook.containers.create') }}" variant="primary" icon="plus">
                Nuevo Contenedor
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Tabs --}}
    @php
        $activeCount = $stats['total_containers'] - \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count();
        $inactiveCount = \App\Models\Container::where('user_id', auth()->id())->where('archived', true)->count();
        $currentTabValue = $viewMode === 'stats' ? 'statistics' : ($filterStatus === 'archived' ? 'inactive' : 'active');
    @endphp

    <x-agro.card :padding="false">
        <div class="border-b border-zinc-200">
            <nav class="flex -mb-px">
                <button
                    wire:click="switchTab('active')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTabValue === 'active' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Activos</span>
                    @if($activeCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTabValue === 'active' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $activeCount }}
                        </span>
                    @endif
                </button>
                <button
                    wire:click="switchTab('inactive')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTabValue === 'inactive' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <span>Archivados</span>
                    @if($inactiveCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $currentTabValue === 'inactive' ? 'bg-agro-700 text-white' : 'bg-zinc-200 text-zinc-700' }}">
                            {{ $inactiveCount }}
                        </span>
                    @endif
                </button>
                <button
                    wire:click="switchTab('statistics')"
                    class="group inline-flex items-center gap-2 px-6 py-4 border-b-2 font-medium text-sm transition-colors
                        {{ $currentTabValue === 'statistics' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Estadisticas</span>
                </button>
            </nav>
        </div>

        <div class="p-6">
            {{-- STATS TAB --}}
            @if($viewMode === 'stats')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-agro.card>
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Capacidad Total</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-zinc-600">Total instalada:</span>
                                <span class="font-bold text-zinc-900">{{ number_format($stats['total_capacity'], 0) }} L</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-600">Ocupada:</span>
                                <span class="font-bold text-blue-600">{{ number_format($stats['used_capacity'], 0) }} L</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-600">Disponible:</span>
                                <span class="font-bold text-green-600">{{ number_format($stats['available_capacity'], 0) }} L</span>
                            </div>
                            <div class="pt-3 border-t">
                                <div class="flex justify-between mb-2">
                                    <span class="text-zinc-600">Ocupacion:</span>
                                    <span class="font-bold text-zinc-900">{{ $stats['occupancy_percentage'] }}%</span>
                                </div>
                                <x-agro.progress-bar :percentage="$stats['occupancy_percentage']" />
                            </div>
                        </div>
                    </x-agro.card>

                    <x-agro.card>
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Distribucion por Estado</h3>
                        <div class="space-y-4">
                            @foreach([
                                ['label' => 'Vacios', 'value' => $stats['empty_containers'], 'color' => 'bg-blue-500'],
                                ['label' => 'Disponibles', 'value' => $stats['available_containers'], 'color' => 'bg-green-500'],
                                ['label' => 'Llenos', 'value' => $stats['full_containers'], 'color' => 'bg-red-500'],
                            ] as $item)
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm text-zinc-600">{{ $item['label'] }}</span>
                                        <span class="text-sm font-semibold text-zinc-900">{{ $item['value'] }}</span>
                                    </div>
                                    <div class="w-full bg-zinc-200 rounded-full h-2">
                                        <div class="{{ $item['color'] }} h-2 rounded-full"
                                             style="width: {{ $stats['total_containers'] > 0 ? ($item['value'] / $stats['total_containers'] * 100) : 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-agro.card>

                    <x-agro.card>
                        <h3 class="text-lg font-bold text-zinc-900 mb-4">Resumen General</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm text-zinc-700">Total Contenedores</span>
                                <span class="text-2xl font-bold text-blue-600">{{ $stats['total_containers'] }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <span class="text-sm text-zinc-700">Capacidad Media</span>
                                <span class="text-lg font-bold text-green-600">
                                    {{ $stats['total_containers'] > 0 ? number_format($stats['total_capacity'] / $stats['total_containers'], 0) : 0 }} L
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm text-zinc-700">Eficiencia de Uso</span>
                                <span class="text-lg font-bold text-purple-600">{{ $stats['occupancy_percentage'] }}%</span>
                            </div>
                        </div>
                    </x-agro.card>
                </div>
            @else
                {{-- Filtros --}}
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <x-agro.filter-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar por nombre o numero de serie..."
                    />
                    <x-agro.filter-select wire:model.live="filterStatus">
                        <option value="">Todos los estados</option>
                        <option value="empty">Vacios</option>
                        <option value="available">Disponibles</option>
                        <option value="full">Llenos</option>
                        <option value="archived">Archivados</option>
                    </x-agro.filter-select>
                    @if($search || $filterStatus)
                        <flux:button wire:click="resetFilters" variant="ghost" size="sm">
                            Limpiar Filtros
                        </flux:button>
                    @endif
                </div>

                {{-- Grid de Contenedores --}}
                @if($containers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($containers as $container)
                            <x-agro.card>
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-zinc-900 text-lg">{{ $container->name }}</h3>
                                        @if($container->serial_number)
                                            <p class="text-xs text-zinc-500 mt-1">SN: {{ $container->serial_number }}</p>
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full flex-shrink-0
                                        {{ $container->isEmpty() ? 'bg-blue-100 text-blue-800' : ($container->isFull() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $container->isEmpty() ? 'Vacio' : ($container->isFull() ? 'Lleno' : 'Disponible') }}
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <x-agro.progress-bar
                                        :percentage="$container->getOccupancyPercentage()"
                                        label="Ocupacion"
                                        :current-value="$container->used_capacity"
                                        :max-value="$container->capacity"
                                        unit="L"
                                    />
                                </div>

                                <div class="pt-3 border-t border-zinc-100 flex gap-2 justify-center">
                                    <x-agro.action-button
                                        variant="view"
                                        href="{{ route('viticulturist.digital-notebook.containers.show', $container->id) }}"
                                    />
                                    <x-agro.action-button
                                        variant="edit"
                                        href="{{ route('viticulturist.digital-notebook.containers.edit', $container->id) }}"
                                    />
                                    @if(!$container->archived)
                                        <x-agro.action-button
                                            variant="archive"
                                            wireClick="archive({{ $container->id }})"
                                        />
                                    @else
                                        <x-agro.action-button
                                            variant="activate"
                                            wireClick="unarchive({{ $container->id }})"
                                        />
                                    @endif
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $containers->links() }}</div>
                @else
                    <x-agro.empty-state
                        message="No hay contenedores"
                        description="Comienza creando tu primer contenedor para gestionar tu bodega."
                    >
                        <x-slot name="action">
                            <flux:button href="{{ route('viticulturist.digital-notebook.containers.create') }}" variant="primary" icon="plus">
                                Crear Contenedor
                            </flux:button>
                        </x-slot>
                    </x-agro.empty-state>
                @endif
            @endif
        </div>
    </x-agro.card>
</div>
