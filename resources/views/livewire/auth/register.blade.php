<div class="w-full max-w-md mx-auto" x-data="{ showPassword: false, showPasswordConfirmation: false }">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('home') }}" class="inline-block">
            <img src="{{ asset('images/logo-nav.png') }}" alt="Agro365" width="128"
                 class="mx-auto max-h-14 object-contain transition-transform hover:scale-105">
        </a>
        <p class="mt-2 text-sm text-zinc-500">
            @auth {{ __('Gestión de usuarios') }} @else {{ __('Crea tu cuenta para comenzar') }} @endauth
        </p>
    </div>

    <x-agro.card>

        <div class="text-center mb-6">
            <flux:heading size="xl">
                @auth {{ __('Crear Usuario') }} @else {{ __('Registro') }} @endauth
            </flux:heading>
            <flux:subheading>
                @auth
                    {{ __('Completa los datos para crear un nuevo usuario') }}
                @else
                    {{ __('Únete a Agro365 y gestiona tu actividad agrícola') }}
                @endauth
            </flux:subheading>
        </div>

        {{-- Honeypot --}}
        <div style="position: absolute; left: -9999px;" aria-hidden="true" tabindex="-1">
            <flux:input wire:model="honeypot" type="text" autocomplete="off" tabindex="-1" />
        </div>

        <form wire:submit="register" class="space-y-4">

            <flux:field>
                <flux:label>{{ __('Nombre Completo') }}</flux:label>
                <flux:input wire:model="name"
                            placeholder="{{ __('Juan Pérez') }}"
                            required autocomplete="name" autofocus />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label>
                <flux:input wire:model="email" type="email"
                            :placeholder="__('correo@ejemplo.com')"
                            required autocomplete="email" />
                <flux:error name="email" />
            </flux:field>

            @if(auth()->check())
                {{-- Selector interno (admin/supervisor): select plano sin decoración --}}
                <flux:field>
                    <flux:label>{{ __('Tipo de Cuenta') }}</flux:label>
                    <flux:select wire:model="role" required>
                        @foreach($this->getAllowedRoles(auth()->user()) as $allowedRole)
                            <flux:select.option value="{{ $allowedRole }}">
                                {{ match($allowedRole) {
                                    'admin'         => __('Administrador'),
                                    'supervisor'    => __('Supervisor'),
                                    'winery'        => __('Bodega'),
                                    'viticulturist' => __('Viticultor'),
                                    'producer'      => __('Productor (viticultor + bodega)'),
                                    default         => ucfirst($allowedRole),
                                } }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>
            @else
                {{-- Selector público: tarjetas de rol con descripción clara --}}
                <div x-data>
                    <flux:label class="mb-2 block">{{ __('¿Cómo vas a usar Agro365?') }}</flux:label>
                    <div class="grid gap-3">

                        {{-- Viticultor --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="viticulturist" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-agro-400
                                        peer-checked:border-agro-600 peer-checked:bg-agro-50">
                                <div class="shrink-0 w-8 h-8 rounded-lg bg-agro-100 text-agro-700 flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">{{ __('Viticultor') }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ __('Cultivo uva y la vendo o entrego a una bodega. Gestiono mis parcelas, cuaderno de campo y entregas.') }}</p>
                                    <p class="text-xs text-agro-700 font-medium mt-1.5">{{ __('Plan básico gratis · Completo desde 9€/mes') }}</p>
                                </div>
                            </div>
                        </label>

                        {{-- Bodega --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="winery" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-agro-400
                                        peer-checked:border-agro-600 peer-checked:bg-agro-50">
                                <div class="shrink-0 w-8 h-8 rounded-lg bg-agro-100 text-agro-700 flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 22h8M7 10h10M12 15v7M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H9c-1.5 4-2 6-2 8a5 5 0 0 0 5 5Z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">{{ __('Bodega') }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ __('Recibo uva de viticultores y gestiono la elaboración. Controlo depósitos, vendimia y facturación a mis proveedores.') }}</p>
                                    <p class="text-xs text-agro-700 font-medium mt-1.5">{{ __('Desde 19€/mes · Incluye gestión de viticultores') }}</p>
                                </div>
                            </div>
                        </label>

                        {{-- Productor --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="producer" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-agro-400
                                        peer-checked:border-agro-600 peer-checked:bg-agro-50">
                                <div class="shrink-0 w-8 h-8 rounded-lg bg-agro-100 text-agro-700 flex items-center justify-center mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">
                                        {{ __('Productor') }}
                                        <span class="text-xs font-normal text-agro-600 ml-1">{{ __('Viticultor + Bodega en uno') }}</span>
                                    </p>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ __('Cultivo mis propias uvas y elaboro mi propio vino. No vendo la uva — la vinfico yo mismo. Soy viñedo y bodega en uno.') }}
                                    </p>
                                    <p class="text-xs text-zinc-400 mt-1 italic">{{ __('Solo si elaboras tu propio vino. Si vendes la uva a una bodega, selecciona Viticultor.') }}</p>
                                    <p class="text-xs text-agro-700 font-medium mt-1.5">{{ __('Bundle 19€/mes · Acceso completo a viñedo y bodega') }}</p>
                                </div>
                            </div>
                        </label>

                        {{-- Denominación de Origen — solo registro interno (admin/supervisor) --}}
                        {{-- Las DOs se dan de alta por invitación o por un administrador --}}

                    </div>
                    <flux:error name="role" class="mt-1" />
                </div>
            @endif

            @guest
            <div x-show="$wire.role === 'viticulturist'" x-cloak>
                <flux:field>
                    <flux:label>{{ __('DNI / NIF') }} <span class="text-zinc-400 font-normal text-xs">({{ __('opcional') }})</span></flux:label>
                    <flux:input wire:model="dni"
                                placeholder="12345678A"
                                maxlength="20"
                                autocomplete="off" />
                    <flux:description class="text-xs text-zinc-400">{{ __('Si tu bodega ya te tiene registrado, lo usaremos para vincular tu cuenta automáticamente.') }}</flux:description>
                    <flux:error name="dni" />
                </flux:field>
            </div>
            @endguest

            <flux:field>
                <flux:label>{{ __('Contraseña') }}</flux:label>
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
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirmar Contraseña') }}</flux:label>
                <div class="relative">
                    <flux:input wire:model="password_confirmation"
                                :type="'password'"
                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                :placeholder="__('Repite la contraseña')"
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
                    @auth {{ __('Crear Usuario') }} @else {{ __('Registrarse') }} @endauth
                </span>
                <span wire:loading wire:target="register" class="flex items-center gap-2">
                    <flux:icon icon="arrow-path" variant="micro" class="animate-spin" />
                    {{ __('Procesando...') }}
                </span>
            </flux:button>

        </form>

        @guest
        <div class="mt-5 pt-5 border-t border-zinc-100">
            <p class="text-xs text-center text-zinc-400 mb-3">{{ __('O regístrate con') }}</p>
            <a href="{{ route('auth.google.redirect') }}"
               class="flex items-center justify-center gap-3 w-full rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 hover:border-zinc-300 transition-colors">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                {{ __('Continuar con Google') }}
            </a>
        </div>
        @endguest

        <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
            @auth
                <a href="{{ route($this->getRedirectRoute()) }}"
                   class="text-sm text-agro-700 hover:text-agro-900 hover:underline font-medium">
                    {{ __('← Volver al dashboard') }}
                </a>
            @else
                <flux:subheading>
                    {{ __('¿Ya tienes cuenta?') }}
                    <a href="{{ route('login') }}"
                       class="text-agro-700 hover:text-agro-900 hover:underline font-semibold">
                        {{ __('Inicia sesión') }}
                    </a>
                </flux:subheading>
            @endauth
        </div>

    </x-agro.card>

</div>
