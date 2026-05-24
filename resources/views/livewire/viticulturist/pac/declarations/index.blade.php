<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="__('Solicitudes Únicas PAC')"
        :description="__('Declaraciones anuales de superficies agrarias ante el organismo pagador.')"
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus"
                href="{{ roleRoute('viticulturist.pac.declarations.create') }}" wire:navigate>
                {{ __('Nueva declaración') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.stats-section key="pac-declarations">
        <x-agro.stat-card :label="__('Total')" :value="$stats['total']" icon="document-text" color="zinc" />
        <x-agro.stat-card :label="__('Borradores')" :value="$stats['draft']" icon="pencil" color="amber" />
        <x-agro.stat-card :label="__('Presentadas')" :value="$stats['submitted']" icon="paper-airplane" color="blue" />
        <x-agro.stat-card :label="__('Aprobadas')" :value="$stats['approved']" icon="check-circle" color="green" />
    </x-agro.stats-section>

    <x-agro.loading-grid target="search" />
    <div wire:loading.remove wire:target="search">
        @if($declarations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($declarations as $declaration)
                    @php $delay = min($loop->index * 50, 300); @endphp
                    <x-agro.card
                        class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                        style="animation-delay: {{ $delay }}ms;"
                        wire:key="decl-{{ $declaration->id }}"
                    >
                        <x-slot:header>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center shrink-0">
                                    <flux:icon icon="document-text" class="size-5 text-sky-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-zinc-900 truncate">PAC {{ $declaration->year }}</h3>
                                    <p class="text-xs text-zinc-500 font-mono">{{ $declaration->reference_number ?? __('Sin referencia') }}</p>
                                </div>
                                <x-agro.status-badge :color="$declaration->statusColor()" :label="$declaration->statusLabel()" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Declarada') }}</p>
                                    <p class="text-lg font-bold text-agro-700 leading-none">{{ number_format($declaration->total_declared_area, 2) }}</p>
                                    <p class="text-[10px] text-agro-400 mt-0.5">ha</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">{{ __('Admisible') }}</p>
                                    <p class="text-lg font-bold text-green-700 leading-none">{{ number_format($declaration->total_eligible_area, 2) }}</p>
                                    <p class="text-[10px] text-agro-400 mt-0.5">ha</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Parcelas') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ $declaration->items_count }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">{{ __('Presentada') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ $declaration->submitted_at?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            <div class="flex items-center justify-end gap-0.5">
                                <x-agro.action-button
                                    variant="view"
                                    href="{{ roleRoute('viticulturist.pac.declarations.show', $declaration) }}"
                                    wire:navigate
                                    :title="__('Ver')"
                                />
                                @if($declaration->isDraft())
                                    <x-agro.action-button
                                        icon="pencil"
                                        variant="edit"
                                        href="{{ roleRoute('viticulturist.pac.declarations.edit', $declaration) }}"
                                        wire:navigate
                                        :title="__('Editar')"
                                    />
                                    <x-agro.action-button
                                        icon="paper-airplane"
                                        variant="primary"
                                        wire:click="submit({{ $declaration->id }})"
                                        wire:confirm="{{ __('¿Presentar esta declaración? Una vez presentada no podrás editarla.') }}"
                                        :title="__('Presentar')"
                                    />
                                    <x-agro.action-button
                                        variant="delete"
                                        wire:click="delete({{ $declaration->id }})"
                                        wire:confirm="{{ __('¿Eliminar esta declaración? Esta acción no se puede deshacer.') }}"
                                        :title="__('Eliminar')"
                                    />
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
        @else
            <x-agro.empty-state
                icon="document-text"
                :title="__('Sin declaraciones PAC')"
                :description="__('Crea tu primera Solicitud Única para declarar tus superficies ante el organismo pagador.')"
            >
                <flux:button variant="primary" icon="plus"
                    href="{{ roleRoute('viticulturist.pac.declarations.create') }}" wire:navigate>
                    {{ __('Nueva declaración') }}
                </flux:button>
            </x-agro.empty-state>
        @endif
    </div>

</div>
