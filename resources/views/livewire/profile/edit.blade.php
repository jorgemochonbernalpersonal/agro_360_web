<div x-data="{ showCurrentPassword: false, showPassword: false, showPasswordConfirmation: false }">
    <div class="space-y-6 animate-fade-in">
        {{-- Tabs Navigation --}}
        <x-agro.card :padding="false">
            <div class="border-b border-zinc-200">
                <nav class="flex space-x-4 px-6" aria-:label="__('Tabs')">
                    <button
                        wire:click="setActiveTab('personal')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'personal' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="user" class="size-5" />
                            {{ __('Información Personal') }}
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('password')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'password' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="lock-closed" class="size-5" />
                            {{ __('Cambiar Contraseña') }}
                        </div>
                    </button>
                    <button
                        wire:click="setActiveTab('contact')"
                        class="py-4 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'contact' ? 'border-agro-700 text-agro-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-400' }}"
                    >
                        <div class="flex items-center gap-2">
                            <flux:icon icon="envelope" class="size-5" />
                            {{ __('Contacto') }}
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
                            <x-agro.form-section :title="__('Información Personal')" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <flux:field>
                                        <flux:label required>{{ __('Nombre Completo') }}</flux:label>
                                        <flux:input wire:model="name" type="text" id="name" :placeholder="__('Tu nombre completo')" required />
                                        <flux:error name="name" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label required>{{ __('Email') }}</flux:label>
                                        <flux:input wire:model="email" type="email" id="email" placeholder="tu@email.com" required />
                                        <flux:error name="email" />
                                    </flux:field>
                                </div>

                                {{-- Imagen de Perfil --}}
                                <div class="mt-6">
                                    <flux:label>{{ __('Foto de Perfil') }}</flux:label>
                                    <div class="mt-2 flex items-center gap-6"
                                         x-data="{
                                             preview: @js($current_profile_image ? Storage::disk('public')->url($current_profile_image) : ''),
                                             handleFile(e) {
                                                 const file = e.target.files[0];
                                                 if (!file) return;
                                                 const reader = new FileReader();
                                                 reader.onload = (ev) => { this.preview = ev.target.result; };
                                                 reader.readAsDataURL(file);
                                             }
                                         }"
                                         x-on:profile-image-saved.window="preview = $event.detail.url"
                                    >
                                        {{-- Preview --}}
                                        <div class="flex-shrink-0 relative">
                                            <img x-show="preview" :src="preview" alt="Preview"
                                                 class="w-20 h-20 rounded-full object-cover border-4 border-agro-400 shadow-lg"
                                                 x-cloak>
                                            <div x-show="!preview"
                                                 class="w-20 h-20 rounded-full bg-gradient-to-br from-agro-500 to-agro-700 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>

                                            {{-- Badge nueva imagen --}}
                                            @if($profile_image_preview)
                                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-agro-500 rounded-full flex items-center justify-center z-10">
                                                    <flux:icon icon="check" class="size-4 text-white" />
                                                </div>
                                            @endif

                                            {{-- Indicador de carga --}}
                                            <div wire:loading wire:target="profile_image" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 rounded-full z-20">
                                                <flux:icon icon="arrow-path" class="animate-spin size-6 text-agro-600" />
                                            </div>
                                        </div>

                                        <div class="flex-1">
                                            <input
                                                type="file"
                                                wire:model="profile_image"
                                                id="profile_image"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                                x-on:change="handleFile($event)"
                                                class="block w-full text-sm text-zinc-500
                                                    file:mr-4 file:py-2 file:px-4
                                                    file:rounded-lg file:border-0
                                                    file:text-sm file:font-semibold
                                                    file:bg-green-50 file:text-agro-700
                                                    hover:file:bg-green-100
                                                    cursor-pointer
                                                    @error('profile_image') border-red-300 @enderror"
                                            >
                                            <p class="mt-1 text-xs text-zinc-500">{{ __('JPG, PNG, GIF o WEBP (Máx. 2MB)') }}</p>

                                            @if($profile_image_preview)
                                                <p class="mt-1 text-xs text-agro-700 font-semibold flex items-center gap-1">
                                                    <flux:icon icon="check" class="size-4" />
                                                    {{ __('Nueva imagen seleccionada. Click "Guardar Cambios" para confirmar.') }}
                                                </p>
                                            @endif

                                            @error('profile_image')
                                                <flux:error>{{ $message }}</flux:error>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">{{ __('Guardar Cambios') }}</flux:button>
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
                            <x-agro.form-section :title="__('Cambiar Contraseña')" color="green">
                                <div class="space-y-6">
                                    <div>
                                        <flux:label required>{{ __('Contraseña Actual') }}</flux:label>
                                        <div class="relative">
                                            <input
                                                wire:model="current_password"
                                                type="password"
                                                id="current_password"
                                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                                :placeholder="__('Tu contraseña actual')"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20 @error('current_password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showCurrentPassword = !showCurrentPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
                                                tabindex="-1"
                                            >
                                                <flux:icon x-show="!showCurrentPassword" icon="eye" class="size-5" />
                                                <flux:icon x-show="showCurrentPassword" icon="eye-slash" class="size-5" style="display: none;" />
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <div>
                                        <flux:label required>{{ __('Nueva Contraseña') }}</flux:label>
                                        <div class="relative">
                                            <input
                                                wire:model="password"
                                                type="password"
                                                id="password"
                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                :placeholder="__('Nueva contraseña')"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20 @error('password') border-red-400 bg-red-50 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showPassword = !showPassword"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
                                                tabindex="-1"
                                            >
                                                <flux:icon x-show="!showPassword" icon="eye" class="size-5" />
                                                <flux:icon x-show="showPassword" icon="eye-slash" class="size-5" style="display: none;" />
                                            </button>
                                        </div>
                                        @error('password')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <div>
                                        <flux:label required>{{ __('Confirmar Nueva Contraseña') }}</flux:label>
                                        <div class="relative">
                                            <input
                                                wire:model="password_confirmation"
                                                type="password"
                                                id="password_confirmation"
                                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                                :placeholder="__('Confirma la nueva contraseña')"
                                                class="w-full px-4 py-3 pr-12 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:border-agro-700 focus:ring-agro-700/20"
                                                required
                                            />
                                            <button
                                                type="button"
                                                x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-zinc-500 hover:text-zinc-700 focus:outline-none"
                                                tabindex="-1"
                                            >
                                                <flux:icon x-show="!showPasswordConfirmation" icon="eye" class="size-5" />
                                                <flux:icon x-show="showPasswordConfirmation" icon="eye-slash" class="size-5" style="display: none;" />
                                            </button>
                                        </div>
                                        @error('password_confirmation')
                                            <flux:error>{{ $message }}</flux:error>
                                        @enderror
                                    </div>

                                    <flux:callout variant="info">
                                        <flux:callout.heading>{{ __('Requisitos de contraseña:') }}</flux:callout.heading>
                                        <flux:callout.text>
                                            <ul class="text-xs list-disc list-inside space-y-1">
                                                <li>{{ __('Mínimo 8 caracteres') }}</li>
                                                <li>{{ __('Al menos una letra mayúscula') }}</li>
                                                <li>{{ __('Al menos una letra minúscula') }}</li>
                                                <li>{{ __('Al menos un número') }}</li>
                                            </ul>
                                        </flux:callout.text>
                                    </flux:callout>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">{{ __('Actualizar Contraseña') }}</flux:button>
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
                            <x-agro.form-section :title="__('Información de Contacto')" color="green">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <flux:field>
                                            <flux:label>{{ __('Dirección') }}</flux:label>
                                            <flux:input wire:model="address" type="text" id="address" :placeholder="__('Calle, número, piso...')" />
                                            <flux:error name="address" />
                                        </flux:field>
                                    </div>
                                    <flux:field>
                                        <flux:label>{{ __('Ciudad') }}</flux:label>
                                        <flux:input wire:model="city" type="text" id="city" :placeholder="__('Tu ciudad')" />
                                        <flux:error name="city" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Código Postal') }}</flux:label>
                                        <flux:input wire:model="postal_code" type="text" id="postal_code" placeholder="12345" />
                                        <flux:error name="postal_code" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Provincia') }}</flux:label>
                                        <flux:select wire:model="province_id" id="province_id">
                                            <option value="">{{ __('Seleccionar provincia...') }}</option>
                                            @foreach($this->provinces as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="province_id" />
                                    </flux:field>
                                    <flux:field>
                                        <flux:label>{{ __('Teléfono') }}</flux:label>
                                        <flux:input wire:model="phone" type="tel" id="phone" placeholder="+34 600 000 000" />
                                        <flux:error name="phone" />
                                    </flux:field>
                                </div>
                            </x-agro.form-section>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">{{ __('Guardar Contacto') }}</flux:button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </x-agro.card>
    </div>

    @script
    <script>
        // Cuando el servidor confirma la imagen guardada, actualizar Alpine preview
        $wire.on('profile-image-updated', (event) => {
            const fileInput = document.getElementById('profile_image');

            if (event.imageUrl) {
                window.dispatchEvent(new CustomEvent('profile-image-saved', {
                    detail: { url: event.imageUrl }
                }));
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
