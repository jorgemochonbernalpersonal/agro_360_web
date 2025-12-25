<div x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">
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
                                        <div class="flex-shrink-0 relative">
                                            {{-- Imagen de preview (siempre presente para JavaScript) --}}
                                            <img 
                                                id="profile-preview-img" 
                                                src="{{ $current_profile_image ? Storage::disk('public')->url($current_profile_image) : '' }}" 
                                                alt="Preview" 
                                                class="w-20 h-20 rounded-full object-cover border-4 border-gray-200 shadow-lg {{ !$current_profile_image ? 'hidden' : '' }}"
                                                onerror="this.style.display='none'; if(document.getElementById('profile-preview-placeholder')) document.getElementById('profile-preview-placeholder').style.display='flex';"
                                                wire:ignore
                                            >
                                            {{-- Placeholder con inicial (oculto por defecto si hay imagen) --}}
                                            <div 
                                                id="profile-preview-placeholder"
                                                class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)] flex items-center justify-center text-white text-2xl font-bold shadow-md hidden"
                                            >
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                            
                                            {{-- Badge de nueva imagen --}}
                                            @if($profile_image_preview)
                                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-[var(--color-agro-green)] rounded-full flex items-center justify-center z-10">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            
                                            {{-- Indicador de carga --}}
                                            <div wire:loading wire:target="profile_image" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full z-20">
                                                <svg class="animate-spin h-6 w-6 text-[var(--color-agro-green)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="flex-1">
                                            <input 
                                                type="file" 
                                                wire:model="profile_image" 
                                                id="profile_image"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                                x-on:change="
                                                    const file = $event.target.files[0];
                                                    if (file) {
                                                        const previewImg = document.getElementById('profile-preview-img');
                                                        const placeholder = document.getElementById('profile-preview-placeholder');
                                                        
                                                        // Mostrar preview inmediatamente con FileReader
                                                        const reader = new FileReader();
                                                        reader.onload = function(e) {
                                                            if (previewImg) {
                                                                previewImg.src = e.target.result;
                                                                previewImg.style.display = 'block';
                                                                previewImg.classList.remove('border-gray-200');
                                                                previewImg.classList.add('border-[var(--color-agro-green)]');
                                                            }
                                                            if (placeholder) {
                                                                placeholder.style.display = 'none';
                                                            }
                                                        };
                                                        reader.onerror = function() {
                                                            console.error('Error al leer el archivo');
                                                            if (placeholder) {
                                                                placeholder.style.display = 'flex';
                                                            }
                                                        };
                                                        reader.readAsDataURL(file);
                                                    } else {
                                                        // Si no hay archivo, restaurar imagen original
                                                        const previewImg = document.getElementById('profile-preview-img');
                                                        const placeholder = document.getElementById('profile-preview-placeholder');
                                                        if (previewImg && previewImg.dataset.originalSrc) {
                                                            previewImg.src = previewImg.dataset.originalSrc;
                                                        } else if (placeholder) {
                                                            previewImg.style.display = 'none';
                                                            placeholder.style.display = 'flex';
                                                        }
                                                    }
                                                "
                                                class="block w-full text-sm text-gray-500
                                                    file:mr-4 file:py-2 file:px-4
                                                    file:rounded-lg file:border-0
                                                    file:text-sm file:font-semibold
                                                    file:bg-green-50 file:text-[var(--color-agro-green-dark)]
                                                    hover:file:bg-green-100
                                                    cursor-pointer
                                                    @error('profile_image') border-red-300 @enderror"
                                            >
                                            <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF o WEBP (Máx. 2MB)</p>
                                            
                                            @if($profile_image_preview)
                                                <p class="mt-1 text-xs text-[var(--color-agro-green-dark)] font-semibold flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Nueva imagen seleccionada. Click "Guardar Cambios" para confirmar.
                                                </p>
                                            @endif
                                            
                                            @script
                                            <script>
                                                // Guardar la URL original de la imagen al cargar
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    const previewImg = document.getElementById('profile-preview-img');
                                                    if (previewImg && previewImg.src) {
                                                        previewImg.dataset.originalSrc = previewImg.src;
                                                    }
                                                });
                                                
                                                // Asegurar que el preview se mantenga después de actualizaciones de Livewire
                                                document.addEventListener('livewire:init', () => {
                                                    Livewire.hook('morph.updated', ({ el, component }) => {
                                                        // Si hay un archivo seleccionado pero el preview se perdió, restaurarlo
                                                        const fileInput = document.getElementById('profile_image');
                                                        if (fileInput && fileInput.files && fileInput.files.length > 0) {
                                                            const previewImg = document.getElementById('profile-preview-img');
                                                            const placeholder = document.getElementById('profile-preview-placeholder');
                                                            
                                                            // Solo restaurar si el preview se perdió (no tiene src o es diferente)
                                                            if (previewImg) {
                                                                const reader = new FileReader();
                                                                reader.onload = function(e) {
                                                                    // Solo actualizar si no hay un preview activo ya
                                                                    if (!previewImg.src || previewImg.src === previewImg.dataset.originalSrc) {
                                                                        previewImg.src = e.target.result;
                                                                        previewImg.style.display = 'block';
                                                                        previewImg.classList.remove('border-gray-200');
                                                                        previewImg.classList.add('border-[var(--color-agro-green)]');
                                                                        if (placeholder) {
                                                                            placeholder.style.display = 'none';
                                                                        }
                                                                    }
                                                                };
                                                                reader.readAsDataURL(fileInput.files[0]);
                                                            }
                                                        }
                                                    });
                                                });
                                            </script>
                                            @endscript
                                            
                                            @error('profile_image') 
                                                <p class="mt-1 text-sm text-red-600 font-medium flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $message }}
                                                </p> 
                                            @enderror
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
                                        <div class="relative">
                                            <input 
                                                wire:model="current_password" 
                                                type="password" 
                                                id="current_password"
                                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                                placeholder="Tu contraseña actual"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--color-agro-green-dark)] focus:ring-[var(--color-agro-green-dark)]/20 @error('current_password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
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
                                            <div class="flex items-center gap-1 mt-1">
                                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-label for="password" required>Nueva Contraseña</x-label>
                                        <div class="relative">
                                            <input 
                                                wire:model="password" 
                                                type="password" 
                                                id="password"
                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                placeholder="Nueva contraseña"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--color-agro-green-dark)] focus:ring-[var(--color-agro-green-dark)]/20 @error('password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
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
                                            <div class="flex items-center gap-1 mt-1">
                                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <x-label for="password_confirmation" required>Confirmar Nueva Contraseña</x-label>
                                        <div class="relative">
                                            <input 
                                                wire:model="password_confirmation" 
                                                type="password" 
                                                id="password_confirmation"
                                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                                placeholder="Confirma la nueva contraseña"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-[var(--color-agro-green-dark)] focus:ring-[var(--color-agro-green-dark)]/20"
                                                required
                                            />
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
                                            <div class="flex items-center gap-1 mt-1">
                                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
                                            </div>
                                        @enderror
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
    
    @script
    <script>
        // Escuchar cuando se actualiza el perfil para refrescar el header
        $wire.on('profile-updated', () => {
            // Esperar a que el toast se muestre antes de recargar
            // El toast tarda aproximadamente 300ms en aparecer
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    </script>
    @endscript
</div>
