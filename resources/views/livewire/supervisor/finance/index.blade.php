<div class="space-y-6 animate-fade-in">

    <x-agro.page-header
        title="{{ __('Negocio DO') }}"
        :description="__('Suscripciones de viticultores y actividad económica de bodegas adscritas.')"
    />

    {{-- Stats --}}
    <x-agro.stats-section key="supervisor-finance" columns="3">
        <x-agro.stat-card :label="__('Suscripciones activas')" :value="$activeSubscriptions" icon="credit-card" color="agro" />
        <x-agro.stat-card :label="__('Ingresos mensuales (€)')" :value="number_format($monthlyRevenue, 2, ',', '.')" icon="banknotes" color="blue" :description="__('Planes mensuales activos')" />
        <x-agro.stat-card :label="__('Ingresos anuales (€)')" :value="number_format($yearlyRevenue, 2, ',', '.')" icon="banknotes" color="yellow" :description="__('Planes anuales activos')" />
    </x-agro.stats-section>

    {{-- Tabs --}}
    <div>
        <x-agro.tabs :tabs="$tabs" :active="$activeTab" wireMethod="setTab" />

        @if($activeTab === 'subscriptions')
            @if($subscriptions->count() > 0)
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="setTab"
                >
                    @foreach($subscriptions as $sub)
                        @php
                            $delay = min($loop->index * 50, 300);
                            $subStatusColors = [
                                'active'    => ['badge' => 'green',  'iconBg' => 'bg-emerald-100', 'iconText' => 'text-emerald-600'],
                                'cancelled' => ['badge' => 'yellow', 'iconBg' => 'bg-yellow-100',  'iconText' => 'text-yellow-600'],
                                'expired'   => ['badge' => 'red',    'iconBg' => 'bg-red-100',     'iconText' => 'text-red-600'],
                            ];
                            $sColor = $subStatusColors[$sub->status] ?? ['badge' => 'zinc', 'iconBg' => 'bg-zinc-100', 'iconText' => 'text-zinc-600'];
                            $subStatusLabels = ['active' => 'Activa', 'cancelled' => 'Cancelada', 'expired' => 'Expirada'];
                        @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="sub-{{ $sub->id }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="credit-card"
                                    :title="$sub->user?->name ?? '—'"
                                    :subtitle="$sub->user?->email ?? '—'"
                                    :iconBg="$sColor['iconBg']"
                                    :iconColor="$sColor['iconText']"
                                    size="md"
                                    radius="xl"
                                >
                                    <flux:badge :color="$sColor['badge']" size="sm">
                                        {{ $subStatusLabels[$sub->status] ?? $sub->status }}
                                    </flux:badge>
                                </x-agro.card-item-header>
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-blue-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-blue-400 uppercase tracking-wide mb-0.5">{{ __('Plan') }}</p>
                                        <p class="text-sm font-bold text-blue-700 capitalize">{{ $sub->plan_type }}</p>
                                    </div>
                                    <div class="bg-agro-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-agro-400 uppercase tracking-wide mb-0.5">{{ __('Importe') }}</p>
                                        <p class="text-sm font-bold text-agro-700">
                                            {{ number_format($sub->amount, 2, ',', '.') }}
                                            <span class="text-[9px] font-normal text-agro-400">{{ __('EUR') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-400">{{ __('Válida hasta') }}</span>
                                    <span class="text-zinc-700 font-medium">{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>

                <x-agro.pagination :paginator="$subscriptions" />
            @else
                <x-agro.empty-state
                    icon="credit-card"
                    title="{{ __('Sin suscripciones') }}"
                    :description="__('No hay suscripciones de viticultores DO.')"
                />
            @endif

        @else
            {{-- Harvest value by winery --}}
            @if(count($harvestValueByWinery) > 0)
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="setTab"
                >
                    @foreach($harvestValueByWinery as $row)
                        @php $delay = min($loop->index * 50, 300); @endphp
                        <x-agro.card
                            class="animate-fade-in-up flex flex-col hover:-translate-y-1"
                            style="animation-delay: {{ $delay }}ms;"
                            wire:key="winery-val-{{ $loop->index }}"
                        >
                            <x-slot:header>
                                <x-agro.card-item-header
                                    icon="building-office-2"
                                    :title="$row->winery_name"
                                    :subtitle="'Campaña ' . $currentYear"
                                    iconBg="bg-blue-100"
                                    iconColor="text-blue-600"
                                    size="md"
                                    radius="xl"
                                />
                            </x-slot:header>

                            <div class="flex-1 space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-amber-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-amber-400 uppercase tracking-wide mb-0.5">{{ __('Uva (kg)') }}</p>
                                        <p class="text-sm font-bold text-amber-700">
                                            {{ number_format($row->total_kg, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="bg-agro-50 rounded-lg p-2 text-center">
                                        <p class="text-[9px] text-agro-400 uppercase tracking-wide mb-0.5">{{ __('Valor') }}</p>
                                        <p class="text-sm font-bold text-agro-700">
                                            @if($row->total_value > 0)
                                                {{ number_format($row->total_value, 2, ',', '.') }}
                                                <span class="text-[9px] font-normal text-agro-400">{{ __('EUR') }}</span>
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </x-agro.card>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state
                    icon="building-office-2"
                    title="{{ __('Sin datos') }}"
                    :description="__('No hay datos de recepciones para el año actual.')"
                />
            @endif
        @endif
    </div>

</div>
