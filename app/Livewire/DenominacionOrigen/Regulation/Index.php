<?php

namespace App\Livewire\DenominacionOrigen\Regulation;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Normativa DO',
            'icon'        => 'document-text',
            'description' => 'Pliego de condiciones, reglamento interno, autorizaciones de plantación y elaboración, certificaciones ecológicas, INFOVI y comunicaciones MAPAMA.',
            'items'       => ['Pliego de condiciones', 'Reglamento interno', 'Autorizaciones de plantación', 'Autorizaciones de elaboración', 'Certificaciones ecológicas DO', 'INFOVI', 'Comunicaciones MAPAMA'],
        ])->layout('layouts.app');
    }
}
