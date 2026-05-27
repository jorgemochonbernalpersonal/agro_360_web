<div>
    <x-agro.form-card
        :title="__('Editar Cliente')"
        :description="__('Modifica los datos del cliente')"
        :back-url="roleRoute('viticulturist.clients.show', $client->id)"
    >
        <form wire:submit="update" class="space-y-8" data-cy="client-edit-form">
            <x-agro.form-section :title="__('Tipo de Cliente')">
                <flux:field>
                    <flux:label>{{ __('Tipo') }} <span class="text-red-500">*</span></flux:label>
                    <flux:select wire:model.live="client_type" id="client_type" data-cy="client-type">
                        <flux:select.option value="individual">{{ __('Particular') }}</flux:select.option>
                        <flux:select.option value="company">{{ __('Empresa') }}</flux:select.option>
                    </flux:select>
                </flux:field>
            </x-agro.form-section>

            @if($client_type === 'individual')
                <x-agro.form-section :title="__('Datos Personales')">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Nombre') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="first_name" id="first_name" data-cy="first-name" required />
                            <flux:error name="first_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Apellidos') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="last_name" id="last_name" data-cy="last-name" required />
                            <flux:error name="last_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('DNI/NIE') }}</flux:label>
                            <flux:input wire:model="particular_document" id="particular_document" data-cy="particular-document" />
                            <flux:error name="particular_document" />
                        </flux:field>
                    </div>
                </x-agro.form-section>
            @else
                <x-agro.form-section :title="__('Datos de la Empresa')">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>{{ __('Nombre de la Empresa') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="company_name" id="company_name" data-cy="company-name" required />
                            <flux:error name="company_name" />
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('CIF/NIF') }} <span class="text-red-500">*</span></flux:label>
                            <flux:input wire:model="company_document" id="company_document" data-cy="company-document" required />
                            <flux:error name="company_document" />
                        </flux:field>
                    </div>
                </x-agro.form-section>
            @endif

            <x-agro.form-section :title="__('Contacto')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Email') }}</flux:label>
                        <flux:input wire:model="email" id="email" data-cy="email" type="email" />
                        <flux:error name="email" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Teléfono') }}</flux:label>
                        <flux:input wire:model="phone" id="phone" data-cy="phone" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section :title="__('Configuración')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ __('Descuento por defecto (%)') }}</flux:label>
                        <flux:input wire:model="default_discount" id="default_discount" type="number" step="0.01" min="0" max="100" />
                        <flux:error name="default_discount" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Método de pago') }}</flux:label>
                        <flux:select wire:model="payment_method" id="payment_method">
                            <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                            <flux:select.option value="cash">{{ __('Efectivo') }}</flux:select.option>
                            <flux:select.option value="transfer">{{ __('Transferencia') }}</flux:select.option>
                            <flux:select.option value="check">{{ __('Cheque') }}</flux:select.option>
                            <flux:select.option value="other">{{ __('Otro') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="payment_method" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Número de cuenta') }}</flux:label>
                        <flux:input wire:model="account_number" id="account_number" />
                        <flux:error name="account_number" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section :title="__('CAE')">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <flux:checkbox wire:model.live="has_cae" :label="__('Tiene CAE')" />
                    </div>
                    @if($has_cae)
                        <flux:field>
                            <flux:label>{{ __('Número CAE') }}</flux:label>
                            <flux:input wire:model="cae_number" id="cae_number" />
                            <flux:error name="cae_number" />
                        </flux:field>
                    @endif
                </div>
            </x-agro.form-section>

            <x-agro.form-section :title="__('Direcciones')">
                <div class="space-y-4">
                    @foreach($addresses as $index => $address)
                        <div class="border-2 border-zinc-200 rounded-lg p-4 bg-white shadow-xs hover:border-blue-300 transition-colors" data-cy="address-item" data-cy-address-index="{{ $index }}">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-zinc-900">{{ __('Dirección #:num', ['num' => $index + 1]) }}</h4>
                                    @if($address['is_default'])
                                        <flux:badge color="blue" size="sm">{{ __('Por defecto') }}</flux:badge>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    @if(!$address['is_default'])
                                        <button
                                            type="button"
                                            wire:click="setDefaultAddress({{ $index }})"
                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                            data-cy="set-default-address"
                                            data-cy-address-index="{{ $index }}"
                                        >
                                            {{ __('Marcar por defecto') }}
                                        </button>
                                    @endif

                                    @if(count($addresses) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeAddress({{ $index }})"
                                            class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center gap-1"
                                            data-cy="remove-address"
                                            data-cy-address-index="{{ $index }}"
                                        >
                                            <flux:icon icon="trash" class="size-4" />
                                            {{ __('Eliminar') }}
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <flux:field>
                                        <flux:label>{{ __('Dirección completa') }} <span class="text-red-500">*</span></flux:label>
                                        <flux:input
                                            wire:model="addresses.{{ $index }}.address"
                                            id="addresses_{{ $index }}_address"
                                            data-cy="address-address"
                                            data-cy-address-index="{{ $index }}"
                                            :placeholder="__('Calle, número, piso, puerta...')"
                                            required
                                        />
                                        <flux:error name="addresses.{{ $index }}.address" />
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('Comunidad Autónoma') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:select
                                        wire:model.live="addresses.{{ $index }}.autonomous_community_id"
                                        id="addresses_{{ $index }}_autonomous_community_id"
                                        data-cy="address-autonomous-community"
                                        data-cy-address-index="{{ $index }}"
                                    >
                                        <flux:select.option value="">{{ __('Seleccionar...') }}</flux:select.option>
                                        @foreach($autonomousCommunities as $ca)
                                            <flux:select.option value="{{ $ca->id }}">{{ $ca->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.autonomous_community_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Provincia') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:select
                                        wire:model.live="addresses.{{ $index }}.province_id"
                                        id="addresses_{{ $index }}_province_id"
                                        data-cy="address-province"
                                        data-cy-address-index="{{ $index }}"
                                        :disabled="!($addresses[$index]['autonomous_community_id'] ?? null)"
                                    >
                                        <flux:select.option value="">{{ __('Seleccionar...') }}</flux:select.option>
                                        @if(isset($provinces[$index]))
                                            @foreach($provinces[$index] as $province)
                                                <flux:select.option value="{{ $province->id }}">{{ $province->name }}</flux:select.option>
                                            @endforeach
                                        @endif
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.province_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Municipio') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:select
                                        wire:model.live="addresses.{{ $index }}.municipality_id"
                                        id="addresses_{{ $index }}_municipality_id"
                                        data-cy="address-municipality"
                                        data-cy-address-index="{{ $index }}"
                                        :disabled="!($addresses[$index]['province_id'] ?? null)"
                                    >
                                        <flux:select.option value="">{{ __('Seleccionar...') }}</flux:select.option>
                                        @if(isset($municipalities[$index]))
                                            @foreach($municipalities[$index] as $municipality)
                                                <flux:select.option value="{{ $municipality->id }}">{{ $municipality->name }}</flux:select.option>
                                            @endforeach
                                        @endif
                                    </flux:select>
                                    <flux:error name="addresses.{{ $index }}.municipality_id" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Código Postal') }} <span class="text-red-500">*</span></flux:label>
                                    <flux:input
                                        wire:model="addresses.{{ $index }}.postal_code"
                                        id="addresses_{{ $index }}_postal_code"
                                        data-cy="address-postal-code"
                                        data-cy-address-index="{{ $index }}"
                                        placeholder="28001"
                                        required
                                    />
                                    <flux:error name="addresses.{{ $index }}.postal_code" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Observaciones') }}</flux:label>
                                    <flux:input wire:model="addresses.{{ $index }}.description" id="addresses_{{ $index }}_description" data-cy="address-description" data-cy-address-index="{{ $index }}" :placeholder="__('Notas adicionales...')" />
                                </flux:field>

                            </div>
                        </div>
                    @endforeach

                    <button
                        type="button"
                        wire:click="addAddress"
                        class="w-full py-3 border-2 border-dashed border-zinc-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-zinc-600 hover:text-blue-600 font-semibold flex items-center justify-center gap-2"
                        data-cy="add-address-button"
                    >
                        <flux:icon icon="plus" class="size-5" />
                        {{ __('Añadir otra dirección') }}
                    </button>
                </div>
            </x-agro.form-section>

            <x-agro.form-section :title="__('Notas')">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <x-agro.form-actions :cancel-url="roleRoute('viticulturist.clients.show', $client->id)" :submit-label="__('Actualizar Cliente')" />
        </form>
    </x-agro.form-card>
</div>
