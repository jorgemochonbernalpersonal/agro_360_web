<x-agro.form-card
    title="Editar Certificación"
    :description="'Modifica los datos de ' . $certification->certifying_body"
    :back-url="route('viticulturist.certifications.index')"
>
    <form wire:submit="save" class="space-y-8">

        {{-- Sección 1: Tipo de Certificación --}}
        <x-agro.form-section title="Tipo de Certificación">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field class="md:col-span-2">
                    <flux:label required>Tipo de certificación</flux:label>
                    <flux:select wire:model="certification_type">
                        @foreach ($certificationTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="certification_type" />
                </flux:field>

                <flux:field>
                    <flux:label required>Organismo certificador</flux:label>
                    <flux:input wire:model="certifying_body" type="text" />
                    <flux:error name="certifying_body" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de certificado</flux:label>
                    <flux:input wire:model="certificate_number" type="text" />
                    <flux:error name="certificate_number" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 2: Vigencia --}}
        <x-agro.form-section title="Vigencia">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <flux:field>
                    <flux:label required>Fecha de emisión</flux:label>
                    <flux:input wire:model="issue_date" type="date" />
                    <flux:error name="issue_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Fecha de vencimiento</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:description>Dejar vacío si no caduca</flux:description>
                    <flux:error name="expiry_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Próxima auditoría</flux:label>
                    <flux:input wire:model="audit_date" type="date" />
                    <flux:error name="audit_date" />
                </flux:field>

            </div>
        </x-agro.form-section>

        {{-- Sección 3: Alcance --}}
        <x-agro.form-section title="Alcance">
            <div class="grid grid-cols-1 gap-6">

                <flux:field>
                    <flux:label>Alcance de la certificación</flux:label>
                    <flux:textarea
                        wire:model="scope"
                        rows="3"
                    />
                    <flux:description>Máximo 500 caracteres</flux:description>
                    <flux:error name="scope" />
                </flux:field>

                <flux:field>
                    <flux:label>Notas adicionales</flux:label>
                    <flux:textarea
                        wire:model="notes"
                        rows="3"
                    />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.certifications.index')"
            submit-label="Guardar Cambios"
        />
    </form>
</x-agro.form-card>
