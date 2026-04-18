<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Solicitudes Únicas PAC"
        description="Declaraciones anuales de superficies agrarias ante el organismo pagador."
    >
        <x-slot:actions>
            <flux:button variant="primary" icon="plus"
                href="{{ roleRoute('viticulturist.pac.declarations.create') }}" wire:navigate>
                Nueva declaración
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <x-agro.stats-section key="pac-declarations">
        <x-agro.stat-card label="Total" :value="$stats['total']" icon="document-text" color="zinc" />
        <x-agro.stat-card label="Borradores" :value="$stats['draft']" icon="pencil" color="amber" />
        <x-agro.stat-card label="Presentadas" :value="$stats['submitted']" icon="paper-airplane" color="blue" />
        <x-agro.stat-card label="Aprobadas" :value="$stats['approved']" icon="check-circle" color="green" />
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
                                    <p class="text-xs text-zinc-500 font-mono">{{ $declaration->reference_number ?? 'Sin referencia' }}</p>
                                </div>
                                <x-agro.status-badge :color="$declaration->statusColor()" :label="$declaration->statusLabel()" />
                            </div>
                        </x-slot:header>

                        <div class="flex-1 space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Declarada</p>
                                    <p class="text-lg font-bold text-agro-700 leading-none">{{ number_format($declaration->total_declared_area, 2) }}</p>
                                    <p class="text-[10px] text-agro-400 mt-0.5">ha</p>
                                </div>
                                <div class="bg-agro-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-agro-400 uppercase tracking-widest mb-0.5">Admisible</p>
                                    <p class="text-lg font-bold text-green-700 leading-none">{{ number_format($declaration->total_eligible_area, 2) }}</p>
                                    <p class="text-[10px] text-agro-400 mt-0.5">ha</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Parcelas</span>
                                    <span class="text-zinc-700 font-medium">{{ $declaration->items_count }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-zinc-400">Presentada</span>
                                    <span class="text-zinc-700 font-medium">{{ $declaration->submitted_at?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <x-slot:footer>
                            @php $btnBase = 'inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors'; @endphp
                            <div class="flex items-center justify-end gap-0.5">
                                <a href="{{ roleRoute('viticulturist.pac.declarations.show', $declaration) }}"
                                   wire:navigate class="{{ $btnBase }}" title="Ver">
                                    <flux:icon icon="eye" class="size-4" />
                                </a>
                                @if($declaration->isDraft())
                                    <a href="{{ roleRoute('viticulturist.pac.declarations.edit', $declaration) }}"
                                       wire:navigate class="{{ $btnBase }}" title="Editar">
                                        <flux:icon icon="pencil" class="size-4" />
                                    </a>
                                    <button wire:click="submit({{ $declaration->id }})"
                                        wire:confirm="¿Presentar esta declaración? Una vez presentada no podrás editarla."
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Presentar">
                                        <flux:icon icon="paper-airplane" class="size-4" />
                                    </button>
                                    <button wire:click="delete({{ $declaration->id }})"
                                        wire:confirm="¿Eliminar esta declaración? Esta acción no se puede deshacer."
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                        <flux:icon icon="trash" class="size-4" />
                                    </button>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-agro.card>
                @endforeach
            </div>
        @else
            <x-agro.empty-state
                icon="document-text"
                title="Sin declaraciones PAC"
                description="Crea tu primera Solicitud Única para declarar tus superficies ante el organismo pagador."
            >
                <flux:button variant="primary" icon="plus"
                    href="{{ roleRoute('viticulturist.pac.declarations.create') }}" wire:navigate>
                    Nueva declaración
                </flux:button>
            </x-agro.empty-state>
        @endif
    </div>

</div>
