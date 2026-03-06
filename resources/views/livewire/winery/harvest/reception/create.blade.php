<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Nueva Recepción de Uva"
        description="Registra la entrada de uva: viticultor, parcela, variedad y datos de calidad"
    >
        <x-slot:actions>
            <flux:button href="{{ route('winery.grape-reception.index') }}" variant="ghost" icon="arrow-left">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <form wire:submit="save" class="space-y-6">

        {{-- Sección 1: Contexto --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="user" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Viticultor y Parcela</span>
                </div>
            </x-slot:header>

            <div class="space-y-5">
                {{-- Campaña --}}
                <flux:field>
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model.live="campaign_id">
                        <flux:select.option value="">Selecciona una campaña...</flux:select.option>
                        @foreach($campaigns as $campaign)
                            <flux:select.option value="{{ $campaign->id }}">
                                {{ $campaign->name }}{{ $campaign->active ? ' (activa)' : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                    @if($campaigns->isEmpty())
                        <p class="text-xs text-amber-600 mt-1">
                            No tienes campañas.
                            <a href="{{ route('winery.campaigns.create') }}" class="underline font-medium">Crea una campaña primero</a>.
                        </p>
                    @endif
                </flux:field>

                {{-- Viticultor --}}
                <flux:field>
                    <flux:label>Viticultor</flux:label>
                    <flux:select wire:model.live="viticulturist_id">
                        <flux:select.option value="">Selecciona un viticultor...</flux:select.option>
                        @foreach($linkedViticulturists as $v)
                            <flux:select.option value="{{ $v->id }}">{{ $v->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="viticulturist_id" />
                </flux:field>

                {{-- Parcela (carga al seleccionar viticultor) --}}
                @if($viticulturist_id)
                    <flux:field>
                        <flux:label>Parcela</flux:label>
                        <flux:select wire:model.live="plot_id">
                            <flux:select.option value="">Selecciona una parcela...</flux:select.option>
                            @foreach($availablePlots as $plot)
                                <flux:select.option value="{{ $plot['id'] }}">{{ $plot['name'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_id" />
                        @if(empty($availablePlots))
                            <flux:description class="text-amber-600">
                                Este viticultor no tiene parcelas con plantaciones activas.
                            </flux:description>
                        @endif
                    </flux:field>
                @endif

                {{-- Plantación (carga al seleccionar parcela) --}}
                @if($plot_id)
                    <flux:field>
                        <flux:label>Plantación (variedad)</flux:label>
                        <flux:select wire:model.live="plot_planting_id">
                            <flux:select.option value="">Selecciona una plantación...</flux:select.option>
                            @foreach($availablePlantings as $planting)
                                <flux:select.option value="{{ $planting['id'] }}">{{ $planting['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="plot_planting_id" />
                    </flux:field>
                @endif

                {{-- Control de límite de cosecha --}}
                @if($harvestLimitInfo)
                    @php $info = $harvestLimitInfo; @endphp
                    <div class="p-3 rounded-lg border {{ $info['exceeds'] ? 'bg-red-50 border-red-200' : 'bg-agro-50 border-agro-200' }}">
                        <p class="text-xs font-semibold {{ $info['exceeds'] ? 'text-red-700' : 'text-agro-700' }} mb-2">
                            Control de límite de cosecha
                        </p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                            <span class="text-zinc-600">Límite base:</span>
                            <span class="font-medium">{{ number_format($info['raw_limit'], 0) }} kg</span>
                            @if($info['age_factor'] < 100)
                                <span class="text-zinc-600">Factor edad cepa:</span>
                                <span class="font-medium text-amber-700">{{ $info['age_factor'] }}%</span>
                                <span class="text-zinc-600">Límite efectivo:</span>
                                <span class="font-bold text-agro-700">{{ number_format($info['limit'], 0) }} kg</span>
                            @endif
                            <span class="text-zinc-600">Ya cosechado (añada):</span>
                            <span class="font-medium">{{ number_format($info['harvested'], 0) }} kg</span>
                            @if($info['adding'] > 0)
                                <span class="text-zinc-600">Esta recepción:</span>
                                <span class="font-medium">{{ number_format($info['adding'], 0) }} kg</span>
                                <span class="text-zinc-600">Total nuevo:</span>
                                <span class="font-bold {{ $info['exceeds'] ? 'text-red-700' : 'text-agro-700' }}">
                                    {{ number_format($info['new_total'], 0) }} kg ({{ $info['percentage'] }}%)
                                </span>
                            @endif
                        </div>
                        @if($info['age_factor'] < 100)
                            <p class="mt-2 text-xs text-amber-700">
                                Plantación joven — límite reducido por factor de edad (vinai: 3 años=33%, 4 años=75%, ≥5 años=100%).
                            </p>
                        @endif
                        @if($info['exceeds'])
                            <p class="mt-2 text-xs text-red-700 font-medium">
                                Esta recepción supera el límite efectivo. Revisa los datos antes de guardar.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </x-agro.card>

        {{-- Sección 2: Datos de la recepción --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="scale" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Datos de la Recepción</span>
                </div>
            </x-slot:header>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:field>
                        <flux:label>Fecha de recepción</flux:label>
                        <flux:input wire:model="harvest_start_date" type="date" />
                        <flux:error name="harvest_start_date" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nº de ticket / albarán</flux:label>
                        <flux:input wire:model="harvest_ticket_number" placeholder="Ej: VND-2026-001" />
                        <flux:error name="harvest_ticket_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Kg recibidos</flux:label>
                        <flux:input wire:model.live="total_weight" type="number" step="0.001" min="0" placeholder="0.000" />
                        <flux:error name="total_weight" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <flux:field>
                        <flux:label>Precio / kg (€)</flux:label>
                        <flux:input wire:model="price_per_kg" type="number" step="0.0001" min="0" placeholder="0.0000" />
                        <flux:error name="price_per_kg" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Vehículo (matrícula)</flux:label>
                        <flux:input wire:model="vehicle_plate" placeholder="Ej: 1234 ABC" />
                        <flux:error name="vehicle_plate" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Doc. transporte</flux:label>
                        <flux:input wire:model="transport_document_number" placeholder="Nº documento" />
                        <flux:error name="transport_document_number" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Código REGA destino</flux:label>
                    <flux:input wire:model="destination_rega_code" placeholder="Ej: ES010000001234" class="max-w-xs" />
                    <flux:description>Código REGA de tu bodega como destino de la uva.</flux:description>
                    <flux:error name="destination_rega_code" />
                </flux:field>
            </div>
        </x-agro.card>

        {{-- Sección 3: Calidad --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="beaker" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">Parámetros de Calidad</span>
                    <flux:badge color="zinc" size="sm">Opcional</flux:badge>
                </div>
            </x-slot:header>

            <div class="space-y-5">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <flux:field>
                        <flux:label>Grado Baumé (°Bé)</flux:label>
                        <flux:input wire:model="baume_degree" type="number" step="0.1" min="0" max="20" placeholder="—" />
                        <flux:error name="baume_degree" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Brix (°Bx)</flux:label>
                        <flux:input wire:model="brix_degree" type="number" step="0.1" min="0" max="40" placeholder="—" />
                        <flux:error name="brix_degree" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Acidez total (g/L)</flux:label>
                        <flux:input wire:model="acidity_level" type="number" step="0.1" min="0" max="20" placeholder="—" />
                        <flux:error name="acidity_level" />
                    </flux:field>

                    <flux:field>
                        <flux:label>pH</flux:label>
                        <flux:input wire:model="ph_level" type="number" step="0.01" min="0" max="14" placeholder="—" />
                        <flux:error name="ph_level" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Estado sanitario general</flux:label>
                    <flux:select wire:model="health_status" class="max-w-xs">
                        <flux:select.option value="">Sin especificar</flux:select.option>
                        <flux:select.option value="sano">Sano</flux:select.option>
                        <flux:select.option value="daño_leve">Daño leve</flux:select.option>
                        <flux:select.option value="daño_moderado">Daño moderado</flux:select.option>
                        <flux:select.option value="daño_grave">Daño grave</flux:select.option>
                    </flux:select>
                    <flux:error name="health_status" />
                </flux:field>

                {{-- Estado sanitario detallado --}}
                <div>
                    <p class="text-sm font-medium text-zinc-700 mb-3">Estado sanitario detallado (%)</p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <flux:field>
                            <flux:label class="text-xs">Granos sanos</flux:label>
                            <flux:input wire:model="sanitary_state_grapes" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_grapes" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Agraces</flux:label>
                            <flux:input wire:model="sanitary_state_agraces" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_agraces" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Botrytis</flux:label>
                            <flux:input wire:model="sanitary_state_botrytis" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_botrytis" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Oidio</flux:label>
                            <flux:input wire:model="sanitary_state_oidium" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_oidium" />
                        </flux:field>
                        <flux:field>
                            <flux:label class="text-xs">Mildiu</flux:label>
                            <flux:input wire:model="sanitary_state_mildew" type="number" step="0.1" min="0" max="100" placeholder="%" />
                            <flux:error name="sanitary_state_mildew" />
                        </flux:field>
                    </div>
                </div>
            </div>
        </x-agro.card>

        {{-- Notas --}}
        <x-agro.card>
            <flux:field>
                <flux:label>Notas (opcional)</flux:label>
                <flux:textarea wire:model="notes" rows="3" placeholder="Observaciones sobre esta recepción..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.card>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3">
            <flux:button href="{{ route('winery.grape-reception.index') }}" variant="ghost">
                Cancelar
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                Registrar Recepción
            </flux:button>
        </div>

    </form>
</div>
