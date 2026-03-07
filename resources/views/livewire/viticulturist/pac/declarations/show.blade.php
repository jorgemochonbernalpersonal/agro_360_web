<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="'Declaración PAC ' . $declaration->year"
        :description="'Solicitud Única — ' . $declaration->statusLabel()"
        :back-url="route('viticulturist.pac.declarations.index')"
    >
        <x-slot:actions>
            @if($declaration->isDraft())
                <flux:button variant="ghost" icon="pencil"
                    href="{{ route('viticulturist.pac.declarations.edit', $declaration) }}" wire:navigate>
                    Editar
                </flux:button>
                <flux:button variant="primary" icon="paper-airplane"
                    wire:click="submit"
                    wire:confirm="¿Presentar esta declaración? Una vez presentada no podrás editarla.">
                    Presentar declaración
                </flux:button>
            @endif
            <a href="{{ route('viticulturist.pac.declarations.pdf', $declaration) }}" target="_blank">
                <flux:button variant="ghost" icon="document-arrow-down">PDF</flux:button>
            </a>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card label="Parcelas" :value="$declaration->items->count()" icon="map" color="zinc" />
        <x-agro.stat-card label="Superficie declarada" :value="number_format($declaration->total_declared_area, 2) . ' ha'" icon="globe-alt" color="agro" />
        <x-agro.stat-card label="Superficie admisible" :value="number_format($declaration->total_eligible_area, 2) . ' ha'" icon="check-circle" color="green" />
        <x-agro.stat-card label="Estado" :value="$declaration->statusLabel()" icon="document-text" :color="$declaration->statusColor()" />
    </div>

    {{-- Metadatos --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="information-circle" class="size-4 text-zinc-500" />
                <span class="font-semibold text-zinc-900 text-sm">Datos de la declaración</span>
            </div>
        </x-slot:header>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-zinc-500">Año</dt>
                <dd class="font-bold text-zinc-900 text-lg">{{ $declaration->year }}</dd>
            </div>
            <div>
                <dt class="text-zinc-500">Estado</dt>
                <dd class="mt-1">
                    <x-agro.status-badge :color="$declaration->statusColor()" :label="$declaration->statusLabel()" />
                </dd>
            </div>
            <div>
                <dt class="text-zinc-500">Referencia FEGA</dt>
                <dd class="font-mono text-zinc-700">{{ $declaration->reference_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-zinc-500">Fecha presentación</dt>
                <dd class="text-zinc-700">{{ $declaration->submitted_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
        @if($declaration->notes)
            <div class="mt-4 pt-4 border-t border-zinc-100 text-sm">
                <span class="text-zinc-500">Notas: </span>
                <span class="text-zinc-700">{{ $declaration->notes }}</span>
            </div>
        @endif
    </x-agro.card>

    {{-- Tabla de parcelas --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon icon="map" class="size-4 text-agro-600" />
                <span class="font-semibold text-zinc-900 text-sm">Parcelas declaradas</span>
            </div>
        </x-slot:header>
        <x-agro.data-table :headers="['Parcela', 'Municipio', 'Sup. declarada', 'Sup. admisible', 'Coef. admis.', 'Eco-regímenes']">
            @foreach($declaration->items as $item)
                @php
                    $coef = $item->declared_area > 0
                        ? round($item->eligible_area / $item->declared_area, 4)
                        : null;
                @endphp
                <x-agro.table-row wire:key="item-{{ $item->id }}">
                    <x-agro.table-cell>
                        <span class="font-medium text-zinc-900">{{ $item->plot?->name ?? '—' }}</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        {{ $item->plot?->municipality?->name ?? '—' }}
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        {{ number_format($item->declared_area, 3) }} ha
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        <span class="font-medium text-green-700">{{ number_format($item->eligible_area, 3) }} ha</span>
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if($coef !== null)
                            <span class="font-mono text-sm {{ $coef < 0.9 ? 'text-amber-600' : 'text-zinc-700' }}">
                                {{ number_format($coef, 4) }}
                            </span>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </x-agro.table-cell>
                    <x-agro.table-cell>
                        @if(!empty($item->eco_schemes))
                            <div class="flex flex-wrap gap-1">
                                @foreach($item->eco_schemes as $scheme)
                                    <flux:badge color="green" size="sm">
                                        {{ $ecoSchemesMap[$scheme] ?? $scheme }}
                                    </flux:badge>
                                @endforeach
                            </div>
                        @else
                            <span class="text-zinc-400 text-sm">Ninguno</span>
                        @endif
                    </x-agro.table-cell>
                </x-agro.table-row>
            @endforeach
        </x-agro.data-table>

        {{-- Totales --}}
        <div class="border-t border-zinc-200 mt-2 pt-3 px-4 pb-2 flex justify-end gap-8 text-sm font-medium">
            <span class="text-zinc-500">Total declarado:
                <span class="text-zinc-900 ml-1">{{ number_format($declaration->total_declared_area, 3) }} ha</span>
            </span>
            <span class="text-zinc-500">Total admisible:
                <span class="text-green-700 ml-1">{{ number_format($declaration->total_eligible_area, 3) }} ha</span>
            </span>
        </div>
    </x-agro.card>

</div>
