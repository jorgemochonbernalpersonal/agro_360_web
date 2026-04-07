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

    <div x-data="{
        open: localStorage.getItem('pac-declarations-stats-open') !== 'false',
        toggle() {
            this.open = !this.open;
            localStorage.setItem('pac-declarations-stats-open', String(this.open));
        }
    }">
        <button
            @click="toggle()"
            class="flex items-center gap-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-widest hover:text-zinc-600 transition-colors mb-3"
        >
            <span>Estadísticas</span>
            <flux:icon icon="chevron-up" class="size-3.5 transition-transform duration-200" ::class="{ 'rotate-180': !open }" />
        </button>
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
        >
        <div class="grid grid-cols-2 gap-4">
            <x-agro.stat-card label="Total" :value="$stats['total']" icon="document-text" color="zinc" />
            <x-agro.stat-card label="Borradores" :value="$stats['draft']" icon="pencil" color="amber" />
            <x-agro.stat-card label="Presentadas" :value="$stats['submitted']" icon="paper-airplane" color="blue" />
            <x-agro.stat-card label="Aprobadas" :value="$stats['approved']" icon="check-circle" color="green" />
        </div>
        </div>
    </div>

    @if($declarations->isEmpty())
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
    @else
        <x-agro.card>
            <x-agro.data-table :headers="['Año', 'Parcelas', 'Sup. declarada', 'Sup. admisible', 'Referencia', 'Presentada', 'Estado', '']">
                @foreach($declarations as $declaration)
                    <x-agro.table-row wire:key="decl-{{ $declaration->id }}">
                        <x-agro.table-cell>
                            <span class="font-bold text-zinc-900 text-base">{{ $declaration->year }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $declaration->items_count }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ number_format($declaration->total_declared_area, 2) }} ha
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-medium text-green-700">{{ number_format($declaration->total_eligible_area, 2) }} ha</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <span class="font-mono text-xs text-zinc-500">{{ $declaration->reference_number ?? '—' }}</span>
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            {{ $declaration->submitted_at?->format('d/m/Y') ?? '—' }}
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            <x-agro.status-badge :color="$declaration->statusColor()" :label="$declaration->statusLabel()" />
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="ghost" icon="eye"
                                    href="{{ roleRoute('viticulturist.pac.declarations.show', $declaration) }}"
                                    wire:navigate />
                                @if($declaration->isDraft())
                                    <flux:button size="sm" variant="ghost" icon="pencil"
                                        href="{{ roleRoute('viticulturist.pac.declarations.edit', $declaration) }}"
                                        wire:navigate />
                                    <flux:button size="sm" variant="ghost" icon="paper-airplane"
                                        wire:click="submit({{ $declaration->id }})"
                                        wire:confirm="¿Presentar esta declaración? Una vez presentada no podrás editarla." />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500"
                                        wire:click="delete({{ $declaration->id }})"
                                        wire:confirm="¿Eliminar esta declaración? Esta acción no se puede deshacer." />
                                @endif
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
        </x-agro.card>
    @endif

</div>

