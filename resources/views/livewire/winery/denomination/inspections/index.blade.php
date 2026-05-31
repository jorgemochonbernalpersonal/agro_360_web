<div class="space-y-6 animate-fade-in">

    @if(!$embedded)
    <x-agro.page-header
        title="{{ __('Inspecciones DO') }}"
        :description="__('Inspecciones realizadas por tu Denominación de Origen sobre esta bodega.')"
        icon="shield-check"
    />
    @endif

    {{-- Filtro estado --}}
    <div class="flex flex-wrap gap-3">
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
            ['key' => 'all',         'label' => 'Total',        'color' => 'bg-zinc-100 text-zinc-700'],
            ['key' => 'scheduled',   'label' => 'Programadas',  'color' => 'bg-blue-100 text-blue-700'],
            ['key' => 'in_progress', 'label' => 'En curso',     'color' => 'bg-amber-100 text-amber-700'],
            ['key' => 'completed',   'label' => 'Completadas',  'color' => 'bg-green-100 text-green-700'],
        ] as $pill)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $pill['color'] }}">
                {{ $counts[$pill['key']] }} {{ $pill['label'] }}
            </span>
        @endforeach
    </div>

    {{-- Lista --}}
    <x-agro.card>
        @forelse($inspections as $inspection)
            @php
                $resultColor = $resultColors[$inspection->result] ?? 'zinc';
            @endphp
            <div class="flex items-start gap-4 py-3 border-b border-zinc-100 last:border-0">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center shrink-0">
                    <span class="text-[10px] font-bold leading-none text-indigo-700">{{ $inspection->inspection_date->format('d') }}</span>
                    <span class="text-[9px] uppercase leading-none text-indigo-500">{{ $inspection->inspection_date->format('M') }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-zinc-800">
                        {{ $inspection->reference_number ?? 'Inspección #' . $inspection->id }}
                    </p>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        {{ $inspection->inspection_date->format('d/m/Y') }}
                        @if($inspection->supervisor)
                            · {{ $inspection->supervisor->name }}
                        @endif
                    </p>
                    @if($inspection->result && $inspection->result !== 'pending')
                        <p class="text-xs font-medium mt-1 text-{{ $resultColor }}-600">
                            {{ $resultLabels[$inspection->result] ?? $inspection->result }}
                        </p>
                    @endif
                    @if($inspection->findings)
                        <p class="text-xs text-zinc-500 mt-0.5 line-clamp-2">{{ $inspection->findings }}</p>
                    @endif
                </div>
                <x-agro.status-badge :status="$inspection->status" />
            </div>
        @empty
            <x-agro.empty-state
                icon="shield-check"
                title="{{ __('Sin inspecciones') }}"
                :description="__('Tu denominación de origen aún no ha registrado inspecciones para esta bodega.')"
            />
        @endforelse
    </x-agro.card>

    <x-agro.pagination :paginator="$inspections" />

</div>
