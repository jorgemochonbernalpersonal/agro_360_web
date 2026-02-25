<div class="w-full max-w-md mx-auto" x-data="{ showPassword: false, showPasswordConfirmation: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
        <p class="mt-2 text-sm text-zinc-500">Cuaderno de campo digital para viticultores</p>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-agro-100 mb-4">
                <flux:icon icon="lock-closed" class="size-6 text-agro-700" />
            </div>
            <flux:heading size="xl">Nueva Contraseña</flux:heading>
            <flux:subheading class="mt-1">Elige una contraseña segura para tu cuenta</flux:subheading>
        </div>

        @if(session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-5">
                <flux:callout.text>{{ session('status') }}</flux:callout.text>
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger" icon="x-circle" class="mb-5">
                <flux:callout.text>{{ session('error') }}</flux:callout.text>
                <flux:callout.link href="{{ route('password.request') }}">Solicitar nuevo enlace</flux:callout.link>
            </flux:callout>
        @else

            <form wire:submit="resetPassword" class="space-y-4">

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email"
                                placeholder="correo@ejemplo.com"
                                required autofocus autocomplete="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Nueva Contraseña</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password"
                                    :type="'password'"
                                    x-bind:type="showPassword ? 'text' : 'password'"
                                    placeholder="Mínimo 8 caracteres"
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
                    <flux:description>Mínimo 8 caracteres</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Confirmar Nueva Contraseña</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password_confirmation"
                                    :type="'password'"
                                    x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                    placeholder="Repite la nueva contraseña"
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

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="resetPassword">Restablecer Contraseña</span>
                    <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                        <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                        Procesando...
                    </span>
                </flux:button>

            </form>

        @endif

        <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-agro-700 hover:text-agro-900 hover:underline font-medium">
                ← Volver al inicio de sesión
            </a>
        </div>

    </x-agro.card>

</div>
