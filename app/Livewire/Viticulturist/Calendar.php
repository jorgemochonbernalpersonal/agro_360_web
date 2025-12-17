<?php

namespace App\Livewire\Viticulturist;

use Livewire\Component;

class Calendar extends Component
{
    public function render()
    {
        return view('livewire.viticulturist.calendar')
            ->layout('layouts.app');
    }
}

