<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4">
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
        
        <!-- Formulario -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-1 text-center">Restablecer Contraseña</h2>
            <p class="text-gray-500 mb-6 text-center text-sm">Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña</p>
            
            <!-- Mensajes Flash -->
            @if(session('status'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
                    <p class="text-sm font-semibold text-green-800">{{ session('status') }}</p>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
                    <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
                </div>
            @endif
            
            <form wire:submit="sendResetLink" class="space-y-5">
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
                        autofocus
                    >
                    @error('email') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white py-3.5 px-4 rounded-lg font-bold hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-agro-green-dark)] transition-all transform hover:scale-[1.02] shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove>Enviar Enlace de Restablecimiento</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-[var(--color-agro-green-dark)] hover:underline font-medium">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</div>
