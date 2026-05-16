<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Disputas abiertas"
        description="Entregas declaradas por viticultores con diferencia pendiente de respuesta"
    />

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">

        <flux:select wire:model.live="vintageFilter">
            <flux:select.option value="">Todas las añadas</flux:select.option>
            @foreach($vintageYears as $year)
                <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="noteFilter">
            <flux:select.option value="">Todas las reclamaciones</flux:select.option>
            <flux:select.option value="with_note">Con nota enviada</flux:select.option>
            <flux:select.option value="without_note">Sin nota aún</flux:select.option>
        </flux:select>

        @if($disputes->count() > 0)
            <span class="ml-auto text-sm text-zinc-500">
                {{ $disputes->count() }} {{ $disputes->count() === 1 ? 'disputa' : 'disputas' }}
            </span>
        @endif

    </div>

    {{-- Lista --}}
    @if($disputes->count() > 0)
        <div class="space-y-3" wire:loading.class="opacity-60 pointer-events-none">
            @foreach($disputes as $delivery)
                @php
                    $planting = $delivery->plotPlanting;
                    $variety  = $planting?->grapeVariety?->name ?? $planting?->name ?? '—';
                    $plot     = $planting?->plot?->name ?? '—';
                    $hasNote  = $delivery->hasDisputeNote();
                @endphp

                <x-agro.card wire:key="dispute-{{ $delivery->id }}">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                        {{-- Icono --}}
                        <div class="w-10 h-10 {{ $hasNote ? 'bg-amber-50' : 'bg-zinc-50' }} rounded-xl flex items-center justify-center shrink-0">
                            <flux:icon icon="exclamation-triangle" class="size-5 {{ $hasNote ? 'text-amber-500' : 'text-zinc-400' }}" />
                        </div>

                        {{-- Datos principales --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="font-semibold text-zinc-900 text-sm">{{ $delivery->viticulturist?->name ?? '—' }}</span>
                                <span class="text-zinc-300">·</span>
                                <span class="text-sm text-zinc-600">{{ $variety }}</span>
                                <span class="text-zinc-300">·</span>
                                <span class="text-sm text-zinc-500">{{ $plot }}</span>
                                <flux:badge color="zinc" size="sm">{{ $delivery->vintage_year }}</flux:badge>
                                @if($hasNote)
                                    <flux:badge color="amber" size="sm">Con reclamación</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Sin nota aún</flux:badge>
                                @endif
                            </div>

                            {{-- Diferencia --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-3">
                                <div>
                                    <p class="text-zinc-400 uppercase tracking-wide font-medium mb-0.5">Declarado</p>
                                    <p class="font-bold text-violet-700">{{ number_format($delivery->delivered_kg, 0) }} kg</p>
                                </div>
                                <div>
                                    <p class="text-zinc-400 uppercase tracking-wide font-medium mb-0.5">Recibido</p>
                                    <p class="font-bold text-blue-700">{{ $delivery->harvest ? number_format($delivery->harvest->total_weight, 0) . ' kg' : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-400 uppercase tracking-wide font-medium mb-0.5">Diferencia</p>
                                    <p class="font-bold text-amber-600">
                                        {{ number_format($delivery->discrepancy_kg, 0) }} kg
                                        @if($delivery->discrepancyPercentage())
                                            <span class="font-normal text-zinc-400">({{ $delivery->discrepancyPercentage() }}%)</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-zinc-400 uppercase tracking-wide font-medium mb-0.5">Ticket</p>
                                    <p class="font-mono text-zinc-600">{{ $delivery->ticket_number ?? '—' }}</p>
                                </div>
                            </div>

                            {{-- Nota del viticultor --}}
                            @if($hasNote)
                                <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 mb-2">
                                    <p class="text-[10px] font-semibold text-amber-600 mb-1 uppercase tracking-wide">
                                        Reclamación — {{ $delivery->dispute_submitted_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="text-xs text-amber-800 line-clamp-2">{{ $delivery->dispute_note }}</p>
                                </div>
                            @else
                                <p class="text-xs text-zinc-400 italic mb-2">El viticultor aún no ha enviado su nota de reclamación.</p>
                            @endif
                        </div>

                        {{-- Acción --}}
                        <div class="shrink-0">
                            @if($delivery->harvest_id)
                                <flux:button
                                    href="{{ roleRoute('grape-reception.show', $delivery->harvest_id) }}"
                                    variant="{{ $hasNote ? 'primary' : 'outline' }}"
                                    size="sm"
                                    icon="arrow-top-right-on-square"
                                >
                                    {{ $hasNote ? 'Responder' : 'Ver recepción' }}
                                </flux:button>
                            @endif
                        </div>

                    </div>
                </x-agro.card>
            @endforeach
        </div>

    @else
        <x-agro.empty-state
            icon="check-circle"
            message="Sin disputas abiertas"
            description="No hay entregas con diferencias pendientes de resolver{{ $vintageFilter ? ' para la añada ' . $vintageFilter : '' }}."
        />
    @endif

</div>
