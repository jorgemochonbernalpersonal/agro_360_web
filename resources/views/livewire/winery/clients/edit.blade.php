<div>
    <x-agro.form-card title="Editar Cliente" description="Modifica los datos del cliente"
        :back-url="route('winery.clients.index')">

        <form wire:submit.prevent="update" class="space-y-8">

            <x-agro.form-section title="Tipo de Cliente">
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <flux:radio wire:model.live="client_type" value="individual" />
                        <span>Particular</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <flux:radio wire:model.live="client_type" value="company" />
                        <span>Empresa</span>
                    </label>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Datos de Contacto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($client_type === 'individual')
                        <flux:field>
                            <flux:label for="first_name">Nombre *</flux:label>
                            <flux:input wire:model="first_name" type="text" id="first_name" required />
                            <flux:error name="first_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label for="last_name">Apellidos</flux:label>
                            <flux:input wire:model="last_name" type="text" id="last_name" />
                            <flux:error name="last_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label for="particular_document">DNI / NIE</flux:label>
                            <flux:input wire:model="particular_document" type="text" id="particular_document" />
                            <flux:error name="particular_document" />
                        </flux:field>
                    @else
                        <flux:field>
                            <flux:label for="company_name">Razón Social *</flux:label>
                            <flux:input wire:model="company_name" type="text" id="company_name" required />
                            <flux:error name="company_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label for="company_document">CIF / NIF Empresa</flux:label>
                            <flux:input wire:model="company_document" type="text" id="company_document" />
                            <flux:error name="company_document" />
                        </flux:field>
                    @endif

                    <flux:field>
                        <flux:label for="email">Email</flux:label>
                        <flux:input wire:model="email" type="email" id="email" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="phone">Teléfono</flux:label>
                        <flux:input wire:model="phone" type="tel" id="phone" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Datos de Pago">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label for="payment_method">Método de pago</flux:label>
                        <flux:select wire:model="payment_method" id="payment_method">
                            <option value="">Sin especificar</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia bancaria</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </flux:select>
                        <flux:error name="payment_method" />
                    </flux:field>

                    <flux:field>
                        <flux:label for="account_number">Número de cuenta (IBAN)</flux:label>
                        <flux:input wire:model="account_number" type="text" id="account_number" />
                        <flux:error name="account_number" />
                    </flux:field>
                </div>

                <div class="mt-6">
                    <flux:field>
                        <flux:label for="notes">Notas</flux:label>
                        <flux:textarea wire:model="notes" id="notes" rows="3" />
                        <flux:error name="notes" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="CAE">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:checkbox wire:model.live="has_cae" label="Tiene CAE" />
                    </div>
                    @if($has_cae)
                        <flux:field>
                            <flux:label>Número CAE</flux:label>
                            <flux:input wire:model="cae_number" id="cae_number" />
                            <flux:error name="cae_number" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="route('winery.clients.index')" submit-label="Guardar Cambios" />
        </form>
    </x-agro.form-card>
</div>
