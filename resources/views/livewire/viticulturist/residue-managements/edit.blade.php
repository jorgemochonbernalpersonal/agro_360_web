<x-agro.form-card
    title="Editar Gestión de Residuos"
    :description="'Modifica el registro del ' . $residueManagement->date->format('d/m/Y')"
    :back-url="route('viticulturist.residue-managements.index')"
>
    <form wire:submit="save" class="space-y-8">

        <x-agro.form-section title="Datos Generales">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Seleccionar...</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field>
                    <flux:label>Parcela (opcional)</flux:label>
                    <flux:select wire:model="plot_id">
                        <option value="">Global campaña</option>
                        @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Plantación (opcional)</flux:label>
                    <flux:select wire:model="plot_planting_id">
                        <option value="">Sin especificar</option>
                        @foreach($plantings as $p)
                            <option value="{{ $p->id }}">{{ $p->plot->name }} — {{ $p->grape->name ?? '' }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="plot_planting_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Tipo de Residuo y Práctica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Tipo de material</flux:label>
                    <flux:select wire:model="material_type">
                        <option value="">Seleccionar...</option>
                        @foreach($materialTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="material_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Práctica aplicada</flux:label>
                    <flux:select wire:model.live="practice_type">
                        <option value="">Seleccionar...</option>
                        @foreach($practiceTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="practice_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Cantidad estimada</flux:label>
                    <flux:input wire:model="estimated_quantity" type="number" step="0.01" min="0" placeholder="0.00" />
                    <flux:error name="estimated_quantity" />
                </flux:field>

                <flux:field>
                    <flux:label>Unidad</flux:label>
                    <flux:select wire:model="quantity_unit">
                        @foreach($units as $unit)
                            <option value="{{ $unit->symbol }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="quantity_unit" />
                </flux:field>

                @if($practice_type === 'burning')
                    <flux:field class="md:col-span-2">
                        <flux:label required>Justificación (obligatoria para quema)</flux:label>
                        <flux:textarea wire:model="justification" rows="3" placeholder="Justifica la necesidad de la quema..." />
                        <flux:error name="justification" />
                    </flux:field>
                @endif

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.residue-managements.index')"
            submit-label="Actualizar Gestión"
        />
    </form>
</x-agro.form-card>
