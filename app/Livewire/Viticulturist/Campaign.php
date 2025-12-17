<?php

namespace App\Livewire\Viticulturist;

use Livewire\Component;

class Campaign extends Component
{
    public function render()
    {
        return view('livewire.viticulturist.campaign')
            ->layout('layouts.app');
    }
}

