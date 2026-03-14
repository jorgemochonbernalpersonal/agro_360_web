<?php

namespace App\Livewire\DenominacionOrigen\Growers;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.denominacion-origen.stub', [
            'title'       => 'Viticultores DO',
            'icon'        => 'leaf',
            'description' => 'Viticultores creados y gestionados directamente por la DO. Parcelas, plantaciones, SIGPAC y asignación a bodegas adscritas.',
            'items'       => ['Crear / editar viticultores', 'Parcelas', 'Plantaciones', 'SIGPAC', 'Gestión territorial', 'Teledetección', 'Asignación a bodegas', 'Cuaderno de campo', 'Tratamientos y labores', 'Cumplimiento cuaderno'],
        ])->layout('layouts.app');
    }
}
