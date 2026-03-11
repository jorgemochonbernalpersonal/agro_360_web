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

            @if(auth()->check())
                {{-- Selector interno (admin/supervisor): select plano sin decoración --}}
                <flux:field>
                    <flux:label>Tipo de Cuenta</flux:label>
                    <flux:select wire:model="role" required>
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
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>
            @else
                {{-- Selector público: tarjetas de rol con descripción clara --}}
                <div x-data>
                    <flux:label class="mb-2 block">¿Cómo vas a usar Agro365?</flux:label>
                    <div class="grid gap-3">

                        {{-- Viticultor --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="viticulturist" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-green-300
                                        peer-checked:border-green-500 peer-checked:bg-green-50">
                                <span class="text-2xl leading-none mt-0.5">🌿</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">Viticultor</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">Cultivo uva y la vendo o entrego a una bodega. Gestiono mis parcelas, cuaderno de campo y entregas.</p>
                                    <p class="text-xs text-green-700 font-medium mt-1.5">✓ Plan básico gratis · Completo desde 9€/mes</p>
                                </div>
                            </div>
                        </label>

                        {{-- Bodega --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="winery" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-red-300
                                        peer-checked:border-red-500 peer-checked:bg-red-50">
                                <span class="text-2xl leading-none mt-0.5">🍷</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">Bodega</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">Recibo uva de viticultores y gestiono la elaboración. Controlo depósitos, vendimia y facturación a mis proveedores.</p>
                                    <p class="text-xs text-red-700 font-medium mt-1.5">✓ Desde 14€/mes · Incluye gestión de viticultores</p>
                                </div>
                            </div>
                        </label>

                        {{-- Productor --}}
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="role" value="producer" class="sr-only peer">
                            <div class="flex items-start gap-3 rounded-xl border-2 p-4 transition-all duration-200
                                        border-zinc-200 hover:border-violet-300
                                        peer-checked:border-violet-500 peer-checked:bg-violet-50">
                                <span class="text-2xl leading-none mt-0.5">🌿🍷</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-zinc-800 text-sm">
                                        Productor
                                        <span class="text-xs font-normal text-violet-600 ml-1">Viticultor + Bodega en uno</span>
                                    </p>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        Cultivo mis propias uvas <strong>y</strong> elaboro mi propio vino.
                                        No vendo la uva — la vinifco yo mismo. Soy viñedo y bodega en uno.
                                    </p>
                                    <p class="text-xs text-zinc-400 mt-1 italic">Solo si elaboras tu propio vino. Si vendes la uva a una bodega, selecciona Viticultor.</p>
                                    <p class="text-xs text-violet-700 font-medium mt-1.5">✓ Bundle 19€/mes · Acceso completo a viñedo y bodega</p>
                                </div>
                            </div>
                        </label>

                    </div>
                    <flux:error name="role" class="mt-1" />
                </div>
            @endif

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
