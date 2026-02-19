<div x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">
    <div class="space-y-6 animate-fade-in">
        {{-- Tabs Navigation --}}
        <x-agro.card :padding="false">
            <div class="border-b border-zinc-200">
                <nav class="flex space-x-4 px-6" aria-label="Tabs">
                    <button
                        wire:click="setActiveTab('personal')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'personal' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="user" class="size-5" />
                            Información Personal
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('password')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'password' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="lock-closed" class="size-5" />
                            Cambiar Contraseña
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('contact')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'contact' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="envelope" class="size-5" />
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
                            <flux:callout variant="success" class="mb-6">
                                <flux:callout.text>{{ session('message') }}</flux:callout.text>
                            </flux:callout>
                        @endif

                        <form wire:submit="updatePersonalInfo" class="space-y-6">
                            <x-agro.form-section title="Información Personal" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <flux:field>
                                        <flux:label required>Nombre Completo</flux:label>
                                        <flux:input wire:model="name" type="text" id="name" placeholder="Tu nombre completo" required />
                                        <flux:error name="name" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label required>Email</flux:label>
                                        <flux:input wire:model="email" type="email" id="email" placeholder="tu@email.com" required />
                                        <flux:error name="email" />
                                    </flux:field>
                                </div>

                                {{-- Imagen de Perfil --}}
                                    <div class="mt-6">
                                    <flux:label>Foto de Perfil</flux:label>
                                    <div class="mt-2 flex items-center gap-6">
                                        {{-- Preview con imagen temporal o actual --}}
                                        <div class="flex-shrink-0 relative">
                                            {{-- Imagen de preview (siempre presente para JavaScript) --}}
                                            @if($current_profile_image)
                                                <img 
                                                    id="profile-preview-img" 
                                                    src="{{ Storage::disk('public')->url($current_profile_image) }}" 
                                                    data-original-src="{{ Storage::disk('public')->url($current_profile_image) }}"
                                                    alt="Preview" 
                                                    class="w-20 h-20 rounded-full object-cover border-4 border-zinc-200 shadow-lg"
                                                    onerror="this.style.display='none'; const placeholder = document.getElementById('profile-preview-placeholder'); if(placeholder) placeholder.style.display='flex';"
                                                    wire:ignore
                                                >
                                            @else
                                                <img 
                                                    id="profile-preview-img" 
                                                    src="" 
                                                    data-original-src=""
                                                    alt="Preview" 
                                                    class="w-20 h-20 rounded-full object-cover border-4 border-zinc-200 shadow-lg hidden"
                                                    onerror="this.style.display='none'; const placeholder = document.getElementById('profile-preview-placeholder'); if(placeholder) placeholder.style.display='flex';"
                                                    wire:ignore
                                                >
                                            @endif
                                            {{-- Placeholder con inicial (visible solo si no hay imagen) --}}
                                            <div 
                                                id="profile-preview-placeholder"
                                                class="w-20 h-20 rounded-full bg-gradient-to-br from-agro-500 to-agro-700 flex items-center justify-center text-white text-2xl font-bold shadow-md {{ $current_profile_image ? 'hidden' : '' }}"
                                                wire:ignore
                                            >
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                            
                                            {{-- Badge de nueva imagen --}}
                                            @if($profile_image_preview)
                                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-agro-500 rounded-full flex items-center justify-center z-10">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            
                                            {{-- Indicador de carga --}}
                                            <div wire:loading wire:target="profile_image" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full z-20">
                                                <svg class="animate-spin h-6 w-6 text-agro-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                                                                previewImg.classList.remove('hidden');
                                                                previewImg.style.display = 'block';
                                                                previewImg.classList.remove('border-zinc-200');
                                                                previewImg.classList.add('border-agro-500');
                                                                previewImg.onerror = null; // Reset error handler
                                                            }
                                                            if (placeholder) {
                                                                placeholder.classList.add('hidden');
                                                                placeholder.style.display = 'none';
                                                            }
                                                        };
                                                        reader.onerror = function() {
                                                            console.error('Error al leer el archivo');
                                                            if (previewImg) {
                                                                previewImg.classList.add('hidden');
                                                                previewImg.style.display = 'none';
                                                            }
                                                            if (placeholder) {
                                                                placeholder.classList.remove('hidden');
                                                                placeholder.style.display = 'flex';
                                                            }
                                                        };
                                                        reader.onabort = function() {
                                                            console.error('Lectura del archivo cancelada');
                                                            if (previewImg && previewImg.dataset.originalSrc) {
                                                                previewImg.src = previewImg.dataset.originalSrc;
                                                                previewImg.classList.remove('hidden');
                                                                previewImg.style.display = 'block';
                                                                if (placeholder) {
                                                                    placeholder.classList.add('hidden');
                                                                    placeholder.style.display = 'none';
                                                                }
                                                            } else if (placeholder) {
                                                                previewImg.classList.add('hidden');
                                                                previewImg.style.display = 'none';
                                                                placeholder.classList.remove('hidden');
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
                                                            previewImg.classList.remove('hidden');
                                                            previewImg.style.display = 'block';
                                                            if (placeholder) {
                                                                placeholder.classList.add('hidden');
                                                                placeholder.style.display = 'none';
                                                            }
                                                        } else if (placeholder) {
                                                            previewImg.classList.add('hidden');
                                                            previewImg.style.display = 'none';
                                                            placeholder.classList.remove('hidden');
                                                            placeholder.style.display = 'flex';
                                                        }
                                                    }
                                                "
                                                class="block w-full text-sm text-zinc-500
                                                    file:mr-4 file:py-2 file:px-4
                                                    file:rounded-lg file:border-0
                                                    file:text-sm file:font-semibold
                                                    file:bg-green-50 file:text-agro-700
                                                    hover:file:bg-green-100
                                                    cursor-pointer
                                                    @error('profile_image') border-red-300 @enderror"
                                            >
                                            <p class="mt-1 text-xs text-zinc-500">JPG, PNG, GIF o WEBP (Máx. 2MB)</p>
                                            
                                            @if($profile_image_preview)
                                                <p class="mt-1 text-xs text-agro-700 font-semibold flex items-center gap-1">
                                                    <flux:icon icon="check" class="size-4" />
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
                                            </script>
                                            @endscript
                                            
                                            @error('profile_image')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Password Tab --}}
                @if($activeTab === 'password')
                    <div class="animate-fade-in">
                        @if (session()->has('password_message'))
                            <flux:callout variant="success" class="mb-6">
                                <flux:callout.text>{{ session('password_message') }}</flux:callout.text>
                            </flux:callout>
                        @endif

                        <form wire:submit="updatePassword" class="space-y-6">
                            <x-agro.form-section title="Cambiar Contraseña" color="green">
                                <div class="space-y-6">
                                    <div>
                                        <flux:label required>Contraseña Actual</flux:label>
                                        <div class="relative">
                                            <input 
                                                wire:model="current_password" 
                                                type="password" 
                                                id="current_password"
                                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                                placeholder="Tu contraseña actual"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20 @error('current_password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showCurrentPassword = !showCurrentPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
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
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <div>
                                        <flux:label required>Nueva Contraseña</flux:label>
                                        <div class="relative">
                                            <input 
                                                wire:model="password" 
                                                type="password" 
                                                id="password"
                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                placeholder="Nueva contraseña"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20 @error('password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showPassword = !showPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
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
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <div>
                                        <flux:label required>Confirmar Nueva Contraseña</flux:label>
                                        <div class="relative">
                                            <input 
                                                wire:model="password_confirmation" 
                                                type="password" 
                                                id="password_confirmation"
                                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                                placeholder="Confirma la nueva contraseña"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
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
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <flux:callout variant="info">
                                        <flux:callout.heading>Requisitos de contraseña:</flux:callout.heading>
                                        <flux:callout.text>
                                            <ul class="text-xs list-disc list-inside space-y-1">
                                                <li>Mínimo 8 caracteres</li>
                                                <li>Al menos una letra mayúscula</li>
                                                <li>Al menos una letra minúscula</li>
                                                <li>Al menos un número</li>
                                            </ul>
                                        </flux:callout.text>
                                    </flux:callout>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">Actualizar Contraseña</flux:button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Contact Tab --}}
                @if($activeTab === 'contact')
                    <div class="animate-fade-in">
                        @if (session()->has('contact_message'))
                            <flux:callout variant="success" class="mb-6">
                                <flux:callout.text>{{ session('contact_message') }}</flux:callout.text>
                            </flux:callout>
                        @endif

                        <form wire:submit="updateContactInfo" class="space-y-6">
                            <x-agro.form-section title="Información de Contacto" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <flux:field>
                                            <flux:label>Dirección</flux:label>
                                            <flux:input wire:model="address" type="text" id="address" placeholder="Calle, número, piso..." />
                                            <flux:error name="address" />
                                        </flux:field>
                                    </div>
                                    <flux:field>
                                        <flux:label>Ciudad</flux:label>
                                        <flux:input wire:model="city" type="text" id="city" placeholder="Tu ciudad" />
                                        <flux:error name="city" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Código Postal</flux:label>
                                        <flux:input wire:model="postal_code" type="text" id="postal_code" placeholder="12345" />
                                        <flux:error name="postal_code" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Provincia</flux:label>
                                        <flux:select wire:model="province_id" id="province_id">
                                            <option value="">Seleccionar provincia...</option>
                                            @foreach($this->provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="province_id" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>Teléfono</flux:label>
                                        <flux:input wire:model="phone" type="tel" id="phone" placeholder="+34 600 000 000" />
                                        <flux:error name="phone" />
                                    </flux:field>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">Guardar Contacto</flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </x-agro.card>
    </div>

    @script
    <script>
        // Escuchar cuando se actualiza la imagen de perfil
        $wire.on('profile-image-updated', (event) => {
            const previewImg = document.getElementById('profile-preview-img');
            const placeholder = document.getElementById('profile-preview-placeholder');
            const fileInput = document.getElementById('profile_image');
            
            if (previewImg && event.imageUrl) {
                // Actualizar la imagen con la nueva URL
                previewImg.src = event.imageUrl;
                previewImg.dataset.originalSrc = event.imageUrl;
                // Asegurar que la imagen esté visible
                previewImg.classList.remove('hidden');
                previewImg.style.display = 'block';
                previewImg.classList.remove('border-agro-500');
                previewImg.classList.add('border-zinc-200');
                
                // Asegurar que el placeholder esté oculto
                if (placeholder) {
                    placeholder.classList.add('hidden');
                    placeholder.style.display = 'none';
                }
            }
            
            // Limpiar el input de archivo
            if (fileInput) {
                fileInput.value = '';
            }
        });
        
        // Escuchar cuando se actualiza el perfil para refrescar el header
        // (solo se dispara si NO se actualizó la imagen)
        $wire.on('profile-updated', () => {
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    </script>
    @endscript
</div>
