<x-agro.form-card
    :title="__('Registrar entrega de uva')"
    :description="__('Anota una entrega manual a cooperativa, almacenista u otro comprador')"
    icon="truck"
    icon-color="from-blue-500 to-blue-700"
    :back-url="roleRoute('viticulturist.harvests.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Parcela y plantación --}}
        <x-agro.form-section :title="__('Parcela y plantación')" color="green">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Parcela') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:select wire:model.live="plot_id" id="plot_id">
                        <option value="">{{ __('Sin parcela concreta') }}</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}">{{ $plot->name }} ({{ $plot->area }} ha)</option>
                        @endforeach
                    </flux:select>
                    <flux:description>{{ __('Solo se muestran parcelas activas') }}</flux:description>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Plantación / Variedad') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:select wire:model="plot_planting_id" id="plot_planting_id" :disabled="!$plot_id || $availablePlantings->isEmpty()">
                        <option value="">{{ __('Sin plantación concreta') }}</option>
                        @foreach($availablePlantings as $planting)
                            <option value="{{ $planting->id }}">
                                @if($planting->name){{ $planting->name }} — @endif
                                {{ $planting->grapeVariety->name ?? __('Sin variedad') }} ({{ $planting->area_planted }} ha)
                            </option>
                        @endforeach
                    </flux:select>
                    @if($plot_id && $availablePlantings->isEmpty())
                        <flux:description class="text-amber-600">{{ __('Esta parcela no tiene plantaciones activas') }}</flux:description>
                    @endif
                    <flux:error name="plot_planting_id" />
                </flux:field>
            </div>
        </x-agro.form-section>

        {{-- Datos de la entrega --}}
        <x-agro.form-section :title="__('Datos de la entrega')" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Comprador / Destino') }}</flux:label>
                    <flux:input
                        wire:model="buyer_name"
                        type="text"
                        id="buyer_name"
                        :placeholder="__('Cooperativa, almacenista, particular...')"
                        required
                    />
                    <flux:error name="buyer_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Añada') }}</flux:label>
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
                    <flux:label required>{{ __('Kg entregados') }}</flux:label>
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
                    <flux:label required>{{ __('Fecha de entrega') }}</flux:label>
                    <flux:input
                        wire:model="delivery_date"
                        type="date"
                        id="delivery_date"
                        required
                    />
                    <flux:error name="delivery_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Hora de entrega') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
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
        <x-agro.form-section :title="__('Precio')" color="amber">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('Precio / kg (€)') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
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
                    <flux:label>{{ __('Importe total (€)') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
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
                        placeholder="{{ $computedTotal ? number_format((float)$computedTotal, 3, '.', '') : __('Se calcula automáticamente') }}"
                    />
                    <flux:description>{{ __('Se calcula desde precio × kg. Puedes introducir un importe pactado diferente.') }}</flux:description>
                    <flux:error name="total_price" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Trazabilidad --}}
        <x-agro.form-section :title="__('Trazabilidad')" color="zinc">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label>{{ __('Nº ticket / pesada') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input
                        wire:model="ticket_number"
                        type="text"
                        id="ticket_number"
                        placeholder="{{ __('Ej: 2026-00123') }}"
                        maxlength="100"
                    />
                    <flux:description>{{ __('Número asignado por el comprador en báscula. Se usa para cruzar automáticamente con la recepción de bodega.') }}</flux:description>
                    <flux:error name="ticket_number" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Código REGA destino') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input
                        wire:model="destination_rega_code"
                        type="text"
                        id="destination_rega_code"
                        placeholder="{{ __('Ej: ES12345678') }}"
                        maxlength="20"
                    />
                    <flux:description>{{ __('Código de registro de la instalación de destino (PAC)') }}</flux:description>
                    <flux:error name="destination_rega_code" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Matrícula vehículo') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input
                        wire:model="vehicle_plate"
                        type="text"
                        id="vehicle_plate"
                        placeholder="{{ __('Ej: 1234-ABC') }}"
                        maxlength="20"
                    />
                    <flux:error name="vehicle_plate" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Parámetros de calidad --}}
        <x-agro.form-section :title="__('Parámetros de calidad')" color="violet">
            <flux:description class="mb-4">{{ __('Datos analíticos proporcionados por el comprador en el momento de la recepción. Todos opcionales.') }}</flux:description>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

                <flux:field>
                    <flux:label>{{ __('Baumé (°Bé)') }}</flux:label>
                    <flux:input wire:model="baume_degree" type="number" step="0.1" min="0" max="20" id="baume_degree" :placeholder="__('0–20')" />
                    <flux:error name="baume_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Brix (°Bx)') }}</flux:label>
                    <flux:input wire:model="brix_degree" type="number" step="0.1" min="0" max="40" id="brix_degree" :placeholder="__('0–40')" />
                    <flux:error name="brix_degree" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Alc. potencial (%vol)') }}</flux:label>
                    <flux:input wire:model="potential_alcohol" type="number" step="0.01" min="0" max="25" id="potential_alcohol" :placeholder="__('0–25')" />
                    <flux:error name="potential_alcohol" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Acidez (g/L)') }}</flux:label>
                    <flux:input wire:model="acidity_level" type="number" step="0.1" min="0" max="20" id="acidity_level" :placeholder="__('0–20')" />
                    <flux:error name="acidity_level" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('pH') }}</flux:label>
                    <flux:input wire:model="ph_level" type="number" step="0.01" min="0" max="14" id="ph_level" :placeholder="__('0–14')" />
                    <flux:error name="ph_level" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Descarte --}}
        <x-agro.form-section :title="__('Descarte')" color="red">
            <div class="space-y-4">
                <flux:field>
                    <flux:checkbox wire:model.live="disqualified" id="disqualified" :label="__('Esta entrega ha sido descartada o rechazada por el comprador')" />
                    <flux:description>{{ __('Marca esta opción si el comprador rechaza la entrega completa. Si solo rechaza una parte, registra dos entregas separadas.') }}</flux:description>
                </flux:field>
                @if($disqualified)
                    <flux:field>
                        <flux:label>{{ __('Motivo del descarte') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                        <flux:textarea
                            wire:model="disqualified_reason"
                            id="disqualified_reason"
                            rows="2"
                            :placeholder="__('Describe el motivo: exceso de botrytis, madurez insuficiente, problemas fitosanitarios...')"
                            maxlength="500"
                        />
                        <flux:error name="disqualified_reason" />
                    </flux:field>
                @endif
            </div>
        </x-agro.form-section>

        {{-- Notas --}}
        <x-agro.form-section :title="__('Notas adicionales')" color="zinc">
            <flux:field>
                <flux:label>{{ __('Notas') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                <flux:textarea
                    wire:model="notes"
                    id="notes"
                    rows="3"
                    :placeholder="__('Observaciones sobre la entrega, calidad de la uva, condiciones acordadas...')"
                />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.harvests.index')"
            :submit-label="__('Registrar entrega')"
        />
    </form>
</x-agro.form-card>
