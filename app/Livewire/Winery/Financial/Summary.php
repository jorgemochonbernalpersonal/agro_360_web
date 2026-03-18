<?php

namespace App\Livewire\Winery\Financial;

use Livewire\Component;

class Summary extends Component
{
    public function render()
    {
        return view('livewire.winery.financial.summary')->layout('layouts.app');
    }
}
