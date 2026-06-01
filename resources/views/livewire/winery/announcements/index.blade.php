<div class="space-y-6 animate-fade-in">

    <x-agro.page-header title="{{ __('Avisos a Viticultores') }}" :description="__('Comunica novedades, instrucciones y alertas a tus viticultores')">
        <x-slot:actions>
            <flux:button wire:click="openCreate" variant="primary" icon="megaphone">{{ __('Nuevo aviso') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Lista de avisos --}}
    @if($announcements->count() > 0)
        <div class="space-y-3">
            @foreach($announcements as $a)
                <x-agro.card wire:key="announcement-{{ $a->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-{{ \App\Models\WineryAnnouncement::TYPE_COLORS[$a->type] }}-50 flex items-center justify-center shrink-0 mt-0.5">
                            <flux:icon :icon="\App\Models\WineryAnnouncement::TYPE_ICONS[$a->type]" class="size-5 text-{{ \App\Models\WineryAnnouncement::TYPE_COLORS[$a->type] }}-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-zinc-900 truncate">{{ $a->title }}</h3>
                                <flux:badge :color="\App\Models\WineryAnnouncement::TYPE_COLORS[$a->type]" size="sm">{{ $a->typeLabel() }}</flux:badge>
                                @if($a->target === 'specific')
                                    <flux:badge size="sm">{{ $a->viticulturists()->count() }} destinatarios</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Todos') }}</flux:badge>
                                @endif
                                @if($a->isExpired())
                                    <flux:badge color="red" size="sm">{{ __('Expirado') }}</flux:badge>
                                @endif
                            </div>
                            <p class="text-sm text-zinc-600 whitespace-pre-line">{{ $a->body }}</p>
                            <p class="text-xs text-zinc-400 mt-2">
                                Publicado {{ $a->published_at->diffForHumans() }}
                                @if($a->expires_at)
                                    · Expira {{ $a->expires_at->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @unless($a->isExpired())
                                <flux:button wire:click="expire({{ $a->id }})" wire:confirm="{{ __('¿Marcar este aviso como expirado?') }}" variant="ghost" size="sm" icon="clock">
                                    Expirar
                                </flux:button>
                            @endunless
                            <flux:button wire:click="delete({{ $a->id }})" wire:confirm="{{ __('¿Eliminar este aviso?') }}" variant="ghost" size="sm" icon="trash" class="text-red-500 hover:text-red-700" />
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>
        <x-agro.pagination :paginator="$announcements" />
    @else
        <x-agro.empty-state
            icon="megaphone"
            :message="__('Sin avisos publicados')"
            :description="__('Crea tu primer aviso para comunicarte con tus viticultores')"
        >
            <x-slot:action>
                <flux:button wire:click="openCreate" variant="primary" icon="plus">{{ __('Crear aviso') }}</flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @endif

    {{-- Modal crear aviso --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2 rounded-lg bg-agro-50">
                    <flux:icon icon="megaphone" class="size-5 text-agro-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">{{ __('Nuevo aviso') }}</h3>
                    <p class="text-xs text-zinc-500">{{ __('Se enviará por email y aparecerá en el panel del viticultor') }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <flux:label>{{ __('Tipo de aviso') }}</flux:label>
                    <flux:select wire:model.live="type">
                        @foreach($typeLabels as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div>
                    <flux:label>{{ __('Titulo') }}</flux:label>
                    <flux:input wire:model="title" :placeholder="__('Vendimia Tempranillo zona norte...')" />
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <flux:label>{{ __('Mensaje') }}</flux:label>
                    <flux:textarea wire:model="body" rows="4" :placeholder="__('Describe el aviso con detalle...')" />
                    @error('body') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <flux:label>{{ __('Destinatarios') }}</flux:label>
                    <flux:select wire:model.live="target">
                        <flux:select.option value="all">{{ __('Todos los viticultores activos') }}</flux:select.option>
                        <flux:select.option value="specific">{{ __('Seleccionar viticultores') }}</flux:select.option>
                    </flux:select>
                </div>

                @if($target === 'specific')
                    <div class="max-h-40 overflow-y-auto border border-zinc-200 rounded-lg p-2 space-y-1">
                        @forelse($viticulturists as $wv)
                            <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-zinc-50 cursor-pointer">
                                <input type="checkbox" wire:model="selectedViticulturists" value="{{ $wv->viticulturist_id }}"
                                    class="rounded border-zinc-300 text-agro-600 focus:ring-agro-500" />
                                <span class="text-sm text-zinc-700">{{ $wv->viticulturist->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-zinc-400 text-center py-2">{{ __('No hay viticultores activos') }}</p>
                        @endforelse
                    </div>
                    @error('selectedViticulturists') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @endif

                <div>
                    <flux:label>{{ __('Fecha de expiración (opcional)') }}</flux:label>
                    <flux:input wire:model="expires_at" type="date" />
                    @error('expires_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="$set('showCreateModal', false)" variant="ghost">{{ __('Cancelar') }}</flux:button>
                <flux:button wire:click="publish" variant="primary" icon="paper-airplane">{{ __('Publicar aviso') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>
