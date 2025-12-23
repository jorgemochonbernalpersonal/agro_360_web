@php
    $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
@endphp

<x-form-card
    title="Nuevo Ticket de Soporte"
    description="Cuéntanos qué necesitas y te ayudaremos lo antes posible"
    :icon="$icon"
    icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    :back-url="route('viticulturist.support.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-form-section title="Tipo y Prioridad" color="green">
            {{-- Tipo de Ticket --}}
            <div>
                <x-label class="mb-3">Tipo de Ticket *</x-label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="bug" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">🐛</div>
                            <div class="text-sm font-medium">Bug</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="feature" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">✨</div>
                            <div class="text-sm font-medium">Nueva Funcionalidad</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="improvement" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">🚀</div>
                            <div class="text-sm font-medium">Mejora</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="question" class="sr-only peer" checked>
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">❓</div>
                            <div class="text-sm font-medium">Pregunta</div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Prioridad --}}
            <div class="mt-6">
                <x-label class="mb-3">Prioridad *</x-label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="low" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-sm font-medium">⚪ Baja</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="medium" class="sr-only peer" checked>
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🟡 Media</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="high" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🟠 Alta</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="urgent" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-[var(--color-agro-green)] peer-checked:bg-[var(--color-agro-green-bg)] hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🔴 Urgente</div>
                        </div>
                    </label>
                </div>
                @error('priority')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
            </div>
        </x-form-section>

        <x-form-section title="Información del Ticket" color="green">
            {{-- Título --}}
            <div>
                <x-label for="title" required>Título</x-label>
                <x-input 
                    wire:model="title" 
                    id="title"
                    placeholder="Resume tu consulta en pocas palabras"
                    :error="$errors->first('title')"
                    required
                />
            </div>

            {{-- Descripción --}}
            <div class="mt-6">
                <x-label for="description" required>Descripción</x-label>
                <x-textarea 
                    wire:model="description" 
                    id="description"
                    rows="6"
                    placeholder="Describe detalladamente tu problema, sugerencia o pregunta..."
                    :error="$errors->first('description')"
                    required
                />
                <p class="text-xs text-gray-500 mt-1">
                    💡 Cuanto más detalles proporciones, mejor podremos ayudarte.
                </p>
            </div>
        </x-form-section>

        <x-form-section title="Archivos Adjuntos" color="green" class="pb-6">
            {{-- Imagen (Opcional) --}}
            <div>
                <x-label for="image">Imagen (Opcional)</x-label>
                <input 
                    type="file" 
                    wire:model="image" 
                    id="image"
                    accept="image/*"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const previewImg = document.getElementById('support-image-preview');
                                const previewContainer = document.getElementById('support-image-preview-container');
                                if (previewImg) {
                                    previewImg.src = e.target.result;
                                    previewImg.classList.remove('hidden');
                                }
                                if (previewContainer) {
                                    previewContainer.classList.remove('hidden');
                                }
                            };
                            reader.readAsDataURL(file);
                        }
                    "
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[var(--color-agro-green-bg)] file:text-[var(--color-agro-green-dark)] hover:file:bg-[var(--color-agro-green)] hover:file:text-white transition-colors"
                >
                @error('image')
                    <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
                @enderror
                <div id="support-image-preview-container" class="mt-3 {{ $image_preview ? '' : 'hidden' }}">
                    <p class="text-sm text-gray-600 mb-2">Vista previa:</p>
                    <img 
                        id="support-image-preview" 
                        src="{{ $image_preview ? $image_preview : '' }}" 
                        alt="Vista previa" 
                        class="max-w-full h-auto max-h-64 rounded-lg border border-gray-300 {{ $image_preview ? '' : 'hidden' }}"
                        onerror="this.style.display='none'; document.getElementById('support-image-preview-container').classList.add('hidden');"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    📷 Puedes adjuntar una imagen para ayudarnos a entender mejor tu consulta (máx. 5MB).
                </p>
            </div>
        </x-form-section>

        {{-- Información Adicional --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Información</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Responderemos tu ticket lo antes posible.</li>
                        <li>• Recibirás notificaciones por email cuando haya actualizaciones.</li>
                        <li>• Puedes seguir el progreso desde la sección de Soporte.</li>
                    </ul>
                </div>
            </div>
        </div>

        <x-form-actions 
            :cancel-url="route('viticulturist.support.index')"
            submit-label="Enviar Ticket"
        />
    </form>
</x-form-card>
