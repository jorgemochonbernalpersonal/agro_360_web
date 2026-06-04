<?php

namespace App\Livewire\Winery\Alerts;

use App\Models\BottlingAuthorization;
use App\Models\Container;
use App\Models\EcoCertification;
use App\Models\LabelBatch;
use App\Models\ProductLot;
use App\Models\SanitaryRegistration;
use App\Models\WineAnalysis;
use App\Models\WineryDocument;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $userId = Auth::id();

        // ── SO₂ bajo: último análisis de vinos en proceso con free_so2 < 20 mg/L ──
        $lowSo2Wines = WineAnalysis::where('user_id', $userId)
            ->where('analysis_date', '>=', now()->subDays(60))
            ->whereHas('wine', fn ($q) => $q->where('status', 'in_progress'))
            ->with('wine')
            ->orderBy('analysis_date', 'desc')
            ->get()
            ->filter(fn ($a) => $a->isSo2FreeLow())
            ->unique('wine_id')
            ->values();

        // ── Depósitos casi llenos (>= 85% de capacidad) ──────────────────────────
        $nearlyFullContainers = Container::where('user_id', $userId)
            ->where('archived', false)
            ->whereRaw('capacity > 0')
            ->whereRaw('(used_capacity / capacity) >= 0.85')
            ->with(['containerRoom', 'currentStates' => fn ($q) => $q->latest('last_movement_at')->with('wine')])
            ->orderByRaw('(used_capacity / capacity) DESC')
            ->get();

        // ── Lotes de producto con stock disponible bajo (<= 10% del inicial) ─────
        $lowStockLots = ProductLot::where('user_id', $userId)
            ->where('archived', false)
            ->where('initial_quantity', '>', 0)
            ->whereRaw('(available_quantity / initial_quantity) <= 0.10')
            ->where('available_quantity', '>', 0)
            ->orderBy('available_quantity')
            ->get();

        // ── Lotes de etiquetas casi agotados (<= 10% disponible) ─────────────────
        $lowLabelBatches = LabelBatch::where('user_id', $userId)
            ->withStock()
            ->where('total_quantity', '>', 0)
            ->whereRaw('((total_quantity - used_quantity - wasted_quantity) / total_quantity) <= 0.10')
            ->orderByRaw('(total_quantity - used_quantity - wasted_quantity) ASC')
            ->get();

        return view('livewire.winery.alerts.dashboard', [
            // ── Elaboración ──────────────────────────────────────────────────────
            'lowSo2Wines' => $lowSo2Wines,
            'nearlyFullContainers' => $nearlyFullContainers,
            'lowStockLots' => $lowStockLots,
            'lowLabelBatches' => $lowLabelBatches,
            // ── Insumos ──────────────────────────────────────────────────────────
            'lowStockSupplies' => WinerySupply::where('user_id', $userId)
                ->whereNotNull('min_stock_alert')
                ->whereColumn('current_stock', '<=', 'min_stock_alert')
                ->get(),
            // ── Documentación ────────────────────────────────────────────────────
            'expiringDocuments' => WineryDocument::where('user_id', $userId)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->get(),
            'expiredDocuments' => WineryDocument::where('user_id', $userId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->get(),
            'expiringCerts' => EcoCertification::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->whereBetween('valid_until', [now(), now()->addDays(30)])
                ->get(),
            'expiredCerts' => EcoCertification::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->where('valid_until', '<', now())
                ->get(),
            'expiringSanitary' => SanitaryRegistration::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('renewal_date')
                ->whereBetween('renewal_date', [now(), now()->addDays(30)])
                ->get(),
            'expiringBottlingAuths' => BottlingAuthorization::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->whereBetween('valid_until', [now(), now()->addDays(30)])
                ->get(),
        ])->layout('layouts.app');
    }
}
