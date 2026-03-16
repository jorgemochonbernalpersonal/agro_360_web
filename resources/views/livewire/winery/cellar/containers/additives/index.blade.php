<x-agro.page-header
    title="Aditivos — {{ $container->name }}"
    description="Registro de aditivos enológicos aplicados al contenido de este depósito."
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="ghost" icon="arrow-left"
            href="{{ roleRoute('containers.index') }}" wire:navigate>
            Volver
        </flux:button>
        <flux:button variant="primary" icon="plus"
            href="{{ roleRoute('containers.additives.create', $container) }}" wire:navigate>
            Añadir aditivo
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.card>
    <x-agro.data-table :headers="['Aditivo', 'Cantidad', 'Fecha', 'Registrado por', 'Acciones']">
        @forelse($additives as $additive)
            <x-agro.table-row>
                <x-agro.table-cell>
                    <div class="font-medium">{{ $additive->display_name }}</div>
                    @if($additive->notes)
                        <div class="text-xs text-zinc-500">{{ Str::limit($additive->notes, 60) }}</div>
                    @endif
                </x-agro.table-cell>
                <x-agro.table-cell>
                    {{ number_format($additive->quantity, 3) }}
                    {{ $additive->unitOfMeasurement?->symbol }}
                </x-agro.table-cell>
                <x-agro.table-cell>{{ $additive->additive_date->format('d/m/Y') }}</x-agro.table-cell>
                <x-agro.table-cell>{{ $additive->creator?->name ?? '—' }}</x-agro.table-cell>
                <x-agro.table-cell align="right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ roleRoute('containers.additives.edit', [$container, $additive]) }}" wire:navigate
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors" title="Editar">
                            <flux:icon icon="pencil-square" class="size-4" />
                        </a>
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $additive->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Eliminar este registro de aditivo?" />
                    </div>
                </x-agro.table-cell>
            </x-agro.table-row>
        @empty
            <x-agro.empty-state icon="beaker" title="Sin aditivos registrados"
                description="Registra los aditivos aplicados a este depósito (SO₂, bentonita, etc.)." />
        @endforelse
    </x-agro.data-table>
    {{ $additives->links() }}
</x-agro.card>
