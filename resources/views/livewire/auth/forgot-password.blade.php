<div class="min-h-screen flex items-center justify-center bg-agro-50 py-6 px-4">
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
                <flux:subheading>Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña</flux:subheading>
            </div>

            @if(session('status'))
                <flux:callout variant="success" icon="check-circle" class="mb-6">
                    <flux:callout.text>{{ session('status') }}</flux:callout.text>
                </flux:callout>
            @endif

            @if(session('error'))
                <flux:callout variant="danger" icon="x-circle" class="mb-6">
                    <flux:callout.text>{{ session('error') }}</flux:callout.text>
                </flux:callout>
            @endif

            <form wire:submit="sendResetLink" class="space-y-5">
                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="correo@ejemplo.com" required autofocus />
                    <flux:error name="email" />
                </flux:field>

                <flux:button type="submit" variant="primary" class="w-full">
                    Enviar Enlace de Restablecimiento
                </flux:button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-agro-700 hover:underline font-medium">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </x-agro.card>
    </div>
</div>
