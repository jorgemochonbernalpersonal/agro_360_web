<div class="min-h-screen flex items-center justify-center bg-agro-50 p-4" x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">
    <div class="w-full max-w-md">
        <x-agro.card>
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                    <flux:icon icon="exclamation-triangle" class="size-8 text-yellow-600" />
                </div>
                <flux:heading size="xl">Cambio de Contraseña Requerido</flux:heading>
                <flux:subheading class="mt-2">Tu cuenta fue creada por otro usuario. Por seguridad, debes cambiar tu contraseña temporal antes de continuar.</flux:subheading>
            </div>

            <form wire:submit="changePassword" class="space-y-6">
                <flux:field>
                    <flux:label>Contraseña Temporal (del PDF) *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="current_password" :type="'password'" x-bind:type="showCurrentPassword ? 'text' : 'password'" placeholder="Ingresa la contraseña del PDF" required autofocus />
                        <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showCurrentPassword" icon="eye" variant="micro" />
                            <flux:icon x-show="showCurrentPassword" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="current_password" />
                </flux:field>

                <flux:field>
                    <flux:label>Nueva Contraseña *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password" :type="'password'" x-bind:type="showPassword ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" required />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                            <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="password" />
                    <flux:description>La contraseña debe tener al menos 8 caracteres.</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Confirmar Nueva Contraseña *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password_confirmation" :type="'password'" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" placeholder="Repite la nueva contraseña" required />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showPasswordConfirmation" icon="eye" variant="micro" />
                            <flux:icon x-show="showPasswordConfirmation" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="password_confirmation" />
                </flux:field>

                <flux:callout icon="information-circle">
                    <flux:callout.heading>Importante</flux:callout.heading>
                    <flux:callout.text>Al cambiar tu contraseña, tu email será verificado automáticamente y podrás acceder a todas las funcionalidades del sistema.</flux:callout.text>
                </flux:callout>

                <flux:button type="submit" variant="primary" class="w-full">
                    Cambiar Contraseña y Continuar
                </flux:button>
            </form>
        </x-agro.card>
    </div>
</div>
