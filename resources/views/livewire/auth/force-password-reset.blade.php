<div class="min-h-screen flex items-center justify-center bg-agro-50 p-4" x-data="{ showCurrentPassword: false, showNewPassword: false, showNewPasswordConfirmation: false }">
    <div class="w-full max-w-md">
        <x-agro.card>
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-agro-100 mb-4">
                    <flux:icon icon="lock-closed" class="size-8 text-agro-700" />
                </div>
                <flux:heading size="xl" class="!text-agro-700">Cambio de Contraseña Obligatorio</flux:heading>
                <flux:subheading class="mt-2">Por seguridad, debes cambiar tu contraseña temporal antes de continuar.</flux:subheading>
            </div>

            @if(session()->has('message'))
                <flux:callout variant="success" icon="check-circle" class="mb-4">
                    <flux:callout.text>{{ session('message') }}</flux:callout.text>
                </flux:callout>
            @endif

            <form wire:submit.prevent="updatePassword" class="space-y-6">
                <flux:field>
                    <flux:label>Contraseña Temporal *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="current_password" :type="'password'" x-bind:type="showCurrentPassword ? 'text' : 'password'" placeholder="Ingresa la contraseña temporal recibida por email" required autocomplete="current-password" />
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
                        <flux:input wire:model="new_password" :type="'password'" x-bind:type="showNewPassword ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" />
                        <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showNewPassword" icon="eye" variant="micro" />
                            <flux:icon x-show="showNewPassword" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="new_password" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirmar Nueva Contraseña *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="new_password_confirmation" :type="'password'" x-bind:type="showNewPasswordConfirmation ? 'text' : 'password'" placeholder="Repite tu nueva contraseña" required autocomplete="new-password" />
                        <button type="button" @click="showNewPasswordConfirmation = !showNewPasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showNewPasswordConfirmation" icon="eye" variant="micro" />
                            <flux:icon x-show="showNewPasswordConfirmation" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Actualizar Contraseña
                </flux:button>

                <flux:callout icon="information-circle">
                    <flux:callout.heading>Información importante</flux:callout.heading>
                    <flux:callout.text>
                        Al cambiar tu contraseña, tu email quedará automáticamente verificado. Tu nueva contraseña debe tener al menos 8 caracteres.
                    </flux:callout.text>
                </flux:callout>
            </form>
        </x-agro.card>
    </div>
</div>
