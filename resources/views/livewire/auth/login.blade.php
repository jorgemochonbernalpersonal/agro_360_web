<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4">
    <!-- Elementos decorativos -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-agro-green-light)]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--color-agro-green)]/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="w-full max-w-md mx-auto relative z-10">
        <!-- Logo y Header -->
        <div class="text-center mb-4">
            <div class="inline-block max-w-[180px] mx-auto mb-2">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="Agro365 Logo" 
                    class="w-full h-auto max-h-24 object-contain drop-shadow-lg"
                >
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Agro365</h1>
            <p class="text-gray-600 text-sm font-medium">Cuaderno de campo digital para viticultores</p>
        </div>

        <!-- Features Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 mb-4 border border-gray-200 shadow-md">
            <div class="grid grid-cols-3 gap-3">
                <!-- Cuaderno Digital -->
                <div class="text-center group">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Cuaderno Digital</p>
                </div>

                <!-- Parcelas -->
                <div class="text-center group">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Gestión de Parcelas</p>
                </div>

                <!-- Maquinaria -->
                <div class="text-center group">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-lg bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)] flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Control de Maquinaria</p>
                </div>
            </div>
            <p class="text-xs text-center text-gray-500 mt-3 leading-relaxed">
                Controla tus parcelas, tratamientos y equipo desde cualquier lugar
            </p>
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
                    <input 
                        wire:model="password" 
                        type="password" 
                        id="password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="••••••••"
                        required
                    >
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

        <!-- Footer Info -->
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-500">
                🌾 Digitaliza tu actividad vitícola de forma profesional
            </p>
        </div>
    </div>
</div>
