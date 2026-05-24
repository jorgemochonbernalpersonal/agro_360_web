<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        :title="($planting->grapeVariety?->name ?? $planting->name) . ' · ' . $vintageYear"
        :description="__('Parcela: :plot · :area ha', ['plot' => $planting->plot?->name ?? '—', 'area' => number_format($planting->area_planted, 2)])"
        :back="roleRoute('viticulturist.harvests.index')"
    />

    {{-- Resumen global --}}
    <div class="{{ $cupoKg ? 'grid-cols-4' : 'grid-cols-3' }} grid gap-4">
        <x-agro.stat-card
            :label="__('Cosechado (cuaderno)')"
            :value="$totalNotebook > 0 ? number_format($totalNotebook, 0) . ' kg' : '—'"
            icon="document-text"
            color="agro"
        />
        <x-agro.stat-card
            :label="__('Recibido por bodega')"
            :value="$totalWinery > 0 ? number_format($totalWinery, 0) . ' kg' : '—'"
            icon="building-storefront"
            color="blue"
        />
        <x-agro.stat-card
            :label="__('Diferencia')"
            :value="$globalDiscrepancy !== null ? number_format($globalDiscrepancy, 0) . ' kg' : '—'"
            icon="arrows-right-left"
            :color="$globalDiscrepancy > 0 ? 'amber' : 'green'"
        />
        @if($cupoKg)
            <x-agro.stat-card
                :label="__('Cupo PAC')"
                :value="number_format($cupoKg, 0) . ' kg'"
                icon="shield-check"
                :color="$cupoExceeded ? 'red' : ($cupoPct >= 80 ? 'amber' : 'zinc')"
            />
        @endif
    </div>

    {{-- Barra de uso del cupo --}}
    @if($cupoKg && $cupoPct !== null)
        @php
            $barPct   = min($cupoPct, 100);
            $barColor = $cupoExceeded ? 'bg-red-500' : ($cupoPct >= 80 ? 'bg-amber-400' : 'bg-agro-500');
            $textColor = $cupoExceeded ? 'text-red-700' : ($cupoPct >= 80 ? 'text-amber-700' : 'text-zinc-600');
        @endphp
        <div class="bg-white border border-zinc-200 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-2">
                    <flux:icon icon="shield-check" class="size-4 {{ $cupoExceeded ? 'text-red-500' : 'text-zinc-400' }}" />
                    <span class="text-sm font-semibold text-zinc-700">{{ __('Uso del cupo PAC') }}</span>
                    <span class="text-xs text-zinc-400">
                        · {{ number_format($totalWinery > 0 ? $totalWinery : $totalDeclared, 0) }} kg
                        {{ __('de :total kg permitidos', ['total' => number_format($cupoKg, 0)]) }}
                    </span>
                </div>
                <span class="text-sm font-bold {{ $textColor }}">
                    {{ $cupoPct }}%
                    @if($cupoExceeded)
                        &nbsp;·&nbsp;<span class="text-red-600 font-semibold">{{ __('EXCEDIDO') }}</span>
                    @endif
                </span>
            </div>
            <div class="w-full bg-zinc-100 rounded-full h-2 overflow-hidden">
                <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $barPct }}%"></div>
            </div>
            @if($cupoExceeded)
                <p class="text-xs text-red-600 mt-2 flex items-center gap-1.5">
                    <flux:icon icon="exclamation-triangle" class="size-3.5 shrink-0" />
                    {{ __('Has superado el cupo permitido en :kg kg. Revisa con tu asesor o con la Denominación de Origen.', ['kg' => number_format(($totalWinery > 0 ? $totalWinery : $totalDeclared) - $cupoKg, 0)]) }}
                </p>
            @elseif($cupoPct >= 80)
                <p class="text-xs text-amber-600 mt-2 flex items-center gap-1.5">
                    <flux:icon icon="exclamation-triangle" class="size-3.5 shrink-0" />
                    {{ __('Estás cerca del límite del cupo (:kg kg restantes).', ['kg' => number_format($cupoKg - ($totalWinery > 0 ? $totalWinery : $totalDeclared), 0)]) }}
                </p>
            @endif
        </div>
    @endif

    {{-- ═══ Recepciones de bodega ═══ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <flux:icon icon="building-storefront" class="size-4 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ __('Recepciones de bodega') }}</p>
                    <p class="text-xs text-zinc-400">{{ __('Lo que la bodega ha registrado de tus entregas') }}</p>
                </div>
                <span class="ml-auto text-xs font-semibold text-blue-700">
                    {{ number_format($totalWinery, 0) }} kg {{ __('total') }}
                </span>
            </div>
        </x-slot:header>

        @if($wineryReceptions->count() > 0)
            <div class="divide-y divide-zinc-100">
                @foreach($wineryReceptions as $reception)
                    @php
                        $delivery = $reception->delivery;
                        $statusConfig = match($delivery?->status) {
                            'matched'  => ['color' => 'green', 'label' => __('Coincide')],
                            'disputed' => ['color' => 'amber', 'label' => __('Diferencia')],
                            'resolved' => ['color' => 'blue',  'label' => __('Resuelta')],
                            default    => ['color' => null,    'label' => null],
                        };
                    @endphp
                    <div class="py-3 flex items-start gap-4">
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Bodega') }}</p>
                                <p class="text-zinc-800 font-medium">{{ $reception->winery?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Fecha') }}</p>
                                <p class="text-zinc-700">{{ $reception->harvest_start_date?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Kg recibidos') }}</p>
                                <p class="text-blue-700 font-bold">{{ number_format($reception->total_weight, 0) }} kg</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Calidad') }}</p>
                                <p class="text-zinc-700">
                                    @if($reception->baume_degree)
                                        {{ $reception->baume_degree }}° Baumé
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Estado del enlace con entrega declarada --}}
                        <div class="shrink-0">
                            @if($statusConfig['label'])
                                <flux:badge color="{{ $statusConfig['color'] }}" size="sm">
                                    {{ $statusConfig['label'] }}
                                    @if($delivery?->discrepancy_kg > 0)
                                        · {{ number_format($delivery->discrepancy_kg, 0) }} kg
                                    @endif
                                </flux:badge>
                            @else
                                <flux:badge size="sm">{{ __('Sin declaración') }}</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Detalle sanitario si existe --}}
                    @if($reception->disqualified)
                        <div class="pb-3 -mt-1">
                            <p class="text-xs text-red-600 flex items-center gap-1.5">
                                <flux:icon icon="x-circle" class="size-3.5" />
                                {{ __('Descalificado: :reason', ['reason' => $reception->disqualified_reason ?? __('sin motivo')]) }}
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-4 text-center">{{ __('La bodega aún no ha registrado ninguna recepción para esta plantación.') }}</p>
        @endif
    </x-agro.card>

    {{-- ═══ Entregas declaradas por el viticultor ═══ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-violet-50 rounded-lg flex items-center justify-center">
                    <flux:icon icon="truck" class="size-4 text-violet-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ __('Mis entregas declaradas') }}</p>
                    <p class="text-xs text-zinc-400">{{ __('Lo que tú has registrado como entregado') }}</p>
                </div>
                <flux:button
                    href="{{ roleRoute('viticulturist.harvests.delivery.create', ['planting' => $planting->id, 'vintage' => $vintageYear]) }}"
                    variant="ghost" size="sm" icon="plus" class="ml-auto">
                    {{ __('Nueva entrega') }}
                </flux:button>
            </div>
        </x-slot:header>

        @if($declaredDeliveries->count() > 0)
            <div class="divide-y divide-zinc-100">
                @foreach($declaredDeliveries as $delivery)
                    @php
                        $badgeConfig = match($delivery->status) {
                            'matched'  => ['color' => 'green',  'label' => __('Confirmada por bodega')],
                            'disputed' => ['color' => 'amber',  'label' => __('Con diferencia (:kg kg)', ['kg' => number_format($delivery->discrepancy_kg, 0)])],
                            'resolved' => ['color' => 'blue',   'label' => __('Resuelta por bodega')],
                            default    => ['color' => null,     'label' => __('Pendiente de confirmar')],
                        };
                    @endphp
                    <div class="py-3 flex items-start gap-4">
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Comprador') }}</p>
                                <p class="text-zinc-800 font-medium {{ $delivery->disqualified ? 'line-through text-zinc-400' : '' }}">
                                    {{ $delivery->buyer_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Fecha') }}</p>
                                <p class="text-zinc-700">{{ $delivery->delivery_date?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Kg declarados') }}</p>
                                <p class="{{ $delivery->disqualified ? 'text-red-400 line-through' : 'text-violet-700 font-bold' }}">
                                    {{ number_format($delivery->delivered_kg, 0) }} kg
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Ticket') }}</p>
                                <p class="text-zinc-500 font-mono text-xs">{{ $delivery->ticket_number ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            @if($delivery->disqualified)
                                <flux:badge color="red" size="sm">{{ __('Descartada') }}</flux:badge>
                            @else
                                <flux:badge color="{{ $badgeConfig['color'] }}" size="sm">{{ $badgeConfig['label'] }}</flux:badge>
                            @endif
                            <a href="{{ roleRoute('viticulturist.harvests.delivery.albaran', $delivery) }}"
                               target="_blank"
                               class="p-1.5 rounded-lg text-zinc-400 hover:text-violet-600 hover:bg-violet-50 transition-colors"
                               title="{{ __('Descargar albarán PDF') }}">
                                <flux:icon icon="document-arrow-down" class="size-4" />
                            </a>
                            <a href="{{ roleRoute('viticulturist.harvests.delivery.edit', $delivery) }}"
                               class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors"
                               title="{{ __('Editar') }}">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                        </div>
                    </div>

                    {{-- Bloque de disputa / resolución --}}
                    @if(in_array($delivery->status, ['disputed', 'resolved']) && !$delivery->disqualified)
                        <div class="pb-3 -mt-1 space-y-2">

                            {{-- Nota enviada por el viticultor --}}
                            @if($delivery->hasDisputeNote())
                                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <flux:icon icon="exclamation-triangle" class="size-3.5 text-amber-600 shrink-0" />
                                        <p class="text-xs font-semibold text-amber-700">
                                            {{ __('Reclamación enviada el :date', ['date' => $delivery->dispute_submitted_at->format('d/m/Y \a \l\a\s H:i')]) }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-amber-800 whitespace-pre-line">{{ $delivery->dispute_note }}</p>
                                </div>
                            @elseif($disputingId === $delivery->id)
                                {{-- Formulario activo --}}
                                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 space-y-3">
                                    <p class="text-xs font-semibold text-amber-700 flex items-center gap-1.5">
                                        <flux:icon icon="exclamation-triangle" class="size-3.5" />
                                        {{ __('Explica la diferencia de :kg kg', ['kg' => number_format($delivery->discrepancy_kg, 0)]) }}
                                    </p>
                                    <textarea
                                        wire:model="disputeNote"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="{{ __('Describe el motivo de la reclamación: pesaje incorrecto, mermas no acordadas, ticket erróneo...') }}"
                                        class="w-full text-xs rounded-lg border border-amber-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                                    ></textarea>
                                    @error('disputeNote')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div class="flex gap-2 justify-end">
                                        <flux:button wire:click="cancelDispute" variant="ghost" size="sm">
                                            {{ __('Cancelar') }}
                                        </flux:button>
                                        <flux:button wire:click="submitDispute" variant="primary" size="sm" icon="paper-airplane">
                                            {{ __('Enviar reclamación') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @else
                                {{-- Botón para abrir disputa --}}
                                <button
                                    wire:click="openDispute({{ $delivery->id }})"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg px-3 py-1.5 transition-colors"
                                >
                                    <flux:icon icon="exclamation-triangle" class="size-3.5" />
                                    {{ __('Reclamar diferencia') }}
                                </button>
                            @endif

                            {{-- Respuesta de la bodega (resolved) --}}
                            @if($delivery->isResolved())
                                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <flux:icon icon="check-circle" class="size-3.5 text-blue-600 shrink-0" />
                                        <p class="text-xs font-semibold text-blue-700">
                                            {{ __('Respuesta de la bodega') }}
                                            <span class="font-normal text-blue-500">· {{ $delivery->dispute_resolved_at->format('d/m/Y \a \l\a\s H:i') }}</span>
                                        </p>
                                    </div>
                                    <p class="text-xs text-blue-800 whitespace-pre-line">{{ $delivery->dispute_resolution_note }}</p>
                                </div>
                            @endif

                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="py-6 text-center">
                <p class="text-sm text-zinc-400 mb-3">{{ __('Aún no has declarado ninguna entrega para esta plantación.') }}</p>
                <flux:button
                    href="{{ roleRoute('viticulturist.harvests.delivery.create', ['planting' => $planting->id, 'vintage' => $vintageYear]) }}"
                    variant="outline" icon="plus" size="sm">
                    {{ __('Registrar primera entrega') }}
                </flux:button>
            </div>
        @endif
    </x-agro.card>

    {{-- ═══ Registros del cuaderno ═══ --}}
    <x-agro.card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-agro-50 rounded-lg flex items-center justify-center">
                    <flux:icon icon="document-text" class="size-4 text-agro-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ __('Registros del cuaderno de campo') }}</p>
                    <p class="text-xs text-zinc-400">{{ __('Cosechas anotadas en tu cuaderno digital') }}</p>
                </div>
                <span class="ml-auto text-xs font-semibold text-agro-700">
                    {{ number_format($totalNotebook, 0) }} kg {{ __('total') }}
                </span>
            </div>
        </x-slot:header>

        @if($notebookHarvests->count() > 0)
            <div class="divide-y divide-zinc-100">
                @foreach($notebookHarvests as $harvest)
                    <div class="py-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Fecha') }}</p>
                            <p class="text-zinc-700">{{ $harvest->activity?->activity_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Kg cosechados') }}</p>
                            <p class="text-agro-700 font-bold">{{ number_format($harvest->total_weight, 0) }} kg</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Rendimiento') }}</p>
                            <p class="text-zinc-600">
                                {{ $harvest->yield_per_hectare ? number_format($harvest->yield_per_hectare, 0) . ' kg/ha' : '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wide mb-0.5">{{ __('Calidad') }}</p>
                            <p class="text-zinc-600">
                                {{ $harvest->baume_degree ? $harvest->baume_degree . '° Baumé' : '—' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-400 py-4 text-center">{{ __('No hay registros de cosecha en el cuaderno para esta plantación y añada.') }}</p>
        @endif
    </x-agro.card>

</div>
