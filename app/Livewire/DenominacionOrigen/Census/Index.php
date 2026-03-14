<?php

namespace App\Livewire\DenominacionOrigen\Census;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Censo',
            'icon'        => 'users',
            'description' => 'Gestión del censo de bodegas, viticultores y productores adscritos a la denominación. Altas, bajas y modificaciones.',
            'items'       => ['Bodegas registradas', 'Viticultores registrados', 'Productores registrados', 'Altas / bajas / modificaciones', 'Variedades admitidas', 'Mapa territorial DO'],
        ])->layout('layouts.app');
    }
}
