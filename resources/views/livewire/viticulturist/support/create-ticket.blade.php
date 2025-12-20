<div>
    <x-page-header 
        title="Nuevo Ticket de Soporte"
        subtitle="Cuéntanos qué necesitas"
    >
        <x-slot name="actionButton">
            <x-button href="{{ route('viticulturist.support.index') }}" variant="secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
        </x-slot>
    </x-page-header>

    <div class="max-w-3xl mx-auto mt-6">
        <form wire:submit.prevent="save" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
            {{-- Tipo de Ticket --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Tipo de Ticket *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="bug" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">🐛</div>
                            <div class="text-sm font-medium">Bug</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="feature" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">✨</div>
                            <div class="text-sm font-medium">Nueva Funcionalidad</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="improvement" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">🚀</div>
                            <div class="text-sm font-medium">Mejora</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="question" class="sr-only peer" checked>
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-2xl mb-1">❓</div>
                            <div class="text-sm font-medium">Pregunta</div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <div class="mt-2 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            {{-- Prioridad --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Prioridad *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="low" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-sm font-medium">⚪ Baja</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="medium" class="sr-only peer" checked>
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🟡 Media</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="high" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🟠 Alta</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="urgent" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition">
                            <div class="text-sm font-medium">🔴 Urgente</div>
                        </div>
                    </label>
                </div>
                @error('priority')
                    <div class="mt-2 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            {{-- Título --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Título *
                </label>
                <input 
                    type="text" 
                    wire:model="title" 
                    id="title" 
                    class="form-input w-full"
                    placeholder="Resume tu consulta en pocas palabras"
                    required
                >
                @error('title')
                    <div class="mt-2 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción *
                </label>
                <textarea 
                    wire:model="description" 
                    id="description" 
                    rows="6" 
                    class="form-textarea w-full"
                    placeholder="Describe detalladamente tu problema, sugerencia o pregunta..."
                    required
                ></textarea>
                @error('description')
                    <div class="mt-2 flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    </div>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    💡 Cuanto más detalles proporciones, mejor podremos ayudarte.
                </p>
            </div>

            {{-- Imagen (Opcional) --}}
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                    Imagen (Opcional)
                </label>
                <div class="mt-1">
                    <input 
                        type="file" 
                        wire:model="image" 
                        id="image"
                        accept="image/*"
                        class="form-input w-full"
                    >
                    @error('image')
                        <div class="mt-2 flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        </div>
                    @enderror
                    @if($image_preview)
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 mb-2">Vista previa:</p>
                            <img src="{{ $image_preview }}" alt="Vista previa" class="max-w-full h-auto max-h-64 rounded-lg border border-gray-300">
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    📷 Puedes adjuntar una imagen para ayudarnos a entender mejor tu consulta (máx. 5MB).
                </p>
            </div>

            {{-- Acciones --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <x-button href="{{ route('viticulturist.support.index') }}" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary">
                    Enviar Ticket
                </x-button>
            </div>
        </form>

        {{-- Información Adicional --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <h3 class="font-semibold text-blue-900 mb-2">ℹ️ Información</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Responderemos tu ticket lo antes posible.</li>
                <li>• Recibirás notificaciones por email cuando haya actualizaciones.</li>
                <li>• Puedes seguir el progreso desde la sección de Soporte.</li>
            </ul>
        </div>
    </div>
</div>
