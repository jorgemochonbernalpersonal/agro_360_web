<div>
    <x-agro.form-card title="Editar Cliente" description="Modifica los datos del cliente"
        :back-url="roleRoute('clients.index')">

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
                            <flux:label for="first_name">Nombre <span class="text-red-500">*</span></flux:label>
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
                            <flux:label for="company_name">Razón Social <span class="text-red-500">*</span></flux:label>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:field>
                        <flux:label for="default_discount">Descuento por defecto (%)</flux:label>
                        <flux:input wire:model="default_discount" id="default_discount" type="number" step="0.01" min="0" max="100" />
                        <flux:error name="default_discount" />
                    </flux:field>

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

            <x-agro.form-section title="Direcciones">
                <div class="space-y-4">
                    @foreach($addresses as $index => $address)
                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white shadow-xs hover:border-blue-300 transition-colors">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-zinc-900">Dirección #{{ $index + 1 }}</h4>
                                    @if($address['is_default'])
                                        <flux:badge color="blue" size="sm">Por defecto</flux:badge>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    @if(!$address['is_default'])
                                        <flux:button type="button" wire:click="setDefaultAddress({{ $index }})" variant="ghost" size="sm">
                                            Marcar por defecto
                                        </flux:button>
                                    @endif

                                    @if(count($addresses) > 1)
                                        <flux:button type="button" wire:click="removeAddress({{ $index }})" variant="ghost" size="sm" icon="trash" class="text-red-500 hover:text-red-700">
                                            Eliminar
                                        </flux:button>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label>Dirección completa <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="addresses.{{ $index }}.address"
                                            placeholder="Calle, número, piso, puerta..."
                                            required
                                        />
                                        <flux:error name="addresses.{{ $index }}.address" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>Comunidad Autónoma <span class="text-red-500">*</span></flux:label>
                                    <flux:select wire:model.live="addresses.{{ $index }}.autonomous_community_id">
                                        <flux:select.option value="">Seleccionar...</flux:select.option>
                                        @foreach($autonomousCommunities as $ca)
                                            <flux:select.option value="{{ $ca->id }}">{{ $ca->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.autonomous_community_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Provincia <span class="text-red-500">*</span></flux:label>
                                    <flux:select
                                        wire:model.live="addresses.{{ $index }}.province_id"
                                        :disabled="!($addresses[$index]['autonomous_community_id'] ?? null)"
                                    >
                                        <flux:select.option value="">Seleccionar...</flux:select.option>
                                        @if(isset($provinces[$index]))
                                            @foreach($provinces[$index] as $province)
                                                <flux:select.option value="{{ $province->id }}">{{ $province->name }}</flux:select.option>
                                            @endforeach
                                        @endif
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.province_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Municipio <span class="text-red-500">*</span></flux:label>
                                    <flux:select
                                        wire:model.live="addresses.{{ $index }}.municipality_id"
                                        :disabled="!($addresses[$index]['province_id'] ?? null)"
                                    >
                                        <flux:select.option value="">Seleccionar...</flux:select.option>
                                        @if(isset($municipalities[$index]))
                                            @foreach($municipalities[$index] as $municipality)
                                                <flux:select.option value="{{ $municipality->id }}">{{ $municipality->name }}</flux:select.option>
                                            @endforeach
                                        @endif
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.municipality_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Código Postal <span class="text-red-500">*</span></flux:label>
                                    <flux:input
                                        wire:model="addresses.{{ $index }}.postal_code"
                                        placeholder="28001"
                                        required
                                    />
                                    <flux:error name="addresses.{{ $index }}.postal_code" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Observaciones</flux:label>
                                    <flux:input wire:model="addresses.{{ $index }}.description" placeholder="Notas adicionales..." />
                                </flux:field>
                            </div>
                        </div>
                    @endforeach

                    <flux:button type="button" wire:click="addAddress" variant="ghost" icon="plus"
                        class="w-full border-2 border-dashed border-zinc-300 hover:border-blue-500 hover:bg-blue-50 hover:text-blue-600 py-3">
                        Añadir otra dirección
                    </flux:button>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="Notas">
                <flux:field>
                    <flux:label for="notes">Notas</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('clients.index')" submit-label="Guardar Cambios" />
        </form>
    </x-agro.form-card>
</div>
