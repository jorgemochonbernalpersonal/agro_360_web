@php
    $profileIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">
        {{-- Header --}}
        <x-page-header
            :icon="$profileIcon"
            title="Mi Perfil"
            description="Gestiona tu información personal y configuración de cuenta"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        >
            <x-slot:actionButton>
                <a href="{{ route('profile.show') }}" class="group">
                    <button class="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-gray-500 to-gray-700 text-white hover:from-gray-600 hover:to-gray-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver Perfil
                    </button>
                </a>
            </x-slot:actionButton>
        </x-page-header>

        {{-- Tabs Navigation --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <nav class="flex space-x-4 px-6" aria-label="Tabs">
                    <button
                        wire:click="setActiveTab('personal')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'personal' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Información Personal
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('password')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'password' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Seguridad
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('contact')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'contact' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Contacto
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Personal Info Tab --}}
                @if($activeTab === 'personal')
                    <div class="animate-fade-in">
                        @if (session()->has('message'))
                            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form wire:submit="updatePersonalInfo" class="space-y-6">
                            <x-form-section title="Información Personal" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-label for="name" required>Nombre Completo</x-label>
                                        <x-input 
                                            wire:model="name" 
                                            type="text" 
                                            id="name"
                                            placeholder="Tu nombre completo"
                                            :error="$errors->first('name')"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <x-label for="email" required>Email</x-label>
                                        <x-input 
                                            wire:model="email" 
                                            type="email" 
                                            id="email"
                                            placeholder="tu@email.com"
                                            :error="$errors->first('email')"
                                            required
                                        />
                                    </div>
                                </div>

                                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-green-800">Información de cuenta</p>
                                            <p class="text-sm text-green-700 mt-1">
                                                Rol: <span class="font-bold">{{ \App\Helpers\NavigationHelper::getRoleName(auth()->user()->role) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </x-form-section>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 text-white hover:from-blue-600 hover:to-blue-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Password Tab --}}
                @if($activeTab === 'password')
                    <div class="animate-fade-in">
                        @if (session()->has('password_message'))
                            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">{{ session('password_message') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form wire:submit="updatePassword" class="space-y-6">
                            <x-form-section title="Cambiar Contraseña" color="green">
                                <div class="space-y-6">
                                    <div>
                                        <x-label for="current_password" required>Contraseña Actual</x-label>
                                        <x-input 
                                            wire:model="current_password" 
                                            type="password" 
                                            id="current_password"
                                            placeholder="Tu contraseña actual"
                                            :error="$errors->first('current_password')"
                                            required
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <x-label for="password" required>Nueva Contraseña</x-label>
                                            <x-input 
                                                wire:model="password" 
                                                type="password" 
                                                id="password"
                                                placeholder="Nueva contraseña"
                                                :error="$errors->first('password')"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <x-label for="password_confirmation" required>Confirmar Contraseña</x-label>
                                            <x-input 
                                                wire:model="password_confirmation" 
                                                type="password" 
                                                id="password_confirmation"
                                                placeholder="Confirma tu contraseña"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-yellow-800">Requisitos de contraseña</p>
                                                <ul class="text-sm text-yellow-700 mt-1 list-disc list-inside">
                                                    <li>Mínimo 8 caracteres</li>
                                                    <li>Al menos una letra mayúscula y minúscula</li>
                                                    <li>Al menos un número</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-form-section>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 text-white hover:from-blue-600 hover:to-blue-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                                     Actualizar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Contact Info Tab --}}
                @if($activeTab === 'contact')
                    <div class="animate-fade-in">
                        @if (session()->has('contact_message'))
                            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">{{ session('contact_message') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form wire:submit="updateContactInfo" class="space-y-6">
                            <x-form-section title="Información de Contacto" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <x-label for="address">Dirección</x-label>
                                        <x-input 
                                            wire:model="address" 
                                            type="text" 
                                            id="address"
                                            placeholder="Calle, número, piso..."
                                            :error="$errors->first('address')"
                                        />
                                    </div>

                                    <div>
                                        <x-label for="city">Ciudad</x-label>
                                        <x-input 
                                            wire:model="city" 
                                            type="text" 
                                            id="city"
                                            placeholder="Tu ciudad"
                                            :error="$errors->first('city')"
                                        />
                                    </div>

                                    <div>
                                        <x-label for="postal_code">Código Postal</x-label>
                                        <x-input 
                                            wire:model="postal_code" 
                                            type="text" 
                                            id="postal_code"
                                            placeholder="12345"
                                            :error="$errors->first('postal_code')"
                                        />
                                    </div>

                                    <div>
                                        <x-label for="province">Provincia</x-label>
                                        <x-input 
                                            wire:model="province" 
                                            type="text" 
                                            id="province"
                                            placeholder="Tu provincia"
                                            :error="$errors->first('province')"
                                        />
                                    </div>

                                    <div>
                                        <x-label for="country">País</x-label>
                                        <x-input 
                                            wire:model="country" 
                                            type="text" 
                                            id="country"
                                            placeholder="España"
                                            :error="$errors->first('country')"
                                        />
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-label for="phone">Teléfono</x-label>
                                        <x-input 
                                            wire:model="phone" 
                                            type="tel" 
                                            id="phone"
                                            placeholder="+34 600 000 000"
                                            :error="$errors->first('phone')"
                                        />
                                    </div>
                                </div>
                            </x-form-section>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-blue)] to-blue-700 text-white hover:from-blue-600 hover:to-blue-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Guardar Información
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
