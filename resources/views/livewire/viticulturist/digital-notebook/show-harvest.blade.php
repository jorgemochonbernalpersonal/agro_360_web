<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Detalle de Cosecha"
        description="Información completa de la cosecha y sus contenedores"
    >
        <x-slot:actions>
            <flux:button
                :href="route('viticulturist.digital-notebook.harvest.edit', $harvest->id)"
                variant="primary"
                icon="pencil-square"
            >
                Editar
            </flux:button>
            <flux:button
                :href="route('viticulturist.digital-notebook')"
                variant="ghost"
                icon="arrow-left"
            >
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Información Básica --}}
            <x-agro.card>
                <x-slot:header>
                    <flux:heading size="sm">Información Básica</flux:heading>
                </x-slot:header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Parcela</p>
                        <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->plot->name ?? 'Sin parcela' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Variedad</p>
                        @if($harvest->plotPlanting->name)
                            <p class="text-xs text-purple-600 font-medium mt-0.5">{{ $harvest->plotPlanting->name }}</p>
                        @endif
                        <p class="text-sm font-semibold text-zinc-900">{{ $harvest->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Campaña</p>
                        <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->campaign->name ?? 'Sin campaña' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Fecha de Cosecha</p>
                        <p class="text-sm font-semibold text-zinc-900 mt-0.5">
                            {{ $harvest->harvest_start_date->format('d/m/Y') }}
                            @if($harvest->harvest_end_date)
                                — {{ $harvest->harvest_end_date->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Peso Total</p>
                        <p class="text-lg font-bold text-purple-700 mt-0.5">{{ number_format($harvest->total_weight, 2) }} kg</p>
                    </div>

                    @if($harvest->yield_per_hectare)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Rendimiento</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->yield_per_hectare, 2) }} kg/ha</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            {{-- Calidad de la Uva --}}
            @if($harvest->baume_degree || $harvest->brix_degree || $harvest->acidity_level || $harvest->ph_level || $harvest->color_rating || $harvest->aroma_rating || $harvest->health_status)
                <x-agro.card>
                    <x-slot:header>
                        <flux:heading size="sm">Calidad de la Uva</flux:heading>
                    </x-slot:header>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                        @if($harvest->baume_degree)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Grados Baumé</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->baume_degree, 2) }} °Bé</p>
                            </div>
                        @endif

                        @if($harvest->brix_degree)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Grados Brix</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->brix_degree, 2) }} °Bx</p>
                            </div>
                        @endif

                        @if($harvest->acidity_level)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Acidez Total</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->acidity_level, 2) }} g/L</p>
                            </div>
                        @endif

                        @if($harvest->ph_level)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">pH</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->ph_level, 2) }}</p>
                            </div>
                        @endif

                        @if($harvest->color_rating)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Color</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5 capitalize">{{ $harvest->color_rating }}</p>
                            </div>
                        @endif

                        @if($harvest->aroma_rating)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Aroma</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5 capitalize">{{ $harvest->aroma_rating }}</p>
                            </div>
                        @endif

                        @if($harvest->health_status)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Estado de Salud</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5 capitalize">{{ str_replace('_', ' ', $harvest->health_status) }}</p>
                            </div>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            {{-- Destino y Valor Económico --}}
            @if($harvest->destination_type || $harvest->destination || $harvest->buyer_name || $harvest->price_per_kg || $harvest->total_value)
                <x-agro.card>
                    <x-slot:header>
                        <flux:heading size="sm">Destino y Valor Económico</flux:heading>
                    </x-slot:header>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        @if($harvest->destination_type)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Tipo de Destino</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5 capitalize">{{ str_replace('_', ' ', $harvest->destination_type) }}</p>
                            </div>
                        @endif

                        @if($harvest->destination)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Destino</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->destination }}</p>
                            </div>
                        @endif

                        @if($harvest->buyer_name)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Comprador</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->buyer_name }}</p>
                            </div>
                        @endif

                        @if($harvest->price_per_kg)
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Precio por kg</p>
                                <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->price_per_kg, 3) }} €/kg</p>
                            </div>
                        @endif

                        @if($harvest->total_value)
                            <div class="sm:col-span-2">
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Valor Total</p>
                                <p class="text-2xl font-bold text-agro-700 mt-0.5">{{ number_format($harvest->total_value, 2) }} €</p>
                            </div>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            {{-- Contenedor --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">Contenedor</flux:heading>
                        <flux:button
                            :href="route('viticulturist.containers.create') . '?harvest_id=' . $harvest->id"
                            variant="ghost"
                            size="sm"
                            icon="plus"
                        >
                            Agregar
                        </flux:button>
                    </div>
                </x-slot:header>

                @if($harvest->container)
                    @php
                        $container = $harvest->container;
                        $containerType  = $container->isEmpty() ? 'gray' : ($container->isFull() ? 'info' : 'success');
                        $containerLabel = $container->isEmpty() ? 'Vacío' : ($container->isFull() ? 'Lleno' : 'Parcial');
                    @endphp

                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-zinc-900">
                                        {{ $container->name }}
                                        @if($container->serial_number)
                                            <span class="text-zinc-400">#{{ $container->serial_number }}</span>
                                        @endif
                                    </p>
                                    <x-agro.status-badge :active="!$container->archived" />
                                    <x-agro.status-badge :label="$containerLabel" :type="$containerType" />
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-3 text-sm">
                                    <div>
                                        <span class="text-zinc-500">Cantidad</span>
                                        <p class="font-semibold text-zinc-900">{{ $container->quantity }}</p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500">Capacidad</span>
                                        <p class="font-semibold text-zinc-900">
                                            {{ number_format($container->used_capacity, 2) }} / {{ number_format($container->capacity, 2) }} kg
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500">Ocupación</span>
                                        <p class="font-semibold text-zinc-900">{{ number_format($container->getOccupancyPercentage(), 1) }}%</p>
                                    </div>
                                    @if($container->currentState?->location)
                                        <div>
                                            <span class="text-zinc-500">Ubicación</span>
                                            <p class="font-semibold text-zinc-900">{{ $container->currentState->location }}</p>
                                        </div>
                                    @endif
                                    @if($container->purchase_date)
                                        <div>
                                            <span class="text-zinc-500">Compra</span>
                                            <p class="font-semibold text-zinc-900">{{ $container->purchase_date->format('d/m/Y') }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if($container->description)
                                    <p class="mt-3 pt-3 border-t border-zinc-100 text-sm text-zinc-600">{{ $container->description }}</p>
                                @endif
                            </div>

                            <flux:button
                                :href="route('viticulturist.containers.edit', $container)"
                                variant="ghost"
                                size="sm"
                                icon="pencil-square"
                            />
                        </div>

                        {{-- Barra de ocupación --}}
                        <x-agro.progress-bar
                            :percentage="$container->getOccupancyPercentage()"
                            label="Ocupación del contenedor"
                            :currentValue="$container->used_capacity"
                            :maxValue="$container->capacity"
                            unit="kg"
                        />
                    </div>
                @else
                    <x-agro.empty-state
                        icon="archive-box"
                        message="Sin contenedor asignado"
                        description="Esta cosecha no tiene contenedor asignado"
                    >
                        <x-slot:action>
                            <flux:button
                                :href="route('viticulturist.containers.create') . '?harvest_id=' . $harvest->id"
                                variant="primary"
                                icon="plus"
                            >
                                Agregar Contenedor
                            </flux:button>
                        </x-slot:action>
                    </x-agro.empty-state>
                @endif
            </x-agro.card>

            {{-- Información de la Actividad --}}
            <x-agro.card>
                <x-slot:header>
                    <flux:heading size="sm">Información de la Actividad</flux:heading>
                </x-slot:header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Fecha de Actividad</p>
                        <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->activity_date->format('d/m/Y') }}</p>
                    </div>

                    @if($harvest->activity->crew)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Equipo</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->crew->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->crewMember?->viticulturist)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Viticultor</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->crewMember->viticulturist->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->machinery)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Maquinaria</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->machinery->name }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->weather_conditions)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Condiciones Meteorológicas</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ $harvest->activity->weather_conditions }}</p>
                        </div>
                    @endif

                    @if($harvest->activity->temperature)
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Temperatura</p>
                            <p class="text-sm font-semibold text-zinc-900 mt-0.5">{{ number_format($harvest->activity->temperature, 1) }} °C</p>
                        </div>
                    @endif

                    @if($harvest->activity->notes)
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Notas</p>
                            <p class="text-sm text-zinc-700 mt-0.5 whitespace-pre-wrap">{{ $harvest->activity->notes }}</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>
        </div>

        {{-- Sidebar (1/3) --}}
        <div class="space-y-4">

            {{-- Rendimiento Estimado --}}
            @if($estimatedYield)
                <x-agro.card>
                    <x-slot:header>
                        <flux:heading size="sm">Rendimiento Estimado</flux:heading>
                    </x-slot:header>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Estimado</p>
                            <p class="text-sm font-bold text-zinc-900 mt-0.5">{{ number_format($estimatedYield->estimated_total_yield, 2) }} kg</p>
                            <p class="text-xs text-zinc-500">{{ number_format($estimatedYield->estimated_yield_per_hectare, 2) }} kg/ha</p>
                        </div>

                        @if($estimatedYield->hasActualYield())
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Real</p>
                                <p class="text-sm font-bold text-zinc-900 mt-0.5">{{ number_format($estimatedYield->actual_total_yield, 2) }} kg</p>
                                <p class="text-xs text-zinc-500">{{ number_format($estimatedYield->actual_yield_per_hectare, 2) }} kg/ha</p>
                            </div>

                            @if($estimatedYield->variance_percentage)
                                <div>
                                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Diferencia</p>
                                    @php $vp = $estimatedYield->variance_percentage; @endphp
                                    <p class="text-sm font-bold mt-0.5 {{ $vp > 0 ? 'text-agro-600' : 'text-red-600' }}">
                                        {{ $vp > 0 ? '+' : '' }}{{ number_format($vp, 2) }}%
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </x-agro.card>
            @endif

            {{-- Estado vs Límite de Plantación --}}
            @if($harvestLimitInfo)
                <x-agro.card>
                    <x-slot:header>
                        <flux:heading size="sm">Estado vs Límite</flux:heading>
                    </x-slot:header>

                    <div class="space-y-4">
                        <x-agro.progress-bar
                            :percentage="$harvestLimitInfo['percentage']"
                            label="Uso del límite de plantación"
                            :currentValue="$harvestLimitInfo['harvested']"
                            :maxValue="$harvestLimitInfo['limit']"
                            unit="kg"
                        />

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Límite máximo</span>
                                <span class="font-semibold text-zinc-900">{{ number_format($harvestLimitInfo['limit'], 2) }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Total cosechado</span>
                                <span class="font-semibold text-zinc-900">{{ number_format($harvestLimitInfo['harvested'], 2) }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Disponible</span>
                                <span class="font-semibold {{ $harvestLimitInfo['remaining'] < 0 ? 'text-red-600' : 'text-agro-600' }}">
                                    {{ number_format($harvestLimitInfo['remaining'], 2) }} kg
                                </span>
                            </div>
                        </div>

                        @if($harvestLimitInfo['exceeds'])
                            <flux:callout variant="danger" icon="exclamation-triangle">
                                <flux:callout.text>El total cosechado excede el límite de la plantación.</flux:callout.text>
                            </flux:callout>
                        @elseif($harvestLimitInfo['percentage'] > 80)
                            <flux:callout variant="warning" icon="exclamation-circle">
                                <flux:callout.text>Se ha utilizado más del 80% del límite.</flux:callout.text>
                            </flux:callout>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            {{-- Historial de Ediciones --}}
            @if($harvest->wasEdited())
                <x-agro.card class="border-amber-200 bg-amber-50">
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="pencil-square" class="size-4 text-amber-600" />
                            <flux:heading size="sm">Historial de Ediciones</flux:heading>
                        </div>
                    </x-slot:header>

                    <div class="space-y-2 text-sm">
                        <div>
                            <p class="text-xs font-medium text-amber-700">Editada el</p>
                            <p class="font-semibold text-amber-900">{{ $harvest->edited_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($harvest->editor)
                            <div>
                                <p class="text-xs font-medium text-amber-700">Por</p>
                                <p class="font-semibold text-amber-900">{{ $harvest->editor->name }}</p>
                            </div>
                        @endif
                        @if($harvest->edit_notes)
                            <div>
                                <p class="text-xs font-medium text-amber-700">Motivo</p>
                                <p class="text-amber-900">{{ $harvest->edit_notes }}</p>
                            </div>
                        @endif
                    </div>
                </x-agro.card>
            @endif

            {{-- Fechas --}}
            <x-agro.card>
                <x-slot:header>
                    <flux:heading size="sm">Registro</flux:heading>
                </x-slot:header>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-zinc-500">Creada</span>
                        <span class="font-medium text-zinc-700">{{ $harvest->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-zinc-500">Actualizada</span>
                        <span class="font-medium text-zinc-700">{{ $harvest->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </x-agro.card>
        </div>
    </div>
</div>
