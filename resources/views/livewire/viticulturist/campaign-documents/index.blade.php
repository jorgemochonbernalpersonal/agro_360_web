<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Documentos de Campaña"
        subtitle="Facturas, certificados, informes y autorizaciones del cuaderno de campo"
        icon="document-text"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="arrow-up-tray" wire:click="openCreate">Subir Documento</flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterCampaign" label="Campaña">
            <option value="">Todas las campañas</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </x-agro.filter-select>
        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos los tipos</option>
            @foreach($documentTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="document-text"
                title="Sin documentos"
                description="Sube facturas de insumos, certificados, informes de laboratorio y otras autorizaciones vinculadas al cuaderno de campo."
            />
        @else
            <x-agro.data-table :headers="['Nombre', 'Tipo', 'Archivo original', 'Tamaño', 'Fecha subida', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell class="font-medium">
                            {{ $entry->name }}
                            @if($entry->notes)
                                <span class="text-zinc-400 text-xs block">{{ Str::limit($entry->notes, 60) }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->document_type" :label="$entry->document_type_label" />
                        </x-agro.table-cell>
                        <x-agro.table-cell class="text-sm text-zinc-500">{{ $entry->original_filename ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell class="text-sm">{{ $entry->file_size_formatted }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->created_at->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                @if($entry->file_path)
                                    <flux:button size="sm" variant="ghost" icon="arrow-down-tray" href="{{ route('viticulturist.campaign-documents.download', $entry) }}">Descargar</flux:button>
                                @endif
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $entry->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $entry->id }})" wire:confirm="¿Eliminar este documento? Esta acción no se puede deshacer.">Eliminar</flux:button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-agro.card>

    <flux:modal wire:model="showModal" class="max-w-xl">
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ $editingId ? 'Editar Documento' : 'Subir Documento' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label required>Campaña</flux:label>
                    <flux:select wire:model="campaign_id">
                        <option value="">Seleccionar...</option>
                        @foreach($campaigns as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="campaign_id" />
                </flux:field>
                <flux:field>
                    <flux:label required>Tipo de documento</flux:label>
                    <flux:select wire:model="document_type">
                        @foreach($documentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="document_type" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label required>Nombre descriptivo</flux:label>
                    <flux:input wire:model="name" type="text" placeholder="Ej: Factura herbicidas primavera 2026" />
                    <flux:error name="name" />
                </flux:field>

                @if(!$editingId)
                    <flux:field class="md:col-span-2">
                        <flux:label required>Archivo</flux:label>
                        <input
                            type="file"
                            wire:model="uploadedFile"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                            class="block w-full text-sm text-zinc-700 border border-zinc-300 rounded-lg cursor-pointer bg-zinc-50 focus:outline-none p-2"
                        />
                        <flux:description>PDF, imágenes, Word o Excel. Máx. 20 MB.</flux:description>
                        <flux:error name="uploadedFile" />
                        <div wire:loading wire:target="uploadedFile" class="text-sm text-blue-600 mt-1">Subiendo archivo...</div>
                    </flux:field>
                @endif

                <flux:field class="md:col-span-2">
                    <flux:label>Notas</flux:label>
                    <flux:textarea wire:model="notes" rows="2" placeholder="Descripción adicional..." />
                    <flux:error name="notes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-200">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
                    {{ $editingId ? 'Actualizar' : 'Subir Documento' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
