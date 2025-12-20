<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4" x-data="{ showPassword: false }">
    <!-- Elementos decorativos -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-agro-green-light)]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--color-agro-green)]/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md mx-auto relative z-10">
        <!-- Logo y Header -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group">
                <div class="inline-block max-w-[180px] mx-auto mb-3">
                    <img 
                        src="{{ asset('images/logo.png') }}" 
                        alt="Agro365 Logo" 
                        class="w-full h-auto max-h-24 object-contain drop-shadow-lg group-hover:scale-105 transition-transform duration-200"
                    >
                </div>
            </a>
            <p class="text-gray-600 text-sm font-medium">Cuaderno de campo digital para viticultores</p>
        </div>
        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-1 text-center">Iniciar Sesión</h2>
            <p class="text-gray-500 mb-6 text-center text-sm">Ingresa tus credenciales para continuar</p>
            
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email
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

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña
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

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            wire:model="remember" 
                            type="checkbox" 
                            id="remember"
                            class="w-4 h-4 text-[var(--color-agro-green-dark)] border-gray-300 rounded focus:ring-[var(--color-agro-green-dark)]"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Recordarme
                        </label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm text-[var(--color-agro-green-dark)] hover:underline font-medium">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove>Iniciar Sesión</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Iniciando sesión...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿No tienes cuenta? 
                    <a href="{{ route('register') }}" class="text-[var(--color-agro-green-dark)] hover:underline font-semibold">
                        Regístrate aquí
                    </a>
                </p>
        </div>
    </div>
</div>
