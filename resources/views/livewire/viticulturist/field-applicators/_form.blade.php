<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <flux:field class="md:col-span-2">
        <flux:label required>Nombre completo</flux:label>
        <flux:input wire:model="name" type="text" placeholder="Nombre del aplicador" />
        <flux:error name="name" />
    </flux:field>

    <flux:field>
        <flux:label required>Número ROPO</flux:label>
        <flux:input wire:model="ropo_number" type="text" placeholder="Ej: ES12345678" />
        <flux:description>Registro Oficial de Productores y Operadores</flux:description>
        <flux:error name="ropo_number" />
    </flux:field>

    <flux:field>
        <flux:label required>Categoría ROPO</flux:label>
        <flux:select wire:model="ropo_category">
            @foreach($categories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </flux:select>
        <flux:error name="ropo_category" />
    </flux:field>

    <flux:field>
        <flux:label>Caducidad del carné</flux:label>
        <flux:input wire:model="ropo_expiry_date" type="date" />
        <flux:error name="ropo_expiry_date" />
    </flux:field>

    <flux:field>
        <flux:label>Teléfono</flux:label>
        <flux:input wire:model="phone" type="tel" placeholder="+34 600 000 000" />
        <flux:error name="phone" />
    </flux:field>

    <flux:field class="md:col-span-2">
        <flux:label>Email</flux:label>
        <flux:input wire:model="email" type="email" placeholder="aplicador@ejemplo.com" />
        <flux:error name="email" />
    </flux:field>

    <div class="md:col-span-2 space-y-3">
        <div class="flex items-center gap-3">
            <flux:checkbox wire:model.live="is_advisor" id="is_advisor_form" />
            <flux:label for="is_advisor_form" class="cursor-pointer">También actúa como asesor técnico cualificado</flux:label>
        </div>
        @if($is_advisor)
            <flux:field class="ml-7">
                <flux:label required>Nº colegiado / licencia de asesor</flux:label>
                <flux:input wire:model="advisor_license" type="text" placeholder="Nº de licencia" />
                <flux:error name="advisor_license" />
            </flux:field>
        @endif
    </div>
</div>
