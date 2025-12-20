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
