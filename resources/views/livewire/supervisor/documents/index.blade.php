<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Documentos DO') }}"
        :description="__('Gestiona los pliegos de condiciones y reglamentos publicados para tus viticultores.')"
    >
        <x-slot:actions>
            <flux:button wire:click="openCreate" variant="primary" icon="plus">{{ __('Nuevo documento') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Status tabs --}}
    <div class="flex items-center gap-1 bg-zinc-100 rounded-xl p-1 w-fit">
        @foreach([
            'active'   => ['label' => 'Vigentes',  'color' => 'text-emerald-700'],
            'draft'    => ['label' => 'Borradores', 'color' => 'text-amber-700'],
            'archived' => ['label' => 'Archivados', 'color' => 'text-zinc-500'],
        ] as $tab => $meta)
            <button
                wire:click="setTab('{{ $tab }}')"
                @class([
                    'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
                    'bg-white shadow text-zinc-900' => $activeTab === $tab,
                    'text-zinc-500 hover:text-zinc-700' => $activeTab !== $tab,
                ])
            >
                {{ $meta['label'] }}
                <span @class([
                    'inline-flex items-center justify-center min-w-[1.25rem] h-5 rounded-full text-[10px] font-bold px-1',
                    'bg-zinc-200 text-zinc-600' => $activeTab !== $tab,
                    'bg-agro-100 text-agro-700' => $activeTab === $tab,
                ])>{{ $counts[$tab] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Document list --}}
    @if($documents->count() > 0)
        <div class="space-y-3">
            @foreach($documents as $doc)
                @php
                    $typeLabel = $doc->type === 'pliego' ? 'Pliego de condiciones' : 'Reglamento';
                    $typeIcon  = $doc->type === 'pliego' ? 'document-text' : 'scale';
                    $typeBg    = $doc->type === 'pliego' ? 'bg-indigo-100 text-indigo-600' : 'bg-violet-100 text-violet-600';
                @endphp
                <x-agro.card wire:key="doc-{{ $doc->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl {{ $typeBg }} flex items-center justify-center shrink-0">
                            <flux:icon icon="{{ $typeIcon }}" class="size-5" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                <h3 class="text-sm font-semibold text-zinc-900">{{ $doc->title }}</h3>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-500 font-medium">
                                    {{ $typeLabel }}
                                </span>
                                @if($doc->version)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 font-medium">
                                        v{{ $doc->version }}
                                    </span>
                                @endif
                                @if($doc->status === 'active')
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium border border-emerald-200">{{ __('Vigente') }}</span>
                                @elseif($doc->status === 'draft')
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium border border-amber-200">{{ __('Borrador') }}</span>
                                @else
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-zinc-100 text-zinc-500 font-medium border border-zinc-200">{{ __('Archivado') }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-zinc-400">
                                @if($doc->effective_date)
                                    <span>Vigente desde {{ $doc->effective_date->format('d/m/Y') }}</span>
                                @endif
                                <span>Actualizado {{ $doc->updated_at->diffForHumans() }}</span>
                            </div>
                            @if($doc->content)
                                <p class="text-xs text-zinc-500 mt-2 line-clamp-2">{{ $doc->content }}</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1 shrink-0">
                            @if($doc->status === 'draft')
                                <flux:button wire:click="openEdit({{ $doc->id }})" variant="ghost" size="sm" icon="pencil" title="{{ __('Editar') }}" />
                                <flux:button
                                    wire:click="publish({{ $doc->id }})"
                                    wire:confirm="{{ __('¿Publicar \':title\'? El documento anterior del mismo tipo quedará archivado.', ['title' => $doc->title]) }}"
                                    variant="ghost" size="sm" icon="arrow-up-tray"
                                    title="{{ __('Publicar') }}"
                                />
                                <flux:button
                                    wire:click="delete({{ $doc->id }})"
                                    wire:confirm="{{ __('¿Eliminar \':title\'? Esta acción no se puede deshacer.', ['title' => $doc->title]) }}"
                                    variant="ghost" size="sm" icon="trash"
                                    class="text-red-400 hover:text-red-600"
                                    title="{{ __('Eliminar') }}"
                                />
                            @elseif($doc->status === 'active')
                                <flux:button
                                    wire:click="archive({{ $doc->id }})"
                                    wire:confirm="{{ __('¿Archivar \':title\'? Dejará de ser visible para los viticultores.', ['title' => $doc->title]) }}"
                                    variant="ghost" size="sm" icon="archive-box"
                                    title="{{ __('Archivar') }}"
                                />
                            @endif
                        </div>
                    </div>
                </x-agro.card>
            @endforeach
        </div>
    @else
        @if($activeTab === 'draft')
            <x-agro.empty-state icon="document-text" title="{{ __('Sin borradores') }}" :description="__('Crea un nuevo documento para empezar.')" />
        @elseif($activeTab === 'active')
            <x-agro.empty-state icon="document-text" title="{{ __('Sin documentos vigentes') }}" :description="__('Publica un borrador para que sea visible por tus viticultores.')" />
        @else
            <x-agro.empty-state icon="archive-box" title="{{ __('Sin documentos archivados') }}" :description="__('Los documentos archivados aparecerán aquí.')" />
        @endif
    @endif

    {{-- Modal: create / edit --}}
    <flux:modal wire:model="showModal" class="w-full max-w-2xl">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-indigo-50">
                    <flux:icon icon="document-text" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">
                        {{ $editingId ? 'Editar documento' : 'Nuevo documento' }}
                    </h3>
                    <p class="text-xs text-zinc-500">{{ __('Se guardará como borrador. Publícalo cuando esté listo.') }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Tipo') }} <span class="text-red-400">*</span></flux:label>
                        <flux:select wire:model="formType">
                            <flux:select.option value="pliego">{{ __('Pliego de condiciones') }}</flux:select.option>
                            <flux:select.option value="reglamento">{{ __('Reglamento') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="formType" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Versión') }} <span class="text-zinc-400 text-xs font-normal">{{ __('(opcional)') }}</span></flux:label>
                        <flux:input wire:model="formVersion" :placeholder="__('Ej: 2024-v3')" />
                        <flux:error name="formVersion" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Título') }} <span class="text-red-400">*</span></flux:label>
                    <flux:input wire:model="formTitle" :placeholder="__('Pliego de condiciones DO Rioja 2024')" autofocus />
                    <flux:error name="formTitle" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Fecha de entrada en vigor') }} <span class="text-zinc-400 text-xs font-normal">{{ __('(opcional)') }}</span></flux:label>
                    <flux:input wire:model="formDate" type="date" />
                    <flux:error name="formDate" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Contenido') }} <span class="text-zinc-400 text-xs font-normal">{{ __('(opcional)') }}</span></flux:label>
                    <flux:textarea wire:model="formContent" rows="6" :placeholder="__('Texto del pliego o reglamento...')" />
                    <flux:description class="text-xs text-zinc-400">
                        Puedes incluir el texto completo o un resumen. Los viticultores lo verán en su panel.
                    </flux:description>
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                <flux:button variant="ghost" wire:click="closeModal">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Guardar cambios' : 'Crear borrador' }}</span>
                    <span wire:loading wire:target="save">{{ __('Guardando...') }}</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
