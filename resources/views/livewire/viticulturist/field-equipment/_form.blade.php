<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <flux:field class="md:col-span-2">
        <flux:label required>{{ __('Nombre del equipo') }}</flux:label>
        <flux:input wire:model="name" type="text" :placeholder="__('Ej: Pulverizador Jacto 600L')" />
        <flux:error name="name" />
    </flux:field>

    <flux:field>
        <flux:label required>{{ __('Tipo de equipo') }}</flux:label>
        <flux:select wire:model="equipment_type">
            @foreach($types as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>
        <flux:error name="equipment_type" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Matrícula / Número de serie') }}</flux:label>
        <flux:input wire:model="registration_number" type="text" :placeholder="__('Ej: 1234-ABB')" />
        <flux:error name="registration_number" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Fecha de compra') }}</flux:label>
        <flux:input wire:model="purchase_date" type="date" />
        <flux:error name="purchase_date" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Última inspección (ITEA)') }}</flux:label>
        <flux:input wire:model="last_inspection_date" type="date" />
        <flux:error name="last_inspection_date" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Próxima inspección') }}</flux:label>
        <flux:input wire:model="next_inspection_date" type="date" />
        <flux:error name="next_inspection_date" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Entidad inspectora') }}</flux:label>
        <flux:input wire:model="inspection_entity" type="text" :placeholder="__('Nombre de la entidad')" />
        <flux:error name="inspection_entity" />
    </flux:field>

    <flux:field class="md:col-span-2">
        <flux:label>{{ __('Notas') }}</flux:label>
        <flux:textarea wire:model="notes" rows="2" :placeholder="__('Observaciones sobre el equipo...')" />
        <flux:error name="notes" />
    </flux:field>
</div>
