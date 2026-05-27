<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="{{ __('Documentos de Campaña') }}"
        :description="__('Facturas, certificados, informes y autorizaciones del cuaderno de campo')"
        icon="document-text"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="arrow-up-tray" wire:click="openCreate">{{ __('Subir Documento') }}</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            :label="__('Total documentos')"
            :value="$stats['total']"
            icon="document-text"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Esta campaña')"
            :value="$stats['this_campaign']"
            icon="calendar-days"
            color="agro"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterType" class="w-44">
            <flux:select.option value="">{{ __('Todos los tipos') }}</flux:select.option>
            @foreach($documentTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterType)
            <flux:button wire:click="$set('filterCampaign', ''); $set('filterType', '')" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="document-text"
            title="{{ $filterCampaign || $filterType ? 'Ningún documento coincide con los filtros' : 'Sin documentos' }}"
            description="{{ $filterCampaign || $filterType ? 'Prueba a cambiar o limpiar los filtros.' : 'Sube facturas de insumos, certificados, informes de laboratorio y otras autorizaciones vinculadas al cuaderno de campo.' }}"
        >
            <x-slot:action>
                @if($filterCampaign || $filterType)
                    <flux:button wire:click="$set('filterCampaign', ''); $set('filterType', '')" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                @else
                    <flux:button variant="primary" icon="arrow-up-tray" wire:click="openCreate">{{ __('Subir Documento') }}</flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($entries as $entry)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="doc-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="document-text"
                            :title="$entry->name"
                            :subtitle="$entry->created_at->format('d/m/Y')"
                            iconBg="bg-blue-100"
                            iconColor="text-blue-600"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="blue" size="sm">{{ $entry->document_type_label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($entry->notes)
                            <p class="text-xs text-zinc-500 line-clamp-2">{{ $entry->notes }}</p>
                        @endif

                        @if($entry->original_filename)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="paper-clip" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $entry->original_filename }}</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-xs text-zinc-400">
                            <flux:icon icon="document" class="size-3.5 shrink-0" />
                            <span>{{ $entry->file_size_formatted }}</span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            @if($entry->file_path)
                                <x-agro.action-button
                                    icon="arrow-down-tray"
                                    variant="default"
                                    href="{{ roleRoute('viticulturist.campaign-documents.download', $entry) }}"
                                    title="{{ __('Descargar') }}"
                                />
                            @endif
                            <x-agro.action-button
                                variant="edit"
                                wire:click="openEdit({{ $entry->id }})"
                                title="{{ __('Editar') }}"
                            />
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $entry->id }})"
                                wire:confirm="{{ __('¿Eliminar este documento? Esta acción no se puede deshacer.') }}"
                                title="{{ __('Eliminar') }}"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$entries" />
    @endif

    {{-- Modal subida/edición de documento --}}
    <flux:modal wire:model="showModal" class="max-w-xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? __('Editar Documento') : __('Subir Documento') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>{{ __('Campaña') }}</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">{{ __('Seleccionar...') }}</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>
                <flux:field>
                    <flux:label required>{{ __('Tipo de documento') }}</flux:label>
                    <flux:select wire:model="document_type">
                        @foreach($documentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="document_type" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label required>{{ __('Nombre descriptivo') }}</flux:label>
                    <flux:input wire:model="name" type="text" :placeholder="__('Ej: Factura herbicidas primavera 2026')" />
                    <flux:error name="name" />
                </flux:field>

                @if(!$editingId)
                    <flux:field class="md:col-span-2">
                        <flux:label required>{{ __('Archivo') }}</flux:label>
                        <input
                            type="file"
                            wire:model="uploadedFile"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm text-zinc-700 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 focus:outline-none p-2"
                        />
                        <flux:description>{{ __('PDF, imágenes, Word o Excel. Máx. 20 MB.') }}</flux:description>
                        <flux:error name="uploadedFile" />
                        <div wire:loading wire:target="uploadedFile" class="text-sm text-blue-600 mt-1">Subiendo archivo...</div>
                    </flux:field>
                @endif

                <flux:field class="md:col-span-2">
                    <flux:label>{{ __('Notas') }}</flux:label>
                    <flux:textarea wire:model="notes" rows="2" :placeholder="__('Descripción adicional...')" />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancelar') }}</flux:button>
                <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? __('Actualizar') : __('Subir Documento') }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <flux:icon icon="arrow-path" class="w-4 h-4 animate-spin" />
                        Guardando...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
