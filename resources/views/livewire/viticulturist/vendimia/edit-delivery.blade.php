<x-agro.form-card
    title="Editar entrega de uva"
    description="Modifica los datos de esta entrega manual"
    icon="truck"
    icon-color="from-blue-500 to-blue-700"
    :back-url="route('viticulturist.vendimia.index')"
>
    @if($delivery->harvest_id)
        @if($delivery->isResolved())
            <flux:callout variant="info" icon="check-circle" class="mb-6">
                <flux:callout.heading>Disputa resuelta por la bodega</flux:callout.heading>
                <flux:callout.text>
                    Esta entrega tiene una disputa ya resuelta. Si modificas los kg declarados, la plantación o la añada, se perderá la vinculación con la recepción de bodega y quedará pendiente de re-confirmar.
                </flux:callout.text>
            </flux:callout>
        @elseif($delivery->isMatched())
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                <flux:callout.heading>Entrega confirmada por la bodega</flux:callout.heading>
                <flux:callout.text>
                    Esta entrega ya fue confirmada. Si modificas los kg declarados, la plantación o la añada, se perderá la confirmación y quedará pendiente de re-confirmar.
                </flux:callout.text>
            </flux:callout>
        @elseif($delivery->isDisputed())
            <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
                <flux:callout.heading>Entrega en reclamación</flux:callout.heading>
                <flux:callout.text>
                    Esta entrega tiene una diferencia en curso con la bodega. Si modificas los kg, la plantación o la añada, se perderá la vinculación y la reclamación quedará sin efecto.
                </flux:callout.text>
            </flux:callout>
        @endif
    @endif

    <form wire:submit="save" class="space-y-8">

        {{-- Parcela y plantación --}}
        <x-agro.form-section title="Parcela y plantación" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Parcela <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id">
                        <option value="">Sin parcela concreta</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }} ({{ $plot->area }} ha)</option>
                        @endforeach
                    </flux:select>
                    <flux:description>Solo se muestran parcelas activas</flux:description>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Plantación / Variedad <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:select wire:model="plot_planting_id" id="plot_planting_id" :disabled="!$plot_id || $availablePlantings->isEmpty()">
                        <option value="">Sin plantación concreta</option>
                        @foreach($availablePlantings as $planting)
                            <option value="{{ $planting->id }}">
                                @if($planting->name){{ $planting->name }} — @endif
                                {{ $planting->grapeVariety->name ?? 'Sin variedad' }} ({{ $planting->area_planted }} ha)
                            </option>
                        @endforeach
                    </flux:select>
                    @if($plot_id && $availablePlantings->isEmpty())
                        <flux:description class="text-amber-600">Esta parcela no tiene plantaciones activas</flux:description>
                    @endif
                    <flux:error name="plot_planting_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Datos de la entrega --}}
        <x-agro.form-section title="Datos de la entrega" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Comprador / Destino</flux:label>
                    <flux:input
                        wire:model="buyer_name"
                        type="text"
                        id="buyer_name"
                        placeholder="Cooperativa, almacenista, particular..."
                        required
                    />
                    <flux:error name="buyer_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Añada</flux:label>
                    <flux:input
                        wire:model="vintage_year"
                        type="number"
                        id="vintage_year"
                        min="2000"
                        max="2100"
                        required
                    />
                    <flux:error name="vintage_year" />
                </flux:field>

                <flux:field>
                    <flux:label required>Kg entregados</flux:label>
                    <flux:input
                        wire:model.live="delivered_kg"
                        type="number"
                        step="0.01"
                        min="0.01"
                        id="delivered_kg"
                        placeholder="0.00"
                        required
                    />
                    <flux:error name="delivered_kg" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de entrega</flux:label>
                    <flux:input
                        wire:model="delivery_date"
                        type="date"
                        id="delivery_date"
                        required
                    />
                    <flux:error name="delivery_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Hora de entrega <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model="harvest_time"
                        type="time"
                        id="harvest_time"
                    />
                    <flux:error name="harvest_time" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Precio --}}
        <x-agro.form-section title="Precio" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>Precio / kg (€) <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model.live="price_per_kg"
                        type="number"
                        step="0.001"
                        min="0"
                        id="price_per_kg"
                        placeholder="0.000"
                    />
                    <flux:error name="price_per_kg" />
                </flux:field>

                <flux:field>
                    <flux:label>Importe total (€) <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    @php
                        $computedTotal = ($price_per_kg && $delivered_kg)
                            ? number_format((float) $price_per_kg * (float) $delivered_kg, 3, '.', '')
                            : null;
                    @endphp
                    <flux:input
                        wire:model="total_price"
                        type="number"
                        step="0.001"
                        min="0"
                        id="total_price"
                        placeholder="{{ $computedTotal ? number_format((float)$computedTotal, 3, '.', '') : 'Se calcula automáticamente' }}"
                    />
                    <flux:description>Se calcula desde precio × kg. Puedes introducir un importe pactado diferente.</flux:description>
                    <flux:error name="total_price" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Trazabilidad --}}
        <x-agro.form-section title="Trazabilidad" color="zinc">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>Nº ticket / pesada <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model="ticket_number"
                        type="text"
                        id="ticket_number"
                        placeholder="Ej: 2026-00123"
                        maxlength="100"
                    />
                    <flux:description>Número asignado por el comprador en báscula. Se usa para cruzar automáticamente con la recepción de bodega.</flux:description>
                    <flux:error name="ticket_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Código REGA destino <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model="destination_rega_code"
                        type="text"
                        id="destination_rega_code"
                        placeholder="Ej: ES12345678"
                        maxlength="20"
                    />
                    <flux:description>Código de registro de la instalación de destino (PAC)</flux:description>
                    <flux:error name="destination_rega_code" />
                </flux:field>

                <flux:field>
                    <flux:label>Matrícula vehículo <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model="vehicle_plate"
                        type="text"
                        id="vehicle_plate"
                        placeholder="Ej: 1234-ABC"
                        maxlength="20"
                    />
                    <flux:error name="vehicle_plate" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Parámetros de calidad --}}
        <x-agro.form-section title="Parámetros de calidad" color="violet">
            <flux:description class="mb-4">Datos analíticos proporcionados por el comprador en el momento de la recepción. Todos opcionales.</flux:description>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

                <flux:field>
                    <flux:label>Baumé (°Bé)</flux:label>
                    <flux:input wire:model="baume_degree" type="number" step="0.1" min="0" max="20" id="baume_degree" placeholder="0–20" />
                    <flux:error name="baume_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>Brix (°Bx)</flux:label>
                    <flux:input wire:model="brix_degree" type="number" step="0.1" min="0" max="40" id="brix_degree" placeholder="0–40" />
                    <flux:error name="brix_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>Alc. potencial (%vol)</flux:label>
                    <flux:input wire:model="potential_alcohol" type="number" step="0.01" min="0" max="25" id="potential_alcohol" placeholder="0–25" />
                    <flux:error name="potential_alcohol" />
                </flux:field>

                <flux:field>
                    <flux:label>Acidez (g/L)</flux:label>
                    <flux:input wire:model="acidity_level" type="number" step="0.1" min="0" max="20" id="acidity_level" placeholder="0–20" />
                    <flux:error name="acidity_level" />
                </flux:field>

                <flux:field>
                    <flux:label>pH</flux:label>
                    <flux:input wire:model="ph_level" type="number" step="0.01" min="0" max="14" id="ph_level" placeholder="0–14" />
                    <flux:error name="ph_level" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Descarte --}}
        <x-agro.form-section title="Descarte" color="red">
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="disqualified" id="disqualified" label="Esta entrega ha sido descartada o rechazada por el comprador" />
                    <flux:description>Marca esta opción si el comprador rechaza la entrega completa. Si solo rechaza una parte, registra dos entregas separadas.</flux:description>
                </flux:field>
                @if($disqualified)
                    <flux:field>
                        <flux:label>Motivo del descarte <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                        <flux:textarea
                            wire:model="disqualified_reason"
                            id="disqualified_reason"
                            rows="2"
                            placeholder="Describe el motivo: exceso de botrytis, madurez insuficiente, problemas fitosanitarios..."
                            maxlength="500"
                        />
                        <flux:error name="disqualified_reason" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        {{-- Notas --}}
        <x-agro.form-section title="Notas adicionales" color="zinc">
            <flux:field>
                <flux:label>Notas <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                <flux:textarea
                    wire:model="notes"
                    id="notes"
                    rows="3"
                    placeholder="Observaciones sobre la entrega, calidad de la uva, condiciones acordadas..."
                />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <div class="flex items-center justify-between pt-6 border-t border-zinc-200">
            <flux:button
                wire:click="delete"
                wire:confirm="¿Eliminar esta entrega? Esta acción no se puede deshacer."
                variant="danger"
                icon="trash"
                type="button"
            >
                Eliminar entrega
            </flux:button>

            <div class="flex items-center gap-3">
                <flux:button href="{{ route('viticulturist.vendimia.index') }}" variant="outline">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed">
                    <span wire:loading.remove>Guardar cambios</span>
                    <span wire:loading class="flex items-center gap-2">
                        <flux:icon icon="arrow-path" class="w-4 h-4 animate-spin" />
                        Guardando...
                    </span>
                </flux:button>
            </div>
        </div>
    </form>
</x-agro.form-card>
