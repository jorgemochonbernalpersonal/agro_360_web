<div>
    <x-form-card
        title="Editar Cliente"
        description="Modifica los datos del cliente"
        icon="👥"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        :back-url="route('viticulturist.clients.show', $client->id)"
    >
        <form wire:submit="update" class="space-y-8">
            <x-form-section title="Tipo de Cliente" color="green">
                <div>
                    <x-label for="client_type" required>Tipo</x-label>
                    <x-select wire:model.live="client_type" id="client_type" required>
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
                            <x-input wire:model="first_name" id="first_name" required />
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="last_name" required>Apellidos</x-label>
                            <x-input wire:model="last_name" id="last_name" required />
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="particular_document">DNI/NIE</x-label>
                            <x-input wire:model="particular_document" id="particular_document" />
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
                            <x-input wire:model="company_name" id="company_name" required />
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-label for="company_document">CIF/NIF</x-label>
                            <x-input wire:model="company_document" id="company_document" />
                            @error('company_document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-form-section>
            @endif

            <x-form-section title="Contacto" color="green">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="email">Email</x-label>
                        <x-input wire:model="email" id="email" type="email" />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-label for="phone">Teléfono</x-label>
                        <x-input wire:model="phone" id="phone" />
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
                    <div class="flex items-center">
                        <x-checkbox wire:model="active" id="active" />
                        <x-label for="active" class="ml-2">Cliente activo</x-label>
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
                        <div class="border-2 border-gray-200 rounded-lg p-4 bg-white shadow-sm hover:border-blue-300 transition-colors">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-gray-900">Dirección #{{ $index + 1 }}</h4>
                                    @if($address['is_default'])
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                            Por defecto
                                        </span>
                                    @endif
                                    @if($address['is_delivery_note_address'])
                                        <span class="px-2 py-0.5 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
                                            Albarán
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex gap-2">
                                    @if(!$address['is_default'])
                                        <button 
                                            type="button"
                                            wire:click="setDefaultAddress({{ $index }})"
                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium"
                                        >
                                            Marcar por defecto
                                        </button>
                                    @endif
                                    
                                    @if(count($addresses) > 1)
                                        <button 
                                            type="button"
                                            wire:click="removeAddress({{ $index }})"
                                            class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center gap-1"
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
                                    <x-label for="addresses_{{ $index }}_name">Alias (ej: Oficina, Almacén...)</x-label>
                                    <x-input wire:model="addresses.{{ $index }}.name" id="addresses_{{ $index }}_name" placeholder="Nombre identificativo" />
                                </div>
                                
                                <div class="md:col-span-2">
                                    <x-label for="addresses_{{ $index }}_address" required>Dirección completa</x-label>
                                    <x-input 
                                        wire:model="addresses.{{ $index }}.address" 
                                        id="addresses_{{ $index }}_address" 
                                        placeholder="Calle, número, piso, puerta..."
                                        required 
                                    />
                                    @error('addresses.' . $index . '.address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_postal_code">Código Postal</x-label>
                                    <x-input wire:model="addresses.{{ $index }}.postal_code" id="addresses_{{ $index }}_postal_code" placeholder="28001" />
                                </div>
                                
                                <div>
                                    <x-label for="addresses_{{ $index }}_description">Observaciones</x-label>
                                    <x-input wire:model="addresses.{{ $index }}.description" id="addresses_{{ $index }}_description" placeholder="Notas adicionales..." />
                                </div>
                                
                                <div class="md:col-span-2 flex flex-wrap items-center gap-4 pt-2 border-t border-gray-200">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            wire:model="addresses.{{ $index }}.is_delivery_note_address"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Usar para albaranes</span>
                                    </label>
                                    
                                    <p class="text-xs text-gray-500 ml-auto">
                                        💡 Puedes tener múltiples direcciones para diferentes propósitos
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <button 
                        type="button"
                        wire:click="addAddress"
                        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-gray-600 hover:text-blue-600 font-semibold flex items-center justify-center gap-2"
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
                <x-button type="button" variant="ghost" href="{{ route('viticulturist.clients.show', $client->id) }}">Cancelar</x-button>
                <x-button type="submit" variant="primary">Actualizar Cliente</x-button>
            </div>
        </form>
    </x-form-card>
</div>
