<div class="space-y-6 animate-fade-in">

    @if(!$embedded)
    <x-agro.page-header
        title="Calificaciones DO"
        description="Calificaciones registradas por tu Denominación de Origen para los vinos de esta bodega."
        icon="star"
    />
    @endif

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3">
        <select wire:model.live="vintageFilter"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todas las añadas</option>
            @foreach($availableVintages as $v)
                <option value="{{ $v }}">{{ $v }}</option>
            @endforeach
        </select>

        <select wire:model.live="resultFilter"
                class="text-sm border border-zinc-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los resultados</option>
            @foreach($resultLabels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- KPI pills --}}
    <div class="flex flex-wrap gap-3">
        @foreach([
            ['key' => 'all',          'label' => 'Total',           'color' => 'bg-zinc-100 text-zinc-700'],
            ['key' => 'pending',      'label' => 'Pendientes',      'color' => 'bg-amber-100 text-amber-700'],
            ['key' => 'qualified',    'label' => 'Calificados DO',  'color' => 'bg-green-100 text-green-700'],
            ['key' => 'disqualified', 'label' => 'Descalificados',  'color' => 'bg-red-100 text-red-700'],
        ] as $pill)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $pill['color'] }}">
                {{ $counts[$pill['key']] }} {{ $pill['label'] }}
            </span>
        @endforeach
    </div>

    {{-- Lista --}}
    <x-agro.card>
        @forelse($qualifications as $q)
            @php
                $rc = $resultColors[$q->result] ?? 'zinc';
            @endphp
            <div class="flex items-center gap-4 py-3 border-b border-zinc-100 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-zinc-800">{{ $q->wine_name }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Añada {{ $q->vintage }}
                        @if($q->color)
                            · {{ $colorLabels[$q->color] ?? $q->color }}
                        @endif
                        @if($q->alcohol_percentage)
                            · {{ $q->alcohol_percentage }}% vol.
                        @endif
                    </p>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        {{ $q->qualification_date->format('d/m/Y') }}
                        @if($q->supervisor)
                            · {{ $q->supervisor->name }}
                        @endif
                    </p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $rc }}-100 text-{{ $rc }}-700">
                    {{ $resultLabels[$q->result] ?? $q->result }}
                </span>
            </div>
        @empty
            <x-agro.empty-state
                icon="star"
                title="Sin calificaciones"
                description="Tu denominación de origen aún no ha registrado calificaciones para los vinos de esta bodega."
            />
        @endforelse
    </x-agro.card>

    <div>{{ $qualifications->links() }}</div>

</div>
