<?php

namespace App\Livewire\DenominacionOrigen\Oversight\Growers;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Supervisión — Viticultores',
            'icon'        => 'eye',
            'description' => 'Supervisión de todos los viticultores (propios DO y de bodega): parcelas, cuaderno de campo, registros, recursos y cosecha comercializada.',
            'items'       => ['Panel de viticultores', 'Parcelas y plantaciones', 'Cuaderno de campo', 'Registros y cumplimiento', 'Recursos', 'Cosecha comercializada'],
        ])->layout('layouts.app');
    }
}
