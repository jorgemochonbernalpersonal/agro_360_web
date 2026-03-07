<div class="space-y-6 animate-fade-in">
    @php
        $planting    = $harvest->plotPlanting;
        $isCancelled = $harvest->status === 'cancelled';
    @endphp

    <x-agro.page-header
        :title="'Recepción #' . $harvest->id"
        :description="$harvest->harvest_start_date?->format('d/m/Y') . ($harvest->harvest_time ? ' · ' . $harvest->harvest_time : '')"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.grape-reception.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
            @if(!$isCancelled)
                <flux:button href="{{ route('winery.grape-reception.edit', $harvest) }}" variant="primary" icon="pencil-square">
                    Editar
                </flux:button>
            @endif
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Estado --}}
    @if($isCancelled)
        <flux:callout variant="danger" icon="x-circle">
            <flux:callout.text>Esta recepción ha sido <strong>anulada</strong> y no se contabiliza en los totales.</flux:callout.text>
        </flux:callout>
    @elseif($harvest->disqualified)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.text>
                <strong>Uva descartada.</strong>
                @if($harvest->disqualified_reason) Motivo: {{ $harvest->disqualified_reason }} @endif
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Contexto --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="map-pin" class="size-4 text-agro-600" />
                        <span class="font-semibold text-zinc-900">Viticultor y Parcela</span>
                    </div>
                </x-slot:header>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">Viticultor</dt>
                        <dd class="font-medium text-zinc-900">{{ $harvest->activity?->viticulturist?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Añada</dt>
                        <dd class="font-medium text-zinc-900">{{ $harvest->activity?->campaign?->year ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Parcela</dt>
                        <dd class="font-medium text-zinc-900">{{ $planting?->plot?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Variedad</dt>
                        <dd class="font-medium text-zinc-900">{{ $planting?->grapeVariety?->name ?? $planting?->name ?? '—' }}</dd>
                    </div>
                    @if($planting?->area_planted)
                        <div>
                            <dt class="text-zinc-500">Superficie</dt>
                            <dd class="font-medium text-zinc-900">{{ number_format($planting->area_planted, 2) }} ha</dd>
                        </div>
                    @endif
                    @if($planting?->harvest_limit_kg)
                        <div>
                            <dt class="text-zinc-500">Límite plantación</dt>
                            <dd class="font-medium text-zinc-900">{{ number_format($planting->harvest_limit_kg, 0) }} kg</dd>
                        </div>
                    @endif
                </dl>
            </x-agro.card>

            {{-- Datos de la recepción --}}
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="scale" class="size-4 text-agro-600" />
                        <span class="font-semibold text-zinc-900">Datos de la Recepción</span>
                    </div>
                </x-slot:header>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-zinc-500">Fecha</dt>
                        <dd class="font-medium text-zinc-900">{{ $harvest->harvest_start_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    @if($harvest->harvest_time)
                        <div>
                            <dt class="text-zinc-500">Hora de descarga</dt>
                            <dd class="font-medium text-zinc-900">{{ $harvest->harvest_time }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500">Kg recibidos</dt>
                        <dd class="font-bold text-zinc-900 text-lg">{{ number_format($harvest->total_weight, 0) }} kg</dd>
                    </div>
                    @if($harvest->yield_per_hectare)
                        <div>
                            <dt class="text-zinc-500">Rendimiento real</dt>
                            <dd class="font-medium text-zinc-900">{{ number_format($harvest->yield_per_hectare, 0) }} kg/ha</dd>
                        </div>
                    @endif
                    @if($harvest->harvest_ticket_number)
                        <div>
                            <dt class="text-zinc-500">Nº ticket / albarán</dt>
                            <dd class="font-mono text-zinc-900">{{ $harvest->harvest_ticket_number }}</dd>
                        </div>
                    @endif
                    @if($harvest->price_per_kg)
                        <div>
                            <dt class="text-zinc-500">Precio / kg</dt>
                            <dd class="font-medium text-zinc-900">{{ number_format($harvest->price_per_kg, 4) }} €</dd>
                        </div>
                    @endif
                    @if($harvest->total_value)
                        <div>
                            <dt class="text-zinc-500">Valor total</dt>
                            <dd class="font-bold text-agro-700">{{ number_format($harvest->total_value, 2) }} €</dd>
                        </div>
                    @endif
                    @if($harvest->vehicle_plate)
                        <div>
                            <dt class="text-zinc-500">Matrícula</dt>
                            <dd class="font-medium text-zinc-900">{{ $harvest->vehicle_plate }}</dd>
                        </div>
                    @endif
                    @if($harvest->transport_document_number)
                        <div>
                            <dt class="text-zinc-500">Doc. transporte</dt>
                            <dd class="font-medium text-zinc-900">{{ $harvest->transport_document_number }}</dd>
                        </div>
                    @endif
                    @if($harvest->destination_rega_code)
                        <div>
                            <dt class="text-zinc-500">Código REGA destino</dt>
                            <dd class="font-mono text-zinc-900">{{ $harvest->destination_rega_code }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-zinc-500">Depósito destino</dt>
                        <dd class="font-medium text-zinc-900">{{ $harvest->container?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </x-agro.card>

            {{-- Calidad --}}
            @if($harvest->baume_degree || $harvest->brix_degree || $harvest->potential_alcohol || $harvest->acidity_level || $harvest->ph_level || $harvest->health_status)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="beaker" class="size-4 text-blue-600" />
                            <span class="font-semibold text-zinc-900">Parámetros de Calidad</span>
                        </div>
                    </x-slot:header>
                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                        @if($harvest->potential_alcohol)
                            <div>
                                <dt class="text-zinc-500">Alcohol potencial</dt>
                                <dd class="font-bold text-zinc-900">{{ $harvest->potential_alcohol }}%</dd>
                            </div>
                        @endif
                        @if($harvest->baume_degree)
                            <div>
                                <dt class="text-zinc-500">Grado Baumé</dt>
                                <dd class="font-medium text-zinc-900">{{ $harvest->baume_degree }} °Bé</dd>
                            </div>
                        @endif
                        @if($harvest->brix_degree)
                            <div>
                                <dt class="text-zinc-500">Brix</dt>
                                <dd class="font-medium text-zinc-900">{{ $harvest->brix_degree }} °Bx</dd>
                            </div>
                        @endif
                        @if($harvest->acidity_level)
                            <div>
                                <dt class="text-zinc-500">Acidez total</dt>
                                <dd class="font-medium text-zinc-900">{{ $harvest->acidity_level }} g/L</dd>
                            </div>
                        @endif
                        @if($harvest->ph_level)
                            <div>
                                <dt class="text-zinc-500">pH</dt>
                                <dd class="font-medium text-zinc-900">{{ $harvest->ph_level }}</dd>
                            </div>
                        @endif
                        @if($harvest->health_status)
                            <div>
                                <dt class="text-zinc-500">Estado sanitario</dt>
                                <dd class="font-medium text-zinc-900">
                                    @php
                                        $labels = ['sano' => 'Sano', 'daño_leve' => 'Daño leve', 'daño_moderado' => 'Daño moderado', 'daño_grave' => 'Daño grave'];
                                    @endphp
                                    {{ $labels[$harvest->health_status] ?? $harvest->health_status }}
                                </dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Estado sanitario detallado --}}
                    @if($harvest->sanitary_state_grapes || $harvest->sanitary_state_agraces || $harvest->sanitary_state_botrytis || $harvest->sanitary_state_oidium || $harvest->sanitary_state_mildew)
                        <div class="mt-4 pt-4 border-t border-zinc-100">
                            <p class="text-xs font-medium text-zinc-500 mb-3">Estado sanitario detallado (%)</p>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                @foreach(['sanitary_state_grapes' => 'Granos sanos', 'sanitary_state_agraces' => 'Agraces', 'sanitary_state_botrytis' => 'Botrytis', 'sanitary_state_oidium' => 'Oidio', 'sanitary_state_mildew' => 'Mildiu'] as $field => $label)
                                    @if($harvest->$field)
                                        <div class="text-center p-2 bg-zinc-50 rounded-lg">
                                            <p class="text-xs text-zinc-500">{{ $label }}</p>
                                            <p class="font-bold text-zinc-900">{{ $harvest->$field }}%</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-agro.card>
            @endif

            {{-- Notas --}}
            @if($harvest->notes)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon icon="chat-bubble-left-ellipsis" class="size-4 text-zinc-500" />
                            <span class="font-semibold text-zinc-900">Observaciones</span>
                        </div>
                    </x-slot:header>
                    <p class="text-sm text-zinc-700 whitespace-pre-line">{{ $harvest->notes }}</p>
                </x-agro.card>
            @endif
        </div>

        {{-- Columna lateral --}}
        <div class="space-y-6">

            {{-- Resumen --}}
            <x-agro.card>
                <x-slot:header>
                    <span class="font-semibold text-zinc-900">Resumen</span>
                </x-slot:header>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Estado</span>
                        @if($isCancelled)
                            <flux:badge color="zinc">Anulada</flux:badge>
                        @elseif($harvest->disqualified)
                            <flux:badge color="red">Descartada</flux:badge>
                        @else
                            <flux:badge color="green">Activa</flux:badge>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Depósito</span>
                        @if($harvest->container)
                            <flux:badge color="green">{{ $harvest->container->name }}</flux:badge>
                        @else
                            <flux:badge color="zinc">Sin asignar</flux:badge>
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Registrada</span>
                        <span class="text-zinc-700">{{ $harvest->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($harvest->wasEdited())
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Última edición</span>
                            <span class="text-zinc-700">{{ $harvest->edited_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($harvest->editor)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Editada por</span>
                                <span class="text-zinc-700">{{ $harvest->editor->name }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </x-agro.card>

            {{-- Acciones --}}
            @if(!$isCancelled)
                <x-agro.card>
                    <x-slot:header>
                        <span class="font-semibold text-zinc-900">Acciones</span>
                    </x-slot:header>
                    <div class="space-y-2">
                        <flux:button href="{{ route('winery.grape-reception.edit', $harvest) }}" variant="primary" class="w-full" icon="pencil-square">
                            Editar recepción
                        </flux:button>
                        <flux:button href="{{ route('winery.grape-reception.assign', $harvest) }}" variant="ghost" class="w-full" icon="cube">
                            {{ $harvest->container_id ? 'Reasignar depósito' : 'Asignar depósito' }}
                        </flux:button>
                        <flux:button
                            href="{{ route('winery.grape-reception.export-pdf-single', $harvest) }}"
                            target="_blank"
                            variant="ghost"
                            class="w-full"
                            icon="document-arrow-down"
                        >
                            Descargar PDF
                        </flux:button>
                    </div>
                </x-agro.card>
            @endif
        </div>
    </div>
</div>
