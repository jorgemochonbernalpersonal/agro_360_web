<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Gestión de Residuos Agrícolas')"
        :description="__('Registro de gestión de podas, orujos y subproductos vitícolas')"
        icon="trash"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.residue-managements.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Gestión') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            :label="__('Total registros')"
            :value="$stats['total']"
            icon="trash"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Esta campaña')"
            :value="$stats['this_campaign']"
            icon="calendar-days"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Compostados')"
            :value="$stats['composted']"
            icon="arrow-path"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Retirados')"
            :value="$stats['removed']"
            icon="truck"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 flex-wrap">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterPractice" class="w-44">
            <flux:select.option value="">{{ __('Todas las prácticas') }}</flux:select.option>
            @foreach($practiceTypes as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign || $filterPractice)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="trash"
            :title="$filterCampaign || $filterPractice ? __('Ningún registro coincide con los filtros') : __('Sin registros de gestión de residuos')"
            :description="$filterCampaign || $filterPractice ? __('Prueba a cambiar o limpiar los filtros.') : __('Registra cómo gestionas los residuos de poda, orujo y otros subproductos vitícolas.')"
        >
            <x-slot:action>
                @if($filterCampaign || $filterPractice)
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtros') }}</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.residue-managements.create') }}" variant="primary" icon="plus">
                        {{ __('Nueva Gestión') }}
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
                    wire:key="residue-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="trash"
                            :title="$entry->plot->name ?? ($entry->plotPlanting?->plot->name ?? __('Global campaña'))"
                            :subtitle="$entry->date->format('d/m/Y')"
                            iconBg="bg-agro-100"
                            iconColor="text-agro-600"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="blue" size="sm">{{ $entry->practice_label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2">
                            <flux:badge color="zinc" size="sm">{{ $entry->material_label }}</flux:badge>
                        </div>

                        @if($entry->estimated_quantity)
                            <div class="bg-zinc-50 rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">{{ __('Cantidad estimada') }}</p>
                                <p class="text-xl font-bold text-zinc-700 leading-none">
                                    {{ number_format($entry->estimated_quantity, 2, ',', '.') }}
                                    <span class="text-xs font-normal text-zinc-400 ml-0.5">{{ $entry->quantity_unit }}</span>
                                </p>
                            </div>
                        @endif

                        @if($entry->notes)
                            <p class="text-xs text-zinc-500 line-clamp-2">{{ $entry->notes }}</p>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.residue-managements.edit', $entry) }}"
                                :title="__('Editar')"
                            />
                            <x-agro.action-button
                                variant="archive"
                                wire:click="deactivate({{ $entry->id }})"
                                wire:confirm="{{ __('¿Archivar este registro?') }}"
                                :title="__('Archivar')"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro.pagination :paginator="$entries" />
    @endif

</div>
