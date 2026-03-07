<div class="w-full max-w-md mx-auto">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain">
        </a>
        <p class="mt-2 text-sm text-zinc-500">Cuaderno de campo digital para viticultores</p>
    </div>

    @if(!$tokenValid)
        {{-- Token inválido o ya usado --}}
        <x-agro.card>
            <div class="text-center py-6 space-y-4">
                <div class="mx-auto w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <flux:icon icon="x-mark" class="size-6 text-red-600" />
                </div>
                <flux:heading size="lg">Enlace no válido</flux:heading>
                <flux:subheading>
                    Este enlace de activación ha expirado, ya fue usado, o no es correcto.
                    Contacta con tu bodega para solicitar una nueva invitación.
                </flux:subheading>
                <flux:button href="{{ route('login') }}" variant="ghost" wire:navigate>
                    Ir al inicio de sesión
                </flux:button>
            </div>
        </x-agro.card>

    @else
        {{-- Formulario de activación --}}
        <x-agro.card>

            <div class="text-center mb-6">
                <div class="mx-auto w-12 h-12 rounded-full bg-agro-100 flex items-center justify-center mb-3">
                    <flux:icon icon="key" class="size-6 text-agro-600" />
                </div>
                <flux:heading size="xl">Activa tu cuenta</flux:heading>
                <flux:subheading>
                    Tu bodega ya ha configurado tus parcelas y plantaciones.<br>
                    Elige una contraseña para acceder a tu cuaderno de campo.
                </flux:subheading>
            </div>

            <div class="space-y-4">

                <flux:field>
                    <flux:label>Nombre</flux:label>
                    <flux:input wire:model="name" type="text" autocomplete="name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Email</flux:label>
                    <flux:input wire:model="email" type="email" autocomplete="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Contraseña</flux:label>
                    <flux:input wire:model="password" type="password" autocomplete="new-password" />
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirmar contraseña</flux:label>
                    <flux:input wire:model="password_confirmation" type="password" autocomplete="new-password" />
                </flux:field>

                <flux:button
                    wire:click="activate"
                    wire:loading.attr="disabled"
                    variant="primary"
                    class="w-full"
                >
                    <span wire:loading.remove>Activar cuenta y entrar</span>
                    <span wire:loading>Activando...</span>
                </flux:button>

            </div>

        </x-agro.card>
    @endif

</div>
