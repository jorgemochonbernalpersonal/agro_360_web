<x-agro.form-card
    title="Registrar entrega de uva"
    description="Anota una entrega manual a cooperativa, almacenista u otro comprador"
    icon="truck"
    icon-color="from-blue-500 to-blue-700"
    :back-url="route('viticulturist.vendimia.index')"
>
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

        {{-- Precio y albarán --}}
        <x-agro.form-section title="Precio y documentación" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>Precio / kg (€) <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model.live="price_per_kg"
                        type="number"
                        step="0.0001"
                        min="0"
                        id="price_per_kg"
                        placeholder="0.0000"
                    />
                    <flux:error name="price_per_kg" />
                </flux:field>

                <flux:field>
                    <flux:label>Importe total (€) — Calculado</flux:label>
                    @php
                        $computedTotal = ($price_per_kg && $delivered_kg)
                            ? number_format((float) $price_per_kg * (float) $delivered_kg, 2, ',', '.')
                            : null;
                    @endphp
                    <flux:input
                        type="text"
                        value="{{ $computedTotal ?? '' }}"
                        placeholder="Se calcula automáticamente"
                        readonly
                        class="bg-zinc-50 font-semibold"
                    />
                    <flux:description>Precio/kg × Kg entregados</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Nº albarán <span class="text-zinc-400 font-normal text-xs">(opcional)</span></flux:label>
                    <flux:input
                        wire:model="ticket_number"
                        type="text"
                        id="ticket_number"
                        placeholder="Ej: ALB-2026-001"
                        maxlength="100"
                    />
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
                    <flux:checkbox wire:model.live="disqualified" id="disqualified" label="El comprador ha descartado o rechazado parte de esta entrega" />
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

        <x-agro.form-actions
            :cancel-url="route('viticulturist.vendimia.index')"
            submit-label="Registrar entrega"
        />
    </form>
</x-agro.form-card>
