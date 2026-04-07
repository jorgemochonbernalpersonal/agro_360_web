<x-agro.form-card
    title="Nueva Autorización Comercial"
    description="Registra una autorización comercial de la explotación"
    :back-url="roleRoute('viticulturist.commercial-authorizations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Datos de la Autorización">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>Tipo de autorización</flux:label>
                    <flux:select wire:model="authorization_type">
                        @foreach($authTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="authorization_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Código de autorización</flux:label>
                    <flux:input wire:model="authorization_code" type="text" placeholder="Ej: DO-2024-001" />
                    <flux:error name="authorization_code" />
                </flux:field>

                <flux:field>
                    <flux:label required>Fecha de emisión</flux:label>
                    <flux:input wire:model="issue_date" type="date" />
                    <flux:error name="issue_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de vencimiento</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Organismo emisor</flux:label>
                    <flux:input wire:model="issuing_body" type="text" placeholder="Ej: Consejo Regulador DO Ribera del Duero" />
                    <flux:error name="issuing_body" />
                </flux:field>

                <flux:field>
                    <flux:label>Explotación</flux:label>
                    <flux:select wire:model="exploitation_id">
                        <option value="">Sin explotación específica</option>
                        @foreach($exploitations as $e)
                            <option value="{{ $e->id }}">{{ $e->exploitation_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exploitation_id" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:input wire:model="description" type="text" placeholder="Descripción breve" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Documento (URL o referencia)</flux:label>
                    <flux:input wire:model="document_file" type="text" placeholder="Ruta o referencia del documento" />
                    <flux:error name="document_file" />
                </flux:field>

                <flux:field>
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.commercial-authorizations.index')"
            submit-label="Registrar Autorización"
        />
    </form>
</x-agro.form-card>
