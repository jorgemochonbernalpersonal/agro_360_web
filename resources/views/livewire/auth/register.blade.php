<div class="w-full max-w-md mx-auto" x-data="{ showPassword: false, showPasswordConfirmation: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="160"
                 class="mx-auto max-h-20 object-contain transition-transform hover:scale-105">
        </a>
        <p class="mt-2 text-sm text-zinc-500">
            @auth Gestión de usuarios @else Crea tu cuenta para comenzar @endauth
        </p>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <flux:heading size="xl">
                @auth Crear Usuario @else Registro @endauth
            </flux:heading>
            <flux:subheading>
                @auth
                    Completa los datos para crear un nuevo usuario
                @else
                    Únete a Agro365 y gestiona tu actividad agrícola
                @endauth
            </flux:subheading>
        </div>

        {{-- Honeypot --}}
        <div style="position: absolute; left: -9999px;" aria-hidden="true" tabindex="-1">
            <flux:input wire:model="honeypot" type="text" autocomplete="off" tabindex="-1" />
        </div>

        <form wire:submit="register" class="space-y-4">

            <flux:field>
                <flux:label>Nombre Completo</flux:label>
                <flux:input wire:model="name"
                            placeholder="Juan Pérez"
                            required autocomplete="name" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input wire:model="email" type="email"
                            placeholder="correo@ejemplo.com"
                            required autocomplete="email" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Tipo de Cuenta</flux:label>
                <flux:select wire:model="role" required>
                    @if(auth()->check())
                        @foreach($this->getAllowedRoles(auth()->user()) as $allowedRole)
                            <flux:select.option value="{{ $allowedRole }}">
                                {{ match($allowedRole) {
                                    'admin'         => 'Administrador',
                                    'supervisor'    => 'Supervisor',
                                    'winery'        => 'Bodega',
                                    'viticulturist' => 'Viticultor',
                                    'producer'      => 'Productor (viticultor + bodega)',
                                    default         => ucfirst($allowedRole),
                                } }}
                            </flux:select.option>
                        @endforeach
                    @else
                        <flux:select.option value="viticulturist">Viticultor</flux:select.option>
                        <flux:select.option value="winery">Bodega</flux:select.option>
                        <flux:select.option value="producer">Productor (viticultor + bodega)</flux:select.option>
                    @endif
                </flux:select>
                <flux:error name="role" />
                @if(!auth()->check())
                    <flux:description>Selecciona tu perfil de usuario</flux:description>
                @endif
            </flux:field>

            <flux:field>
                <flux:label>Contraseña</flux:label>
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
            </flux:field>

            <flux:field>
                <flux:label>Confirmar Contraseña</flux:label>
                <div class="relative">
                    <flux:input wire:model="password_confirmation"
                                :type="'password'"
                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                placeholder="Repite la contraseña"
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
                <span wire:loading.remove wire:target="register">
                    @auth Crear Usuario @else Registrarse @endauth
                </span>
                <span wire:loading wire:target="register" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    Procesando...
                </span>
            </flux:button>

        </form>

        <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
            @auth
                <a href="{{ route($this->getRedirectRoute()) }}"
                   class="text-sm text-agro-700 hover:text-agro-900 hover:underline font-medium">
                    ← Volver al dashboard
                </a>
            @else
                <flux:subheading>
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}"
                       class="text-agro-700 hover:text-agro-900 hover:underline font-semibold">
                        Inicia sesión
                    </a>
                </flux:subheading>
            @endauth
        </div>

    </x-agro.card>

</div>
