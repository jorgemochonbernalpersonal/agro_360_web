<div class="space-y-6 animate-fade-in">
<x-agro.page-header
    :title="__('Aditivos') . ' — ' . $container->name"
    :description="__('Registro de aditivos enológicos aplicados al contenido de este depósito.')"
    icon="beaker"
>
    <x-slot:actions>
        <flux:button variant="ghost" icon="arrow-left"
            href="{{ roleRoute('containers.index') }}" wire:navigate>
            {{ __('Volver') }}
        </flux:button>
        <flux:button variant="primary" icon="plus"
            href="{{ roleRoute('containers.additives.create', $container) }}" wire:navigate>
            {{ __('Añadir aditivo') }}
        </flux:button>
    </x-slot:actions>
</x-agro.page-header>

<x-agro.loading-grid target="nextPage, previousPage" />

<div wire:loading.remove wire:target="nextPage, previousPage">
    @if($additives->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($additives as $additive)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="additive-{{ $additive->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$additive->display_name"
                            :subtitle="$additive->additive_date->format('d/m/Y')"
                            iconBg="bg-teal-100"
                            iconColor="text-teal-600"
                            size="md"
                            radius="xl"
                        />
                    </x-slot:header>

                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-agro-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad') }}</p>
                                <p class="text-2xl font-bold text-agro-700 leading-none">
                                    {{ number_format($additive->quantity, 3) }}
                                    <span class="text-sm font-medium text-agro-400">{{ $additive->unitOfMeasurement?->symbol }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-400">{{ __('Registrado por') }}</span>
                                <span class="text-zinc-700 font-medium">{{ $additive->creator?->name ?? '—' }}</span>
                            </div>
                        </div>

                        @if($additive->notes)
                            <p class="text-xs text-zinc-400 line-clamp-2">{{ Str::limit($additive->notes, 60) }}</p>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button variant="edit" href="{{ roleRoute('containers.additives.edit', [$container, $additive]) }}" wire:navigate title="{{ __('Editar') }}" />
                            <x-agro.action-button variant="delete" wire:click="delete({{ $additive->id }})" wire:loading.attr="disabled" wire:confirm="{{ __('¿Eliminar este registro de aditivo?') }}" title="{{ __('Eliminar') }}" />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
        <x-agro-pagination :paginator="$additives" />
    @else
        <x-agro.empty-state icon="beaker" title="{{ __('Sin aditivos registrados') }}"
            :description="__('Registra los aditivos aplicados a este depósito (SO₂, bentonita, etc.).')" />
    @endif
</div>
</div>
