<?php

namespace App\Livewire\Winery\Financial;

use Livewire\Component;

class Stats extends Component
{
    public function render()
    {
        return view('livewire.winery.financial.stats')->layout('layouts.app');
    }
}
