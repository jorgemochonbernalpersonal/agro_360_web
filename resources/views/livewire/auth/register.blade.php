<div class="min-h-screen flex items-center justify-center bg-agro-50 py-6 px-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
    <div class="w-full max-w-md mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-3">
            <a href="{{ route('home') }}" class="inline-block group">
                <img src="{{ asset('images/logo.png') }}" alt="Agro365" width="180" class="mx-auto max-h-24 object-contain group-hover:scale-105 transition-transform">
            </a>
            <flux:subheading class="mt-2">
                @auth Crear nuevo usuario @else Crea tu cuenta para comenzar @endauth
            </flux:subheading>
        </div>

        <x-agro.card>
            <div class="text-center mb-6">
                <flux:heading size="xl">Registro</flux:heading>
                <flux:subheading>
                    @auth Completa los datos para crear un nuevo usuario @else Únete a Agro365 y gestiona tu actividad agrícola @endauth
                </flux:subheading>
            </div>

            <form wire:submit="register" class="space-y-5">
                <flux:field>
                    <flux:label>Nombre Completo *</flux:label>
                    <flux:input wire:model="name" placeholder="Juan Pérez" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Email *</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="correo@ejemplo.com" required />
                    <flux:error name="email" />
                </flux:field>

                {{-- Honeypot --}}
                <div style="position: absolute; left: -9999px;" aria-hidden="true" tabindex="-1">
                    <flux:input wire:model="honeypot" type="text" autocomplete="off" tabindex="-1" />
                </div>

                <flux:field>
                    <flux:label>Tipo de Cuenta *</flux:label>
                    <flux:select wire:model="role" required>
                        @if(auth()->check())
                            @foreach($this->getAllowedRoles(auth()->user()) as $allowedRole)
                                @if($allowedRole !== 'winery')
                                    <flux:select.option value="{{ $allowedRole }}">
                                        {{ match($allowedRole) {
                                            'admin' => 'Administrador',
                                            'supervisor' => 'Supervisor',
                                            'viticulturist' => 'Viticultor',
                                            default => ucfirst($allowedRole),
                                        } }}
                                    </flux:select.option>
                                @endif
                            @endforeach
                        @else
                            <flux:select.option value="viticulturist">Viticultor</flux:select.option>
                        @endif
                    </flux:select>
                    <flux:error name="role" />
                    @if(!auth()->check())
                        <flux:description>Selecciona si eres un viticultor</flux:description>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>Contraseña *</flux:label>
                    <div class="relative">
                        <flux:input wire:model="password" :type="'password'" x-bind:type="showPassword ? 'text' : 'password'" placeholder="••••••••" required />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600" tabindex="-1">
                            <flux:icon x-show="!showPassword" icon="eye" variant="micro" />
                            <flux:icon x-show="showPassword" icon="eye-slash" variant="micro" x-cloak />
                        </button>
                    </div>
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirmar Contraseña *</flux:label>
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
                    @auth Crear Usuario @else Registrarse @endauth
                </flux:button>
            </form>

            <div class="mt-6 text-center">
                @auth
                    <a href="{{ route($this->getRedirectRoute()) }}" class="text-sm text-agro-700 hover:underline">Volver al dashboard</a>
                @else
                    <flux:subheading>
                        ¿Ya tienes cuenta?
                        <a href="{{ route('login') }}" class="text-agro-700 hover:underline font-semibold">Inicia sesión</a>
                    </flux:subheading>
                @endauth
            </div>
        </x-agro.card>
    </div>
</div>
