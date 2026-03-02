<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Autorizaciones Comerciales"
        subtitle="DO, certificaciones ecológicas, derechos de plantación y replantación"
        icon="shield-check"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.commercial-authorizations.create') }}" variant="primary" icon="plus">
                Nueva Autorización
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    @if($expiring > 0)
        <flux:callout variant="warning" icon="exclamation-triangle">
            Tienes <strong>{{ $expiring }}</strong> {{ $expiring === 1 ? 'autorización que vence' : 'autorizaciones que vencen' }} en los próximos 60 días. Revisa y renuévalas a tiempo.
        </flux:callout>
    @endif

    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos</option>
            @foreach($authTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>
    </x-agro.filter-bar>

    <x-agro.card>
        @if($entries->isEmpty())
            <x-agro.empty-state
                icon="shield-check"
                title="Sin autorizaciones registradas"
                description="Registra tus inscripciones en Denominaciones de Origen, certificaciones ecológicas y derechos de plantación."
            >
                <x-slot:action>
                    <flux:button href="{{ route('viticulturist.commercial-authorizations.create') }}" variant="primary" icon="plus">
                        Nueva Autorización
                    </flux:button>
                </x-slot:action>
            </x-agro.empty-state>
        @else
            <x-agro.data-table :headers="['Tipo', 'Código / Expediente', 'Organismo', 'Emisión', 'Caducidad', 'Estado', 'Acciones']">
                @foreach($entries as $entry)
                    <x-agro.table-row>
                        <x-agro.table-cell>
                            <x-agro.status-badge :status="$entry->authorization_type" :label="$entry->authorization_type_label" />
                            @if($entry->description)
                                <span class="text-zinc-400 text-xs block">{{ $entry->description }}</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell class="font-mono text-sm">{{ $entry->authorization_code ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->issuing_body ?? '-' }}</x-agro.table-cell>
                        <x-agro.table-cell>{{ $entry->issue_date->format('d/m/Y') }}</x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->expiry_date)
                                <span class="{{ $entry->isExpired() ? 'text-red-600 font-semibold' : ($entry->isExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-600') }}">
                                    {{ $entry->expiry_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-zinc-400">Indefinida</span>
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell>
                            @if($entry->isExpired())
                                <x-agro.status-badge label="Vencida" type="danger" />
                            @elseif($entry->isExpiringSoon())
                                <x-agro.status-badge label="Por vencer" type="warning" />
                            @else
                                <x-agro.status-badge label="Vigente" type="success" />
                            @endif
                        </x-agro.table-cell>
                        <x-agro.table-cell align="right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('viticulturist.commercial-authorizations.edit', $entry) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                                   title="Editar">
                                    <flux:icon icon="pencil-square" class="size-4" />
                                </a>
                                <button
                                    wire:click="deactivate({{ $entry->id }})"
                                    wire:confirm="¿Archivar esta autorización?"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                    title="Archivar">
                                    <flux:icon icon="archive-box" class="size-4" />
                                </button>
                            </div>
                        </x-agro.table-cell>
                    </x-agro.table-row>
                @endforeach
            </x-agro.data-table>
            @if($entries->hasPages())
                <div class="mt-4">{{ $entries->links() }}</div>
            @endif
        @endif
    </x-agro.card>

</div>
