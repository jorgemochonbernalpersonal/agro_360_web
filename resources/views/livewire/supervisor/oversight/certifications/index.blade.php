<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="Certificaciones DO"
        description="Certificaciones activas de los viticultores adscritos a la denominación."
    />

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-agro.stat-card label="Vigentes" :value="$totalActive" icon="check-badge" color="emerald" />
        <x-agro.stat-card label="Próximas a vencer (60d)" :value="$totalExpiring" icon="clock" color="amber" />
        <x-agro.stat-card label="Caducadas" :value="$totalExpired" icon="x-circle" color="red" />
    </div>

    {{-- Alerta si hay caducadas o próximas a vencer --}}
    @if($totalExpired > 0 || $totalExpiring > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-3">
            <flux:icon icon="exclamation-triangle" class="size-5 text-amber-500 mt-0.5 shrink-0" />
            <div class="text-sm text-amber-700">
                @if($totalExpired > 0)
                    <strong>{{ $totalExpired }} certificación{{ $totalExpired !== 1 ? 'es' : '' }} caducada{{ $totalExpired !== 1 ? 's' : '' }}.</strong>
                @endif
                @if($totalExpiring > 0)
                    {{ $totalExpiring }} vence{{ $totalExpiring !== 1 ? 'n' : '' }} en los próximos 60 días.
                @endif
                Revisa el estado con los viticultores afectados.
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <x-agro.filter-select wire:model.live="filterVit" label="Viticultor">
            <option value="">Todos</option>
            @foreach($viticulturists as $vit)
                <option value="{{ $vit->id }}">{{ $vit->name }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterType" label="Tipo">
            <option value="">Todos los tipos</option>
            @foreach($certTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-agro.filter-select>

        <x-agro.filter-select wire:model.live="filterStatus" label="Estado">
            <option value="">Cualquier estado</option>
            <option value="active">Vigentes</option>
            <option value="expiring">Próximas a vencer</option>
            <option value="expired">Caducadas</option>
        </x-agro.filter-select>

        <button wire:click="clearFilters" class="text-xs text-zinc-400 hover:text-zinc-600 transition px-2 py-1.5">
            Limpiar
        </button>
    </x-agro.filter-bar>

    {{-- Card Grid --}}
    @if($certifications->count() > 0)
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
            wire:loading.class="opacity-60 pointer-events-none"
            wire:target="filterVit, filterType, filterStatus, clearFilters"
        >
            @foreach($certifications as $cert)
                @php
                    $delay = min($loop->index * 50, 300);
                    $certColorMap = [
                        'ecologico' => ['iconBg' => 'bg-green-100', 'iconText' => 'text-green-600'],
                        'produccion_integrada' => ['iconBg' => 'bg-teal-100', 'iconText' => 'text-teal-600'],
                        'globalgap' => ['iconBg' => 'bg-blue-100', 'iconText' => 'text-blue-600'],
                        'denominacion_origen' => ['iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
                        'indicacion_geografica' => ['iconBg' => 'bg-violet-100', 'iconText' => 'text-violet-600'],
                    ];
                    $cStyle = $certColorMap[$cert->certification_type] ?? ['iconBg' => 'bg-zinc-100', 'iconText' => 'text-zinc-600'];
                @endphp
                <x-agro.card
                    class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                    style="animation-delay: {{ $delay }}ms;"
                    wire:key="cert-{{ $cert->id }}"
                >
                    <x-slot:header>
                        <x-agro.card-item-header
                            icon="check-badge"
                            :title="$cert->certification_type_label"
                            :subtitle="$cert->viticulturist?->name ?? '—'"
                            :iconBg="$cStyle['iconBg']"
                            :iconColor="$cStyle['iconText']"
                            size="md"
                            radius="xl"
                        >
                            @if($cert->is_expired)
                                <flux:badge color="red" size="sm">Caducada</flux:badge>
                            @elseif($cert->is_expiring_soon)
                                <flux:badge color="yellow" size="sm">Pronto vence</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">Vigente</flux:badge>
                            @endif
                        </x-agro.card-item-header>
                    </x-slot:header>

                    <div class="flex-1 space-y-3">
                        {{-- Metric boxes --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-zinc-50 rounded-lg p-2 text-center">
                                <p class="text-[9px] text-zinc-400 uppercase tracking-wide mb-0.5">Emisión</p>
                                <p class="text-sm font-bold text-zinc-700">
                                    {{ $cert->issue_date?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                            <div class="rounded-lg p-2 text-center {{ $cert->is_expired ? 'bg-red-50' : ($cert->is_expiring_soon ? 'bg-amber-50' : 'bg-emerald-50') }}">
                                <p class="text-[9px] uppercase tracking-wide mb-0.5 {{ $cert->is_expired ? 'text-red-400' : ($cert->is_expiring_soon ? 'text-amber-400' : 'text-emerald-400') }}">Vencimiento</p>
                                <p class="text-sm font-bold {{ $cert->is_expired ? 'text-red-700' : ($cert->is_expiring_soon ? 'text-amber-700' : 'text-emerald-700') }}">
                                    {{ $cert->expiry_date?->format('d/m/Y') ?? 'Sin caducidad' }}
                                </p>
                            </div>
                        </div>

                        {{-- Key-value rows --}}
                        @if($cert->certifying_body)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">Organismo</span>
                                <span class="text-zinc-700 truncate ml-2">{{ $cert->certifying_body }}</span>
                            </div>
                        @endif
                        @if($cert->certificate_number)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-400">N.º certificado</span>
                                <span class="text-zinc-600 font-mono text-xs">{{ $cert->certificate_number }}</span>
                            </div>
                        @endif
                    </div>
                </x-agro.card>
            @endforeach
        </div>

        <x-agro-pagination :paginator="$certifications" />
    @else
        <x-agro.empty-state
            icon="check-badge"
            title="Sin certificaciones"
            description="No se encontraron certificaciones con los filtros seleccionados."
        />
    @endif

</div>
