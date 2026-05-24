<div class="space-y-6 animate-fade-in">

    <!-- Header -->
    <x-agro.page-header
        :title="$campaign->name"
        :description="__('Campaña del año') . ' ' . $campaign->year"
    >
        <x-slot:actions>
            @if(!$campaign->active)
                @can('activate', $campaign)
                    <flux:button wire:click="activate" variant="primary" data-cy="activate-campaign-button">
                        {{ __('Activar Campaña') }}
                    </flux:button>
                @endcan
            @endif
            @can('update', $campaign)
                <flux:button href="{{ roleRoute('viticulturist.campaign.edit', $campaign) }}" variant="outline" icon="pencil-square" data-cy="edit-campaign-button">
                    {{ __('Editar') }}
                </flux:button>
            @endcan
            <flux:button href="{{ roleRoute('viticulturist.campaign.index') }}" variant="outline" icon="arrow-left" data-cy="back-button">
                {{ __('Volver') }}
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Estadísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4" data-cy="campaign-statistics">
        <x-agro.stat-card :label="__('Total')"           :value="$campaign->activities_count"    icon="clipboard-document-list" color="agro"   />
        <x-agro.stat-card :label="__('Tratamientos')"    :value="$campaign->phytosanitary_count" icon="beaker"                  color="red"    />
        <x-agro.stat-card :label="__('Fertilizaciones')" :value="$campaign->fertilization_count" icon="sparkles"                color="blue"   />
        <x-agro.stat-card :label="__('Riegos')"          :value="$campaign->irrigation_count"    icon="cloud"                   color="purple" />
        <x-agro.stat-card :label="__('Labores')"         :value="$campaign->cultural_count"      icon="wrench-screwdriver"      color="orange" />
        <x-agro.stat-card :label="__('Observaciones')"   :value="$campaign->observation_count"   icon="eye"                     color="yellow" />
        <x-agro.stat-card :label="__('Cosechas')"        :value="$campaign->harvest_count"       icon="archive-box"             color="green"  />
    </div>

    <!-- Información de la Campaña -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información General -->
        <x-agro.card data-cy="campaign-info">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="information-circle" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Información General') }}</span>
                </div>
            </x-slot:header>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Estado:') }}</span>
                    @if($campaign->active)
                        <x-agro.status-badge :active="true" />
                    @else
                        <x-agro.status-badge :active="false" />
                    @endif
                </div>
                <div>
                    <span class="text-sm font-semibold text-zinc-600">{{ __('Año:') }}</span>
                    <span class="ml-2 text-sm text-zinc-900 font-medium">{{ $campaign->year }}</span>
                </div>
                @if($campaign->start_date && $campaign->end_date)
                    <div>
                        <span class="text-sm font-semibold text-zinc-600">{{ __('Período:') }}</span>
                        <span class="ml-2 text-sm text-zinc-900">
                            {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                        </span>
                    </div>
                @endif
                @if($campaign->description)
                    <div>
                        <span class="text-sm font-semibold text-zinc-600">{{ __('Descripción:') }}</span>
                        <p class="mt-1 text-sm text-zinc-900">{{ $campaign->description }}</p>
                    </div>
                @endif
            </div>
        </x-agro.card>

        <!-- Acciones Rápidas -->
        <x-agro.card data-cy="campaign-quick-actions">
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-agro-50">
                        <flux:icon icon="bolt" class="size-4 text-agro-600" />
                    </div>
                    <span class="font-semibold text-zinc-900 text-sm">{{ __('Acciones Rápidas') }}</span>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                <flux:button
                    href="{{ roleRoute('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                    variant="primary"
                    class="w-full justify-center"
                    data-cy="view-activities-button"
                >
                    {{ __('Ver Actividades en Cuaderno Digital') }}
                </flux:button>
                @can('create', \App\Models\AgriculturalActivity::class)
                    <div class="grid grid-cols-2 gap-2">
                        <flux:button
                            href="{{ roleRoute('viticulturist.digital-notebook.treatment.create') }}"
                            variant="danger"
                            size="sm"
                            data-cy="create-treatment-button"
                        >
                            + {{ __('Tratamiento') }}
                        </flux:button>
                        <flux:button
                            href="{{ roleRoute('viticulturist.digital-notebook.fertilization.create') }}"
                            variant="primary"
                            size="sm"
                            data-cy="create-fertilization-button"
                        >
                            + {{ __('Fertilización') }}
                        </flux:button>
                    </div>
                @endcan
            </div>
        </x-agro.card>
    </div>

    <!-- Últimas Actividades -->
    @if($recentActivities->count() > 0)
        <x-agro.card data-cy="recent-activities">
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="clock" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Últimas Actividades') }}</span>
                    </div>
                    <flux:button
                        href="{{ roleRoute('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                        variant="ghost"
                        size="sm"
                        data-cy="view-all-activities-link"
                    >
                        {{ __('Ver todas') }} &rarr;
                    </flux:button>
                </div>
            </x-slot:header>
            <div class="space-y-3">
                @foreach($recentActivities as $activity)
                    @php
                        [$typeLabel, $typeColor] = match($activity->activity_type) {
                            'phytosanitary' => [__('Tratamiento'), 'red'],
                            'fertilization' => [__('Fertilización'), 'blue'],
                            'irrigation'    => [__('Riego'), 'purple'],
                            'cultural'      => [__('Labor'), 'orange'],
                            'harvest'       => [__('Cosecha'), 'green'],
                            default         => [__('Observación'), null],
                        };
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-zinc-50 rounded-lg hover:bg-zinc-100 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="text-sm font-semibold text-zinc-900">
                                {{ $activity->activity_date->format('d/m/Y') }}
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-zinc-900">{{ $activity->plot->name }}</span>
                                @if($activity->plotPlanting)
                                    <span class="text-xs text-zinc-500">
                                        — {{ $activity->plotPlanting->name }}
                                        @if($activity->plotPlanting->grapeVariety)
                                            ({{ $activity->plotPlanting->grapeVariety->name }})
                                        @endif
                                    </span>
                                @endif
                                <flux:badge :color="$typeColor" size="sm">{{ $typeLabel }}</flux:badge>
                            </div>
                        </div>
                        @can('view', $activity)
                            <flux:button
                                href="{{ roleRoute('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                                variant="ghost"
                                size="xs"
                            >
                                {{ __('Ver') }} &rarr;
                            </flux:button>
                        @endcan
                    </div>
                @endforeach
            </div>
        </x-agro.card>
    @else
        <x-agro.empty-state
            :message="__('No hay actividades registradas')"
            :description="__('Esta campaña aún no tiene actividades registradas')"
            icon="document-text"
            data-cy="no-activities-message"
        >
            <x-slot name="action">
                @can('create', \App\Models\AgriculturalActivity::class)
                    <flux:button
                        href="{{ roleRoute('viticulturist.digital-notebook.treatment.create') }}"
                        variant="primary"
                        icon="plus"
                        data-cy="register-first-activity-button"
                    >
                        {{ __('Registrar Primera Actividad') }}
                    </flux:button>
                @endcan
            </x-slot>
        </x-agro.empty-state>
    @endif
</div>
