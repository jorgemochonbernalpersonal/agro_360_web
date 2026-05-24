<div class="w-full max-w-md mx-auto" x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-100 mb-4">
                <flux:icon icon="exclamation-triangle" class="size-6 text-yellow-600" />
            </div>
            <flux:heading size="xl">{{ __('Cambio de Contraseña Requerido') }}</flux:heading>
            <flux:subheading class="mt-1">
                {{ __('Tu cuenta fue creada por otro usuario. Debes cambiar tu contraseña temporal antes de continuar.') }}
            </flux:subheading>
        </div>

        <form wire:submit="changePassword" class="space-y-4">

            <flux:field>
                <flux:label>{{ __('Contraseña Temporal (del PDF)') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="current_password"
                                :type="'password'"
                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                :placeholder="__('Ingresa la contraseña del PDF')"
                                required autofocus autocomplete="current-password" />
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
                    <flux:input wire:model="password"
                                :type="'password'"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                :placeholder="__('Mínimo 8 caracteres')"
                                required autocomplete="new-password" />
                    <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                        <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
                <flux:error name="password" />
                <flux:description>{{ __('Mínimo 8 caracteres') }}</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirmar Nueva Contraseña') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="password_confirmation"
                                :type="'password'"
                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                :placeholder="__('Repite la nueva contraseña')"
                                required autocomplete="new-password" />
                    <button type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600"
                            tabindex="-1">
                        <flux:icon x-show="!showPasswordConfirmation" icon="eye" variant="micro" />
                        <flux:icon x-show="showPasswordConfirmation" icon="eye-slash" variant="micro" x-cloak />
                    </button>
                </div>
                <flux:error name="password_confirmation" />
            </flux:field>

            <flux:callout icon="information-circle">
                <flux:callout.text>
                    {{ __('Al cambiar tu contraseña, tu email será verificado automáticamente y podrás acceder al sistema.') }}
                </flux:callout.text>
            </flux:callout>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="changePassword">{{ __('Cambiar Contraseña y Continuar') }}</span>
                <span wire:loading wire:target="changePassword" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    {{ __('Procesando...') }}
                </span>
            </flux:button>

        </form>

    </x-agro.card>

</div>
