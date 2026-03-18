<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    title="Documentos de Bodega"
    description="Gestión de licencias, permisos, certificados y otros documentos oficiales."
    icon="folder-open"
>
    <x-slot:actions>
        <flux:button variant="primary" icon="plus" href="{{ roleRoute('documents.create') }}" wire:navigate>
            Nuevo documento
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.filter-bar>
        <x-agro.filter-input wire:model.live="search" placeholder="Buscar por título, referencia..." />
        <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.data-table :headers="['Título', 'Tipo', 'Referencia', 'Fecha emisión', 'Caducidad', 'Acciones']">
        @forelse($documents as $document)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $document->title }}</div>
                    @if($document->issuing_authority)
                        <div class="text-xs text-zinc-500">{{ $document->issuing_authority }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    <x-agro.status-badge :label="$document->type_label" color="blue" />
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $document->reference_number ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ $document->issue_date?->format('d/m/Y') ?? '—' }}
                </x-agro.table-cell>
                <x-agro.table-cell>
                    @if($document->expiry_date)
                        <span class="inline-flex items-center gap-1">
                            {{ $document->expiry_date->format('d/m/Y') }}
                            @if($document->isExpired())
                                <x-agro.status-badge label="Caducado" color="red" />
                            @elseif($document->isExpiringSoon())
                                <x-agro.status-badge label="Próximo" color="amber" />
                            @endif
                        </span>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            href="{{ roleRoute('documents.edit', $document) }}" wire:navigate />
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $document->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este documento?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="folder-open" title="Sin documentos registrados"
                description="Añade licencias, permisos, certificados y otros documentos oficiales de la bodega." />
        @endforelse
    </x-agro.data-table>

    {{ $documents->links() }}
</x-agro.card>
</div>
