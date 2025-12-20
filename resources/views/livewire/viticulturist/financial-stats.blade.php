<div class="space-y-6 animate-fade-in">
    @php
        $icon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    @endphp
    
    <x-page-header
        :icon="$icon"
        title="Estadísticas Financieras"
        description="Análisis completo de tu negocio vitivinícola"
        icon-color="from-indigo-500 to-purple-600"
    />

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Filtros:</span>
            </div>
            <select wire:model.live="selectedYear" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- KPIs Ingresos --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Ingresos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Facturado --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Facturado {{ $selectedYear }}</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($totalInvoiced, 2) }} €</p>
                        @if($growthPercentage != 0)
                            <p class="text-xs {{ $growthPercentage > 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                {{ $growthPercentage > 0 ? '↑' : '↓' }} {{ number_format(abs($growthPercentage), 1) }}% vs {{ $selectedYear - 1 }}
                            </p>
                        @endif
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Pendiente de Cobro --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pendiente Cobro</p>
                        <p class="text-3xl font-bold text-orange-600">{{ number_format($pendingAmount, 2) }} €</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Facturas Vencidas --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Vencido</p>
                        <p class="text-3xl font-bold text-red-600">{{ number_format($overdueAmount, 2) }} €</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $overdueCount }} factura(s)</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Tasa de Cobro --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Tasa de Cobro</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format($collectionRate, 1) }}%</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs Comerciales --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Comercial</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Clientes Activos --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Clientes Activos</p>
                        <p class="text-3xl font-bold text-indigo-600">{{ $activeClients }}</p>
                        <p class="text-xs text-gray-400 mt-1">con compras en {{ $selectedYear }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Factura Media --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Factura Media</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format($averageInvoice, 2) }} €</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $invoiceCount }} facturas</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Enlace a Clientes --}}
            <a href="{{ route('viticulturist.clients.index') }}" wire:navigate class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl shadow-lg border-2 border-teal-200 p-6 hover:shadow-xl hover:border-teal-300 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-teal-600 mb-1">👥 Clientes</p>
                        <p class="text-lg font-bold text-teal-900">Ver estadísticas</p>
                        <p class="text-xs text-teal-500 mt-1">Análisis detallado</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-teal-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Gráfico Principal: Evolución de Ingresos --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-6">Evolución de Ingresos (12 meses)</h3>
        
        @if(count($monthlyIncome) > 0)
            <div class="space-y-3">
                @php
                    $maxIncome = max(array_column($monthlyIncome, 'income'));
                @endphp
                @foreach($monthlyIncome as $month)
                    @php
                        $percentage = $maxIncome > 0 ? ($month['income'] / $maxIncome) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $month['month'] }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ number_format($month['income'], 2) }} €</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-3 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No hay datos disponibles</p>
        @endif
    </div>

    {{-- Fila de Análisis --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top 10 Clientes --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Top 10 Clientes</h3>
                <a href="{{ route('viticulturist.clients.index') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    Ver todos →
                </a>
            </div>
            
            @if($topClients->count() > 0)
                <div class="space-y-3">
                    @foreach($topClients as $index => $client)
                        <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-indigo-600">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $client->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($client->total_invoiced, 2) }} €</p>
                            </div>
                            <div class="flex-shrink-0">
                                @php
                                    $percentage = $totalInvoiced > 0 ? ($client->total_invoiced / $totalInvoiced) * 100 : 0;
                                @endphp
                                <span class="text-xs font-semibold text-indigo-600">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay datos de clientes</p>
            @endif
        </div>

        {{-- Ventas por Variedad --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Ventas por Variedad</h3>
            
            @if($salesByVariety->count() > 0)
                <div class="space-y-3">
                    @php
                        $maxSale = $salesByVariety->max('total');
                    @endphp
                    @foreach($salesByVariety as $variety => $data)
                        @php
                            $percentage = $maxSale > 0 ? ($data['total'] / $maxSale) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $variety }}</span>
                                <span class="text-sm font-bold text-gray-900">{{ number_format($data['total'], 2) }} €</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ number_format($data['weight'], 0) }} kg</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay ventas registradas</p>
            @endif
        </div>
    </div>

    {{-- Fila de Detalle --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Facturas Próximas a Vencer --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Próximas a Vencer</h3>
                <span class="text-xs text-gray-500">Próximos 15 días</span>
            </div>
            
            @if($upcomingInvoices->count() > 0)
                <div class="space-y-3">
                    @foreach($upcomingInvoices as $invoice)
                        @php
                            $daysUntilDue = now()->diffInDays($invoice->due_date, false);
                            $isUrgent = $daysUntilDue <= 3;
                        @endphp
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $isUrgent ? 'bg-red-50 border border-red-200' : 'hover:bg-gray-50' }} transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                                <p class="text-xs text-gray-500">{{ $invoice->client->full_name ?? 'Sin cliente' }}</p>
                                <p class="text-sm font-bold text-gray-900 mt-1">{{ number_format($invoice->total_amount, 2) }} €</p>
                                <p class="text-xs {{ $isUrgent ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    Vence: {{ $invoice->due_date->format('d/m/Y') }} ({{ abs($daysUntilDue) }} día{{ abs($daysUntilDue) != 1 ? 's' : '' }})
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay facturas próximas a vencer</p>
            @endif
        </div>

        {{-- Stock por Variedad --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Estado de Stock por Variedad</h3>
            
            @if($stockByVariety->count() > 0)
                <div class="space-y-4">
                    @foreach($stockByVariety as $variety => $stock)
                        <div class="border-l-4 border-purple-500 pl-4">
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $variety }}</p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <span class="text-gray-500">Disponible:</span>
                                    <p class="font-bold text-green-600">{{ number_format($stock['available'], 0) }} kg</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Reservado:</span>
                                    <p class="font-bold text-orange-600">{{ number_format($stock['reserved'], 0) }} kg</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Vendido:</span>
                                    <p class="font-bold text-blue-600">{{ number_format($stock['sold'], 0) }} kg</p>
                                </div>
                            </div>
                            <div class="mt-2 flex gap-1 h-2 rounded-full overflow-hidden bg-gray-200">
                                @php
                                    $total = $stock['total'];
                                    $availablePercent = $total > 0 ? ($stock['available'] / $total) * 100 : 0;
                                    $reservedPercent = $total > 0 ? ($stock['reserved'] / $total) * 100 : 0;
                                    $soldPercent = $total > 0 ? ($stock['sold'] / $total) * 100 : 0;
                                @endphp
                                <div class="bg-green-500" style="width: {{ $availablePercent }}%"></div>
                                <div class="bg-orange-500" style="width: {{ $reservedPercent }}%"></div>
                                <div class="bg-blue-500" style="width: {{ $soldPercent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay datos de stock disponibles</p>
            @endif
        </div>
    </div>
</div>
