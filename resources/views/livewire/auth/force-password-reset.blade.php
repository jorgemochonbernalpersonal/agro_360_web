<div class="w-full max-w-md mx-auto" x-data="{ showCurrentPassword: false, showNewPassword: false, showNewPasswordConfirmation: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-agro-100 mb-4">
                <flux:icon icon="lock-closed" class="size-6 text-agro-700" />
            </div>
            <flux:heading size="xl">{{ __('Cambio de Contraseña Obligatorio') }}</flux:heading>
            <flux:subheading class="mt-1">
                {{ __('Por seguridad, debes cambiar tu contraseña temporal antes de continuar.') }}
            </flux:subheading>
        </div>

        @if(session()->has('message'))
            <flux:callout variant="success" icon="check-circle" class="mb-5">
                <flux:callout.text>{{ session('message') }}</flux:callout.text>
            </flux:callout>
        @endif

        <form wire:submit.prevent="updatePassword" class="space-y-4">

            <flux:field>
                <flux:label>{{ __('Contraseña Temporal') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="current_password"
                                :type="'password'"
                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                :placeholder="__('Contraseña temporal recibida por email')"
                                required autocomplete="current-password" autofocus />
                    <button type="button"
                            @click="showCurrentPassword = !showCurrentPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showCurrentPassword" icon="eye" variant="micro" />
                        <flux:icon x-show="showCurrentPassword" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
                <flux:error name="current_password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Nueva Contraseña') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="new_password"
                                :type="'password'"
                                x-bind:type="showNewPassword ? 'text' : 'password'"
                                :placeholder="__('Mínimo 8 caracteres')"
                                required autocomplete="new-password" />
                    <button type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showNewPassword" icon="eye" variant="micro" />
                        <flux:icon x-show="showNewPassword" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
                <flux:error name="new_password" />
                <flux:description>{{ __('Mínimo 8 caracteres') }}</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirmar Nueva Contraseña') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="new_password_confirmation"
                                :type="'password'"
                                x-bind:type="showNewPasswordConfirmation ? 'text' : 'password'"
                                :placeholder="__('Repite tu nueva contraseña')"
                                required autocomplete="new-password" />
                    <button type="button"
                            @click="showNewPasswordConfirmation = !showNewPasswordConfirmation"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showNewPasswordConfirmation" icon="eye" variant="micro" />
                        <flux:icon x-show="showNewPasswordConfirmation" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
                <flux:error name="new_password_confirmation" />
            </flux:field>

            <flux:callout icon="information-circle">
                <flux:callout.text>
                    {{ __('Al cambiar tu contraseña, tu email quedará automáticamente verificado y tendrás acceso completo al sistema.') }}
                </flux:callout.text>
            </flux:callout>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updatePassword">{{ __('Actualizar Contraseña') }}</span>
                <span wire:loading wire:target="updatePassword" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    {{ __('Procesando...') }}
                </span>
            </flux:button>

        </form>

    </x-agro.card>

</div>
