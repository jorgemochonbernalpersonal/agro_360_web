<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Cosecha Comercializada"
        description="Registro de entregas y ventas por campaña"
        icon="truck"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.marketed-harvests.create') }}" variant="primary" icon="plus">
                Nueva Entrega
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total entregas"
            :value="$stats['total']"
            icon="truck"
            color="zinc"
        />
        <x-agro.stat-card
            label="Esta campaña"
            :value="$stats['this_campaign']"
            icon="calendar-days"
            color="agro"
        />
        <x-agro.stat-card
            label="Total kg"
            :value="number_format($stats['total_kg'], 0, ',', '.')"
            icon="scale"
            color="agro"
        />
        <x-agro.stat-card
            label="Facturadas"
            :value="$stats['invoiced']"
            icon="document-check"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">Todas las campañas</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterDestination" class="w-44">
            <flux:select.option value="">Todos los destinos</flux:select.option>
            @foreach($destinations as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterDestination)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="truck"
            title="{{ $filterCampaign || $filterDestination ? 'Ninguna entrega coincide con los filtros' : 'Sin entregas registradas' }}"
            description="{{ $filterCampaign || $filterDestination ? 'Prueba a cambiar o limpiar los filtros.' : 'Registra las entregas de uva a bodega, cooperativa o venta directa.' }}"
        >
            <x-slot:action>
                @if($filterCampaign || $filterDestination)
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.marketed-harvests.create') }}" variant="primary" icon="plus">
                        Nueva Entrega
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($entries as $entry)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="harvest-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="truck"
                            :title="$entry->harvest->plotPlanting->plot->name ?? '—'"
                            :subtitle="$entry->delivery_date->format('d/m/Y')"
                            iconBg="bg-agro-100"
                            iconColor="text-agro-600"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="blue" size="sm">{{ $entry->destination_type_label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($entry->harvest->plotPlanting?->grapeVariety)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="scissors" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $entry->harvest->plotPlanting->grapeVariety->name }}</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Cantidad</p>
                                <p class="text-xl font-bold text-zinc-700 leading-none">
                                    {{ number_format($entry->quantity_kg, 0, ',', '.') }}<span class="text-xs font-normal text-zinc-400 ml-0.5">kg</span>
                                </p>
                            </div>
                            @if($entry->price_per_kg)
                                <div class="bg-zinc-50 rounded-xl p-3">
                                    <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">€/kg</p>
                                    <p class="text-xl font-bold text-zinc-700 leading-none">
                                        {{ number_format($entry->price_per_kg, 3, ',', '.') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if($entry->total_value)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Valor total</span>
                                <span class="text-lg font-bold text-zinc-900">{{ number_format($entry->total_value, 2, ',', '.') }} €</span>
                            </div>
                        @endif

                        {{-- Factura --}}
                        @if($entry->destination_type === 'third_party')
                            @if($entry->invoice_id)
                                <a href="{{ roleRoute('viticulturist.invoices.show', $entry->invoice_id) }}"
                                   class="flex items-center gap-2 text-xs text-agro-600 hover:text-agro-700">
                                    <flux:icon icon="document-check" class="size-3.5 shrink-0" />
                                    <span>Ver factura</span>
                                </a>
                            @else
                                <button
                                    wire:click="generateInvoice({{ $entry->id }})"
                                    wire:confirm="¿Generar factura para esta entrega?"
                                    class="flex items-center gap-2 text-xs text-zinc-500 hover:text-agro-600 transition-colors">
                                    <flux:icon icon="document-plus" class="size-3.5 shrink-0" />
                                    <span>Generar factura</span>
                                </button>
                            @endif
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.marketed-harvests.edit', $entry) }}"
                                title="Editar"
                            />
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $entry->id }})"
                                wire:confirm="¿Eliminar esta entrega?"
                                title="Eliminar"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        @if($entries->hasPages())
            <div class="mt-6">{{ $entries->links() }}</div>
        @endif
    @endif

</div>
