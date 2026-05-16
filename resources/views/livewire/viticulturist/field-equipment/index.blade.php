<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Equipos de Aplicación"
        description="Registro de maquinaria y equipos de aplicación (ITB pulverizadores)"
        icon="wrench-screwdriver"
    >
        <x-slot:actions>
            <flux:button href="{{ roleRoute('viticulturist.field-equipment.create') }}" variant="primary" icon="plus">
                Añadir Equipo
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        Los pulverizadores deben pasar la <strong>Inspección Técnica de Equipos de Aplicación (ITEA)</strong> cada 3 años (RD 1702/2011). Registra la fecha para recibir alertas de vencimiento.
    </flux:callout>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total equipos"
            :value="$stats['total']"
            icon="wrench-screwdriver"
            color="zinc"
        />
        <x-agro.stat-card
            label="Inspección vencida"
            :value="$stats['overdue']"
            icon="x-circle"
            color="amber"
        />
        <x-agro.stat-card
            label="Próxima inspección"
            :value="$stats['due']"
            icon="clock"
            color="agro"
        />
        <x-agro.stat-card
            label="Sin fecha ITEA"
            :value="$stats['no_date']"
            icon="question-mark-circle"
            color="zinc"
        />
    </div>

    {{-- Grid de cards --}}
    @if($equipment->isEmpty())
        <x-agro.empty-state
            icon="wrench-screwdriver"
            title="Sin equipos registrados"
            description="Registra los equipos de aplicación que utilizas en tu explotación."
        >
            <x-slot:action>
                <flux:button href="{{ roleRoute('viticulturist.field-equipment.create') }}" variant="primary" icon="plus">
                    Añadir Equipo
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($equipment as $item)
                @php
                    $delay = min($loop->index * 50, 300);
                    if ($item->next_inspection_date && $item->isInspectionOverdue()) {
                        $inspBg = 'bg-red-50'; $inspText = 'text-red-700'; $inspBadge = 'red';
                    } elseif ($item->next_inspection_date && $item->isInspectionDue()) {
                        $inspBg = 'bg-amber-50'; $inspText = 'text-amber-700'; $inspBadge = 'amber';
                    } else {
                        $inspBg = 'bg-agro-50'; $inspText = 'text-agro-700'; $inspBadge = 'green';
                    }
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="equipment-{{ $item->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="wrench-screwdriver"
                            :title="$item->name"
                            :subtitle="$item->registration_number ?? null"
                            size="md"
                            radius="xl"
                        >
                            <flux:badge color="blue" size="sm">{{ $item->type_label }}</flux:badge>
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        @if($item->last_inspection_date)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Última ITEA</span>
                                <span class="text-zinc-700">{{ $item->last_inspection_date->format('d/m/Y') }}</span>
                            </div>
                        @endif

                        @if($item->next_inspection_date)
                            <div class="{{ $inspBg }} rounded-xl p-3">
                                <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Próxima ITEA</p>
                                <p class="text-base font-bold {{ $inspText }} leading-none">
                                    {{ $item->next_inspection_date->format('d/m/Y') }}
                                    @if($item->isInspectionOverdue())
                                        <span class="text-xs font-normal ml-1">VENCIDA</span>
                                    @elseif($item->isInspectionDue())
                                        <span class="text-xs font-normal ml-1">Próxima</span>
                                    @endif
                                </p>
                            </div>
                        @else
                            <div class="bg-zinc-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-zinc-400">Sin fecha de inspección registrada</p>
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ roleRoute('viticulturist.field-equipment.edit', $item) }}"
                               title="Editar"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="deactivate({{ $item->id }})"
                                wire:confirm="¿Dar de baja este equipo?"
                                title="Dar de baja"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                <flux:icon icon="archive-box" class="size-4" />
                            </button>
                        </div>
                    </x-slot:footer>
                </x-agro.card>
            @endforeach
        </div>
    @endif

</div>
