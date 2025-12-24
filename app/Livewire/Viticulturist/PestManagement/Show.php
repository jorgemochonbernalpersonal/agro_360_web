<?php

namespace App\Livewire\Viticulturist\PestManagement;

use App\Models\Pest;
use Livewire\Component;

class Show extends Component
{
    public Pest $pest;

    public function mount(Pest $pest)
    {
        $this->pest = $pest->load(['products', 'observations', 'treatments']);
    }

    public function render()
    {
        return view('livewire.viticulturist.pest-management.show')
            ->layout('layouts.app');
    }
}
