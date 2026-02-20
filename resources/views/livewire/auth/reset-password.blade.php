<div class="min-h-screen flex items-center justify-center bg-agro-50 py-6 px-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
    <div class="w-full max-w-md mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="180" class="mx-auto max-h-24 object-contain group-hover:scale-105 transition-transform">
            </a>
            <flux:subheading class="mt-2">Cuaderno de campo digital para viticultores</flux:subheading>
        </div>

        <x-agro.card>
            <div class="text-center mb-6">
                <flux:heading size="xl">Restablecer Contraseña</flux:heading>
                <flux:subheading>Ingresa tu nueva contraseña</flux:subheading>
            </div>

            @if(session('status'))
                <flux:callout variant="success" icon="check-circle" class="mb-6">
                    <flux:callout.text>{{ session('status') }}</flux:callout.text>
                </flux:callout>
            @endif

            @if(session('error'))
                <flux:callout variant="danger" icon="x-circle" class="mb-6">
                    <flux:callout.text>{{ session('error') }}</flux:callout.text>
                    <flux:callout.link href="{{ route('password.request') }}">Solicitar nuevo enlace</flux:callout.link>
                </flux:callout>
            @else
                <form wire:submit="resetPassword" class="space-y-5">
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="correo@ejemplo.com" required autofocus />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nueva Contraseña</flux:label>
                        <div class="relative">
                            <flux:input wire:model="password" :type="'password'" x-bind:type="showPassword ? 'text' : 'password'" placeholder="••••••••" required />
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                                <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                                <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                            </button>
                        </div>
                        <flux:error name="password" />
                        <flux:description>La contraseña debe tener al menos 8 caracteres.</flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>Confirmar Nueva Contraseña</flux:label>
                        <div class="relative">
                            <flux:input wire:model="password_confirmation" :type="'password'" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" placeholder="••••••••" required />
                            <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                                <flux:icon x-show="!showPasswordConfirmation" icon="eye" variant="micro" />
                                <flux:icon x-show="showPasswordConfirmation" icon="eye-slash" variant="micro" x-cloak />
                            </button>
                        </div>
                        <flux:error name="password_confirmation" />
                    </flux:field>

                    <flux:button type="submit" variant="primary" class="w-full">
                        Restablecer Contraseña
                    </flux:button>
                </form>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-agro-700 hover:underline font-medium">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </x-agro.card>
    </div>
</div>
