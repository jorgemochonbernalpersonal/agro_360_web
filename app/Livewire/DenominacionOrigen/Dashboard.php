<?php

namespace App\Livewire\DenominacionOrigen;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.dashboard')
            ->layout('layouts.app');
    }
}
