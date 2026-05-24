<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        :title="$container->name"
        :description="$container->serial_number ? 'SN: ' . $container->serial_number : __('Contenedor de bodega')"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.containers.edit', $container->id) }}" variant="primary" icon="pencil-square">
                {{ __('Editar') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Estado y badges --}}
            <x-agro.card>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge }}">
                        {{ $statusText }}
                    </span>
                    @if($maintenanceWarning)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            ⚠️ {{ $maintenanceWarning['message'] }}
                        </span>
                    @endif
                </div>
            </x-agro.card>

            {{-- Ocupacion --}}
            <x-agro.card>
                <h2 class="text-xl font-bold text-zinc-900 mb-4">{{ __('Ocupación') }}</h2>
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-zinc-600 mb-2">
                        <span>{{ __('Capacidad Usada') }}</span>
                        <span class="font-semibold text-lg">{{ $container->getOccupancyPercentage() }}%</span>
                    </div>
                    <x-agro.progress-bar :percentage="$container->getOccupancyPercentage()" />
                </div>
                <div class="grid grid-cols-3 gap-4 text-center mt-4">
                    <div class="bg-zinc-50 rounded-lg p-3">
                        <p class="text-xs text-zinc-600">{{ __('Capacidad Total') }}</p>
                        <p class="text-lg font-bold text-zinc-900">{{ number_format($container->capacity, 0) }} L</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-zinc-600">{{ __('Ocupado') }}</p>
                        <p class="text-lg font-bold text-blue-900">{{ number_format($container->used_capacity, 0) }} L</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-zinc-600">{{ __('Disponible') }}</p>
                        <p class="text-lg font-bold text-green-900">{{ number_format($container->getAvailableCapacity(), 0) }} L</p>
                    </div>
                </div>
            </x-agro.card>

            {{-- Vendimias Almacenadas --}}
            @if($container->harvests->count() > 0)
                <x-agro.card>
                    <h2 class="text-xl font-bold text-zinc-900 mb-4">
                        {{ __('Vendimias Almacenadas') }} ({{ $container->harvests->count() }})
                    </h2>
                    <div class="space-y-3">
                        @foreach($container->harvests as $harvest)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition">
                                <div>
                                    <p class="font-medium text-zinc-900">
                                        {{ $harvest->plotPlanting->grapeVariety->name ?? __('Vendimia') }}
                                    </p>
                                    <p class="text-sm text-zinc-600">
                                        {{ number_format($harvest->total_weight, 0) }} kg |
                                        {{ $harvest->harvest_start_date->format('d/m/Y') }}
                                    </p>
                                    @if($harvest->brix_degree)
                                        <p class="text-xs text-zinc-500 mt-1">
                                            Brix: {{ $harvest->brix_degree }}° | pH: {{ $harvest->ph_level }}
                                        </p>
                                    @endif
                                </div>
                                <x-agro.action-button
                                    variant="view"
                                    href="{{ roleRoute('viticulturist.digital-notebook.harvest.show', $harvest->id) }}"
                                />
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif

            {{-- Historial de Operaciones --}}
            @if($container->histories->count() > 0)
                <x-agro.card>
                    <h2 class="text-xl font-bold text-zinc-900 mb-4">{{ __('Historial de Operaciones') }}</h2>
                    <div class="space-y-2">
                        @foreach($container->histories->take(10) as $history)
                            <div class="flex items-center text-sm py-2 border-b border-zinc-100 last:border-0">
                                <span class="text-zinc-500 w-36 flex-shrink-0">{{ $history->start_date->format('d/m/Y H:i') }}</span>
                                <span class="mx-2 text-zinc-300">•</span>
                                <span class="text-zinc-900">
                                    {{ ucfirst($history->operation_type) }}:
                                    {{ $history->quantity > 0 ? '+' : '' }}{{ number_format($history->quantity, 0) }} L
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif
        </div>

        {{-- Columna Lateral --}}
        <div class="space-y-6">
            {{-- Informacion General --}}
            <x-agro.card>
                <h3 class="font-bold text-zinc-900 mb-4">{{ __('Información') }}</h3>
                <dl class="space-y-3 text-sm">
                    @if($container->description)
                        <div>
                            <dt class="text-zinc-500 text-xs uppercase tracking-wide">{{ __('Descripción') }}</dt>
                            <dd class="text-zinc-900 mt-1">{{ $container->description }}</dd>
                        </div>
                    @endif
                    @if($container->purchase_date)
                        <div>
                            <dt class="text-zinc-500 text-xs uppercase tracking-wide">{{ __('Fecha de Compra') }}</dt>
                            <dd class="text-zinc-900 mt-1">{{ $container->purchase_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </x-agro.card>

            {{-- Acciones Rapidas --}}
            <x-agro.card>
                <h3 class="font-bold text-zinc-900 mb-4">{{ __('Acciones Rápidas') }}</h3>
                <div class="space-y-2">
                    <flux:button
                        href="{{ roleRoute('viticulturist.digital-notebook.harvest.create', ['container_id' => $container->id]) }}"
                        variant="primary"
                        icon="plus"
                        class="w-full justify-center"
                    >
                        {{ __('Nueva Vendimia') }}
                    </flux:button>
                    <flux:button
                        href="{{ roleRoute('viticulturist.containers.edit', $container->id) }}"
                        variant="ghost"
                        icon="pencil-square"
                        class="w-full justify-center"
                    >
                        {{ __('Editar Contenedor') }}
                    </flux:button>
                </div>
            </x-agro.card>
        </div>
    </div>
</div>
