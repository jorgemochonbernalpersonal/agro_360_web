@php
    use App\Models\WineryViticulturist;
    use App\Models\Harvest;
    use App\Models\WineLot;

    $user     = auth()->user();
    $wineryId = $user->id;

    $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
        ->pluck('viticulturist_id');

    // KPIs
    $totalViticulturists = $viticulturistIds->count();

    $totalReceptions = Harvest::whereHas('activity', fn($q) => $q->whereIn('viticulturist_id', $viticulturistIds))
        ->whereYear('harvest_start_date', date('Y'))
        ->count();

    $totalKg = Harvest::whereHas('activity', fn($q) => $q->whereIn('viticulturist_id', $viticulturistIds))
        ->whereYear('harvest_start_date', date('Y'))
        ->sum('total_weight') ?? 0;

    $totalLots = WineLot::where('user_id', $wineryId)->count();

    // Viticultores recientes
    $recentViticulturists = WineryViticulturist::where('winery_id', $wineryId)
        ->with('viticulturist')
        ->latest()
        ->take(5)
        ->get();

    // Recepciones recientes
    $recentReceptions = Harvest::whereHas('activity', fn($q) => $q->whereIn('viticulturist_id', $viticulturistIds))
        ->with(['activity.viticulturist', 'plotPlanting.grapeVariety'])
        ->orderBy('harvest_start_date', 'desc')
        ->take(5)
        ->get();

    $dashboardIcon = '<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>';
@endphp

<x-app-layout>
    <div class="space-y-6 animate-fade-in">

        {{-- Header --}}
        <x-page-header
            :icon="$dashboardIcon"
            title="Dashboard Bodega"
            description="Resumen de tu bodega"
            icon-color="from-[var(--color-agro-green)] to-[var(--color-agro-green-dark)]"
        />

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Viticultores</p>
                        <p class="text-3xl font-bold text-[var(--color-agro-green-dark)]">{{ $totalViticulturists }}</p>
                        <p class="text-xs text-gray-400">vinculados</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Recepciones</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $totalReceptions }}</p>
                        <p class="text-xs text-gray-400">campaña {{ date('Y') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Uva recibida</p>
                        <p class="text-3xl font-bold text-amber-600">{{ number_format($totalKg / 1000, 1) }}</p>
                        <p class="text-xs text-gray-400">toneladas {{ date('Y') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
            </div>

            <a href="{{ route('winery.wine-lots.index') }}" wire:navigate
               class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg border-2 border-green-200 p-5 hover:shadow-xl hover:border-green-300 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700">🍷 Lotes de vino</p>
                        <p class="text-lg font-bold text-green-800">{{ $totalLots }} lotes</p>
                        <p class="text-xs text-green-600">en bodega</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-200 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Viticultores recientes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">👨‍🌾 Viticultores Vinculados</h3>
                    <a href="{{ route('winery.viticulturists.index') }}" wire:navigate
                       class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:underline">
                        Ver todos →
                    </a>
                </div>

                @if($recentViticulturists->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentViticulturists as $wv)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-sm font-bold text-green-700">
                                    {{ strtoupper(substr($wv->viticulturist->name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $wv->viticulturist->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $wv->viticulturist->email ?? '' }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $wv->created_at->format('d/m') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6">No hay viticultores vinculados</p>
                @endif
            </div>

            {{-- Recepciones recientes --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">📦 Recepciones Recientes</h3>
                    <a href="{{ route('winery.grape-reception.index') }}" wire:navigate
                       class="text-sm font-medium text-[var(--color-agro-green-dark)] hover:underline">
                        Ver todas →
                    </a>
                </div>

                @if($recentReceptions->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentReceptions as $reception)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-sm">🍇</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $reception->activity->viticulturist->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $reception->plotPlanting->grapeVariety->name ?? 'Sin variedad' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-purple-600">{{ number_format($reception->total_weight, 0) }} kg</p>
                                    <p class="text-xs text-gray-400">{{ $reception->harvest_start_date->format('d/m') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6">No hay recepciones este año</p>
                @endif
            </div>

        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('winery.grape-reception.index') }}" wire:navigate
               class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-purple-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-xl">📦</div>
                <div>
                    <p class="font-semibold text-gray-900">Recepción Uva</p>
                    <p class="text-xs text-gray-500">Registrar vendimia</p>
                </div>
            </a>

            <a href="{{ route('winery.wine-lots.index') }}" wire:navigate
               class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-green-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-xl">🍷</div>
                <div>
                    <p class="font-semibold text-gray-900">Lotes de Vino</p>
                    <p class="text-xs text-gray-500">Control de lotes</p>
                </div>
            </a>

            <a href="{{ route('winery.clients.index') }}" wire:navigate
               class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-amber-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-xl">🏪</div>
                <div>
                    <p class="font-semibold text-gray-900">Clientes</p>
                    <p class="text-xs text-gray-500">Cartera de clientes</p>
                </div>
            </a>

            <a href="{{ route('winery.invoices.grape-purchase.index') }}" wire:navigate
               class="bg-white rounded-xl shadow border border-gray-200 p-4 hover:shadow-lg hover:border-indigo-300 transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-xl">📊</div>
                <div>
                    <p class="font-semibold text-gray-900">Facturación</p>
                    <p class="text-xs text-gray-500">Liquidaciones y ventas</p>
                </div>
            </a>
        </div>

    </div>
</x-app-layout>
