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
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    @endphp
    <x-page-header
        :icon="$icon"
        :title="$machinery->name"
        :description="$machinery->type"
        icon-color="from-[var(--color-agro-brown)] to-[var(--color-agro-brown-light)]"
    >
        <x-slot:actionButton>
            <div class="flex items-center gap-3">
                @can('update', $machinery)
                    <a href="{{ route('viticulturist.machinery.edit', $machinery) }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all font-semibold">
                        Editar
                    </a>
                @endcan
                <a href="{{ route('viticulturist.machinery.index') }}" class="px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition-all font-semibold">
                    Volver
                </a>
            </div>
        </x-slot:actionButton>
    </x-page-header>

    <!-- Información Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Detallada -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Información Básica
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Tipo</p>
                        <p class="text-base font-bold text-gray-900">{{ $machinery->type }}</p>
                    </div>
                    @if($machinery->brand || $machinery->model)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Marca / Modelo</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->brand }} {{ $machinery->model }}</p>
                        </div>
                    @endif
                    @if($machinery->serial_number)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Número de Serie</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->serial_number }}</p>
                        </div>
                    @endif
                    @if($machinery->year)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Año</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->year }}</p>
                        </div>
                    @endif
                    @if($machinery->roma_registration)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Inscripción ROMA</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->roma_registration }}</p>
                        </div>
                    @endif
                    @if($machinery->capacity)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Capacidad</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->capacity }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fechas y Valores -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Fechas y Valores
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($machinery->purchase_date)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Fecha de Compra</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->purchase_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($machinery->last_revision_date)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Última Revisión</p>
                            <p class="text-base font-bold text-gray-900">{{ $machinery->last_revision_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($machinery->purchase_price)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Precio de Compra</p>
                            <p class="text-base font-bold text-gray-900">{{ number_format($machinery->purchase_price, 2, ',', '.') }} €</p>
                        </div>
                    @endif
                    @if($machinery->current_value)
                        <div>
                            <p class="text-sm font-semibold text-gray-500">Valor Actual</p>
                            <p class="text-base font-bold text-gray-900">{{ number_format($machinery->current_value, 2, ',', '.') }} €</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notas -->
            @if($machinery->notes)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Notas
                    </h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $machinery->notes }}</p>
                </div>
            @endif

            <!-- Actividades Recientes -->
            @if($recentActivities->count() > 0)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Actividades Recientes ({{ $machinery->activities_count }})
                    </h3>
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <div class="border-l-4 border-[var(--color-agro-brown)] pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $activity->plot->name ?? 'Parcela eliminada' }}</p>
                                        <p class="text-sm text-gray-600">{{ $activity->activity_date->format('d/m/Y') }} - {{ ucfirst($activity->activity_type) }}</p>
                                    </div>
                                    @if($activity->campaign)
                                        <span class="text-xs font-semibold text-gray-500">{{ $activity->campaign->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estado -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4">Estado</h3>
                <div class="space-y-3">
                    @if($machinery->active)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-green-50 text-green-700 ring-1 ring-green-600/20">
                            Activa
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-gray-50 text-gray-700 ring-1 ring-gray-600/20">
                            Inactiva
                        </span>
                    @endif
                    @if($machinery->is_rented)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">
                            Alquilada
                        </span>
                    @endif
                </div>
            </div>

            <!-- Imagen -->
            @if($machinery->image)
                <div class="glass-card rounded-xl p-6">
                    <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4">Imagen</h3>
                    <img 
                        src="{{ Storage::url($machinery->image) }}" 
                        alt="{{ $machinery->name }}" 
                        loading="lazy"
                        decoding="async"
                        class="w-full rounded-lg border-2 border-gray-200"
                    >
                </div>
            @endif

            <!-- Estadísticas -->
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-bold text-[var(--color-agro-brown-dark)] mb-4">Estadísticas</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Actividades Registradas</p>
                        <p class="text-2xl font-bold text-[var(--color-agro-brown-dark)]">{{ $machinery->activities_count }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
