<?php

namespace App\Livewire\Winery\Alerts;

use App\Models\BottlingAuthorization;
use App\Models\EcoCertification;
use App\Models\SanitaryRegistration;
use App\Models\WineryDocument;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $userId = Auth::id();

        return view('livewire.winery.alerts.dashboard', [
            'lowStockSupplies'      => WinerySupply::where('user_id', $userId)
                ->whereNotNull('min_stock_alert')
                ->whereColumn('current_stock', '<=', 'min_stock_alert')
                ->get(),
            'expiringDocuments'     => WineryDocument::where('user_id', $userId)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->get(),
            'expiredDocuments'      => WineryDocument::where('user_id', $userId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->get(),
            'expiringCerts'         => EcoCertification::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->whereBetween('valid_until', [now(), now()->addDays(30)])
                ->get(),
            'expiredCerts'          => EcoCertification::where('user_id', $userId)
                ->where('status', 'active')
                ->whereNotNull('valid_until')
                ->where('valid_until', '<', now())
                ->get(),
            'expiringSanitary'      => SanitaryRegistration::where('user_id', $userId)
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
