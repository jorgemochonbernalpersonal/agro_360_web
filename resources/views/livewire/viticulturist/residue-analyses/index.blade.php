<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Análisis de Residuos Fitosanitarios"
        description="Control de LMR (Límites Máximos de Residuos)"
        icon="beaker"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.residue-analyses.create') }}" variant="primary" icon="plus">
                Nuevo Análisis
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total análisis"
            :value="$stats['total']"
            icon="beaker"
            color="zinc"
        />
        <x-agro.stat-card
            label="Conformes"
            :value="$stats['compliant']"
            icon="check-circle"
            color="agro"
        />
        <x-agro.stat-card
            label="No conformes"
            :value="$stats['failed']"
            icon="x-circle"
            color="amber"
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
        <flux:select wire:model.live="filterCompliant" class="w-44">
            <flux:select.option value="">Todos los resultados</flux:select.option>
            <flux:select.option value="1">Conforme</flux:select.option>
            <flux:select.option value="0">No conforme</flux:select.option>
        </flux:select>
        @if($filterCampaign || $filterCompliant !== '')
            <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">Limpiar</flux:button>
        @endif
    </div>

    {{-- Grid de cards --}}
    @if($entries->isEmpty())
        <x-agro.empty-state
            icon="beaker"
            title="{{ $filterCampaign || $filterCompliant !== '' ? 'Ningún análisis coincide con los filtros' : 'Sin análisis registrados' }}"
            description="{{ $filterCampaign || $filterCompliant !== '' ? 'Prueba a cambiar o limpiar los filtros.' : 'Registra los análisis de residuos de laboratorio para verificar el cumplimiento de los LMR.' }}"
        >
            <x-slot:action>
                @if($filterCampaign || $filterCompliant !== '')
                    <flux:button wire:click="clearFilters" variant="outline" icon="x-mark">Limpiar filtros</flux:button>
                @else
                    <flux:button href="{{ roleRoute('viticulturist.residue-analyses.create') }}" variant="primary" icon="plus">
                        Nuevo Análisis
                    </flux:button>
                @endif
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($entries as $entry)
                @php
                    $delay = min($loop->index * 50, 300);
                    $bg = $entry->overall_compliant ? 'bg-agro-100' : 'bg-red-100';
                    $iconColor = $entry->overall_compliant ? 'text-agro-600' : 'text-red-600';
                    $badge = $entry->overall_compliant ? 'green' : 'red';
                    $label = $entry->overall_compliant ? 'Conforme' : 'No conforme';
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="analysis-{{ $entry->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="beaker"
                            :title="$entry->laboratory_name"
                            :subtitle="$entry->analysis_date->format('d/m/Y')"
                            :iconBg="$bg"
                            :iconColor="$iconColor"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="{{ $badge }}" size="sm">{{ $label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($entry->plotPlanting?->plot)
                            <div class="flex items-center gap-2 text-sm text-zinc-600">
                                <flux:icon icon="map" class="size-4 text-zinc-400 shrink-0" />
                                <span class="truncate">{{ $entry->plotPlanting->plot->name }}</span>
                                @if($entry->plotPlanting->grapeVariety)
                                    <span class="text-xs text-zinc-400 shrink-0">· {{ $entry->plotPlanting->grapeVariety->name }}</span>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-sm text-zinc-400">
                                <flux:icon icon="map" class="size-4 shrink-0" />
                                <span>Global campaña</span>
                            </div>
                        @endif

                        @if($entry->sample_type)
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon icon="tag" class="size-3.5 text-zinc-400 shrink-0" />
                                <span>{{ $entry->sample_type }}</span>
                            </div>
                        @endif

                        @if($entry->laboratory_accreditation)
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <flux:icon icon="shield-check" class="size-3.5 shrink-0" />
                                <span>ENAC: {{ $entry->laboratory_accreditation }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <x-agro.action-button
                                variant="edit"
                                href="{{ roleRoute('viticulturist.residue-analyses.edit', $entry) }}"
                                title="Editar"
                            />
                            <x-agro.action-button
                                variant="archive"
                                wire:click="deactivate({{ $entry->id }})"
                                wire:confirm="¿Archivar este análisis?"
                                title="Archivar"
                            />
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$entries" />
    @endif

</div>
