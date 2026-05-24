<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <flux:field class="md:col-span-2">
        <flux:label required>{{ __('Nombre completo') }}</flux:label>
        <flux:input wire:model="name" type="text" :placeholder="__('Nombre del aplicador')" />
        <flux:error name="name" />
    </flux:field>

    <flux:field>
        <flux:label required>{{ __('Número ROPO') }}</flux:label>
        <flux:input wire:model="ropo_number" type="text" placeholder="Ej: ES12345678" />
        <flux:description>{{ __('Registro Oficial de Productores y Operadores') }}</flux:description>
        <flux:error name="ropo_number" />
    </flux:field>

    <flux:field>
        <flux:label required>{{ __('Categoría ROPO') }}</flux:label>
        <flux:select wire:model="ropo_category">
            @foreach($categories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>
        <flux:error name="ropo_category" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Caducidad del carné') }}</flux:label>
        <flux:input wire:model="ropo_expiry_date" type="date" />
        <flux:error name="ropo_expiry_date" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('Teléfono') }}</flux:label>
        <flux:input wire:model="phone" type="tel" placeholder="+34 600 000 000" />
        <flux:error name="phone" />
    </flux:field>

    <flux:field class="md:col-span-2">
        <flux:label>{{ __('Email') }}</flux:label>
        <flux:input wire:model="email" type="email" :placeholder="__('aplicador@ejemplo.com')" />
        <flux:error name="email" />
    </flux:field>

    <div class="md:col-span-2 space-y-3">
        <flux:checkbox wire:model.live="is_advisor" :label="__('También actúa como asesor técnico cualificado')" />
        @if($is_advisor)
            <flux:field class="ml-7">
                <flux:label required>{{ __('Nº colegiado / licencia de asesor') }}</flux:label>
                <flux:input wire:model="advisor_license" type="text" :placeholder="__('Nº de licencia')" />
                <flux:error name="advisor_license" />
            </flux:field>
        @endif
    </div>
</div>
