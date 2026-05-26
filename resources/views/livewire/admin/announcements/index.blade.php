<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="{{ __('Anuncios del Sistema') }}"
        :description="__('Banners visibles para todos los usuarios autenticados')"
    >
        <x-slot:actions>
            @if($activeCount > 0)
                <flux:badge color="agro">{{ $activeCount }} activo{{ $activeCount > 1 ? 's' : '' }}</flux:badge>
            @endif
            <flux:button wire:click="openCreate" variant="primary" icon="plus">{{ __('Nuevo Anuncio') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-center gap-2">
        <flux:icon icon="megaphone" class="size-4 flex-shrink-0" />
        <span>{{ __('Los anuncios activos aparecen como banner en el panel de todos los usuarios (excluye admins). Se ocultan automáticamente al expirar.') }}</span>
    </div>

    @if($announcements->isEmpty())
        <x-agro.empty-state icon="megaphone" :message="__('Sin anuncios')" :description="__('Crea el primer anuncio para comunicarte con tus usuarios')" />
    @else
        <div class="space-y-3">
            @foreach($announcements as $ann)
                @php
                    $expired = $ann->expires_at && $ann->expires_at->isPast();
                    $colorMap = ['info' => 'blue', 'warning' => 'yellow', 'success' => 'green', 'danger' => 'red'];
                    $color = $colorMap[$ann->type] ?? 'blue';
                @endphp
                <x-agro.card>
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 rounded-lg bg-{{ $color }}-50 flex items-center justify-center flex-shrink-0">
                            <flux:icon icon="{{ $ann->typeIcon() }}" class="size-4 text-{{ $color }}-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-zinc-900">{{ $ann->title }}</span>
                                @if($expired)
                                    <flux:badge color="zinc" size="sm">{{ __('Expirado') }}</flux:badge>
                                @elseif($ann->is_active)
                                    <flux:badge color="green" size="sm">{{ __('Activo') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Inactivo') }}</flux:badge>
                                @endif
                                <flux:badge color="{{ $color }}" size="sm">{{ ucfirst($ann->type) }}</flux:badge>
                            </div>
                            <p class="text-sm text-zinc-600 mt-1">{{ $ann->message }}</p>
                            <p class="text-xs text-zinc-400 mt-1">
                                Por {{ $ann->admin?->name ?? 'Admin' }} · {{ $ann->created_at->format('d/m/Y H:i') }}
                                @if($ann->expires_at) · Expira {{ $ann->expires_at->format('d/m/Y') }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <flux:button wire:click="toggleActive({{ $ann->id }})" variant="ghost" size="sm"
                                icon="{{ $ann->is_active ? 'eye-slash' : 'eye' }}"
                                tooltip="{{ $ann->is_active ? 'Desactivar' : 'Activar' }}"
                            />
                            <flux:button wire:click="openEdit({{ $ann->id }})" variant="ghost" size="sm" icon="pencil" tooltip="Editar" />
                            <flux:button
                                wire:click="delete({{ $ann->id }})"
                                wire:confirm="{{ __('¿Eliminar este anuncio?') }}"
                                variant="ghost" size="sm" icon="trash"
                                class="text-red-400 hover:text-red-600"
                                tooltip="Eliminar"
                            />
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
        <flux:modal name="ann-modal" :show="true" wire:close="closeModal" class="max-w-lg">
            <div class="p-6 space-y-4">
                <h3 class="text-base font-semibold text-zinc-900">{{ $editingId ? 'Editar anuncio' : 'Nuevo anuncio' }}</h3>

                <flux:input wire:model="title" :label="__('Título')" :placeholder="__('Breve título del anuncio')" />
                @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                <flux:textarea wire:model="message" :label="__('Mensaje')" rows="3" :placeholder="__('Texto visible para los usuarios...')" />
                @error('message') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:select wire:model="type" :label="__('Tipo')">
                            <option value="info">{{ __('ℹ️ Info') }}</option>
                            <option value="warning">{{ __('⚠️ Aviso') }}</option>
                            <option value="success">{{ __('✅ Éxito') }}</option>
                            <option value="danger">{{ __('🚨 Urgente') }}</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:input wire:model="expires_at" type="date" :label="__('Expira el')" />
                        @error('expires_at') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model="is_active" id="ann-active" />
                    <label for="ann-active" class="text-sm text-zinc-700 cursor-pointer">{{ __('Publicar inmediatamente') }}</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button wire:click="closeModal" variant="ghost">{{ __('Cancelar') }}</flux:button>
                    <flux:button wire:click="save" variant="primary">
                        {{ $editingId ? 'Guardar' : 'Publicar' }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
