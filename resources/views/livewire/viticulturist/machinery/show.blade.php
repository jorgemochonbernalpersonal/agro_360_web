<div class="space-y-6 animate-fade-in">
    <!-- Mensajes Flash -->
    @if(session('message'))
        <flux:callout variant="success">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout variant="danger">
            {{ session('error') }}
        </flux:callout>
    @endif

    <!-- Header -->
    <x-agro.page-header
        :title="$machinery->name"
        :description="$machinery->type"
    >
        <x-slot:actions>
            @can('update', $machinery)
                <flux:button href="{{ roleRoute('viticulturist.machinery.edit', $machinery) }}" variant="primary">
                    Editar
                </flux:button>
            @endcan
            <flux:button href="{{ roleRoute('viticulturist.machinery.index') }}" variant="outline">
                Volver
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <!-- Informacion Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informacion Detallada -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informacion Basica -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-agro-50">
                            <flux:icon icon="information-circle" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Informacion Basica') }}</span>
                    </div>
                </x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-semibold text-zinc-500">{{ __('Tipo') }}</p>
                        <p class="text-base font-bold text-zinc-900">{{ $machinery->type }}</p>
                    </div>
                    @if($machinery->brand || $machinery->model)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Marca / Modelo') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->brand }} {{ $machinery->model }}</p>
                        </div>
                    @endif
                    @if($machinery->serial_number)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Numero de Serie') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->serial_number }}</p>
                        </div>
                    @endif
                    @if($machinery->year)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Ano') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->year }}</p>
                        </div>
                    @endif
                    @if($machinery->roma_registration)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Inscripcion ROMA') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->roma_registration }}</p>
                        </div>
                    @endif
                    @if($machinery->capacity)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Capacidad') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->capacity }}</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            <!-- Fechas y Valores -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="calendar" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Fechas y Valores') }}</span>
                    </div>
                </x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($machinery->purchase_date)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Fecha de Compra') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->purchase_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($machinery->last_revision_date)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Ultima Revision') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ $machinery->last_revision_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                    @if($machinery->purchase_price)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Precio de Compra') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ number_format($machinery->purchase_price, 2, ',', '.') }} EUR</p>
                        </div>
                    @endif
                    @if($machinery->current_value)
                        <div>
                            <p class="text-sm font-semibold text-zinc-500">{{ __('Valor Actual') }}</p>
                            <p class="text-base font-bold text-zinc-900">{{ number_format($machinery->current_value, 2, ',', '.') }} EUR</p>
                        </div>
                    @endif
                </div>
            </x-agro.card>

            <!-- Notas -->
            @if($machinery->notes)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-zinc-50">
                                <flux:icon icon="document-text" class="size-4 text-zinc-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">{{ __('Notas') }}</span>
                        </div>
                    </x-slot:header>
                    <p class="text-zinc-700 whitespace-pre-wrap">{{ $machinery->notes }}</p>
                </x-agro.card>
            @endif

            <!-- Actividades Recientes -->
            @if($recentActivities->count() > 0)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-agro-50">
                                <flux:icon icon="clipboard-document-list" class="size-4 text-agro-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">Actividades Recientes ({{ $machinery->activities_count }})</span>
                        </div>
                    </x-slot:header>
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <div class="border-l-4 border-agro-500 pl-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-zinc-900">{{ $activity->plot->name ?? 'Parcela eliminada' }}</p>
                                        <p class="text-sm text-zinc-600">{{ $activity->activity_date->format('d/m/Y') }} - {{ ucfirst($activity->activity_type) }}</p>
                                    </div>
                                    @if($activity->campaign)
                                        <span class="text-xs font-semibold text-zinc-500">{{ $activity->campaign->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-agro.card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estado -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-agro-50">
                            <flux:icon icon="check-circle" class="size-4 text-agro-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Estado') }}</span>
                    </div>
                </x-slot:header>
                <div class="flex flex-wrap gap-2">
                    <flux:badge :color="$machinery->active ? 'green' : null">
                        {{ $machinery->active ? 'Activa' : 'Inactiva' }}
                    </flux:badge>
                    @if($machinery->is_rented)
                        <flux:badge color="blue">{{ __('Alquilada') }}</flux:badge>
                    @endif
                </div>
            </x-agro.card>

            <!-- Imagen -->
            @if($machinery->image)
                <x-agro.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-zinc-50">
                                <flux:icon icon="photo" class="size-4 text-zinc-600" />
                            </div>
                            <span class="font-semibold text-zinc-900 text-sm">{{ __('Imagen') }}</span>
                        </div>
                    </x-slot:header>
                    <img
                        src="{{ Storage::url($machinery->image) }}"
                        alt="{{ $machinery->name }}"
                        loading="lazy"
                        decoding="async"
                        class="w-full rounded-lg border-2 border-zinc-200"
                    >
                </x-agro.card>
            @endif

            <!-- Estadisticas -->
            <x-agro.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-blue-50">
                            <flux:icon icon="chart-bar" class="size-4 text-blue-600" />
                        </div>
                        <span class="font-semibold text-zinc-900 text-sm">{{ __('Estadisticas') }}</span>
                    </div>
                </x-slot:header>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-500">{{ __('Actividades Registradas') }}</p>
                        <p class="text-2xl font-bold text-agro-700">{{ $machinery->activities_count }}</p>
                    </div>
                </div>
            </x-agro.card>
        </div>
    </div>
</div>
