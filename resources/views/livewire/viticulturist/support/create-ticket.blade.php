<x-agro.form-card
    title="Nuevo Ticket de Soporte"
    description="Cuéntanos qué necesitas y te ayudaremos lo antes posible"
    :back-url="route('viticulturist.support.index')"
>
    <form wire:submit="save" class="space-y-8">
        <x-agro.form-section title="Tipo y Prioridad">
            {{-- Tipo de Ticket --}}
            <div>
                <flux:label class="mb-3">Tipo de Ticket *</flux:label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="bug" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-2xl mb-1">Bug</div>
                            <div class="text-sm font-medium">Bug</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="feature" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-2xl mb-1">Nueva</div>
                            <div class="text-sm font-medium">Nueva Funcionalidad</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="improvement" class="sr-only peer">
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-2xl mb-1">Mejora</div>
                            <div class="text-sm font-medium">Mejora</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="type" value="question" class="sr-only peer" checked>
                        <div class="p-4 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-2xl mb-1">Pregunta</div>
                            <div class="text-sm font-medium">Pregunta</div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Prioridad --}}
            <div class="mt-6">
                <flux:label class="mb-3">Prioridad *</flux:label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="low" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-sm font-medium">Baja</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="medium" class="sr-only peer" checked>
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-sm font-medium">Media</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="high" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-sm font-medium">Alta</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="priority" value="urgent" class="sr-only peer">
                        <div class="p-3 text-center border-2 rounded-lg peer-checked:border-agro-500 peer-checked:bg-agro-50 hover:border-zinc-400 transition">
                            <div class="text-sm font-medium">Urgente</div>
                        </div>
                    </label>
                </div>
                @error('priority')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>
        </x-agro.form-section>

        <x-agro.form-section title="Información del Ticket">
            {{-- Título --}}
            <flux:field>
                <flux:label for="title">Título *</flux:label>
                <flux:input
                    wire:model="title"
                    id="title"
                    placeholder="Resume tu consulta en pocas palabras"
                    required
                />
                <flux:error name="title" />
            </flux:field>

            {{-- Descripción --}}
            <flux:field class="mt-6">
                <flux:label for="description">Descripción *</flux:label>
                <flux:textarea
                    wire:model="description"
                    id="description"
                    rows="6"
                    placeholder="Describe detalladamente tu problema, sugerencia o pregunta..."
                    required
                />
                <flux:error name="description" />
                <p class="text-xs text-zinc-500 mt-1">
                    Cuanto más detalles proporciones, mejor podremos ayudarte.
                </p>
            </flux:field>
        </x-agro.form-section>

        <x-agro.form-section title="Archivos Adjuntos">
            <div x-data="{ previews: [], addFiles(files) {
                    const remaining = 5 - this.previews.length;
                    const toAdd = Array.from(files).slice(0, remaining);
                    toAdd.forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => this.previews.push({ src: e.target.result, name: file.name });
                        reader.readAsDataURL(file);
                    });
                }, removePreview(index) {
                    this.previews.splice(index, 1);
                }
            }">
                <flux:label>Imágenes (Opcional, máx. 5)</flux:label>
                <input
                    type="file"
                    wire:model="images"
                    id="images"
                    accept="image/*"
                    multiple
                    x-on:change="addFiles($event.target.files); $event.target.value = '';"
                    class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-agro-50 file:text-agro-700 hover:file:bg-agro-500 hover:file:text-white transition-colors"
                >
                @error('images') <flux:error>{{ $message }}</flux:error> @enderror
                @error('images.*') <flux:error>{{ $message }}</flux:error> @enderror

                {{-- Galería de previews --}}
                <div wire:ignore class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2" x-show="previews.length > 0">
                    <template x-for="(preview, index) in previews" :key="index">
                        <div class="relative group">
                            <img :src="preview.src" :alt="preview.name" class="w-full h-20 object-cover rounded-lg border border-zinc-300">
                            <button
                                type="button"
                                x-on:click="removePreview(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                            >&times;</button>
                        </div>
                    </template>
                </div>

                <p class="text-xs text-zinc-500 mt-2">
                    Adjunta hasta 5 imágenes (máx. 5MB cada una) para ayudarnos a entender tu consulta.
                </p>
            </div>
        </x-agro.form-section>

        {{-- Información Adicional --}}
        <flux:callout variant="info">
            <flux:callout.heading>Información</flux:callout.heading>
            <flux:callout.text>
                <ul class="text-sm space-y-1">
                    <li>Responderemos tu ticket lo antes posible.</li>
                    <li>Recibirás notificaciones por email cuando haya actualizaciones.</li>
                    <li>Puedes seguir el progreso desde la sección de Soporte.</li>
                </ul>
            </flux:callout.text>
        </flux:callout>

        <x-agro.form-actions
            :cancel-url="route('viticulturist.support.index')"
            submit-label="Enviar Ticket"
        />
    </form>
</x-agro.form-card>
