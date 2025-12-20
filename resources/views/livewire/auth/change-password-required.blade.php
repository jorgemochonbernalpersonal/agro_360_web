<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[var(--color-agro-green)] via-[var(--color-agro-green-dark)] to-blue-900 p-4" x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">
    <div class="w-full max-w-md">
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Cambio de Contraseña Requerido</h1>
                <p class="text-gray-600 text-sm">
                    Tu cuenta fue creada por otro usuario. Por seguridad, debes cambiar tu contraseña temporal antes de continuar.
                </p>
            </div>

            <!-- Mensajes Flash -->
            @if(session('message'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
                    <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
                </div>
            @endif

            <!-- Formulario -->
            <form wire:submit="changePassword" class="space-y-6">
                <!-- Contraseña Actual -->
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña Temporal (del PDF) *
                    </label>
                    <div class="relative">
                        <input 
                            wire:model="current_password" 
                            type="password" 
                            id="current_password"
                            x-bind:type="showCurrentPassword ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="Ingresa la contraseña del PDF"
                            required
                            autofocus
                        >
                        <button
                            type="button"
                            x-on:click="showCurrentPassword = !showCurrentPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password') 
                        <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Nueva Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nueva Contraseña *
                    </label>
                    <div class="relative">
                        <input 
                            wire:model="password" 
                            type="password" 
                            id="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="Mínimo 8 caracteres"
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
                    <p class="mt-1 text-xs text-gray-500">
                        La contraseña debe tener al menos 8 caracteres.
                    </p>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Nueva Contraseña *
                    </label>
                    <div class="relative">
                        <input 
                            wire:model="password_confirmation" 
                            type="password" 
                            id="password_confirmation"
                            x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-transparent transition-all"
                            placeholder="Repite la nueva contraseña"
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

                <!-- Información -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800 mb-1">Importante:</p>
                            <p class="text-xs text-blue-800">
                                Al cambiar tu contraseña, tu email será verificado automáticamente y podrás acceder a todas las funcionalidades del sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botón -->
                <button 
                    type="submit"
                    class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold"
                >
                    Cambiar Contraseña y Continuar
                </button>
            </form>
        </div>
    </div>
</div>

