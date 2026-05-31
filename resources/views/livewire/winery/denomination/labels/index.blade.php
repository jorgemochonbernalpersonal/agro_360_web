<div class="space-y-6 animate-fade-in">

    @if(!$embedded)
    <x-agro.page-header
        title="{{ __('Contraetiquetas DO') }}"
        :description="__('Solicitudes de contraetiquetas emitidas por tu Denominación de Origen.')"
        icon="tag"
    />
    @endif

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3">
        <flux:select wire:model.live="vintageFilter">
            <flux:select.option value="">{{ __('Todas las añadas') }}</flux:select.option>
            @foreach($availableVintages as $v)
                <flux:select.option value="{{ $v }}">{{ $v }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            @foreach($statusLabels as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- KPI pills --}}
    <div class="flex flex-wrap gap-3">
        @foreach([
            ['key' => 'all',      'label' => 'Total',      'color' => 'bg-zinc-100 text-zinc-700'],
            ['key' => 'pending',  'label' => 'Pendientes',  'color' => 'bg-amber-100 text-amber-700'],
            ['key' => 'approved', 'label' => 'Aprobadas',   'color' => 'bg-blue-100 text-blue-700'],
            ['key' => 'issued',   'label' => 'Emitidas',    'color' => 'bg-green-100 text-green-700'],
        ] as $pill)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $pill['color'] }}">
                {{ $counts[$pill['key']] }} {{ $pill['label'] }}
            </span>
        @endforeach
    </div>

    {{-- Tabla --}}
    <x-agro.card>
        @forelse($labels as $label)
            <div class="flex items-center gap-4 py-3 border-b border-zinc-100 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-zinc-800">
                        Añada {{ $label->vintage }}
                        @if($label->batch_number)
                            · <span class="text-zinc-500">{{ $label->batch_number }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Solicitadas: <span class="font-medium text-zinc-600">{{ number_format($label->quantity_requested) }}</span>
                        @if($label->quantity_issued)
                            · Emitidas: <span class="font-medium text-zinc-600">{{ number_format($label->quantity_issued) }}</span>
                        @endif
                        @if($label->issued_at)
                            · {{ $label->issued_at->format('d/m/Y') }}
                        @endif
                    </p>
                    @if($label->notes)
                        <p class="text-xs text-zinc-400 mt-0.5 truncate">{{ $label->notes }}</p>
                    @endif
                </div>
                <x-agro.status-badge :status="$label->status" />
            </div>
        @empty
            <x-agro.empty-state
                icon="tag"
                title="{{ __('Sin contraetiquetas') }}"
                :description="__('Tu denominación de origen aún no ha registrado solicitudes de contraetiquetas para esta bodega.')"
            />
        @endforelse
    </x-agro.card>

    <x-agro.pagination :paginator="$labels" />

</div>
