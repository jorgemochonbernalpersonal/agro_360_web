<div x-data="{ showCurrentPassword: false, showNewPassword: false, showNewPasswordConfirmation: false }">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-[var(--color-agro-green-light)] to-[var(--color-agro-green)]">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-2xl">
            <!-- Logo/Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            <!-- Title and Description -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-[var(--color-agro-green-dark)] mb-2">
                    Cambio de Contraseña Obligatorio
                </h2>
                <p class="text-sm text-gray-600">
                    Por seguridad, debes cambiar tu contraseña temporal antes de continuar.
                </p>
            </div>

            <!-- Flash Message -->
            @if (session()->has('message'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-sm text-green-700">{{ session('message') }}</p>
                </div>
            @endif

            <!-- Form -->
            <form wire:submit.prevent="updatePassword" class="space-y-6">
                <!-- Contraseña Temporal -->
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña Temporal *
                    </label>
                    <div class="relative">
                        <input
                            wire:model="current_password"
                            type="password"
                            id="current_password"
                            x-bind:type="showCurrentPassword ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition @error('current_password') border-red-500 @enderror"
                            placeholder="Ingresa la contraseña temporal recibida por email"
                            required
                            autocomplete="current-password"
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
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nueva Contraseña -->
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nueva Contraseña *
                    </label>
                    <div class="relative">
                        <input
                            wire:model="new_password"
                            type="password"
                            id="new_password"
                            x-bind:type="showNewPassword ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition @error('new_password') border-red-500 @enderror"
                            placeholder="Mínimo 8 caracteres"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            x-on:click="showNewPassword = !showNewPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Nueva Contraseña -->
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirmar Nueva Contraseña *
                    </label>
                    <div class="relative">
                        <input
                            wire:model="new_password_confirmation"
                            type="password"
                            id="new_password_confirmation"
                            x-bind:type="showNewPasswordConfirmation ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--color-agro-green-dark)] focus:border-[var(--color-agro-green-dark)] transition"
                            placeholder="Repite tu nueva contraseña"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            x-on:click="showNewPasswordConfirmation = !showNewPasswordConfirmation"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showNewPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] text-white font-semibold rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition-all duration-200"
                    >
                        Actualizar Contraseña
                    </button>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Información importante:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Al cambiar tu contraseña, tu email quedará automáticamente verificado</li>
                                <li>Tu nueva contraseña debe tener al menos 8 caracteres</li>
                                <li>Podrás acceder al sistema inmediatamente después</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
