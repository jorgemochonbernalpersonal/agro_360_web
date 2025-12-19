<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green-bg)] via-white to-[var(--color-agro-green-bright)]/30 py-6 px-4">
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

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Contraseña *
                    </label>
                    <input 
                        wire:model="password_confirmation" 
                        type="password" 
                        id="password_confirmation"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition bg-gray-50 focus:bg-white"
                        placeholder="••••••••"
                        required
                    >
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
