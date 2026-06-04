<?php

namespace App\Livewire\Beta;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Expired extends Component
{
    public function render()
    {
        $user = Auth::user();

        return view('livewire.beta.expired', [
            'monthlyPrice' => $user->viticulturistMonthlyPrice(),
            'yearlyPrice' => $user->viticulturistYearlyPrice(),
            'isWineryLinked' => $user->hasWinery(),
            'isProducer' => $user->isProducer(),
        ])->layout('layouts.app');
    }
}
