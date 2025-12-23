<div>
    <x-form-card
        title="Editar Cliente"
        description="Modifica los datos del cliente"
        icon="👥"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.clients.show', $client->id)"
    >
        <form wire:submit="update" class="space-y-8" data-cy="client-edit-form">
            <x-form-section title="Tipo de Cliente" color="green">
                <div>
                    <x-label for="client_type" required>Tipo</x-label>
                    <x-select wire:model.live="client_type" id="client_type" data-cy="client-type" required>
                        <option value="individual">Particular</option>
                        <option value="company">Empresa</option>
                    </x-select>
                </div>
            </x-form-section>

            @if($client_type === 'individual')
                <x-form-section title="Datos Personales" color="green">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-label for="first_name" required>Nombre</x-label>
                            <x-input wire:model="first_name" id="first_name" data-cy="first-name" required />
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="last_name" required>Apellidos</x-label>
                            <x-input wire:model="last_name" id="last_name" data-cy="last-name" required />
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="particular_document">DNI/NIE</x-label>
                            <x-input wire:model="particular_document" id="particular_document" data-cy="particular-document" />
                            @error('particular_document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-form-section>
            @else
                <x-form-section title="Datos de la Empresa" color="green">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-label for="company_name" required>Nombre de la Empresa</x-label>
                            <x-input wire:model="company_name" id="company_name" data-cy="company-name" required />
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="company_document" required>CIF/NIF</x-label>
                            <x-input 
                                wire:model="company_document" 
                                id="company_document" 
                                data-cy="company-document"
                                :error="$errors->first('company_document')"
                                required
                            />
                        </div>
                    </div>
                </x-form-section>
            @endif

            <x-form-section title="Contacto" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="email">Email</x-label>
                        <x-input wire:model="email" id="email" data-cy="email" type="email" />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="phone">Teléfono</x-label>
                        <x-input wire:model="phone" id="phone" data-cy="phone" />
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="Configuración" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="default_discount">Descuento por defecto (%)</x-label>
                        <x-input wire:model="default_discount" id="default_discount" type="number" step="0.01" min="0" max="100" />
                        @error('default_discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="payment_method">Método de pago</x-label>
                        <x-select wire:model="payment_method" id="payment_method">
                            <option value="">Selecciona...</option>
                            <option value="cash">Efectivo</option>
                            <option value="transfer">Transferencia</option>
                            <option value="check">Cheque</option>
                            <option value="other">Otro</option>
                        </x-select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="account_number">Número de cuenta</x-label>
                        <x-input wire:model="account_number" id="account_number" />
                        @error('account_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-form-section>

            <x-form-section title="CAE (Canarias)" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center">
                        <x-checkbox wire:model.live="has_cae" id="has_cae" />
                        <x-label for="has_cae" class="ml-2">Tiene CAE</x-label>
                    </div>
                    @if($has_cae)
                        <div>
                            <x-label for="cae_number">Número CAE</x-label>
                            <x-input wire:model="cae_number" id="cae_number" />
                            @error('cae_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </x-form-section>

            <x-form-section title="Direcciones" color="blue">
                <div class="space-y-4">
                    @foreach($addresses as $index => $address)
                        <div class="border-2 border-gray-200 rounded-lg p-4 bg-white shadow-sm hover:border-blue-300 transition-colors" data-cy="address-item" data-cy-address-index="{{ $index }}">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-gray-900">Dirección #{{ $index + 1 }}</h4>
                                    @if($address['is_default'])
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                            Por defecto
                                        </span>
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
                                            Marcar por defecto
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
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <x-label for="addresses_{{ $index }}_address" required>Dirección completa</x-label>
                                    <x-input 
                                        wire:model="addresses.{{ $index }}.address" 
                                        id="addresses_{{ $index }}_address" 
                                        data-cy="address-address"
                                        data-cy-address-index="{{ $index }}"
                                        placeholder="Calle, número, piso, puerta..."
                                        required 
                                    />
                                    @error('addresses.' . $index . '.address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_autonomous_community_id" required>Comunidad Autónoma</x-label>
                                    <x-select 
                                        wire:model.live="addresses.{{ $index }}.autonomous_community_id" 
                                        id="addresses_{{ $index }}_autonomous_community_id" 
                                        data-cy="address-autonomous-community" 
                                        data-cy-address-index="{{ $index }}"
                                        :error="$errors->first('addresses.' . $index . '.autonomous_community_id')"
                                        required
                                    >
                                        <option value="">Seleccionar...</option>
                                        @foreach($autonomousCommunities as $ca)
                                            <option value="{{ $ca->id }}">{{ $ca->name }}</option>
                                        @endforeach
                                    </x-select>
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_province_id" required>Provincia</x-label>
                                    <x-select 
                                        wire:model.live="addresses.{{ $index }}.province_id" 
                                        id="addresses_{{ $index }}_province_id" 
                                        data-cy="address-province" 
                                        data-cy-address-index="{{ $index }}"
                                        :error="$errors->first('addresses.' . $index . '.province_id')"
                                        :disabled="!($addresses[$index]['autonomous_community_id'] ?? null)"
                                        required
                                    >
                                        <option value="">Seleccionar...</option>
                                        @if(isset($provinces[$index]))
                                            @foreach($provinces[$index] as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_municipality_id" required>Municipio</x-label>
                                    <x-select 
                                        wire:model.live="addresses.{{ $index }}.municipality_id" 
                                        id="addresses_{{ $index }}_municipality_id" 
                                        data-cy="address-municipality" 
                                        data-cy-address-index="{{ $index }}"
                                        :error="$errors->first('addresses.' . $index . '.municipality_id')"
                                        :disabled="!($addresses[$index]['province_id'] ?? null)"
                                        required
                                    >
                                        <option value="">Seleccionar...</option>
                                        @if(isset($municipalities[$index]))
                                            @foreach($municipalities[$index] as $municipality)
                                                <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_postal_code" required>Código Postal</x-label>
                                    <x-input 
                                        wire:model="addresses.{{ $index }}.postal_code" 
                                        id="addresses_{{ $index }}_postal_code" 
                                        data-cy="address-postal-code" 
                                        data-cy-address-index="{{ $index }}" 
                                        placeholder="28001"
                                        :error="$errors->first('addresses.' . $index . '.postal_code')"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_description">Observaciones</x-label>
                                    <x-input wire:model="addresses.{{ $index }}.description" id="addresses_{{ $index }}_description" data-cy="address-description" data-cy-address-index="{{ $index }}" placeholder="Notas adicionales..." />
                                </div>
                                
                            </div>
                        </div>
                    @endforeach
                    
                    <button 
                        type="button"
                        wire:click="addAddress"
                        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-gray-600 hover:text-blue-600 font-semibold flex items-center justify-center gap-2"
                        data-cy="add-address-button"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir otra dirección
                    </button>
                </div>
            </x-form-section>

            <x-form-section title="Notas" color="green">
                <div>
                    <x-label for="notes">Notas</x-label>
                    <x-textarea wire:model="notes" id="notes" rows="3" />
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-form-section>

            <div class="flex justify-end gap-4">
                <x-button type="button" variant="ghost" href="{{ route('viticulturist.clients.show', $client->id) }}" data-cy="cancel-button">Cancelar</x-button>
                <x-button type="submit" variant="primary" data-cy="submit-button">Actualizar Cliente</x-button>
            </div>
        </form>
    </x-form-card>
</div>
