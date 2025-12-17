<div>
    <div class="space-y-6 animate-fade-in">
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
                           Cambiar Contraseña
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('contact')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'contact' ? 'border-[var(--color-agro-green-dark)] text-[var(--color-agro-green-dark)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Contacto
                        </div>
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-8">
                {{-- Personal Tab --}}
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

                                {{-- Imagen de Perfil --}}
                                <div class="mt-6">
                                    <x-label for="profile_image">Foto de Perfil</x-label>
                                    <div class="mt-2 flex items-center gap-6">
                                        {{-- Preview con imagen temporal o actual --}}
                                        <div class="flex-shrink-0">
                                            @if($profile_image)
                                                {{-- Preview de la imagen NUEVA (temporal) --}}
                                                <img src="{{ $profile_image->temporaryUrl() }}" alt="Preview" class="w-20 h-20 rounded-full object-cover border-4 border-[var(--color-agro-green)] shadow-lg animate-pulse">
                                            @elseif($current_profile_image)
                                                {{-- Imagen actual guardada --}}
                                                <img src="{{ Storage::url($current_profile_image) }}" alt="Profile" class="w-20 h-20 rounded-full object-cover border-4 border-gray-200 shadow-md">
                                            @else
                                                {{-- Placeholder con inicial --}}
                                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center text-white text-2xl font-bold shadow-md">
                                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1">
                                            <input 
                                                type="file" 
                                                wire:model="profile_image" 
                                                id="profile_image"
                                                accept="image/*"
                                                class="block w-full text-sm text-gray-500
                                                    file:mr-4 file:py-2 file:px-4
                                                    file:rounded-lg file:border-0
                                                    file:text-sm file:font-semibold
                                                    file:bg-green-50 file:text-[var(--color-agro-green-dark)]
                                                    hover:file:bg-green-100
                                                    cursor-pointer"
                                            >
                                            <p class="mt-1 text-xs text-gray-500">JPG, PNG o GIF (Máx. 2MB)</p>
                                            
                                            @if($profile_image)
                                                <p class="mt-1 text-xs text-[var(--color-agro-green-dark)] font-semibold">
                                                    ✓ Nueva imagen seleccionada. Click "Guardar Cambios" para confirmar.
                                                </p>
                                            @endif
                                            
                                            @error('profile_image') 
                                                <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> 
                                            @enderror
                                            
                                            <div wire:loading wire:target="profile_image" class="mt-2">
                                                <p class="text-sm text-[var(--color-agro-green-dark)]">⏳ Cargando preview...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-form-section>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
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
                                        <x-label for="password_confirmation" required>Confirmar Nueva Contraseña</x-label>
                                        <x-input 
                                            wire:model="password_confirmation" 
                                            type="password" 
                                            id="password_confirmation"
                                            placeholder="Confirma la nueva contraseña"
                                            required
                                        />
                                    </div>

                                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <p class="text-sm font-medium text-green-800">Requisitos de contraseña:</p>
                                        <ul class="mt-2 text-xs text-green-700 list-disc list-inside space-y-1">
                                            <li>Mínimo 8 caracteres</li>
                                            <li>Al menos una letra mayúscula</li>
                                            <li>Al menos una letra minúscula</li>
                                            <li>Al menos un número</li>
                                        </ul>
                                    </div>
                                </div>
                            </x-form-section>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Actualizar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Contact Tab --}}
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
                                        <x-label for="province_id">Provincia</x-label>
                                        <x-select 
                                            wire:model="province_id" 
                                            id="province_id"
                                            :error="$errors->first('province_id')"
                                        >
                                            <option value="">Seleccionar provincia...</option>
                                            @foreach($this->provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </x-select>
                                    </div>

                                    <div>
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
                                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Guardar Contacto
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
