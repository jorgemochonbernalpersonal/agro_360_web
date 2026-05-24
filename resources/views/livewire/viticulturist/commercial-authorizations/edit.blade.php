<x-agro.form-card
    :title="__('Editar Autorización Comercial')"
    :description="__('Modifica la autorización') . ' ' . ($commercialAuthorization->authorization_code ?? $commercialAuthorization->authorization_type)"
    :back-url="roleRoute('viticulturist.commercial-authorizations.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section :title="__('Datos de la Autorización')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <flux:field>
                    <flux:label required>{{ __('Tipo de autorización') }}</flux:label>
                    <flux:select wire:model="authorization_type">
                        @foreach($authTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="authorization_type" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Código de autorización') }}</flux:label>
                    <flux:input wire:model="authorization_code" type="text" />
                    <flux:error name="authorization_code" />
                </flux:field>

                <flux:field>
                    <flux:label required>{{ __('Fecha de emisión') }}</flux:label>
                    <flux:input wire:model="issue_date" type="date" />
                    <flux:error name="issue_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de vencimiento') }}</flux:label>
                    <flux:input wire:model="expiry_date" type="date" />
                    <flux:error name="expiry_date" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Organismo emisor') }}</flux:label>
                    <flux:input wire:model="issuing_body" type="text" />
                    <flux:error name="issuing_body" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Explotación') }}</flux:label>
                    <flux:select wire:model="exploitation_id">
                        <option value="">{{ __('Sin explotación específica') }}</option>
                        @foreach($exploitations as $e)
                            <option value="{{ $e->id }}">{{ $e->exploitation_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exploitation_id" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Descripción') }}</flux:label>
                    <flux:input wire:model="description" type="text" />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Documento (URL o referencia)') }}</flux:label>
                    <flux:input wire:model="document_file" type="text" />
                    <flux:error name="document_file" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>

            </div>
        </x-agro.form-section>

        <x-agro.form-actions
            :cancel-url="roleRoute('viticulturist.commercial-authorizations.index')"
            :submit-label="__('Actualizar Autorización')"
        />
    </form>
</x-agro.form-card>
