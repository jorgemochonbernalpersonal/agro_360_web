<div class="w-full max-w-md mx-auto">

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
                <flux:icon icon="key" class="size-6 text-agro-700" />
            </div>
            <flux:heading size="xl">Recuperar Contraseña</flux:heading>
            <flux:subheading class="mt-1">
                Te enviaremos un enlace para restablecer tu contraseña
            </flux:subheading>
        </div>

        @if(session('status'))
            <flux:callout variant="success" icon="check-circle" class="mb-5">
                <flux:callout.text>{{ session('status') }}</flux:callout.text>
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger" icon="x-circle" class="mb-5">
                <flux:callout.text>{{ session('error') }}</flux:callout.text>
            </flux:callout>
        @endif

        @error('email')
            <flux:callout variant="danger" icon="x-circle" class="mb-5">
                <flux:callout.text>{{ $message }}</flux:callout.text>
            </flux:callout>
        @enderror

        <form wire:submit="sendResetLink" class="space-y-4">

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input wire:model="email" type="email"
                            placeholder="correo@ejemplo.com"
                            required autofocus autocomplete="email" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="sendResetLink">Enviar Enlace</span>
                <span wire:loading wire:target="sendResetLink" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    Enviando...
                </span>
            </flux:button>

        </form>

        <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
            <a href="{{ route('login') }}"
               class="text-sm text-agro-700 hover:text-agro-900 hover:underline font-medium">
                ← Volver al inicio de sesión
            </a>
        </div>

    </x-agro.card>

</div>
