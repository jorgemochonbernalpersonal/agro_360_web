<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Respuestas Rápidas') }}"
        :description="__('Plantillas de respuesta predefinidas para tickets de soporte')"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreate" variant="primary" icon="plus">{{ __('Nueva Respuesta') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @if($responses->isEmpty())
        <x-agro.empty-state
            icon="chat-bubble-left-right"
            :message="__('Sin respuestas rápidas')"
            :description="__('Crea plantillas para responder tickets comunes con un click desde el panel de soporte')"
        />
    @else
        @foreach($categories->push(null) as $cat)
            @php
                $filtered = $cat ? $responses->where('category', $cat) : $responses->whereNull('category');
            @endphp
            @if($filtered->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">
                        {{ $cat ?? 'Sin categoría' }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($filtered as $r)
                            <x-agro.card>
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-zinc-900">{{ $r->title }}</p>
                                        <p class="text-xs text-zinc-500 mt-1 line-clamp-2">{{ $r->body }}</p>
                                        <p class="text-xs text-zinc-300 mt-1">Orden: {{ $r->sort_order }} · {{ $r->admin?->name ?? 'Admin' }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <flux:button wire:click="openEdit({{ $r->id }})" variant="ghost" size="sm" icon="pencil" />
                                        <flux:button
                                            wire:click="delete({{ $r->id }})"
                                            wire:confirm="{{ __('¿Eliminar esta respuesta rápida?') }}"
                                            variant="ghost" size="sm" icon="trash"
                                            class="text-red-400 hover:text-red-600"
                                        />
                                    </div>
                                </div>
                            </x-agro.card>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- Modal --}}
    @if($showModal)
        <flux:modal name="cr-modal" :show="true" wire:close="closeModal" class="max-w-lg">
            <div class="p-6 space-y-4">
                <h3 class="text-base font-semibold text-zinc-900">{{ $editingId ? 'Editar respuesta' : 'Nueva respuesta' }}</h3>

                <flux:input wire:model="title" :label="__('Título')" :placeholder="__('Ej: Problema de acceso resuelto')" />
                @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                <flux:textarea wire:model="body" :label="__('Texto de la respuesta')" rows="5" :placeholder="__('Cuerpo del mensaje que se insertará en el comentario...')" />
                @error('body') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="category" :label="__('Categoría')" :placeholder="__('Ej: Acceso, Parcelas...')" />
                    <flux:input wire:model="sort_order" type="number" :label="__('Orden')" min="0" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button wire:click="closeModal" variant="ghost">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="save" variant="primary">{{ $editingId ? 'Guardar' : 'Crear' }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
