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
                        <dd class="font-medium text-zinc-900">{{ $harvest->batch?->viticulturist?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500">Añada</dt>
                        <dd class="font-medium text-zinc-900">{{ $harvest->batch?->vintage_year ?? '—' }}</dd>
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
                            <dt class="text-zinc-500">Nº de ticket/Vendimia</dt>
                            <dd class="font-mono text-zinc-900">{{ $harvest->harvest_ticket_number }}</dd>
                        </div>
                    @endif
                    @if($harvest->price_per_kg)
                        <div>
                            <dt class="text-zinc-500">Precio / kg</dt>
                            <dd class="font-medium text-zinc-900">{{ number_format($harvest->price_per_kg, 3) }} €</dd>
                        </div>
                    @endif
                    @if($harvest->total_value)
                        <div>
                            <dt class="text-zinc-500">Valor total</dt>
                            <dd class="font-bold text-agro-700">{{ number_format($harvest->total_value, 3) }} €</dd>
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

            {{-- Declaración del viticultor --}}
            @php $delivery = $harvest->delivery; @endphp
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon icon="truck" class="size-4 text-violet-500" />
                        <span class="font-semibold text-zinc-900">Declaración del viticultor</span>
                    </div>
                </x-slot:header>

                @if($delivery)
                    @php
                        $badgeColor = match($delivery->status) {
                            'matched'  => 'green',
                            'disputed' => 'amber',
                            'resolved' => 'blue',
                            default    => 'zinc',
                        };
                        $badgeLabel = match($delivery->status) {
                            'matched'  => 'Coincide',
                            'disputed' => 'En reclamación',
                            'resolved' => 'Resuelta',
                            default    => 'Pendiente',
                        };
                    @endphp

                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-zinc-500">Estado</span>
                            <flux:badge color="{{ $badgeColor }}" size="sm">{{ $badgeLabel }}</flux:badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">Kg declarados</span>
                            <span class="font-semibold text-zinc-800">{{ number_format($delivery->delivered_kg, 0) }} kg</span>
                        </div>
                        @if($delivery->discrepancy_kg && $delivery->discrepancy_kg > 0)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Diferencia</span>
                                <span class="font-semibold {{ in_array($delivery->status, ['disputed','resolved']) ? 'text-amber-600' : 'text-zinc-600' }}">
                                    {{ number_format($delivery->discrepancy_kg, 0) }} kg
                                    @if($delivery->discrepancyPercentage())
                                        ({{ $delivery->discrepancyPercentage() }}%)
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if($delivery->ticket_number)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Ticket declarado</span>
                                <span class="font-mono text-xs text-zinc-600">{{ $delivery->ticket_number }}</span>
                            </div>
                        @endif
                        @if($delivery->buyer_name)
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Comprador</span>
                                <span class="text-zinc-700">{{ $delivery->buyer_name }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Dispute thread --}}
                    @if(in_array($delivery->status, ['disputed', 'resolved']))
                        <div class="mt-3 pt-3 border-t border-zinc-100 space-y-2">

                            {{-- Viticulturist note --}}
                            @if($delivery->hasDisputeNote())
                                <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5">
                                    <p class="text-xs font-semibold text-amber-700 mb-1">
                                        Reclamación del viticultor
                                        <span class="font-normal text-amber-600">· {{ $delivery->dispute_submitted_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                    <p class="text-xs text-amber-800 whitespace-pre-line">{{ $delivery->dispute_note }}</p>
                                </div>
                            @else
                                <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                    Diferencia de {{ number_format($delivery->discrepancy_kg, 0) }} kg. El viticultor aún no ha enviado reclamación.
                                </p>
                            @endif

                            {{-- Resolution --}}
                            @if($delivery->isResolved())
                                <div class="bg-blue-50 border border-blue-200 rounded-lg px-3 py-2.5">
                                    <p class="text-xs font-semibold text-blue-700 mb-1">
                                        Respuesta de bodega
                                        <span class="font-normal text-blue-600">· {{ $delivery->dispute_resolved_at->format('d/m/Y H:i') }}</span>
                                    </p>
                                    <p class="text-xs text-blue-800 whitespace-pre-line">{{ $delivery->dispute_resolution_note }}</p>
                                </div>

                            @elseif($delivery->hasDisputeNote())
                                {{-- Resolve form / button --}}
                                @if($showResolveForm)
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl px-3 py-3 space-y-2.5">
                                        <p class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                                            <flux:icon icon="chat-bubble-left-right" class="size-3.5" />
                                            Escribe tu respuesta al viticultor
                                        </p>
                                        <textarea
                                            wire:model="resolutionNote"
                                            rows="3"
                                            maxlength="1000"
                                            placeholder="Explica el motivo de la diferencia: merma por transporte, corrección de pesaje, error de ticket..."
                                            class="w-full text-xs rounded-lg border border-blue-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
                                        ></textarea>
                                        @error('resolutionNote')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                        <div class="flex gap-2 justify-end">
                                            <flux:button wire:click="cancelResolveDispute" variant="ghost" size="sm">
                                                Cancelar
                                            </flux:button>
                                            <flux:button wire:click="resolveDispute" variant="primary" size="sm" icon="check-circle">
                                                Enviar respuesta
                                            </flux:button>
                                        </div>
                                    </div>
                                @else
                                    <button
                                        wire:click="openResolveDispute"
                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-1.5 transition-colors"
                                    >
                                        <flux:icon icon="chat-bubble-left-right" class="size-3.5" />
                                        Responder reclamación
                                    </button>
                                @endif
                            @endif

                        </div>
                    @endif
                @else
                    <p class="text-sm text-zinc-400">El viticultor no ha declarado ninguna entrega enlazada con esta recepción.</p>
                @endif
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
                        <flux:button
                            href="{{ route('winery.grape-reception.assign', $harvest) }}"
                            variant="outline"
                            class="w-full"
                            icon="archive-box-arrow-down"
                        >
                            {{ $harvest->container ? 'Cambiar depósito' : 'Asignar depósito' }}
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
