<div class="space-y-6 animate-fade-in">

    {{-- Header --}}
    <x-agro.page-header
        title="Aplicadores Fitosanitarios"
        description="Registro oficial de aplicadores con número ROPO (obligatorio PAC)"
        icon="user-group"
    >
        <x-slot:actions>
            <flux:button href="{{ route('viticulturist.field-applicators.create') }}" variant="primary" icon="plus">
                Añadir Aplicador
            </flux:button>
        </x-slot:actions>
    </x-agro.page-header>

    <flux:callout variant="info" icon="information-circle">
        Los aplicadores de productos fitosanitarios deben estar en posesión del <strong>carné ROPO</strong> (RD 1702/2011). Es obligatorio registrarlos en el cuaderno de campo para cumplimiento PAC.
    </flux:callout>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-agro.stat-card
            label="Total aplicadores"
            :value="$stats['total']"
            icon="user-group"
            color="zinc"
        />
        <x-agro.stat-card
            label="ROPO vencido"
            :value="$stats['expired']"
            icon="x-circle"
            color="amber"
        />
        <x-agro.stat-card
            label="ROPO por vencer"
            :value="$stats['expiring']"
            icon="clock"
            color="amber"
        />
        <x-agro.stat-card
            label="Asesores"
            :value="$stats['advisors']"
            icon="academic-cap"
            color="agro"
        />
    </div>

    {{-- Grid de cards --}}
    @if($applicators->isEmpty())
        <x-agro.empty-state
            icon="user-group"
            title="Sin aplicadores registrados"
            description="Añade los aplicadores que realizan tratamientos fitosanitarios en tu explotación."
        >
            <x-slot:action>
                <flux:button href="{{ route('viticulturist.field-applicators.create') }}" variant="primary" icon="plus">
                    Añadir Aplicador
                </flux:button>
            </x-slot:action>
        </x-agro.empty-state>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($applicators as $applicator)
                @php
                    $delay = min($loop->index * 50, 300);
                    if ($applicator->ropo_expiry_date && $applicator->isRopoExpired()) {
                        $ropoBg = 'bg-red-50'; $ropoText = 'text-red-700';
                    } elseif ($applicator->ropo_expiry_date && $applicator->isRopoExpiringSoon()) {
                        $ropoBg = 'bg-amber-50'; $ropoText = 'text-amber-700';
                    } else {
                        $ropoBg = 'bg-zinc-50'; $ropoText = 'text-zinc-700';
                    }
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="applicator-{{ $applicator->id }}"
                >
                    <x-slot:header>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100">
                                <flux:icon icon="user" class="size-5 text-zinc-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-zinc-900 truncate">{{ $applicator->name }}</h3>
                                @if($applicator->email)
                                    <p class="text-xs text-zinc-400 truncate">{{ $applicator->email }}</p>
                                @endif
                            </div>
                            @if($applicator->is_advisor)
                                <flux:badge color="green" size="sm" class="shrink-0">Asesor</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        <flux:badge color="blue" size="sm">{{ $applicator->category_label }}</flux:badge>

                        <div class="{{ $ropoBg }} rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-0.5">Nº ROPO</p>
                            <p class="text-base font-bold {{ $ropoText }} font-mono leading-none">{{ $applicator->ropo_number }}</p>
                        </div>

                        @if($applicator->ropo_expiry_date)
                            <div class="flex items-center gap-2 text-xs {{ $applicator->isRopoExpired() ? 'text-red-600 font-medium' : ($applicator->isRopoExpiringSoon() ? 'text-amber-600 font-medium' : 'text-zinc-500') }}">
                                <flux:icon icon="clock" class="size-3.5 shrink-0" />
                                <span>Caducidad: {{ $applicator->ropo_expiry_date->format('d/m/Y') }}</span>
                                @if($applicator->isRopoExpired())
                                    <flux:badge color="red" size="sm">Vencido</flux:badge>
                                @elseif($applicator->isRopoExpiringSoon())
                                    <flux:badge color="amber" size="sm">Próximo</flux:badge>
                                @endif
                            </div>
                        @endif
                    </div>

                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('viticulturist.field-applicators.edit', $applicator) }}"
                               title="Editar"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                                <flux:icon icon="pencil-square" class="size-4" />
                            </a>
                            <button
                                wire:click="deactivate({{ $applicator->id }})"
                                wire:confirm="¿Dar de baja este aplicador?"
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
