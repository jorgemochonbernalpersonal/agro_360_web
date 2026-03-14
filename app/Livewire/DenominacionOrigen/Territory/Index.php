<?php

namespace App\Livewire\DenominacionOrigen\Territory;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Territorio',
            'icon'        => 'map',
            'description' => 'SIGPAC consolidado, teledetección regional, zonificación DO, mapas de variedades y evolución de superficie.',
            'items'       => ['SIGPAC consolidado', 'Teledetección regional', 'Zonificación DO', 'Mapas de variedades', 'Evolución de superficie'],
        ])->layout('layouts.app');
    }
}
