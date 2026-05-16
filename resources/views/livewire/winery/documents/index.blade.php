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

<x-agro.filter-bar>
    <x-agro.filter-input wire:model.live="search" placeholder="Buscar por título, referencia..." />
    <x-agro.filter-select wire:model.live="typeFilter" placeholder="Todos los tipos">
        @foreach($types as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
    </x-agro.filter-select>
</x-agro.filter-bar>

<x-agro.loading-grid target="search, typeFilter, nextPage, previousPage" />

<div wire:loading.remove wire:target="search, typeFilter, nextPage, previousPage">
    @if($documents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($documents as $document)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="document-{{ $document->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="folder-open"
                            :title="$document->title"
                            :subtitle="$document->issuing_authority ?? '—'"
                            iconBg="bg-blue-100"
                            iconColor="text-blue-600"
                            size="md"
                            radius="xl"
                        >
                            <x-agro.status-badge :label="$document->type_label" color="blue" />
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Referencia</span>
                                <span class="text-zinc-700 font-medium">{{ $document->reference_number ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Emisión</span>
                                <span class="text-zinc-700 font-medium">{{ $document->issue_date?->format('d/m/Y') ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">Caducidad</span>
                                <span class="text-zinc-700 font-medium">
                                    @if($document->expiry_date)
                                        {{ $document->expiry_date->format('d/m/Y') }}
                                        @if($document->isExpired())
                                            <x-agro.status-badge label="Caducado" color="red" />
                                        @elseif($document->isExpiringSoon())
                                            <x-agro.status-badge label="Próximo" color="amber" />
                                        @endif
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <x-slot:footer>
                        @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ roleRoute('documents.edit', $document) }}" wire:navigate class="{{ $btnBase }}" title="Editar">
                                <flux:icon icon="pencil" class="size-4" />
                            </a>
                            <button
                                wire:click="delete({{ $document->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="¿Eliminar este documento?"
                                class="{{ $btnBase }} hover:!text-red-500 hover:!bg-red-50"
                                title="Eliminar"
                            >
                                <flux:icon icon="trash" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $documents->links() }}</div>
    @else
        <x-agro.empty-state icon="folder-open" title="Sin documentos registrados"
            description="Añade licencias, permisos, certificados y otros documentos oficiales de la bodega." />
    @endif
</div>
</div>
