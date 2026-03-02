<x-agro.form-card
    title="Nuevo Asesor Técnico"
    description="Registra un asesor técnico de la explotación"
    :back-url="route('viticulturist.advisory-memberships.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos del Asesor">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Nombre del asesor</flux:label>
                    <flux:input wire:model="advisor_name" type="text" placeholder="Nombre completo" />
                    <flux:error name="advisor_name" />
                </flux:field>

                <flux:field>
                    <flux:label required>Número de licencia</flux:label>
                    <flux:input wire:model="license_number" type="text" placeholder="Ej: ROPO-12345" />
                    <flux:error name="license_number" />
                </flux:field>

                <flux:field>
                    <flux:label required>Especialidad</flux:label>
                    <flux:select wire:model="specialty">
                        @foreach($specialties as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="specialty" />
                </flux:field>

                <flux:field>
                    <flux:label>Empresa</flux:label>
                    <flux:input wire:model="company_name" type="text" placeholder="Nombre de la empresa" />
                    <flux:error name="company_name" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="phone" type="tel" placeholder="+34 600 000 000" />
                    <flux:error name="phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="asesor@ejemplo.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Sin campaña específica</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.advisory-memberships.index')"
            submit-label="Registrar Asesor"
        />
    </form>
</x-agro.form-card>
