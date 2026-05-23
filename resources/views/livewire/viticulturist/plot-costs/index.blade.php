<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Costes por Parcela"
        description="Registro y análisis de costes por parcela y campaña"
        icon="banknotes"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.plot-costs.create') }}" variant="primary" icon="plus">
                Registrar Coste
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total registros"
            :value="$stats['total']"
            icon="document-text"
            color="zinc"
        />
        <x-agro.stat-card
            label="Importe total"
            :value="number_format($stats['total_amount'], 2) . ' €'"
            icon="banknotes"
            color="zinc"
        />
        <x-agro.stat-card
            label="Importe filtrado"
            :value="number_format($stats['filtered_amount'], 2) . ' €'"
            icon="funnel"
            color="agro"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filter_campaign_id" class="w-48">
            <flux:select.option value="">Todas las campañas</flux:select.option>
            @foreach($campaigns as $campaign)
                <flux:select.option value="{{ $campaign->id }}">Campaña {{ $campaign->year }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filter_plot_id" class="w-44">
            <flux:select.option value="">Todas las parcelas</flux:select.option>
            @foreach($plots as $plot)
                <flux:select.option value="{{ $plot->id }}">{{ $plot->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filter_category" class="w-44">
            <flux:select.option value="">Todas las categorías</flux:select.option>
            @foreach($categories as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filter_campaign_id || $filter_plot_id || $filter_category)
            <flux:button wire:click="$set('filter_campaign_id', ''); $set('filter_plot_id', ''); $set('filter_category', '')" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($costs->count() === 0)
        <x-agro.empty-state
            icon="banknotes"
            title="{{ $filter_campaign_id || $filter_plot_id || $filter_category ? 'Ningún coste coincide con los filtros' : 'Sin costes registrados' }}"
            description="{{ $filter_campaign_id || $filter_plot_id || $filter_category ? 'Prueba a cambiar o limpiar los filtros.' : 'Registra los costes de tus parcelas: mano de obra, fitosanitarios, maquinaria y más.' }}"
        >
            <x-slot:action>
                @if($filter_campaign_id || $filter_plot_id || $filter_category)
                    <flux:button wire:click="$set('filter_campaign_id', ''); $set('filter_plot_id', ''); $set('filter_category', '')" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.plot-costs.create') }}" variant="primary" icon="plus">
                        Registrar primer coste
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($costs as $cost)
                @php $delay = min($loop->index * 50, 300); @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="cost-{{ $cost->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="banknotes"
                            :title="$cost->description"
                            :subtitle="$cost->cost_date->format('d/m/Y')"
                            iconBg="bg-red-100"
                            iconColor="text-red-600"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="blue" size="sm">{{ $cost->category_label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($cost->supplier)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="building-office" class="size-3.5 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $cost->supplier }}</span>
                            </div>
                        @endif

                        @if($cost->plot)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="map" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $cost->plot->name }}</span>
                            </div>
                        @endif

                        @if($cost->campaign)
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <flux:icon icon="calendar-days" class="size-3.5 shrink-0" />
                                <span>Campaña {{ $cost->campaign->year }}</span>
                            </div>
                        @endif

                        <div class="bg-red-50 rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Importe</p>
                            <p class="text-xl font-bold text-red-700 leading-none">
                                {{ number_format($cost->amount, 2) }}<span class="text-xs font-normal text-zinc-400 ml-0.5">€</span>
                            </p>
                        </div>
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.plot-costs.edit', $cost->id) }}"
                                title="Editar"
                            />
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $cost->id }})"
                                wire:confirm="¿Eliminar este coste?"
                                title="Eliminar"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$costs" />
    @endif

</div>
