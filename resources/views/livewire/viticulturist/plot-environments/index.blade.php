<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        :title="__('Entorno de Parcelas')"
        :description="__('Zonas protegidas, captaciones de agua y condiciones ambientales')"
        icon="map"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.plot-environments.create') }}" variant="primary" icon="plus">
                {{ __('Nueva Ficha') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        {{ __('El registro de zonas protegidas y captaciones de agua es obligatorio en programas de Producción Integrada y para el uso de fitosanitarios cerca de masas de agua (RD 1311/2012).') }}
    </flux:callout>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            :label="__('Total fichas')"
            :value="$stats['total']"
            icon="map"
            color="zinc"
        />
        <x-agro.stat-card
            :label="__('Con captación agua')"
            :value="$stats['water']"
            icon="beaker"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Zona protegida')"
            :value="$stats['protected']"
            icon="shield-check"
            color="amber"
        />
        <x-agro.stat-card
            :label="__('Riesgo erosión')"
            :value="$stats['erosion']"
            icon="exclamation-triangle"
            color="zinc"
        />
    </div>

    {{-- Toolbar --}}
    <div class="flex items-center gap-3">
        <flux:select wire:model.live="filterCampaign" class="w-48">
            <flux:select.option value="">{{ __('Todas las campañas') }}</flux:select.option>
            @foreach($campaigns as $c)
                <flux:select.option value="{{ $c->id }}">{{ $c->name }}</flux:select.option>
            @endforeach
        </flux:select>
        @if($filterCampaign)
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Limpiar') }}</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="map"
            :title="$filterCampaign ? __('Ninguna ficha coincide con el filtro') : __('Sin fichas de entorno')"
            :description="$filterCampaign ? __('Prueba a cambiar o limpiar el filtro.') : __('Registra las condiciones ambientales de cada parcela: zonas protegidas, distancia a captaciones de agua, pendiente...')"
        >
            <x-slot:action>
                @if($filterCampaign)
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">{{ __('Limpiar filtro') }}</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.plot-environments.create') }}" variant="primary" icon="plus">
                        {{ __('Nueva Ficha') }}
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
                    wire:key="env-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="map"
                            :title="$entry->plot->name ?? '—'"
                            :subtitle="$entry->plotPlanting?->grapeVariety?->name ?? __('Global parcela')"
                            iconBg="bg-agro-100"
                            iconColor="text-agro-600"
                            size="md"
                            radius="xl"
                        />
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($entry->water_intake_nearby)
                                <flux:badge color="amber" size="sm" icon="exclamation-triangle">{{ __('Captación agua') }}</flux:badge>
                            @endif
                            @if($entry->protected_zone_total)
                                <flux:badge color="red" size="sm">{{ __('Zona protegida total') }}</flux:badge>
                            @elseif($entry->protected_zone_partial)
                                <flux:badge color="orange" size="sm">{{ __('Zona protegida parcial') }}</flux:badge>
                            @endif
                            @if($entry->erosion_risk)
                                <flux:badge color="orange" size="sm">{{ __('Riesgo erosión') }}</flux:badge>
                            @endif
                        </div>

                        @if($entry->water_intake_nearby && $entry->water_intake_distance_m)
                            <div class="flex items-center gap-2 text-xs text-amber-600">
                                <flux:icon icon="beaker" class="size-3.5 shrink-0" />
                                <span>{{ __('Captación a') }} {{ $entry->water_intake_distance_m }} m</span>
                            </div>
                        @endif

                        @if($entry->protected_zone_partial && $entry->protection_zone_type)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="shield-check" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $entry->protection_zone_type }}</span>
                            </div>
                        @endif

                        @if($entry->slope_pct)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">{{ __('Pendiente') }}</span>
                                <span class="text-zinc-700 font-medium">{{ $entry->slope_pct }}%</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.plot-environments.edit', $entry) }}"
                                :title="__('Editar')"
                            />
                            <x-agro.action-button
                                variant="delete"
                                wire:click="delete({{ $entry->id }})"
                                wire:confirm="{{ __('¿Eliminar esta ficha?') }}"
                                :title="__('Eliminar')"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$entries" />
    @endif

</div>
