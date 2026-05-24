<div class="space-y-6">
    {{-- Back link --}}
    <div>
        <a href="{{ roleRoute('viticulturist.pest-management.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-agro-600 hover:text-agro-800 transition-colors">
            <flux:icon icon="arrow-left" class="size-4" />
            {{ __('Volver al catálogo') }}
        </a>
    </div>

    {{-- Header --}}
    <x-agro-card>
        <div class="flex items-start gap-4">
            <span class="text-6xl leading-none">{{ $pest->icon }}</span>
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold text-zinc-900">{{ $pest->name }}</h1>
                @if($pest->scientific_name)
                    <p class="text-lg text-zinc-500 italic mt-1">{{ $pest->scientific_name }}</p>
                @endif
                <div class="flex items-center gap-2 mt-3 flex-wrap">
                    <flux:badge color="{{ $pest->type === 'pest' ? 'orange' : 'purple' }}" size="sm">
                        {{ $pest->type === 'pest' ? '🐛 ' . __('Plaga') : '🦠 ' . __('Enfermedad') }}
                    </flux:badge>
                    @if($pest->isInRiskPeriod())
                        <flux:badge color="yellow" size="sm" data-cy="risk-period-badge">⚠️ {{ __('En período de riesgo') }}</flux:badge>
                    @endif
                </div>
            </div>
        </div>
    </x-agro-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Descripción --}}
            @if($pest->description)
                <x-agro-card>
                    <h2 class="text-base font-semibold text-zinc-900 mb-3">📝 {{ __('Descripción') }}</h2>
                    <p class="text-zinc-700">{{ $pest->description }}</p>
                </x-agro-card>
            @endif

            {{-- Síntomas --}}
            @if($pest->symptoms)
                <x-agro-card>
                    <h2 class="text-base font-semibold text-zinc-900 mb-3">🔍 {{ __('Síntomas y Signos') }}</h2>
                    <p class="text-zinc-700">{{ $pest->symptoms }}</p>
                </x-agro-card>
            @endif

            {{-- Ciclo de Vida --}}
            @if($pest->lifecycle)
                <x-agro-card>
                    <h2 class="text-base font-semibold text-zinc-900 mb-3">🔄 {{ __('Ciclo de Vida') }}</h2>
                    <p class="text-zinc-700">{{ $pest->lifecycle }}</p>
                </x-agro-card>
            @endif

            {{-- Prevención --}}
            @if($pest->prevention_methods)
                <x-agro-card data-cy="prevention-methods-section">
                    <h2 class="text-base font-semibold text-zinc-900 mb-3">🛡️ {{ __('Métodos de Prevención') }}</h2>
                    <p class="text-zinc-700">{{ $pest->prevention_methods }}</p>
                </x-agro-card>
            @endif

            {{-- Métodos de Control IPM (PAC) --}}
            @if($pest->control_methods && count($pest->control_methods) > 0)
                <x-agro-card data-cy="control-methods-section">
                    <h2 class="text-base font-semibold text-zinc-900 mb-3">⚙️ {{ __('Métodos de Control IPM (PAC)') }}</h2>
                    <p class="text-xs text-zinc-500 mb-3">{{ __('Ordenados por prioridad según el Plan de Acción Comunitario') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pest->control_methods as $method)
                            @php
                                $colors = [
                                    'biologico' => 'bg-green-100 text-green-800 border-green-200',
                                    'cultural'  => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'fisico'    => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'quimico'   => 'bg-red-100 text-red-800 border-red-200',
                                ];
                                $labels = \App\Models\Pest::CONTROL_METHOD_LABELS;
                                $colorClass = $colors[$method] ?? 'bg-zinc-100 text-zinc-800 border-zinc-200';
                                $label = $labels[$method] ?? $method;
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border {{ $colorClass }}"
                                  data-cy="control-method-{{ $method }}">
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </x-agro-card>
            @endif

            {{-- Productos Eficaces --}}
            @if($pest->products->count() > 0)
                <x-agro-card>
                    <h2 class="text-base font-semibold text-zinc-900 mb-4">💊 {{ __('Productos Fitosanitarios Eficaces') }}</h2>
                    <div class="space-y-3">
                        @foreach($pest->products as $product)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-zinc-900">{{ $product->commercial_name }}</p>
                                    <p class="text-sm text-zinc-600">{{ $product->active_substance }}</p>
                                </div>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <flux:icon icon="star" variant="solid" class="size-5 {{ $i <= $product->pivot->effectiveness_rating ? 'text-yellow-400' : 'text-zinc-300' }}" />
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-agro-card>
            @endif
        </div>

        {{-- Columna Lateral --}}
        <div class="space-y-6">
            {{-- Umbral de Tratamiento --}}
            @if($pest->threshold)
                <div class="bg-blue-50 rounded-xl p-4 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-blue-900 mb-2">📊 {{ __('Umbral de Tratamiento') }}</h3>
                    <p class="text-sm text-blue-800">{{ $pest->threshold }}</p>
                </div>
            @endif

            {{-- Meses de Riesgo --}}
            @if($pest->risk_months && count($pest->risk_months) > 0)
                <x-agro-card>
                    <h3 class="font-semibold text-zinc-900 mb-3">📅 {{ __('Meses de Riesgo') }}</h3>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([__('Ene'), __('Feb'), __('Mar'), __('Abr'), __('May'), __('Jun'), __('Jul'), __('Ago'), __('Sep'), __('Oct'), __('Nov'), __('Dic')] as $index => $month)
                            <div class="text-center p-2 rounded text-xs font-medium {{ in_array($index + 1, $pest->risk_months) ? 'bg-red-100 text-red-800 font-semibold' : 'bg-zinc-100 text-zinc-500' }}">
                                {{ $month }}
                            </div>
                        @endforeach
                    </div>
                </x-agro-card>
            @endif

            {{-- Acciones Rápidas --}}
            <x-agro-card>
                <h3 class="font-semibold text-zinc-900 mb-3">⚡ {{ __('Acciones Rápidas') }}</h3>
                <div class="space-y-2">
                    <a href="{{ roleRoute('viticulturist.digital-notebook.observation.create', ['pest_id' => $pest->id]) }}"
                       class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                        <flux:icon icon="eye" class="size-4" />
                        {{ __('Registrar Observación') }}
                    </a>
                    <a href="{{ roleRoute('viticulturist.digital-notebook.treatment.create', ['pest_id' => $pest->id]) }}"
                       class="flex items-center justify-center gap-2 w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                        <flux:icon icon="beaker" class="size-4" />
                        {{ __('Registrar Tratamiento') }}
                    </a>
                </div>
            </x-agro-card>

            {{-- Historial --}}
            @if($pest->observations->count() > 0 || $pest->treatments->count() > 0)
                <x-agro-card>
                    <h3 class="font-semibold text-zinc-900 mb-3">📊 {{ __('Historial') }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Observaciones:') }}</span>
                            <span class="font-semibold text-zinc-900">{{ $pest->observations->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Tratamientos:') }}</span>
                            <span class="font-semibold text-zinc-900">{{ $pest->treatments->count() }}</span>
                        </div>
                    </div>
                </x-agro-card>
            @endif
        </div>
    </div>
</div>
