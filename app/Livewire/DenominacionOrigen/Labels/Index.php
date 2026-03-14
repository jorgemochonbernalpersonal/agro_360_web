<?php

namespace App\Livewire\DenominacionOrigen\Labels;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Contraetiquetas',
            'icon'        => 'tag',
            'description' => 'Gestión de solicitudes de contraetiquetas, emisión y numeración, stock, trazabilidad de etiquetas y embotellado autorizado.',
            'items'       => ['Solicitudes', 'Emisión y numeración', 'Stock de contraetiquetas', 'Trazabilidad de etiquetas', 'Embotellado autorizado'],
        ])->layout('layouts.app');
    }
}
