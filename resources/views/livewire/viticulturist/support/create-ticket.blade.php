<x-agro.form-card
    title="Nuevo Ticket de Soporte"
    description="Cuéntanos qué necesitas y te ayudaremos lo antes posible"
    :back-url="roleRoute('viticulturist.support.index')"
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
            <div x-data="{
                previews: [],
                dragging: false,
                lightbox: null,
                get canAdd() { return this.previews.length < 5; },
                addFiles(files) {
                    const toAdd = Array.from(files).slice(0, 5 - this.previews.length);
                    toAdd.forEach(file => {
                        if (!file.type.startsWith('image/')) return;
                        const reader = new FileReader();
                        reader.onload = (e) => this.previews.push({ src: e.target.result, name: file.name, size: (file.size / 1024 / 1024).toFixed(1) });
                        reader.readAsDataURL(file);
                    });
                },
                removePreview(index) { this.previews.splice(index, 1); },
                onDrop(e) {
                    this.dragging = false;
                    if (this.canAdd) this.addFiles(e.dataTransfer.files);
                },
                gridClass() {
                    const n = this.previews.length;
                    if (n === 1) return 'grid-cols-1';
                    if (n === 2) return 'grid-cols-2';
                    return 'grid-cols-2';
                },
                itemClass(index) {
                    const n = this.previews.length;
                    if (n === 1) return 'col-span-1 h-64';
                    if (n === 2) return 'col-span-1 h-52';
                    if (n === 3) return index === 0 ? 'col-span-2 h-52' : 'col-span-1 h-40';
                    if (n === 4) return 'col-span-1 h-40';
                    if (n === 5) return index === 0 ? 'col-span-2 h-52' : 'col-span-1 h-36';
                    return 'col-span-1 h-40';
                }
            }"
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave.prevent="dragging = false"
                x-on:drop.prevent="onDrop($event)"
            >
                {{-- Drop zone / selector --}}
                <label
                    for="images"
                    :class="dragging ? 'border-agro-500 bg-agro-50' : 'border-zinc-300 bg-zinc-50 hover:border-agro-400 hover:bg-agro-50'"
                    class="relative flex flex-col items-center justify-center gap-2 w-full rounded-xl border-2 border-dashed p-6 cursor-pointer transition-colors"
                    x-show="canAdd"
                >
                    <flux:icon icon="photo" class="size-8 text-zinc-400" />
                    <div class="text-center">
                        <span class="text-sm font-medium text-zinc-700">Arrastra imágenes aquí o <span class="text-agro-600 underline">selecciona</span></span>
                        <p class="text-xs text-zinc-400 mt-0.5">PNG, JPG, WEBP · máx. 5MB por imagen · <span x-text="5 - previews.length"></span> restantes</p>
                    </div>
                    <input
                        type="file"
                        wire:model="images"
                        id="images"
                        accept="image/*"
                        multiple
                        class="sr-only"
                        x-on:change="addFiles($event.target.files); $event.target.value = '';"
                    >
                </label>

                <p x-show="!canAdd" class="text-xs text-zinc-500 mt-1">Límite de 5 imágenes alcanzado. Elimina alguna para añadir más.</p>

                @error('images') <flux:error>{{ $message }}</flux:error> @enderror
                @error('images.*') <flux:error>{{ $message }}</flux:error> @enderror

                {{-- Galería adaptativa --}}
                <div wire:ignore x-show="previews.length > 0" class="mt-3">
                    <div :class="'grid gap-2 ' + gridClass()">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div
                                :class="itemClass(index)"
                                class="relative group overflow-hidden rounded-xl border border-zinc-200 cursor-zoom-in"
                                x-on:click="lightbox = preview.src"
                            >
                                <img :src="preview.src" :alt="preview.name" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105">
                                {{-- Overlay info --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-200 flex items-end">
                                    <span class="px-2 py-1 text-xs text-white truncate max-w-full opacity-0 group-hover:opacity-100 transition-opacity" x-text="preview.name + ' · ' + preview.size + 'MB'"></span>
                                </div>
                                {{-- Botón eliminar --}}
                                <button
                                    type="button"
                                    x-on:click.stop="removePreview(index)"
                                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="Eliminar"
                                >
                                    <flux:icon icon="x-mark" class="size-3.5" />
                                </button>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-zinc-400 mt-2">Haz clic en una imagen para ampliarla · Pasa el cursor para eliminarla</p>
                </div>

                {{-- Lightbox --}}
                <div
                    wire:ignore
                    x-show="lightbox"
                    x-on:click="lightbox = null"
                    x-on:keydown.escape.window="lightbox = null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
                    style="display:none"
                >
                    <img :src="lightbox" class="max-w-full max-h-full rounded-xl shadow-2xl object-contain" x-on:click.stop>
                    <button type="button" x-on:click="lightbox = null" class="absolute top-4 right-4 text-white bg-black/50 hover:bg-black/70 rounded-full w-9 h-9 flex items-center justify-center">
                        <flux:icon icon="x-mark" class="size-5" />
                    </button>
                </div>
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
            :cancel-url="roleRoute('viticulturist.support.index')"
            submit-label="Enviar Ticket"
        />
    </form>
</x-agro.form-card>
