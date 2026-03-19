<x-agro.form-card
    title="Editar Trasvase / Coupage"
    description="Modifica los datos del trasvase registrado."
    icon="arrows-right-left"
    icon-color="from-blue-500 to-violet-600"
    :back-url="roleRoute('wine-transfers.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- ── Identificación ─────────────────────────────────────────── --}}
        <x-agro.form-section title="Identificación" color="blue">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Vino</flux:label>
                    <flux:select wire:model="wine_id" required>
                        <option value="">Seleccionar vino...</option>
                        @foreach($wines as $wine)
                            <option value="{{ $wine->id }}">
                                {{ $wine->name }}{{ $wine->vintage ? ' (' . $wine->vintage . ')' : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="wine_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Tipo de operación</flux:label>
                    <flux:select wire:model="transfer_type" required>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="transfer_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha</flux:label>
                    <flux:input wire:model="transfer_date" type="date" required />
                    <flux:error name="transfer_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Depósitos ────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Depósitos" color="violet">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label>Depósito origen</flux:label>
                    <flux:select wire:model="from_container_id">
                        <option value="">Sin depósito origen</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="from_container_id" />
                </flux:field>

                <flux:field>
                    <flux:label required>Depósito destino</flux:label>
                    <flux:select wire:model="to_container_id" required>
                        <option value="">Seleccionar depósito...</option>
                        @foreach($containers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="to_container_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Cantidad y Enólogo ───────────────────────────────────────── --}}
        <x-agro.form-section title="Cantidad y Responsable" color="green">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Cantidad</flux:label>
                    <flux:input wire:model="quantity" type="number" step="0.001" min="0.001"
                        placeholder="0.000" required />
                    <flux:error name="quantity" />
                </flux:field>

                <flux:field>
                    <flux:label required>Unidad</flux:label>
                    <flux:select wire:model="unit_of_measurement_id" required>
                        <option value="">Seleccionar unidad...</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="unit_of_measurement_id" />
                </flux:field>

                <flux:field>
                    <flux:label>Enólogo responsable</flux:label>
                    <flux:select wire:model="oenologist_id">
                        <option value="">Sin enólogo asignado</option>
                        @foreach($oenologists as $oenologist)
                            <option value="{{ $oenologist->id }}">{{ $oenologist->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="oenologist_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- ── Notas ───────────────────────────────────────────────────── --}}
        <x-agro.form-section title="Notas" color="zinc">
            <flux:field>
                <flux:label>Observaciones</flux:label>
                <flux:textarea wire:model="notes" rows="3"
                    placeholder="Detalles adicionales sobre el trasvase o mezcla..." />
                <flux:error name="notes" />
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-actions :back-url="roleRoute('wine-transfers.index')" submit-label="Actualizar trasvase" />
    </form>
</x-agro.form-card>
