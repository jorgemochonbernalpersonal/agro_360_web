<div>
    <x-agro.form-card
        title="{{ __('Añadir Viticultor') }}"
        :description="__('Crea un viticultor ghost (sin acceso al sistema). Cuando el viticultor se registre con el mismo DNI, las cuentas se fusionarán automáticamente.')"
        :back-url="roleRoute('viticulturists.index')"
    >
        <form wire:submit="save" class="space-y-8">
            <x-agro.form-section title="{{ __('Datos del Viticultor') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Nombre completo') }} <span class="text-red-500">*</span></flux:label>
                        <flux:input
                            wire:model="name"
                            id="name"
                            placeholder="{{ __('Nombre y apellidos') }}"
                            required
                        />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('DNI / NIE') }}</flux:label>
                        <flux:input
                            wire:model="dni"
                            id="dni"
                            placeholder="12345678A"
                        />
                        <flux:description>Clave de fusión — cuando el viticultor se registre con este DNI, las cuentas se unirán.</flux:description>
                        <flux:error name="dni" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Teléfono') }}</flux:label>
                        <flux:input
                            wire:model="phone"
                            id="phone"
                            placeholder="+34 600 000 000"
                        />
                        <flux:error name="phone" />
                    </flux:field>

                    <flux:field class="md:col-span-2">
                        <flux:label>{{ __('Email (opcional)') }}</flux:label>
                        <flux:input
                            wire:model="email"
                            id="email"
                            type="email"
                            placeholder="viticultor@ejemplo.com"
                        />
                        <flux:description>Si se indica, el viticultor podrá vincular su cuenta por email además del DNI.</flux:description>
                        <flux:error name="email" />
                    </flux:field>
                </div>
            </x-agro.form-section>

            <x-agro.form-section title="{{ __('Notas internas') }}">
                <flux:field>
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" id="notes" rows="3" :placeholder="__('Observaciones internas sobre este viticultor...')" />
                    <flux:error name="notes" />
                </flux:field>
            </x-agro.form-section>

            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
                <flux:icon icon="information-circle" class="size-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-amber-800">
                    Este viticultor se creará <strong>{{ __('sin acceso al sistema') }}</strong>. Podrás añadir sus parcelas manualmente.
                    Cuando el viticultor se registre con el mismo DNI, las cuentas se fusionarán y él obtendrá acceso a sus datos.
                </p>
            </div>

            <x-agro.form-actions :cancel-url="roleRoute('viticulturists.index')" submit-:label="__('Crear Viticultor')" />
        </form>
    </x-agro.form-card>
</div>
