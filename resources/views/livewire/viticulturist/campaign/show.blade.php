<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <div class="glass-card rounded-xl p-4 bg-green-50 border-l-4 border-green-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-green-800">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card rounded-xl p-4 bg-red-50 border-l-4 border-red-600">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        :title="$campaign->name"
        :description="'Campaña del año ' . $campaign->year"
        icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                @if(!$campaign->active)
                    @can('activate', $campaign)
                        <button 
                            wire:click="activate"
                            class="px-4 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700 transition-all font-semibold"
                        >
                            Activar Campaña
                        </button>
                    @endcan
                @endif
                @can('update', $campaign)
                    <a href="{{ route('viticulturist.campaign.edit', $campaign) }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all font-semibold">
                        Editar
                    </a>
                @endcan
                <a href="{{ route('viticulturist.campaign.index') }}" class="px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold">
                    Volver
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Estadísticas -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Estadísticas de la Campaña</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-[var(--color-agro-green-dark)]">{{ $campaign->activities_count }}</div>
                <div class="text-sm text-gray-600">Total</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $campaign->phytosanitary_count }}</div>
                <div class="text-sm text-gray-600">Tratamientos</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $campaign->fertilization_count }}</div>
                <div class="text-sm text-gray-600">Fertilizaciones</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-cyan-600">{{ $campaign->irrigation_count }}</div>
                <div class="text-sm text-gray-600">Riegos</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $campaign->cultural_count }}</div>
                <div class="text-sm text-gray-600">Labores</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $campaign->observation_count }}</div>
                <div class="text-sm text-gray-600">Observaciones</div>
            </div>
        </div>
    </div>

    <!-- Información de la Campaña -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Información General -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Información General</h3>
            <div class="space-y-4">
                <div>
                    <span class="text-sm font-semibold text-gray-600">Estado:</span>
                    @if($campaign->active)
                        <span class="ml-2 inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-green-50 text-green-700 ring-1 ring-green-600/20">
                            Activa
                        </span>
                    @else
                        <span class="ml-2 inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-50 text-gray-700 ring-1 ring-gray-600/20">
                            Inactiva
                        </span>
                    @endif
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-600">Año:</span>
                    <span class="ml-2 text-sm text-gray-900 font-medium">{{ $campaign->year }}</span>
                </div>
                @if($campaign->start_date && $campaign->end_date)
                    <div>
                        <span class="text-sm font-semibold text-gray-600">Período:</span>
                        <span class="ml-2 text-sm text-gray-900">
                            {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                        </span>
                    </div>
                @endif
                @if($campaign->description)
                    <div>
                        <span class="text-sm font-semibold text-gray-600">Descripción:</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $campaign->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)] mb-4">Acciones Rápidas</h3>
            <div class="space-y-3">
                <a 
                    href="{{ route('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                    class="block w-full px-4 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all font-semibold text-center"
                >
                    Ver Actividades en Cuaderno Digital
                </a>
                @can('create', \App\Models\AgriculturalActivity::class)
                    <div class="grid grid-cols-2 gap-2">
                        <a 
                            href="{{ route('viticulturist.digital-notebook.treatment.create') }}"
                            class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition text-sm font-semibold text-center"
                        >
                            + Tratamiento
                        </a>
                        <a 
                            href="{{ route('viticulturist.digital-notebook.fertilization.create') }}"
                            class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-semibold text-center"
                        >
                            + Fertilización
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Últimas Actividades -->
    @if($recentActivities->count() > 0)
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-[var(--color-agro-green-dark)]">Últimas Actividades</h3>
                <a 
                    href="{{ route('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                    class="text-sm font-semibold text-[var(--color-agro-green-dark)] hover:underline"
                >
                    Ver todas →
                </a>
            </div>
            <div class="space-y-3">
                @foreach($recentActivities as $activity)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center gap-4">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $activity->activity_date->format('d/m/Y') }}
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $activity->plot->name }}</span>
                                <span class="ml-2 text-xs text-gray-500">
                                    @if($activity->activity_type === 'phytosanitary')
                                        Tratamiento
                                    @elseif($activity->activity_type === 'fertilization')
                                        Fertilización
                                    @elseif($activity->activity_type === 'irrigation')
                                        Riego
                                    @elseif($activity->activity_type === 'cultural')
                                        Labor
                                    @else
                                        Observación
                                    @endif
                                </span>
                            </div>
                        </div>
                        @can('view', $activity)
                            <a 
                                href="{{ route('viticulturist.digital-notebook', ['selectedCampaign' => $campaign->id]) }}"
                                class="text-xs font-semibold text-blue-600 hover:underline"
                            >
                                Ver →
                            </a>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="glass-card rounded-xl p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No hay actividades registradas</h3>
            <p class="text-gray-500 mb-6">Esta campaña aún no tiene actividades registradas</p>
            @can('create', \App\Models\AgriculturalActivity::class)
                <a 
                    href="{{ route('viticulturist.digital-notebook.treatment.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[var(--color-agro-green-dark)] to-[var(--color-agro-green)] text-white hover:from-[var(--color-agro-green)] hover:to-[var(--color-agro-green-dark)] transition-all shadow-lg hover:shadow-xl font-semibold"
                >
                    Registrar Primera Actividad
                </a>
            @endcan
        </div>
    @endif
</div>
