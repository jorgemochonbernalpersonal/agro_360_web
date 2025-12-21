<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4" x-data="{ showPassword: false, showPasswordConfirmation: false }">
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-agro-green-light)]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--color-agro-green)]/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md mx-auto relative z-10">
        <div class="text-center mb-3">
            <a href="{{ route('home') }}" class="inline-block group">
                <div class="inline-block max-w-[180px] mx-auto mb-2">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365 Logo" 
                        class="w-full h-auto max-h-24 object-contain drop-shadow-lg group-hover:scale-105 transition-transform duration-200"
                    >
                </div>
            </a>
            <p class="text-gray-600 text-sm font-medium">
                @auth
                    Crear nuevo usuario
                @else
                    Crea tu cuenta para comenzar
                @endauth
            </p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-1 text-center">Registro</h2>
            <p class="text-gray-500 mb-6 text-center text-sm">
                @auth
                    Completa los datos para crear un nuevo usuario
                @else
                    Únete a Agro365 y gestiona tu actividad agrícola
                @endauth
            </p>
            
            <form wire:submit="register" class="space-y-5">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre Completo *
                    </label>
                    <input 
                        wire:model="name" 
                        type="text" 
                        id="name"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="Juan Pérez"
                        required
                    >
                    @error('name') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email *
                    </label>
                    <input 
                        wire:model="email" 
                        type="email" 
                        id="email"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="correo@ejemplo.com"
                        required
                    >
                    @error('email') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                {{-- Honeypot: Campo oculto anti-bots --}}
                <div style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true" tabindex="-1">
                    <label for="company">Company (leave blank)</label>
                    <input 
                        wire:model="honeypot" 
                        type="text" 
                        id="company" 
                        name="company" 
                        autocomplete="off"
                        tabindex="-1"
                    >
                </div>

                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de Cuenta *
                    </label>
                    <select 
                        wire:model="role" 
                        id="role"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        required
                    >
                        @if(auth()->check())
                            @foreach($this->getAllowedRoles(auth()->user()) as $allowedRole)
                                @if($allowedRole !== 'winery')
                                    <option value="{{ $allowedRole }}">
                                        {{ match($allowedRole) {
                                            'admin' => 'Administrador',
                                            'supervisor' => 'Supervisor',
                                            'viticulturist' => 'Viticultor',
                                            default => ucfirst($allowedRole),
                                        } }}
                                    </option>
                                @endif
                            @endforeach
                        @else
                            <option value="viticulturist">Viticultor</option>
                        @endif
                    </select>
                    @error('role') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                    @if(!auth()->check())
                        <p class="mt-1 text-xs text-gray-500">
                            Selecciona si eres un viticultor
                        </p>
                    @endif
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña *
                    </label>
                    <div class="relative">
                        <input 
                            wire:model="password" 
                            type="password" 
                            id="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                            placeholder="••••••••"
                            required
                        >
                        <button
                            type="button"
                            x-on:click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Contraseña *
                    </label>
                    <div class="relative">
                        <input 
                            wire:model="password_confirmation" 
                            type="password" 
                            id="password_confirmation"
                            x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                            placeholder="••••••••"
                            required
                        >
                        <button
                            type="button"
                            x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Botón -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg"
                >
                    @auth
                        Crear Usuario
                    @else
                        Registrarse
                    @endauth
                </button>
            </form>

            <!-- Link a Login -->
            <div class="mt-6 text-center">
                @auth
                    <a href="{{ route($this->getRedirectRoute()) }}" class="text-sm text-[var(--color-agro-green-dark)] hover:underline">
                        Volver al dashboard
                    </a>
                @else
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes cuenta? 
                        <a href="{{ route('login') }}" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">
                            Inicia sesión
                        </a>
                    </p>
                @endauth
            </div>
        </div>
    </div>
</div>
