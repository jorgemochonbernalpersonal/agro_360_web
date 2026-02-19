<div class="space-y-6 animate-fade-in">
    <x-agro.page-header
        title="Estadísticas Financieras"
        description="Análisis completo de tu negocio vitivinícola"
    />

    {{-- Filtros --}}
    <x-agro.filter-bar>
        <flux:select wire:model.live="selectedYear">
            @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                <option value="{{ $year }}">{{ $year }}</option>
            @endfor
        </flux:select>
    </x-agro.filter-bar>

    {{-- KPIs Ingresos --}}
    <div>
        <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Ingresos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-agro.stat-card
                :label="'Facturado ' . $selectedYear"
                :value="number_format($totalInvoiced, 2) . ' €'"
                :description="$growthPercentage != 0 ? 'vs ' . ($selectedYear - 1) : null"
                icon="currency-euro"
                color="agro"
                :trend="$growthPercentage != 0 ? $growthPercentage : null"
            />
            <x-agro.stat-card
                label="Pendiente Cobro"
                :value="number_format($pendingAmount, 2) . ' €'"
                icon="clock"
                color="orange"
            />
            <x-agro.stat-card
                label="Vencido"
                :value="number_format($overdueAmount, 2) . ' €'"
                :description="$overdueCount . ' factura(s)'"
                icon="exclamation-triangle"
                color="red"
            />
            <x-agro.stat-card
                label="Tasa de Cobro"
                :value="number_format($collectionRate, 1) . '%'"
                icon="check-circle"
                color="blue"
            />
        </div>
    </div>

    {{-- KPIs Comerciales --}}
    <div>
        <h3 class="text-sm font-semibold text-zinc-500 uppercase tracking-wider mb-3">Comercial</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-agro.stat-card
                label="Clientes Activos"
                :value="$activeClients"
                :description="'con compras en ' . $selectedYear"
                icon="users"
                color="purple"
            />
            <x-agro.stat-card
                label="Factura Media"
                :value="number_format($averageInvoice, 2) . ' €'"
                :description="$invoiceCount . ' facturas'"
                icon="document-text"
                color="purple"
            />
            <a href="{{ route('viticulturist.clients.index') }}" wire:navigate>
                <x-agro.card class="h-full hover:border-agro-300 hover:shadow-sm transition-all cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:subheading>Clientes</flux:subheading>
                            <p class="mt-1 text-lg font-bold text-zinc-900">Ver estadísticas</p>
                            <p class="text-xs text-zinc-500 mt-1">Análisis detallado</p>
                        </div>
                        <flux:icon icon="chevron-right" class="size-6 text-zinc-400" />
                    </div>
                </x-agro.card>
            </a>
        </div>
    </div>

    {{-- Evolución de Ingresos --}}
    <x-agro.card>
        <x-slot:header>
            <h3 class="text-base font-bold text-zinc-900">Evolución de Ingresos (12 meses)</h3>
        </x-slot:header>

        @if(count($monthlyIncome) > 0)
            @php $maxIncome = max(array_column($monthlyIncome, 'income')); @endphp
            <div class="space-y-3">
                @foreach($monthlyIncome as $month)
                    @php $percentage = $maxIncome > 0 ? ($month['income'] / $maxIncome) * 100 : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-zinc-700">{{ $month['month'] }}</span>
                            <span class="text-sm font-bold text-zinc-900">{{ number_format($month['income'], 2) }} €</span>
                        </div>
                        <div class="w-full bg-zinc-100 rounded-full h-3">
                            <div class="bg-agro-500 h-3 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-agro.empty-state message="No hay datos disponibles" />
        @endif
    </x-agro.card>

    {{-- Análisis: Top Clientes + Ventas por Variedad --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top 10 Clientes --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-zinc-900">Top 10 Clientes</h3>
                    <a href="{{ route('viticulturist.clients.index') }}" wire:navigate class="text-sm font-medium text-agro-700 hover:text-agro-900">
                        Ver todos →
                    </a>
                </div>
            </x-slot:header>

            @if($topClients->count() > 0)
                <div class="space-y-2">
                    @foreach($topClients as $index => $client)
                        @php $percentage = $totalInvoiced > 0 ? ($client->total_invoiced / $totalInvoiced) * 100 : 0; @endphp
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors">
                            <div class="w-7 h-7 rounded-lg bg-agro-50 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-agro-700">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $client->full_name }}</p>
                                <p class="text-xs text-zinc-500">{{ number_format($client->total_invoiced, 2) }} €</p>
                            </div>
                            <span class="text-xs font-semibold text-agro-700">{{ number_format($percentage, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state message="No hay datos de clientes" />
            @endif
        </x-agro.card>

        {{-- Ventas por Variedad --}}
        <x-agro.card>
            <x-slot:header>
                <h3 class="text-base font-bold text-zinc-900">Ventas por Variedad</h3>
            </x-slot:header>

            @if($salesByVariety->count() > 0)
                @php $maxSale = $salesByVariety->max('total'); @endphp
                <div class="space-y-3">
                    @foreach($salesByVariety as $variety => $data)
                        @php $percentage = $maxSale > 0 ? ($data['total'] / $maxSale) * 100 : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-zinc-700">{{ $variety }}</span>
                                <span class="text-sm font-bold text-zinc-900">{{ number_format($data['total'], 2) }} €</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-zinc-100 rounded-full h-2.5">
                                    <div class="bg-agro-500 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-zinc-500">{{ number_format($data['weight'], 0) }} kg</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state message="No hay ventas registradas" />
            @endif
        </x-agro.card>
    </div>

    {{-- Detalle: Pendientes de Pago + Stock --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Pendientes de Pago --}}
        <x-agro.card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-zinc-900">Pendientes de Pago</h3>
                    <span class="text-xs text-zinc-500">Más antiguas primero</span>
                </div>
            </x-slot:header>

            @if($upcomingInvoices->count() > 0)
                <div class="space-y-2">
                    @foreach($upcomingInvoices as $invoice)
                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-zinc-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0">
                                <flux:icon icon="clock" class="size-5 text-orange-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900">{{ $invoice->invoice_number }}</p>
                                <p class="text-xs text-zinc-500">{{ $invoice->client->full_name ?? 'Sin cliente' }}</p>
                                <p class="text-sm font-bold text-zinc-900 mt-0.5">{{ number_format($invoice->total_amount, 2) }} €</p>
                                <p class="text-xs text-zinc-400">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state message="No hay facturas pendientes de pago" />
            @endif
        </x-agro.card>

        {{-- Stock por Variedad --}}
        <x-agro.card>
            <x-slot:header>
                <h3 class="text-base font-bold text-zinc-900">Estado de Stock por Variedad</h3>
            </x-slot:header>

            @if($stockByVariety->count() > 0)
                <div class="space-y-4">
                    @foreach($stockByVariety as $variety => $stock)
                        @php
                            $total = $stock['total'];
                            $availablePercent = $total > 0 ? ($stock['available'] / $total) * 100 : 0;
                            $reservedPercent  = $total > 0 ? ($stock['reserved']  / $total) * 100 : 0;
                            $soldPercent      = $total > 0 ? ($stock['sold']      / $total) * 100 : 0;
                        @endphp
                        <div class="border-l-4 border-agro-500 pl-4">
                            <p class="text-sm font-semibold text-zinc-900 mb-2">{{ $variety }}</p>
                            <div class="grid grid-cols-3 gap-2 text-xs mb-2">
                                <div>
                                    <span class="text-zinc-500">Disponible</span>
                                    <p class="font-bold text-green-600">{{ number_format($stock['available'], 0) }} kg</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500">Reservado</span>
                                    <p class="font-bold text-orange-600">{{ number_format($stock['reserved'], 0) }} kg</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500">Vendido</span>
                                    <p class="font-bold text-blue-600">{{ number_format($stock['sold'], 0) }} kg</p>
                                </div>
                            </div>
                            <div class="flex gap-0.5 h-2 rounded-full overflow-hidden bg-zinc-100">
                                <div class="bg-green-500"  style="width: {{ $availablePercent }}%"></div>
                                <div class="bg-orange-500" style="width: {{ $reservedPercent }}%"></div>
                                <div class="bg-blue-500"   style="width: {{ $soldPercent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-agro.empty-state message="No hay datos de stock disponibles" />
            @endif
        </x-agro.card>
    </div>
</div>
